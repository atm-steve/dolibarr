<?php
/*************************************************************************************************************************************************
 * Format d'export comptable LD
 *************************************************************************************************************************************************/

class TExportComptaLd extends TExportCompta {

	function __construct($db, $exportAllreadyExported=false,$addExportTimeToBill=false) {

		parent::__construct($db, $exportAllreadyExported,$addExportTimeToBill);

		$this->_format_ecritures_comptables_vente = array(
		    // TYPE 1 1 1 C Type d’enregistrement E ou A, Voir remarque 3
		    array('name' => 'code_type_enregistrement',			'length' => 1,	'default' => 'E',	'type' => 'text'),
		    //JNAL 2 3 2 C Code journal
		    array('name' => 'code_journal',			'length' => 2,	'default' => 'VD',	'type' => 'text'),
		    //NECR 4 11 7,0 N N° écriture Voir remarque 1
		    array('name' => 'num_ecriture',			'length' => 7,	'default' => '',	'type' => 'text'),
		    //NPIE 12 21 10 T N° pièce Voir remarque 3
		    array('name' => 'numero_piece',			'length' => 10,	'default' => '',	'type' => 'text'),
		    //DATP 22 29 8 D Date pièce
		    array('name' => 'date_ecriture',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),
		    //LIBE 30 54 25 T Libellé
		    array('name' => 'libelle',				'length' => 25,	'default' => '',	'type' => 'text'),
		    //DATH 55 62 8 D Date échéance
		    array('name' => 'DATH',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),

		    //CNPI 63 64 2 C Code nature pièce Voir remarque 8 ## FACULTATIF
		    /*
		     * FC=Facture client, AC=Avoir client, FF=Facture fournisseur, AF=Avoir fournisseur
		     */
		    array('name' => 'CNPI',                   'length' => 2,	'default' => '',	'type' => 'text'),
		    //RACI 65 66 2 C Racine compte collectif Voir remarque 2
		    array('name' => 'RACI',                   'length' => 2,	'default' => '',	'type' => 'text'),
		    //MONT 67 81 13,2 N Montant en euros Voir remarque 5
		    array('name' => 'montant',	               'length' => 15,	'default' => '0',	'type' => 'text'),
		    //CODC 82 82 1 C Code Débit/Crédit D ou C
		    array('name' => 'sens',	                   'length' => 1,	'default' => 'D',	'type' => 'text'),
		    //CPTG 83 90 8 C Compte Général Voir remarque 2
		    array('name' => 'CPTG',                   'length' => 8,	'default' => '',	'type' => 'text'),
		    //DATE 91 98 8 D Date Date comptable
		    array('name' => 'date_ecriture',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),
		    //CLET 99 101 3 T Code lettrage A blanc, sauf remarque 6
		    array('name' => 'CLET',	                   'length' => 3,	'default' => '',	'type' => 'text'),
		    //DATL 102 109 8 D Date lettrage A blanc, sauf remarque 6
		    array('name' => 'DATL',	                   'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),
		    //CPTA 110 117 8 C Compte auxiliaire Voir remarque 2
		    array('name' => 'CPTA',		'length' => 17,	'default' => '',	'type' => 'text'),
		    //array('name' => 'CPTA',                   'length' => 8,	'default' => '',	'type' => 'text'),
		    //CNAT 118 118 1 C Code nature tiers Voir remarque 2 : C si client,  F si fournisseur,  A si autre.
		    array('name' => 'CNAT',	                   'length' => 1,	'default' => '',	'type' => 'text'),
		    //CTRE 119 120 2 C Code trésorerie Voir remarque 9  // facultatif
		    array('name' => 'CTRE',	                   'length' => 2,	'default' => '',	'type' => 'text'),
		    //NORL 121 121 1 C N° relance Voir remarque 7
		    array('name' => 'NORL',	                   'length' => 2,	'default' => '',	'type' => 'text'),
		    //DATV 122 129 8 D Date valeur
		    array('name' => 'date_ecriture',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),
		    //REFD 130 139 10 T Référence document Voir remarque 4
		    array('name' => 'numero_piece',			'length' => 10,	'default' => '',	'type' => 'text'),
		    //NECA 140 143 3,0 N N° de séquence analytique Voir remarque 3
		    array('name' => 'NECA',			'length' => 10,	'default' => '1',	'type' => 'text'),
		    //CSEC 144 153 10 C Code section (axe anal. 1) Voir remarque 3
		    array('name' => 'CSEC',			'length' => 10,	'default' => '',	'type' => 'text'),
		    //CAFF 154 163 10 C Code affaire (axe anal. 2) Voir remarque 3
		    array('name' => 'CAFF',			'length' => 10,	'default' => '',	'type' => 'text'),
		    //CDES 154 173 10 C Code destination (axe anal. 2)Voir remarque 3
		    array('name' => 'CDES',			'length' => 10,	'default' => '',	'type' => 'text'),
		    //QTUE 174 183 9,3 N Quantité analytique Voir remarque 3
		    array('name' => 'QTUE',			'length' => 9,	'default' => '',	'type' => 'text'),
		    //MTDV 184 198 13,2 N Montant en devises Voir remarque 5
		    array('name' => 'MTDV',	               'length' => 15,	'default' => '0.00',	'type' => 'text'),
		    //CODV 199 201 3 C Code devise ISO Voir remarque 5
		    array('name' => 'CODV',				'length' => 3,	'default' => '',	'type' => 'text'),
		    //TXDV 202 214 11,7 N Taux de la devise Voir remarque 5
		    array('name' => 'TXDV',				'length' => 10,	'default' => '',	'type' => 'text'),
		    //MOPM 215 216 2 C Mode de paiement Voir remarque 10
		    array('name' => 'MOPM',				'length' => 2,	'default' => '',	'type' => 'text'),
		    //BONP 217 217 1 C Bon à payer Voir remarque 10
		    array('name' => 'BONP',				'length' => 1,	'default' => 'O',	'type' => 'text'),
		    //BQAF 218 219 2 C Code banque affectation Voir remarque 10
		    array('name' => 'BQAF',				'length' => 1,	'default' => '',	'type' => 'text'),
		    //ECES 220 220 1 C Echéance escomptable Voir remarque 10
		    array('name' => 'ECES',				'length' => 1,	'default' => '',	'type' => 'text'),
		    //TXTL 221 732 512 T Zone texte libre Voir remarque 11
		    array('name' => 'ECRM',				'length' => 512,	'default' => '',	'type' => 'text'),
		    //ECRM 733 733 1 C Ecriture modifiable Voir remarque 12
		    array('name' => 'TXTL',				'length' => 1,	'default' => '',	'type' => 'text'),
		    //DATK 734 741 8 D Date création Voir remarque 13
		    array('name' => 'DATK',				'length' => 8,	'default' => '',	'type' => 'text'),
		    //HEUK 742 747 6 H Heure création Voir remarque 13
		    array('name' => 'HEUK',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),


		);

		$this->addExportTimeToBill = $addExportTimeToBill;

		$this->_format_ecritures_comptables_achat = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_achat[0] = array('name' => 'code_type_enregistrement','length' => 2,'default' => 'E',	'type' => 'text');
		$this->_format_ecritures_comptables_achat[1] = array('name' => 'code_journal','length' => 2,'default' => 'AC',	'type' => 'text');

		$this->_format_ecritures_comptables_banque = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_banque[0] = array('name' => 'code_type_enregistrement','length' => 2,'default' => 'E',	'type' => 'text');
		$this->_format_ecritures_comptables_banque[2] = array('name' => 'code_journal','length' => 2,'default' => 'BQ',	'type' => 'text');

		$this->lineSeparator = "\r\n";
		$this->fieldSeparator = ',';
		$this->fieldPadding = false;
	}

	function get_file_ecritures_comptables_ventes($format, $dt_deb, $dt_fin) {
		global $conf, $db;

		if(empty($format)) $format = $this->_format_ecritures_comptables_vente;

		$TabFactures = parent::get_factures_client($dt_deb, $dt_fin);

		$contenuFichier = '';

		$numEcriture = 1;
		$numLignes = 1;

		dol_include_once('compta/facture/class/facture.class.php');

		foreach ($TabFactures as $id_facture => $infosFacture) {

		    $objFacture = new Facture($db);
		    if($objFacture->fetch($id_facture) > 0){
		        $objFacture->fetch_thirdparty();
		    }
		    else{
		        $objFacture = false;
		    }


			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];
			$tiers['nom'] = substr($tiers['nom'], 0, 33); // Bug si nom trop long car coupé et les guillemets ne sont plus présentes

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {

			    $ligneFichier = array(
					'num_ecriture'					=> $numLignes,
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
			    	'CPTG'							=> $conf->global->EXPORT_COMPTA_GENERAL_CUSTOMER_ACCOUNT,
			    	'DATH'		                    => !empty($objFacture->date_lim_reglement)? date('Ymd', $objFacture->date_lim_reglement) : '',
					'CPTA'                          => $code_compta,
					'libelle'						=> '"'.$tiers['nom'].'"',
			        'sens'							=> ($facture['type'] == 2 ? 'C' : 'D'),
			        'montant'						=> sprintf("%011.2f", number_format(abs($montant),2,'.','') ),
			        'CNPI'                          => $objFacture->type == Facture::TYPE_CREDIT_NOTE ? 'AC' : 'FC' ,
					'CNAT'                          => 'C',
			        'MOPM'                          => !empty($objFacture->mode_reglement_code)? $objFacture->mode_reglement_code : '',

				);





				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}

			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				if($facture['type'] == 2 && $montant > 0) {
					$sens = 'C';
				}
				else {
					$sens = ($facture['type'] == 2 || $montant < 0) ? 'D' : 'C';
				}
				$ligneFichier = array(
					'num_ecriture'					=> $numLignes,
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
				    'numero_compte'					=> '',
					'DATH'                          => !empty($objFacture->date_lim_reglement)? date('Ymd', $objFacture->date_lim_reglement ) : '',
					'CPTG'							=> $code_compta,
					'CPTA'							=> '',
					'libelle'						=> '"'.$tiers['nom'].'"',
				    'sens'							=> $sens,
				    'montant'						=> sprintf("%011.2f", number_format(abs($montant),2,'.','') ),
				    'CNPI'                          => $objFacture->type == Facture::TYPE_CREDIT_NOTE ? 'AC' : 'FC' ,
				    'CNAT'                          => '',
				    'MOPM'                          => !empty($objFacture->mode_reglement_code)? $objFacture->mode_reglement_code : '',
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);

				$numLignes++;
			}

			// Lignes TVA
			if(!empty($infosFacture['ligne_tva'])) {
				foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
					$ligneFichier = array(
						'num_ecriture'					=> $numLignes,
						'date_ecriture'					=> $facture['date'],
						'numero_piece'					=> $facture['ref'],
						'CPTG'							=> $code_compta,
						'DATH'  	                    => !empty($objFacture->date_lim_reglement)? date('Ymd',$objFacture->date_lim_reglement ) : '',
						'libelle'						=> '"'.$tiers['nom'].'"',
					    'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
					    'montant'						=> sprintf("%011.2f", number_format(abs($montant),2,'.','') ),
					    'CNPI'                          => $objFacture->type == Facture::TYPE_CREDIT_NOTE ? 'AC' : 'FC' ,
					    'CNAT'                          => '',
					    'MOPM'                          => !empty($objFacture->mode_reglement_code)? $objFacture->mode_reglement_code : '',
					);

					// Ecriture générale
					$contenuFichier .= parent::get_line($format, $ligneFichier);
					$numLignes++;
				}
			}

