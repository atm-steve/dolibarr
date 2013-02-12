<?php
/*************************************************************************************************************************************************
 * Format d'export comptable Quadratus
 *************************************************************************************************************************************************/

dol_include_once("/export-compta/class/export.class.php");

class ExportComptaSage extends ExportCompta {
	function __construct(&$db) {
		parent::__construct($db);
	}
	
	function get_file_ecritures_comptables_ventes($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabFactures = parent::get_journal_ventes($dt_deb, $dt_fin);
		
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

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_plan'					=> '0',
					'numero_compte_general'			=> "41100000",
					'numero_compte_tiers'			=> empty($code_compta) ? (isset($codeCompteTiers) ? $codeCompteTiers : '') : $code_compta,
	
					'libelle'						=> isset($entity) ? 'FC '.mb_substr($entity['label'],0,15,'UTF-8').' '.date('m/y', $facture['date']).' '.$tiers['nom'] : $tiers['nom'],
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_debit'					=> ($facture['type'] == 2 ? 0 : abs($montant)),
					'montant_credit'				=> ($facture['type'] == 2 ? abs($montant) : 0),
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				
				// Ecriture analytique
				$ligneFichier['type_ecriture'] = 'A';
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_compte_general'			=> $code_compta,
					'numero_piece'					=> $facture['ref'],
					'numero_plan'					=> '1',
					'numero_section'				=> $codeAnalytique,
					
					'libelle'						=> isset($entity) ? 'FC '.mb_substr($entity['label'],0,15,'UTF-8').' '.date('m/y', $facture['date']).' '.$tiers['nom'] : $tiers['nom'],
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_debit'					=> ($facture['type'] == 2 ? abs($montant) : 0),
					'montant_credit'				=> ($facture['type'] == 2 ? 0 : abs($montant)),
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				
				// Ecriture analytique
				$ligneFichier['type_ecriture'] = 'A';
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}

			// Lignes TVA
			foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_compte_general'			=> $code_compta,
					'numero_piece'					=> $facture['ref'],
					'numero_plan'					=> '0',
					
					'libelle'						=> isset($entity) ? 'FC '.mb_substr($entity['label'],0,15,'UTF-8').' '.date('m/y', $facture['date']).' '.$tiers['nom'] : $tiers['nom'],
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_debit'					=> ($facture['type'] == 2 ? abs($montant) : 0),
					'montant_credit'				=> ($facture['type'] == 2 ? 0 : abs($montant)),
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				
				// Ecriture analytique
				$ligneFichier['type_ecriture'] = 'A';
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			$numEcriture++;
		}

		return $contenuFichier;
	}

	function get_file_ecritures_comptables_achats($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabFactures = parent::get_journal_achats($dt_deb, $dt_fin);
		
		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numLignes = 1;
		
		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];
			
			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $infosFacture['compteur']['piece'],
					'numero_facture'				=> $facture['facnumber'],
					'numero_compte_tiers'			=> $code_compta,
	
					'libelle'						=> $tiers['nom'],
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_devise'				=> abs($montant * 100),
					'sens'							=> ($facture['type'] == 2 ? 'C' : 'D'),
	
					'montant'						=> abs($montant * 100),
					'montant_signe'					=> abs($montant * 100),
					'montant_debit'					=> ($facture['type'] == 2 ? 0 : abs($montant * 100)),
					'montant_credit'				=> ($facture['type'] == 2 ? abs($montant * 100) : 0)
				);
				
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_compte'					=> $code_compta,
					'numero_piece'					=> $infosFacture['compteur']['piece'],
					'numero_facture'				=> $facture['facnumber'],
					
					'libelle'						=> $tiers['nom'],
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_devise'				=> abs($montant * 100),
					'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),

					'montant'						=> abs($montant * 100),
					'montant_signe'					=> abs($montant * 100),
					'montant_debit'					=> ($facture['type'] == 2 ? abs($montant * 100) : 0),
					'montant_credit'				=> ($facture['type'] == 2 ? 0 : abs($montant * 100)),
					'numero_section'				=> $infosFacture['entity']['id']
				);
				
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}

			// Lignes TVA
			foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_compte'					=> $code_compta,
					'numero_piece'					=> $infosFacture['compteur']['piece'],
					'numero_facture'				=> $facture['facnumber'],
				
					'libelle'						=> $tiers['nom'],
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_devise'				=> abs($montant * 100),
					'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
	
					'montant'						=> abs($montant * 100),
					'montant_signe'					=> abs($montant * 100),
					'montant_debit'					=> ($facture['type'] == 2 ? abs($montant * 100) : 0),
					'montant_credit'				=> ($facture['type'] == 2 ? 0 : abs($montant * 100))
				);
				
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
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
			echo $this->_format_ecritures_comptables; exit;
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