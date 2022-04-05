<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2006-2009 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2007-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2011      Juanjo Menent	    <jmenent@2byte.es>
 * Copyright (C) 2013-2018 Philippe Grand      	<philippe.grand@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *       \file       htdocs/core/modules/societe/mod_codeclient_koesiohlf.php
 *       \ingroup    societe
 *       \brief      File of class to manage third party code with Koesio HLF rule
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/societe/modules_societe.class.php';


/**
 *	Class to manage third party code with Koesio HLF rule
 */
class mod_codeclient_koesiohlf extends ModeleThirdPartyCode
{
	/**
	 * @var string Nom du modele
	 * @deprecated
	 * @see name
	 */
	public $nom='Koesio HLF';

	/**
	 * @var string model name
	 */
	public $name='Koesio HLF';

	public $code_modifiable;				// Code modifiable

	public $code_modifiable_invalide;		// Code modifiable si il est invalide

	public $code_modifiable_null;			// Code modifiables si il est null

	public $code_null;						// Code facultatif

	/**
     * Dolibarr version of the loaded document
     * @var string
     */
	public $version = 'dolibarr';    		// 'development', 'experimental', 'dolibarr'

	public $code_auto;                     // Numerotation automatique

	public $searchcode; // String de recherche

	public $numbitcounter; // Nombre de chiffres du compteur

	public $prefixIsRequired; // Le champ prefix du tiers doit etre renseigne quand on utilise {pre}


	/**
	 *	Constructor
	 */
	public function __construct()
	{
		$this->code_null = 0;
		$this->code_modifiable = 1;
		$this->code_modifiable_invalide = 1;
		$this->code_modifiable_null = 1;
		$this->code_auto = 1;
		$this->prefixIsRequired = 0;
	}


	/**
     *  Return description of module
	 *
	 *  @param	Translate	$langs		Object langs
	 *  @return string      			Description of module
	 */
	public function info($langs)
	{
        $texte = 'Masque spécifique Koesio HLF (Cxxxxx et Fxxxxx)';
        return $texte;
	}


	/**
	 * Return an example of result returned by getNextValue
	 *
	 * @param	Translate	$langs		Object langs
	 * @param	societe		$objsoc		Object thirdparty
	 * @param	int			$type		Type of third party (1:customer, 2:supplier, -1:autodetect)
	 * @return	string					Return string example
	 */
	public function getExample($langs, $objsoc = 0, $type = -1)
	{
		if ($type == 0 || $type == -1)
		{
			$examplecust = $this->getNextValue($objsoc, 0);
			if (! $examplecust)
			{
				$examplecust = $langs->trans('NotConfigured');
			}
			if($examplecust=="ErrorBadMask")
			{
				$langs->load("errors");
				$examplecust=$langs->trans($examplecust);
			}
			if($examplecust=="ErrorCantUseRazIfNoYearInMask")
			{
				$langs->load("errors");
				$examplecust=$langs->trans($examplecust);
			}
			if($examplecust=="ErrorCantUseRazInStartedYearIfNoYearMonthInMask")
			{
				$langs->load("errors");
				$examplecust=$langs->trans($examplecust);
			}
		}
		if ($type == 1 || $type == -1)
		{
			$examplesup = $this->getNextValue($objsoc, 1);
			if (! $examplesup)
			{
				$examplesup = $langs->trans('NotConfigured');
			}
			if($examplesup=="ErrorBadMask")
			{
				$langs->load("errors");
				$examplesup=$langs->trans($examplesup);
			}
			if($examplesup=="ErrorCantUseRazIfNoYearInMask")
			{
				$langs->load("errors");
				$examplesup=$langs->trans($examplesup);
			}
			if($examplesup=="ErrorCantUseRazInStartedYearIfNoYearMonthInMask")
			{
				$langs->load("errors");
				$examplesup=$langs->trans($examplesup);
			}
		}

		if ($type == 0) return $examplecust;
		if ($type == 1) return $examplesup;
		return $examplecust.'<br>'.$examplesup;
	}

	/**
	 * Return next value
	 *
	 * @param	Societe		$objsoc     Object third party
	 * @param  	int		    $type       Client ou fournisseur (0:customer, 1:supplier)
	 * @return 	string      			Value if OK, '' if module not configured, <0 if KO
	 */
	public function getNextValue($objsoc = 0, $type = -1)
	{
		global $db,$conf;

		require_once DOL_DOCUMENT_ROOT .'/core/lib/functions2.lib.php';

		// Get Mask value
		$maskClient = 'C{00000}';
		$maskFourn = 'F{00000}';

        $now=dol_now();
//var_dump($objSoc);
        $numFinalClient=get_next_value($db, $maskClient, 'societe', 'code_client', $where, '', $now);
        $numFinalFourn=get_next_value($db, $maskFourn, 'societe', 'code_fournisseur', $where, '', $now);

        $numOK = max(intval(substr($numFinalClient,1,5)), intval(substr($numFinalFourn,1,5)));

		if ($type == 0)
		{
            $numFinal = 'C'.str_pad($numOK, 5, '0', STR_PAD_LEFT);
		}
		elseif ($type == 1)
		{
            $numFinal = 'F'.str_pad($numOK, 5, '0', STR_PAD_LEFT);
		}
		else return -1;

		return  $numFinal;
	}


    /**
     *  Check validity of code according to its rules
     * 
     *  @param  DoliDB      $db     Database handler
     *  @param  string      $code   Code to check/correct
     *  @param  Societe     $soc    Object third party
     *  @param  int         $type   0 = customer/prospect , 1 = supplier
     *  @return int                 0 if OK
     *                              -1 ErrorBadCustomerCodeSyntax
     *                              -2 ErrorCustomerCodeRequired
     *                              -3 ErrorCustomerCodeAlreadyUsed
     *                              -4 ErrorPrefixRequired
     */
    public function verif($db, &$code, $soc, $type)
    {
        global $conf;

        $result=0;
        $code = trim($code);

        if (empty($code) && $this->code_null && empty($conf->global->MAIN_COMPANY_CODE_ALWAYS_REQUIRED))
        {
            $result=0;
        }
        elseif (empty($code) && (! $this->code_null || ! empty($conf->global->MAIN_COMPANY_CODE_ALWAYS_REQUIRED)) )
        {
            $result=-2;
        }

        dol_syslog(get_class($this)."::verif type=".$type." result=".$result);
        return $result;
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *		Renvoi si un code est pris ou non (par autre tiers)
	 *
	 *		@param	DoliDB		$db			Handler acces base
	 *		@param	string		$code		Code a verifier
	 *		@param	Societe		$soc		Objet societe
	 *		@param  int		  	$type   	0 = customer/prospect , 1 = supplier
	 *		@return	int						0 if available, <0 if KO
	 */
	public function verif_dispo($db, $code, $soc, $type = 0)
	{
        // phpcs:enable
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."societe";
		if ($type == 1) $sql.= " WHERE code_fournisseur = '".$code."'";
		else $sql.= " WHERE code_client = '".$code."'";
		if ($soc->id > 0) $sql.= " AND rowid <> ".$soc->id;

		$resql=$db->query($sql);
		if ($resql)
		{
			if ($db->num_rows($resql) == 0)
			{
				return 0;
			}
			else
			{
				return -1;
			}
		}
		else
		{
			return -2;
		}
	}
}