			$numEcriture++;
		}

		return $contenuFichier;
	}

	function get_file_ecritures_comptables_achats($format, $dt_deb, $dt_fin) {
		global $conf;

		if(empty($format)) $format = $this->_format_ecritures_comptables_achat;

		$TabFactures = parent::get_factures_fournisseur($dt_deb, $dt_fin);

		$contenuFichier = '';

		$numEcriture = 1;
		$numLignes = 1;

		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];
			$tiers['nom'] = substr($tiers['nom'], 0, 33); // Bug si nom trop long car coupé et les guillemets ne sont plus présentes

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'num_ecriture'					=> $numLignes,
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> '"'.$tiers['nom']."'",
					'sens'							=> ($montant > 0 ? 'C' : 'D'),
					'montant'						=> number_format(abs($montant),2,'.',''),
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}

			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'num_ecriture'					=> $numLignes,
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> '"'.$tiers['nom']."'",
					'sens'							=> ($montant > 0? 'D' : 'C'),
					'montant'						=> number_format(abs($montant),2,'.',''),
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);

				$numLignes++;
			}

			// Lignes TVA
			if(!empty($infosFacture['ligne_tva'])) {
				foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
					$ligneFichier = array(
						'num_ecriture'					=> $numLignes,
						'date_ecriture'					=> $facture['date'],
						'numero_piece'					=> $facture['ref'],
						'numero_compte'					=> $code_compta,
						'libelle'						=> '"'.$tiers['nom']."'",
						'sens'							=> ($montant > 0? 'D' : 'C'),
						'montant'						=> number_format(abs($montant),2,'.',''),
					);

					// Ecriture générale
					$contenuFichier .= parent::get_line($format, $ligneFichier);
					$numLignes++;
				}
			}

			$numEcriture++;
		}

		return $contenuFichier;
	}

	function get_file_ecritures_comptables_banque($format, $dt_deb, $dt_fin) {
		global $conf;

		if(empty($format)) $format = $this->_format_ecritures_comptables_banque;

		$TabBank = parent::get_banque($dt_deb, $dt_fin);

		$contenuFichier = '';

		$numEcriture = 1;
		$numLignes = 1;

		foreach ($TabBank as $id_bank => $infosBank) {
			$bankline = &$infosBank['bankline'];
			$bank = &$infosBank['bank'];
			$object = &$infosBank['object'];

			$label = $bankline['label'];
			//pre($object, true);exit;
			if(!empty($object)) {
				if($object->element == 'societe')			$label = $object->name;
				if($object->element == 'chargesociales')	$label = $object->type_libelle;
				if($object->element == 'user')				$label = $object->firstname.' '.$object->lastname;
			}

			// Lignes tiers
			foreach($infosBank['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'num_ecriture'					=> $numLignes,
					'code_journal'					=> $bank['ref'],
					'date_ecriture'					=> $bankline['datev'],
					'numero_piece'					=> $bankline['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> $label,
					'sens'							=> ($montant > 0 ? 'C' : 'D'),
					'montant'						=> number_format(abs($montant),2,'.',''),
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}

			// Ligne banque
			foreach($infosBank['ligne_banque'] as $code_compta => $montant) {
				$ligneFichier = array(
					'num_ecriture'					=> $numLignes,
					'code_journal'					=> $bank['ref'],
					'date_ecriture'					=> $bankline['datev'],
					'numero_piece'					=> $bankline['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> $label,
					'sens'							=> ($montant > 0? 'D' : 'C'),
					'montant'						=> number_format(abs($montant),2,'.',''),
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
