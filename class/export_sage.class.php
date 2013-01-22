<?php
/*************************************************************************************************************************************************
 * Format d'export comptable Quadratus
 *************************************************************************************************************************************************/

dol_include_once("/export-compta/class/export.class.php");

class ExportComptaSage extends ExportCompta {
	var $_format_ecritures_comptables = array(
		array('name' => 'code_journal',			'length' => 6,	'default' => 'VE',	'type' => 'text'),
		array('name' => 'date_piece',			'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'dmy'),
		array('name' => 'numero_compte',		'length' => 13,	'default' => '0',	'type' => 'text'),
		array('name' => 'intitule_compte',		'length' => 35,	'default' => '',	'type' => 'text'),
		array('name' => 'numero_piece',			'length' => 13,	'default' => '',	'type' => 'text'),
		array('name' => 'numero_facture',		'length' => 17,	'default' => '',	'type' => 'text'),
		array('name' => 'reference',			'length' => 17,	'default' => '',	'type' => 'text'),
		array('name' => 'reference_rapproch',	'length' => 17,	'default' => '',	'type' => 'text'),
		array('name' => 'date_rapproch',		'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'dmy'),
		array('name' => 'numero_compte_tiers',	'length' => 17,	'default' => '',	'type' => 'text'),
		array('name' => 'code_taxe',			'length' => 5,	'default' => '',	'type' => 'text'),
		array('name' => 'provenance',			'length' => 1,	'default' => '',	'type' => 'text'),
		array('name' => 'libelle',				'length' => 35,	'default' => '',	'type' => 'text'),
		array('name' => 'mode_rglt',			'length' => 3,	'default' => '',	'type' => 'text'),
		array('name' => 'date_echeance',		'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'dmy'),
		array('name' => 'code_iso_devise',		'length' => 3,	'default' => 'EUR',	'type' => 'text'),
		array('name' => 'cours_devise',			'length' => 14,	'default' => '',	'type' => 'text'),
		array('name' => 'montant_devise',		'length' => 14,	'default' => '0',	'type' => 'text'),
		array('name' => 'type_norme',			'length' => 1,	'default' => '',	'type' => 'text'),
		array('name' => 'sens',					'length' => 1,	'default' => '',	'type' => 'text'),
		array('name' => 'montant',				'length' => 14,	'default' => '0',	'type' => 'text'),
		array('name' => 'montant_signe',		'length' => 14,	'default' => '0',	'type' => 'text'),
		array('name' => 'montant_debit',		'length' => 14,	'default' => '0',	'type' => 'text'),
		array('name' => 'montant_credit',		'length' => 14,	'default' => '0',	'type' => 'text'),
		array('name' => 'lettre_lettrage',		'length' => 3,	'default' => '',	'type' => 'text'),
		array('name' => 'type_ecriture',		'length' => 1,	'default' => '',	'type' => 'text'),
		array('name' => 'numero_plan',			'length' => 2,	'default' => '',	'type' => 'text'),
		array('name' => 'numero_section',		'length' => 13,	'default' => '',	'type' => 'text'),
		array('name' => 'information_libre',	'length' => 69,	'default' => '',	'type' => 'text'),
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

		$numEcriture = 1;
		$numLignes = 1;
		$type = 'M';
		
		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['client'];
			$lignes = &$infosFacture['lignes'];
			$facture = &$infosFacture['facture'];
			$divers = &$infosFacture['divers'];
			
			// Ligne client
			$ligneFichier = array(
				'code_journal'					=> $codeJournal.date('ym'),
				'date_piece'					=> strtotime($facture['datef']),
				'numero_compte'					=> 0, 
				'numero_piece'					=> $numEcriture,
				'numero_facture'				=> $facture['facnumber'],
				'numero_compte_tiers'			=> $tiers['code_compta'],

				'libelle'						=> $tiers['nom'],
				'mode_rglt'						=> $facture['mode_reglement'],
				'date_echeance'					=> strtotime($facture['date_lim_reglement']),
				'montant_devise'				=> abs($facture['total_ttc'] * 100),
				'sens'							=> ($facture['type'] == 2 ? 'C' : 'D'),

				'montant'						=> abs($facture['total_ttc'] * 100),
				'montant_signe'					=> abs($facture['total_ttc'] * 100),
				'montant_debit'					=> ($facture['type'] == 2 ? 0 : abs($facture['total_ttc'] * 100)),
				'montant_credit'				=> ($facture['type'] == 2 ? abs($facture['total_ttc'] * 100) : 0),
				
				'numero_plan'					=> '01',
				'numero_section'				=> ''
			);
			
			$contenuFichier .= parent::get_line($this->_format_ecritures_comptables, $ligneFichier) . $separateurLigne;
			$numLignes++;
			
			// Ligne TVA
			$ligneFichier = array(
				'code_journal'					=> $codeJournal.date('ym'),
				'date_piece'					=> strtotime($facture['datef']),
				'numero_compte'					=> $divers['code_compta_tva'],
				'numero_piece'					=> $numEcriture,
				'numero_facture'				=> $facture['facnumber'],
			
				'libelle'						=> $tiers['nom'],
				'mode_rglt'						=> $facture['mode_reglement'],
				'date_echeance'					=> strtotime($facture['date_lim_reglement']),
				'montant_devise'				=> abs($facture['tva'] * 100),				
				'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),

				'montant'						=> abs($facture['tva'] * 100),
				'montant_signe'					=> abs($facture['tva'] * 100),
				'montant_debit'					=> ($facture['type'] == 2 ? abs($facture['tva'] * 100) : 0),
				'montant_credit'				=> ($facture['type'] == 2 ? 0 : abs($facture['tva'] * 100)),
				
				'numero_plan'					=> '01',
				'numero_section'				=> ''
			);
			
			$contenuFichier .= parent::get_line($this->_format_ecritures_comptables, $ligneFichier) . $separateurLigne;
			$numLignes++;
			
			// Lignes de factures
			foreach ($lignes as $ligne) {
				$ligneFichier = array(
					'code_journal'					=> $codeJournal.date('ym'),
					'date_piece'					=> strtotime($facture['datef']),
					'numero_compte'					=> $ligne['code_compta'],
					'numero_piece'					=> $numEcriture,
					'numero_facture'				=> $facture['facnumber'],
					
					'libelle'						=> $tiers['nom'],
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> strtotime($facture['date_lim_reglement']),
					'montant_devise'				=> abs($ligne['total_ht'] * 100),					
					'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),

					'montant'						=> abs($ligne['total_ht'] * 100),
					'montant_signe'					=> abs($ligne['total_ht'] * 100),
					'montant_debit'					=> ($facture['type'] == 2 ? abs($ligne['total_ht'] * 100) : 0),
					'montant_credit'				=> ($facture['type'] == 2 ? 0 : abs($ligne['total_ht'] * 100)),
					
					'numero_plan'					=> '01',
					'numero_section'				=> ''
				);
				
				$contenuFichier .= parent::get_line($this->_format_ecritures_comptables, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			$numEcriture++;
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