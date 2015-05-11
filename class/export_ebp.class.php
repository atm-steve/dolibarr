<?php
/*************************************************************************************************************************************************
 * Format d'export comptable EBP
 *************************************************************************************************************************************************/

class TExportComptaEbp extends TExportCompta {
	
	function __construct($db, $exportAllreadyExported=false) {
		
		parent::__construct($db, $exportAllreadyExported);
		
		$this->_format_ecritures_comptables_vente = array(
			array('name' => 'num_ecriture',			'length' => 8,	'default' => '',	'type' => 'text'),
			array('name' => 'date_ecriture',		'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'dmy'),
			array('name' => 'code_journal',			'length' => 3,	'default' => 'VE',	'type' => 'text'),
			array('name' => 'numero_compte',		'length' => 17,	'default' => '0',	'type' => 'text'),
			array('name' => '(vide)',				'length' => 8,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle',				'length' => 35,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_piece',			'length' => 35,	'default' => '',	'type' => 'text'),
			array('name' => 'montant',				'length' => 20,	'default' => '0',	'type' => 'text'),
			array('name' => 'sens',					'length' => 1,	'default' => 'D',	'type' => 'text'),
			array('name' => 'date_ecriture',		'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'dmy'),
			array('name' => 'devise',				'length' => 3,	'default' => 'EUR',	'type' => 'text'),
		);
		
		$this->_format_ecritures_comptables_achat = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_achat[2] = array('name' => 'code_journal','length' => 2,'default' => 'AC',	'type' => 'text');
		$this->_format_ecritures_comptables_banque = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_banque[2] = array('name' => 'code_journal','length' => 2,'default' => 'BQ',	'type' => 'text');
		
		$this->lineSeparator = "\r\n";
		$this->fieldSeparator = ',';
		$this->fieldPadding = false;
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

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'num_ecriture'					=> $numLignes,
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> '"'.$tiers['nom'].'"',
					'sens'							=> ($facture['type'] == 2 ? 'C' : 'D'),
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
					'libelle'						=> '"'.$tiers['nom'].'"',
					'sens'							=> (($facture['type'] == 2 || $montant < 0) ? 'D' : 'C'),
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
						'libelle'						=> '"'.$tiers['nom'].'"',
						'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
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

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'num_ecriture'					=> $numLignes,
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> $tiers['nom'],
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
					'libelle'						=> $tiers['nom'],
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
						'libelle'						=> $tiers['nom'],
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
