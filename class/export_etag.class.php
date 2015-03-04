<?php
/*************************************************************************************************************************************************
 * Format d'export comptable eTag
 *************************************************************************************************************************************************/

class TExportComptaEtag extends TExportCompta {
	
	function __construct($db, $exportAllreadyExported=false) {
		
		parent::__construct($db, $exportAllreadyExported);
		
		$this->_format_ecritures_comptables_vente = array(
			array('name' => 'date_ecriture',		'length' => 10,	'default' => '',	'type' => 'date',	'format' => 'd/m/Y'),
			array('name' => 'code_journal',			'length' => 5,	'default' => 'VE',	'type' => 'text'),
			array('name' => 'numero_piece',			'length' => 20,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_compte',		'length' => 20,	'default' => '0',	'type' => 'text'),
			array('name' => 'libelle',				'length' => 50,	'default' => '',	'type' => 'text'),
			array('name' => 'montant_debit',		'length' => 12,	'default' => '0',	'type' => 'text',),
			array('name' => 'montant_credit',		'length' => 12,	'default' => '0',	'type' => 'text',),
		);
		
		$this->lineSeparator = "\r\n";
		$this->fieldSeparator = ';';
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
			
			$numEcriture++;
		}

		return $contenuFichier;
	}
}
