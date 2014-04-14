<?php
/*************************************************************************************************************************************************
 * Format d'export comptable Quadratus
 *************************************************************************************************************************************************/


class TExportComptaQuadratus extends TExportCompta {
	
	function __construct($db) {
		
		parent::__construct($db);
		
		$this->_format_ecritures_comptables_vente = array(
			array('name' => 'type',					'length' => 1,	'default' => 'M',	'type' => 'text'),
			array('name' => 'numero_compte',		'length' => 8,	'default' => '0',	'type' => 'text'),
			array('name' => 'code_journal',			'length' => 2,	'default' => 'VE',	'type' => 'text'),
			array('name' => 'numero_folio',			'length' => 3,	'default' => '000',	'type' => 'text'),
			array('name' => 'date_ecriture',		'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'dmy'),
			array('name' => 'code_libelle',			'length' => 1,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle_libre',		'length' => 20,	'default' => '',	'type' => 'text'),
			array('name' => 'sens',					'length' => 1,	'default' => 'C',	'type' => 'text'),
			array('name' => 'montant_signe',		'length' => 1,	'default' => '+',	'type' => 'text'),
			array('name' => 'montant',				'length' => 12,	'default' => '0',	'type' => 'text'),
			array('name' => 'compte_contrepartie',	'length' => 8,	'default' => '',	'type' => 'text'),
			array('name' => 'date_echeance',		'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'dmy'),
			array('name' => 'code_lettrage',		'length' => 2,	'default' => '',	'type' => 'text'),
			array('name' => 'code_statistiques',	'length' => 3,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_piece5',		'length' => 5,	'default' => '',	'type' => 'text'),
			array('name' => 'code_affaire',			'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'quantite1',			'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_piece8',		'length' => 8,	'default' => '',	'type' => 'text'),
			array('name' => 'code_devise',			'length' => 3,	'default' => 'EUR',	'type' => 'text'),
			array('name' => 'code_journal',			'length' => 3,	'default' => '',	'type' => 'text'),
			array('name' => 'flag_code_tva',		'length' => 1,	'default' => '',	'type' => 'text'),
			array('name' => 'code_tva1',			'length' => 1,	'default' => '',	'type' => 'text'),
			array('name' => 'methode_tva',			'length' => 1,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle_ecriture',		'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'code_tva2',			'length' => 2,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_piece10',		'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'reserve',				'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'montant_devise_signe',	'length' => 1,	'default' => '+',	'type' => 'text'),
			array('name' => 'montant_devise',		'length' => 12,	'default' => '0',	'type' => 'text'),
			array('name' => 'piece_jointe',			'length' => 12,	'default' => '',	'type' => 'text'),
			array('name' => 'quantite2',			'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'num_unique',			'length' => 10,	'default' => '0',	'type' => 'text'),
			array('name' => 'code_operateur',		'length' => 4,	'default' => '',	'type' => 'text'),
			array('name' => 'date_systeme',			'length' => 14,	'default' => '',	'type' => 'date',	'format' => 'dmYHis'),
		);
	
		$this->_format_ecritures_comptables_achat = $this->_format_ecritures_comptables_vente;
	
		$this->_format_reglement_tiers=array(
	
			array('name' => 'type',					'length' => 1,	'default' => 'R',	'type' => 'text'),
			array('name' => 'date_ecriture',		'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'dmy'),
			array('name' => 'montant',				'length' => 13,	'default' => '0',	'type' => 'text'),
			array('name' => 'mode_reglement',		'length' => 2,	'default' => 'CB',	'type' => 'text'),
			array('name' => 'code_journal',			'length' => 2,	'default' => 'RE',	'type' => 'text'),
			array('name' => 'reference',			'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'domiciliation',		'length' => 20,	'default' => '',	'type' => 'text'),
			array('name' => 'code_journal2',		'length' => 3,	'default' => '  ',	'type' => 'text'),
			array('name' => 'numero_compte',		'length' => 8,	'default' => ' ',	'type' => 'text'),
			array('name' => 'mode_reglement2',		'length' => 4,	'default' => 'CB',	'type' => 'text'),
			array('name' => 'bon_a_payer',			'length' => 1,	'default' => '1',	'type' => 'text'),
			array('name' => 'iban',					'length' => 4,	'default' => '',	'type' => 'text'),
			array('name' => 'bic',					'length' => 11,	'default' => '',	'type' => 'text'),
		
		);
		
	}
	
	function get_file_ecritures_comptables_ventes($format, $dt_deb, $dt_fin) {
		global $conf;

		if(empty($format)) $format = $this->_format_ecritures_comptables_vente;

		$TabFactures = parent::get_factures_client($dt_deb, $dt_fin);
		
		$type = 'M';
		$codeJournal='VE';
		
		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numEcriture = 1;
		$numLignes = 1;
		
		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];

			if(!empty($infosFacture['entity'])) {
				$entity = $infosFacture['entity'];
				$tmp = explode(";", $entity['description']);
				$codeCompteTiers = !empty($tmp[0]) ? $tmp[0] : '';
				$codeAnalytique = !empty($tmp[1]) ? $tmp[1] : '';
			}
//var_dump($infosFacture);exit;
			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				
				$ligneFichier = array(
					'type'							=> $type,
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $codeJournal,
					'date_ecriture'					=> strtotime($facture['date']),
					'libelle_libre'					=> $tiers['nom'].' - '.$facture['ref_client'],
					'sens'							=> ($facture['type'] == 2 ? 'C' : 'D'),
					//'montant_signe'					=> floatval($facture['total_ttc']) < 0 ? '-' : '+',
					'montant'						=> abs($montant * 100),
					'date_echeance'					=> strtotime($facture['date_lim_reglement']),
					'numero_piece5'					=> $facture['facnumber'],
					'numero_piece8'					=> $facture['facnumber'],
					'numero_piece10'				=> $facture['facnumber'],
					//'montant_devise_signe'			=> floatval($facture['total_ttc']) < 0 ? '-' : '+',
					'montant_devise'				=> abs($montant * 100),
					'num_unique'					=> $numLignes,
					'date_systeme'					=> time(),
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'type'							=> $type,
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $codeJournal,
					'date_ecriture'					=> strtotime($facture['date']),
					'libelle_libre'					=> $tiers['nom'].' - '.$facture['ref_client'],
					'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
					//'montant_signe'					=> floatval($facture['total_ttc']) < 0 ? '-' : '+',
					'montant'						=> abs($montant * 100),
					'date_echeance'					=> strtotime($facture['date_lim_reglement']),
					'numero_piece5'					=> $facture['facnumber'],
					'numero_piece8'					=> $facture['facnumber'],
					'numero_piece10'				=> $facture['facnumber'],
					//'montant_devise_signe'			=> floatval($facture['total_ttc']) < 0 ? '-' : '+',
					'montant_devise'				=> abs($montant * 100),
					'num_unique'					=> $numLignes,
					'date_systeme'					=> time(),
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				
				// Ecriture analytique
				//$ligneFichier['type_ecriture'] = 'A';
				//$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}

			// Lignes TVA
			foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
					$ligneFichier = array(
						'type'							=> $type,
						'numero_compte'					=> $code_compta,
						'code_journal'					=> $codeJournal,
						'date_ecriture'					=> strtotime($facture['date']),
						'libelle_libre'					=> $tiers['nom'],
						'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
						//'montant_signe'					=> floatval($facture['tva']) < 0 ? '-' : '+',
						'montant'						=> abs($montant * 100),
						'date_echeance'					=> strtotime($facture['date_lim_reglement']),
						'numero_piece5'					=> $facture['facnumber'],
						'numero_piece8'					=> $facture['facnumber'],
						'numero_piece10'				=> $facture['facnumber'],
						//'montant_devise_signe'			=> floatval($facture['tva']) < 0 ? '-' : '+',
						'montant_devise'				=> abs($montant * 100),
						'num_unique'					=> $numLignes,
						'date_systeme'					=> time(),
					);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
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
		$separateurLigne = "\r\n";

		$numEcriture = 1;
		$numLignes = 1;
		
		$type = 'M';
		$codeJournal='AC';
		
		
		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				
				$ligneFichier = array(
					'type'							=> $type,
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $codeJournal,
					'date_ecriture'					=> strtotime($facture['date']),
					'libelle_libre'					=> $tiers['nom'].' - '.$facture['ref_client'],
					'sens'							=> ($facture['type'] == 2 ? 'C' : 'D'),
					//'montant_signe'					=> floatval($facture['total_ttc']) < 0 ? '-' : '+',
					'montant'						=> abs($montant * 100),
					'date_echeance'					=> strtotime($facture['date_lim_reglement']),
					'numero_piece5'					=> $facture['facnumber'],
					'numero_piece8'					=> $facture['facnumber'],
					'numero_piece10'				=> $facture['facnumber'],
					//'montant_devise_signe'			=> floatval($facture['total_ttc']) < 0 ? '-' : '+',
					'montant_devise'				=> abs($montant * 100),
					'num_unique'					=> $numLignes,
					'date_systeme'					=> time(),
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'type'							=> $type,
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $codeJournal,
					'date_ecriture'					=> strtotime($facture['date']),
					'libelle_libre'					=> $tiers['nom'].' - '.$facture['ref_client'],
					'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
					//'montant_signe'					=> floatval($facture['total_ttc']) < 0 ? '-' : '+',
					'montant'						=> abs($montant * 100),
					'date_echeance'					=> strtotime($facture['date_lim_reglement']),
					'numero_piece5'					=> $facture['facnumber'],
					'numero_piece8'					=> $facture['facnumber'],
					'numero_piece10'				=> $facture['facnumber'],
					//'montant_devise_signe'			=> floatval($facture['total_ttc']) < 0 ? '-' : '+',
					'montant_devise'				=> abs($montant * 100),
					'num_unique'					=> $numLignes,
					'date_systeme'					=> time(),
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				
				// Ecriture analytique
				//$ligneFichier['type_ecriture'] = 'A';
				//$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}

			// Lignes TVA
			foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
					$ligneFichier = array(
						'type'							=> $type,
						'numero_compte'					=> $code_compta,
						'code_journal'					=> $codeJournal,
						'date_ecriture'					=> strtotime($facture['date']),
						'libelle_libre'					=> $tiers['nom'],
						'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
						//'montant_signe'					=> floatval($facture['tva']) < 0 ? '-' : '+',
						'montant'						=> abs($montant * 100),
						'date_echeance'					=> strtotime($facture['date_lim_reglement']),
						'numero_piece5'					=> $facture['facnumber'],
						'numero_piece8'					=> $facture['facnumber'],
						'numero_piece10'				=> $facture['facnumber'],
						//'montant_devise_signe'			=> floatval($facture['tva']) < 0 ? '-' : '+',
						'montant_devise'				=> abs($montant * 100),
						'num_unique'					=> $numLignes,
						'date_systeme'					=> time(),
					);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			$numEcriture++;
		}

		return $contenuFichier;
	}
	function get_file_reglement_tiers($format, $dt_deb, $dt_fin) {
		global $conf,$db;	
		
		if(empty($format)) $format = $this->_format_reglement_tiers;
		
		$TabReglement = parent::get_reglement_tiers($dt_deb, $dt_fin);
		
		$contenuFichier = '';
		$separateurLigne = "\r\n";
		$type = 'R';
		$numEcriture = 1;
		$numLignes = 1;
		
		foreach ($TabReglement as $infosReglement) {
			$tiers = &$infosReglement['client'];
			$reglement = &$infosReglement['reglement'];
		
			$ligneFichier = array(
				'type'							=> 'R',
				'numero_compte'					=> $tiers['code_compta'],
				'code_journal'					=> $tiers['code_compta'],
				'date_ecriture'					=> strtotime($reglement['datep']),
				'reference'					=> $tiers['nom'],
				'montant'						=> abs($reglement['amount'] * 100),
				
			);
			
			$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
			$numLignes++;
			
			// Ligne Banque
			$ligneFichier = array(
				'type'							=> 'M',
				'numero_compte'					=> $reglement['code_compta'],
				'code_journal'					=>  $reglement['code_compta'],
				'date_ecriture'					=> strtotime($reglement['datep']),
				'libelle_libre'					=> $tiers['nom'],
				'montant'						=> abs($reglement['amount'] * 100),
				
			);
			
			$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
			$numLignes++;
			
			$numEcriture++;
		}

		return $contenuFichier;
	}	
	
}
