<?php
/*************************************************************************************************************************************************
 * Format d'export comptable pour In Extenso
 *************************************************************************************************************************************************/

class TExportComptaInextenso extends TExportCompta {
	
	function __construct($db, $exportAllreadyExported=false, $addExportTimeToBill=false) {
		
		parent::__construct($db, $exportAllreadyExported, $addExportTimeToBill);
		
		$this->_format_ecritures_comptables_vente = array(
			array('name' => 'date_ecriture',           'length' => 10, 'default' => '',   'type' => 'date',	'format' => 'd/m/Y'),
			array('name' => 'code_journal',            'length' =>  6, 'default' => 'VT', 'type' => 'text'),
			array('name' => 'numero_compte',           'length' =>  9, 'default' => '0',  'type' => 'text'),
			array('name' => 'montant_debit',           'length' => 13, 'default' => '0',  'type' => 'text'),
			array('name' => 'montant_credit',          'length' => 13, 'default' => '0',  'type' => 'text'),
			array('name' => 'libelle',                 'length' => 30, 'default' => '',   'type' => 'text'),
			array('name' => 'numero_piece',            'length' => 12, 'default' => '',   'type' => 'text'),
			array('name' => 'compte_collectif_client', 'length' =>  9, 'default' => '',   'type' => 'text'),
		);
				
		$this->_format_ecritures_comptables_achat = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_achat[1] = array('name' => 'code_journal','length' => 6,'default' => 'AC',	'type' => 'text');
		
		$this->_format_ecritures_comptables_banque = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_banque[1] = array('name' => 'code_journal','length' => 6,'default' => 'BQ',	'type' => 'text');
		
		$this->_format_ecritures_comptables_ndf = $this->_format_ecritures_comptables_achat;
		
		$this->_format_tiers = array(
			array('name' => 'numero_compte',		'length' => 9,	'default' => '0',	'type' => 'text'),
			array('name' => 'nom',					'length' => 50,	'default' => '0',	'type' => 'text',),
			array('name' => 'vide',					'length' => 1,	'default' => '',	'type' => 'text',),
			array('name' => 'vide',					'length' => 1,	'default' => '',	'type' => 'text',),
			array('name' => 'vide',					'length' => 1,	'default' => '',	'type' => 'text',),
			array('name' => 'vide',					'length' => 1,	'default' => '',	'type' => 'text',),
			array('name' => 'vide',					'length' => 1,	'default' => '',	'type' => 'text',),
			array('name' => 'code_pays',			'length' => 3,	'default' => '0',	'type' => 'text',),
		);
		
		$this->lineSeparator = "\r\n";
		$this->fieldSeparator = ';';
		$this->fieldPadding = false;
		
		unset($this->TTypeExport['produits']); // pas encore pris en charge
		unset($this->TTypeExport['reglement_tiers']); // pas encore pris en charge
	}
	
	function get_file_ecritures_comptables_ventes($format, $dt_deb, $dt_fin) {
		global $conf;

		if(empty($format)) $format = $this->_format_ecritures_comptables_vente;

		$TabFactures = parent::get_factures_client($dt_deb, $dt_fin);
		
		$contenuFichier = '';

		$numEcriture = 1;
		$numLignes = 1;
		
		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];

