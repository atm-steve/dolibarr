<?php
/*************************************************************************************************************************************************
 * Format d'export comptable Comptor
 *************************************************************************************************************************************************/

dol_include_once("/exportcompta/class/export.class.php");

class TExportComptaComptor extends TExportCompta {
	function __construct(&$db, $exportAllreadyExported=false, $addExportTime=false) {
		
		parent::__construct($db, $exportAllreadyExported, $addExportTime);
		
		$this->_format_ecritures_comptables_vente = array(
			array('name' => 'code_journal',			'length' => 5,	'default' => '',	'type' => 'text'),
			array('name' => 'date_piece',			'length' => 10,	'default' => '',	'type' => 'date',	'format' => 'd/m/Y'),
			array('name' => 'numero_piece',			'length' => 10,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'numero_compte',		'length' => 8,	'default' => '0',	'type' => 'text'),
			array('name' => 'numero_compte_general','length' => 8,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle',				'length' => 30,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'libelle2',				'length' => 30,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'sens',					'length' => 1,	'default' => 'C',	'type' => 'text'),
			array('name' => 'montant',				'length' => 15,	'default' => '0',	'type' => 'text'),
			array('name' => 'code2',				'length' => 4,	'default' => '',	'type' => 'text'),
			array('name' => 'code3',				'length' => 4,	'default' => '',	'type' => 'text')
		);
		
		$this->lineSeparator = "\r\n";
		$this->fieldSeparator = '';

		unset($this->TTypeExport['ecritures_comptables_achat']);
		unset($this->TTypeExport['ecritures_comptables_banque']);
		unset($this->TTypeExport['ecritures_comptables_ndf']);
		unset($this->TTypeExport['produits']); // pas encore pris en charge
		unset($this->TTypeExport['reglement_tiers']); // pas encore pris en charge
		unset($this->TTypeExport['tiers']); // pas encore pris en charge
		
	}
	
	function get_file_ecritures_comptables_ventes($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabFactures = parent::get_factures_client($dt_deb, $dt_fin);
		
		$contenuFichier = '';

		$numEcriture = 1;
		$numLignes = 1;
		
		$compte_general_client = $conf->global->EXPORT_COMPTA_GENERAL_CUSTOMER_ACCOUNT;
		if(empty($compte_general_client)) $compte_general_client = '41100000';
		
		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];
			
			$libelle = $tiers['nom'];
			if(!empty($facture['ref_client'])) $libelle.= ' - '.$facture['ref_client'];
			
			$journal = '01VFH';

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'code_journal'					=> $journal,
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte_general'			=> $compte_general_client,
					'numero_compte'					=> $code_compta,
	
					'libelle'						=> $libelle,
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'sens'							=> ($facture['type'] == 2 ? 'C' : 'D'),
					'montant'						=> abs($montant),
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'code_journal'					=> $journal,
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
					
					'libelle'						=> $libelle,
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'sens'							=> ($montant<0 ) ? 'D' : 'C',
					'montant'						=> abs($montant),
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}

			// Lignes TVA
			foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
				$ligneFichier = array(
					'code_journal'					=> $journal,
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
					
					'libelle'						=> $libelle,
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'sens'							=> ($montant<0 ) ? 'D' : 'C',
					'montant'						=> abs($montant),
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}
			
			$numEcriture++;
		}

		return $contenuFichier;
	}
}
?>
