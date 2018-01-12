<?php
/*************************************************************************************************************************************************
 * Format d'export comptable eTag
 *************************************************************************************************************************************************/

class TExportComptaAgiris extends TExportCompta {
	
	function __construct($db, $exportAllreadyExported=false) {
		
		parent::__construct($db, $exportAllreadyExported);
		
		$this->_format_ecritures_comptables_vente = array(
			array('name' => 'code_journal',			'length' => 5,	'default' => 'VE',	'type' => 'text'),
			array('name' => 'numero_compte',		'length' => 10,	'default' => '0',	'type' => 'text'),
			array('name' => 'date_ecriture',		'length' => 10,	'default' => '',	'type' => 'date',	'format' => 'd/m/Y'),
			array('name' => 'numero_piece',			'length' => 8,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle',				'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'montant_debit',		'length' => 12,	'default' => '0',	'type' => 'text'),
			array('name' => 'montant_credit',		'length' => 12,	'default' => '0',	'type' => 'text'),
		);
		
		$this->_format_ecritures_comptables_achat = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_achat[0]['default'] = 'AC';
		
		$this->lineSeparator = "\r\n";
		$this->fieldSeparator = ';';
		$this->fieldPadding = false;
		
		unset($this->TTypeExport['reglement_tiers'], $this->TTypeExport['produits'], $this->TTypeExport['ecritures_comptables_banque'], $this->TTypeExport['ecritures_comptables_ndf']); // pas encore pris en charge
		
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

			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> $tiers['nom'],
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
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
						'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
						'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
					);
					
					// Ecriture générale
					$contenuFichier .= parent::get_line($format, $ligneFichier);
					$numLignes++;
				}
			}
			
			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
						'date_ecriture'					=> $facture['date'],
						'numero_piece'					=> $facture['ref'],
						'numero_compte'					=> $code_compta,
						'libelle'						=> $tiers['nom'],
						'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
						'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}
			
			$numEcriture++;
		}

		return $contenuFichier;
	}
	
	
	function get_file_ecritures_comptables_achats($format, $dt_deb, $dt_fin) {
			global $conf;
			
			$TabFactures = parent::get_factures_fournisseur($dt_deb, $dt_fin);
			
			$contenuFichier = '';
			
			$numEcriture = 1;
			$numLignes = 1;
			
			$compte_general_fournisseur = $conf->global->EXPORT_COMPTA_GENERAL_SUPPLIER_ACCOUNT;
			if(empty($compte_general_fournisseur)) $compte_general_fournisseur = '40100000';
			
			foreach ($TabFactures as $id_facture => $infosFacture) {
				$tiers = &$infosFacture['tiers'];
				$facture = &$infosFacture['facture'];
				
				// Lignes de produits
				foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
					$ligneFichier = array(
							'date_ecriture'					=> $facture['date'],
							'numero_piece'					=> $facture['ref'],
							'numero_compte'					=> $code_compta,
							'libelle'						=> $tiers['nom'],
							'montant_credit'					=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
							'montant_debit'				=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
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
								'montant_credit'					=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
								'montant_debit'				=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
						);
						
						// Ecriture générale
						$contenuFichier .= parent::get_line($format, $ligneFichier);
						$numLignes++;
					}
				}
				
				// Lignes client
				foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
					$ligneFichier = array(
							'date_ecriture'					=> $facture['date'],
							'numero_piece'					=> $facture['ref'],
							'numero_compte'					=> $code_compta,
							'libelle'						=> $tiers['nom'],
							'montant_credit'					=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
							'montant_debit'				=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
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
