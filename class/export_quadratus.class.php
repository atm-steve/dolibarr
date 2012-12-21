<?php
/*************************************************************************************************************************************************
 * Format d'export comptable Quadratus
 *************************************************************************************************************************************************/

dol_include_once("/export-compta/class/export.class.php");

class ExportComptaQuadratus extends ExportCompta {
	var $_format_ecritures_comptables = array(
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
	
	function get_file_ecritures_comptables(&$db, &$conf, $dt_deb, $dt_fin, $codeJournal='VE') {
		if($codeJournal == 'VE') {
			$TabFactures = parent::get_journal_vente($db, $conf, $dt_deb, $dt_fin);
		} else if($codeJournal == 'AC') {
			$TabFactures = parent::get_journal_achat($db, $conf, $dt_deb, $dt_fin);
		} else {
			return 'Erreur de code journal';
		}
		
		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numLignes = 1;
		$type = 'M';
		
		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['client'];
			$lignes = &$infosFacture['lignes'];
			$facture = &$infosFacture['facture'];
			$divers = &$infosFacture['divers'];
			
			// Ligne client
			$ligneFichier = array(
				'type'							=> $type,
				'numero_compte'					=> $tiers['code_compta'],
				'code_journal'					=> $codeJournal,
				'date_ecriture'					=> strtotime($facture['datef']),
				'libelle_libre'					=> $tiers['nom'],
				'sens'							=> ($facture['type'] == 2 ? 'C' : 'D'),
				//'montant_signe'					=> floatval($facture['total_ttc']) < 0 ? '-' : '+',
				'montant'						=> abs($facture['total_ttc'] * 100),
				'date_echeance'					=> strtotime($facture['date_lim_reglement']),
				'numero_piece5'					=> $facture['facnumber'],
				'numero_piece8'					=> $facture['facnumber'],
				'numero_piece10'				=> $facture['facnumber'],
				//'montant_devise_signe'			=> floatval($facture['total_ttc']) < 0 ? '-' : '+',
				'montant_devise'				=> abs($facture['total_ttc'] * 100),
				'num_unique'					=> $numLignes,
				'date_systeme'					=> time(),
			);
			
			$contenuFichier .= parent::get_line($this->_format_ecritures_comptables, $ligneFichier) . $separateurLigne;
			$numLignes++;
			
			// Ligne TVA
			$ligneFichier = array(
				'type'							=> $type,
				'numero_compte'					=> $divers['code_compta_tva'],
				'code_journal'					=> $codeJournal,
				'date_ecriture'					=> strtotime($facture['datef']),
				'libelle_libre'					=> $tiers['nom'],
				'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
				//'montant_signe'					=> floatval($facture['tva']) < 0 ? '-' : '+',
				'montant'						=> abs($facture['tva'] * 100),
				'date_echeance'					=> strtotime($facture['date_lim_reglement']),
				'numero_piece5'					=> $facture['facnumber'],
				'numero_piece8'					=> $facture['facnumber'],
				'numero_piece10'				=> $facture['facnumber'],
				//'montant_devise_signe'			=> floatval($facture['tva']) < 0 ? '-' : '+',
				'montant_devise'				=> abs($facture['tva'] * 100),
				'num_unique'					=> $numLignes,
				'date_systeme'					=> time(),
			);
			
			$contenuFichier .= parent::get_line($this->_format_ecritures_comptables, $ligneFichier) . $separateurLigne;
			$numLignes++;
			
			// Lignes de factures
			foreach ($lignes as $ligne) {
				$ligneFichier = array(
					'type'							=> $type,
					'numero_compte'					=> $ligne['code_compta'],
					'code_journal'					=> $codeJournal,
					'date_ecriture'					=> strtotime($facture['datef']),
					'libelle_libre'					=> $tiers['nom'],
					'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
					//'montant_signe'					=> floatval($ligne['total_ht']) < 0 ? '-' : '+',
					'montant'						=> abs($ligne['total_ht'] * 100),
					'date_echeance'					=> strtotime($facture['date_lim_reglement']),
					'numero_piece5'					=> $facture['facnumber'],
					'numero_piece8'					=> $facture['facnumber'],
					'numero_piece10'				=> $facture['facnumber'],
					//'montant_devise_signe'			=> floatval($ligne['total_ht']) < 0 ? '-' : '+',
					'montant_devise'				=> abs($ligne['total_ht'] * 100),
					'num_unique'					=> $numLignes,
					'date_systeme'					=> time(),
				);
				
				$contenuFichier .= parent::get_line($this->_format_ecritures_comptables, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
		}

		return $contenuFichier;
	}

	function get_file_reglement_tiers(&$db, &$conf, $dt_deb, $dt_fin) {
		$TabReglement = parent::get_reglement_tiers($db, $conf, $dt_deb, $dt_fin);
		
		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numLignes = 1;
		$type = 'M';
		$codeJournal = 'VE';
		
		foreach ($TabReglement as $infosReglement) {
			$tiers = &$infosReglement['client'];
			$reglement = &$infosReglement['reglement'];
			
			// Ligne client
			$ligneFichier = array(
				'type'							=> $type,
				'numero_compte'					=> $tiers['code_compta'],
				'code_journal'					=> $codeJournal,
				'date_ecriture'					=> strtotime($reglement['datep']),
				'libelle_libre'					=> $tiers['nom'],
				'sens'							=> 'C',
				'montant'						=> abs($reglement['amount'] * 100),
				'montant_devise'				=> abs($reglement['amount'] * 100),
				'num_unique'					=> $numLignes,
				'date_systeme'					=> time(),
			);
			
			$contenuFichier .= parent::get_line($this->_format_ecritures_comptables, $ligneFichier) . $separateurLigne;
			$numLignes++;
			
			// Ligne Banque
			$ligneFichier = array(
				'type'							=> $type,
				'numero_compte'					=> $reglement['code_compta'],
				'code_journal'					=> $codeJournal,
				'date_ecriture'					=> strtotime($reglement['datep']),
				'libelle_libre'					=> $tiers['nom'],
				'sens'							=> 'D',
				'montant'						=> abs($reglement['amount'] * 100),
				'montant_devise'				=> abs($reglement['amount'] * 100),
				'num_unique'					=> $numLignes,
				'date_systeme'					=> time(),
			);
			
			$contenuFichier .= parent::get_line($this->_format_ecritures_comptables, $ligneFichier) . $separateurLigne;
			$numLignes++;
		}

		return $contenuFichier;
	}
}
?>