            // tiers France
            if ($tiers['country_code'] === 'FR') $compteCollectifClient = '411000';
            // tiers CEE hors France
            elseif (isInEEC((object)$tiers))     $compteCollectifClient = '411001';
            // tiers hors CEE
            else                                 $compteCollectifClient = '411002';

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> $tiers['nom'],
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? 0 : number_format(abs($montant),2,',',''),
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? number_format(abs($montant),2,',','') : 0,
					'compte_collectif_client'		=> $compteCollectifClient,
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> $tiers['nom'],
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? number_format(abs($montant),2,',','') : 0,
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? 0 : number_format(abs($montant),2,',',''),
					'compte_collectif_client'		=> $compteCollectifClient,
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				
				$numLignes++;
			}

			// Lignes TVA
			if(!empty($infosFacture['ligne_tva'])) {
				foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
					$ligneFichier = array(
						'date_ecriture'					=> $facture['date'],
						'numero_piece'					=> $facture['ref'],
						'numero_compte'					=> $code_compta,
						'libelle'						=> $tiers['nom'],
						'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? number_format(abs($montant),2,',','') : 0,
						'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? 0 : number_format(abs($montant),2,',',''),
					    'compte_collectif_client'		=> $compteCollectifClient,
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
			
			$label = $tiers['nom'];
			if(!empty($conf->global->EXPORT_COMPTA_FOURN_INVOICE_LABEL) && !empty($facture['label'])) $label.= ' - ' . $facture['label'];

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> $label,
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? number_format(abs($montant),2,',','') : 0,
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? 0 : number_format(abs($montant),2,',',''),
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> $label,
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? 0 : number_format(abs($montant),2,',',''),
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? number_format(abs($montant),2,',','') : 0,
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				
				// Ecriture analytique
				$numLignes++;
			}

			// Lignes TVA
			if(!empty($infosFacture['ligne_tva'])) {
				foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
						$ligneFichier = array(
							'date_ecriture'					=> $facture['date'],
							'numero_piece'					=> $facture['ref'],
							'numero_compte'					=> $code_compta,
							'libelle'						=> $label,
							'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? 0 : number_format(abs($montant),2,',',''),
							'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? number_format(abs($montant),2,',','') : 0,
						);
					
					// Ecriture générale
					$contenuFichier .= parent::get_line($format, $ligneFichier);
					$numLignes++;
				}
			} else {
				global $db;
				$soc = new Societe($db);
				$soc->fetch($tiers['id']);
				
				if($soc->isInEEC() && $soc->country_id != 1) { // Autoliquidation TVA pour achat intracommunautaires
					$montant = $facture['total_ttc'] * .2;
					$montant = round($montant,2);
					
					$ligneFichier = array(
						'date_ecriture'					=> $facture['date'],
						'numero_piece'					=> $facture['ref'],
						'numero_compte'					=> 445662,
						'libelle'						=> $label,
						'montant_debit'					=> number_format(abs($montant),2,',',''),
						'montant_credit'				=> 0,
					);
					
					// Ecriture générale
					$contenuFichier .= parent::get_line($format, $ligneFichier);
					$numLignes++;
					
					$ligneFichier = array(
						'date_ecriture'					=> $facture['date'],
						'numero_piece'					=> $facture['ref'],
						'numero_compte'					=> 445200,
						'libelle'						=> $label,
						'montant_debit'					=> 0,
						'montant_credit'				=> number_format(abs($montant),2,',',''),
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
				if($object->element == 'chargesociales')	$label = $object->type_libelle . ' - ' . $object->lib;
				if($object->element == 'user')				$label = $object->firstname.' '.$object->lastname;
				if(get_class($object) == 'BonPrelevement')	$label = $object->ref;
			}
			
			// Lignes tiers
			foreach($infosBank['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'code_journal'			=> $bank['ref'],
					'date_ecriture'			=> $bankline['datev'],
					'numero_compte'			=> $code_compta,
					'numero_piece'			=> $bankline['ref'],
					'libelle'				=> $label,
					'montant_debit'			=> ($montant < 0) ? number_format(abs($montant),2,',','') : 0,
					'montant_credit'		=> ($montant < 0) ? 0 : number_format(abs($montant),2,',','')
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}
			
			// Lignes banque
			foreach($infosBank['ligne_banque'] as $code_compta => $montant) {
				$ligneFichier = array(
					'code_journal'			=> $bank['ref'],
					'date_ecriture'			=> $bankline['datev'],
					'numero_compte'			=> $code_compta,
					'numero_piece'			=> $bankline['ref'],
					'libelle'				=> $label,
					'montant_debit'			=> ($montant < 0) ? 0 : number_format(abs($montant),2,',',''),
					'montant_credit'		=> ($montant < 0) ? number_format(abs($montant),2,',','') : 0,
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}
			
			$numEcriture++;
		}

		return $contenuFichier;
	}

	function get_file_ecritures_comptables_ndf($format, $dt_deb, $dt_fin) {
		global $conf;

		if(empty($format)) $format = $this->_format_ecritures_comptables_achat;

		$TabNDF = parent::get_notes_de_frais($dt_deb, $dt_fin);
		
		$contenuFichier = '';

		$numEcriture = 1;
		$numLignes = 1;
		
		foreach ($TabNDF as $id_ndf => $infosNDF) {
			$ndf = &$infosNDF['ndf'];
			$user = &$infosNDF['user'];

			// Lignes client
			foreach($infosNDF['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_ecriture'					=> $ndf['date_valid'],
					'numero_piece'					=> $ndf['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> $user['firstname'].' '.$user['lastname'],
					'montant_debit'					=> 0,
					'montant_credit'				=> number_format(abs($montant),2,',',''),
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosNDF['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_ecriture'					=> $ndf['date_valid'],
					'numero_piece'					=> $ndf['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> $user['firstname'].' '.$user['lastname'],
					'montant_debit'					=> number_format(abs($montant),2,',',''),
					'montant_credit'				=> 0,
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				
				// Ecriture analytique
				$numLignes++;
			}

			// Lignes TVA
			if(!empty($infosNDF['ligne_tva'])) {
				foreach($infosNDF['ligne_tva'] as $code_compta => $montant) {
						$ligneFichier = array(
							'date_ecriture'					=> $ndf['date_valid'],
							'numero_piece'					=> $ndf['ref'],
							'numero_compte'					=> $code_compta,
							'libelle'						=> $user['firstname'].' '.$user['lastname'],
							'montant_debit'					=> number_format(abs($montant),2,',',''),
							'montant_credit'				=> 0,
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

	function get_file_tiers($format, $dt_deb, $dt_fin) {
		global $conf;
		if(empty($format)) $format = $this->_format_tiers;
	
		$Tab = parent::get_tiers($dt_deb, $dt_fin);
		
		$contenuFichier = '';
	
		foreach($Tab as $code_compta=>$tiers) {
			
			$ligneFichier = array(
				'numero_compte'			=> $code_compta,
				'nom'					=> $tiers['nom'],
				'code_pays'				=> $tiers['code_pays'],
			);
			
			$contenuFichier .= parent::get_line($format, $ligneFichier);	
		}
	
		return $contenuFichier;
	}
}
