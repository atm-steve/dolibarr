<?php 
/*
 * Collection de fonction pour gérer les exports comptables
 */
class TExportCompta {
	/*************************************************************************************************************************************************
	 * EXPORTS QUADRATUS :
	 *  - EXPORT DES FACTURES VENTE
	 *  - EXPORT DES REGLEMENTS
	 *  - EXPORT DES FACTURES ACHAT
	 *************************************************************************************************************************************************/
	
	/* 
	 * Récupération dans Dolibarr de la liste des factures clients avec détails ligne + produit + client
	 * Toutes les factures validées, payées, abandonnées, pour l'entité concernée, avec date de facture entre les bornes sélectionnées
	 */
	function get_journal_vente(&$db, &$conf, $dt_deb, $dt_fin) {
		// Requête de récupération des factures
		$sql = "SELECT f.rowid as fact_rowid, f.facnumber as fact_facnumber, f.datef as fact_datef, f.date_lim_reglement as fact_date_lim_reglement, f.total_ttc as fact_total_ttc,"; 
		$sql.= " f.tva as fact_tva, f.type as fact_type,";
		$sql.= " fd.total_ht as ligne_total_ht, fd.total_tva as ligne_total_tva, fd.total_ttc as ligne_total_ttc,";
		$sql.= " p.accountancy_code_sell as prod_accountancy_code_sell, p.fk_product_type as prod_fk_product_type,";
		$sql.= " s.code_compta as client_code_compta, s.nom as client_nom";
		$sql.= " FROM llx_facture f";
		$sql.= " LEFT JOIN llx_facturedet fd ON fd.fk_facture = f.rowid";
		$sql.= " LEFT JOIN llx_product p ON p.rowid = fd.fk_product";
		$sql.= " LEFT JOIN llx_societe s ON s.rowid = f.fk_soc";
		$sql.= " WHERE f.datef BETWEEN '$dt_deb' AND '$dt_fin'";
		$sql.= " AND f.entity = {$conf->entity}";
		$sql.= " AND f.fk_statut IN (1,2,3)";
		$sql.= " AND fd.total_ht != 0";
		$sql.= " ORDER BY f.datef, fd.rang ASC";
		//echo $sql;
		$resql = $db->query($sql);
		
		// Construction du tableau de données
		$TFactures = array();
		while($obj = $db->fetch_object($resql)) {
			$idFact = $obj->fact_rowid;
			
			if(empty($TFactures[$idFact])) $TFactures[$idFact] = array();
			
			if(empty($TFactures[$idFact]['facture'])) {
				$TFactures[$idFact]['facture'] = array();
				$TFactures[$idFact]['facture']['facnumber'] = $obj->fact_facnumber;
				$TFactures[$idFact]['facture']['datef'] = $obj->fact_datef;
				$TFactures[$idFact]['facture']['date_lim_reglement'] = $obj->fact_date_lim_reglement;
				$TFactures[$idFact]['facture']['tva'] = $obj->fact_tva;
				$TFactures[$idFact]['facture']['total_ttc'] = $obj->fact_total_ttc;
				$TFactures[$idFact]['facture']['type'] = $obj->fact_type;
			}
			
			if(empty($TFactures[$idFact]['lignes'])) $TFactures[$idFact]['lignes'] = array();
			
			$codeComptableProduit = $obj->prod_accountancy_code_sell;
			if($codeComptableProduit == '') {
				if($obj->prod_fk_product_type == 0) {
					$codeComptableProduit = $conf->global->COMPTA_SERVICE_SOLD_ACCOUNT;
				} else {
					$codeComptableProduit = $conf->global->COMPTA_PRODUCT_SOLD_ACCOUNT;
				}
			}
			
			if(empty($TFactures[$idFact]['lignes'][$codeComptableProduit])) {
				$TFactures[$idFact]['lignes'][$codeComptableProduit] = array();
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['code_compta'] = $codeComptableProduit;
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['total_ht'] = floatval($obj->ligne_total_ht);
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['total_tva'] = floatval($obj->ligne_total_tva);
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['total_ttc'] = floatval($obj->ligne_total_ttc);
			} else {
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['total_ht'] += floatval($obj->ligne_total_ht);
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['total_tva'] += floatval($obj->ligne_total_tva);
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['total_ttc'] += floatval($obj->ligne_total_ttc);
			}
			
			if(empty($TFactures[$idFact]['client'])) {
				$TFactures[$idFact]['client'] = array();
				$TFactures[$idFact]['client']['code_compta'] = $obj->client_code_compta == '' ? $conf->global->COMPTA_ACCOUNT_CUSTOMER : $obj->client_code_compta;
				$TFactures[$idFact]['client']['nom'] = $obj->client_nom;
			}

			if(empty($TFactures[$idFact]['divers'])) {
				$TFactures[$idFact]['divers'] = array();
				$TFactures[$idFact]['divers']['code_compta_tva'] = $conf->global->COMPTA_VAT_ACCOUNT;
			}
		}	
		
		return $TFactures;
	}

	/* 
	 * Récupération dans Dolibarr de la liste des factures fournisseur avec détails ligne + produit + fournisseur
	 * Toutes les factures validées, payées, abandonnées, pour l'entité concernée, avec date de facture entre les bornes sélectionnées
	 */
	function get_journal_achat(&$db, &$conf, $dt_deb, $dt_fin) {
		// Requête de récupération des factures
		$sql = "SELECT f.rowid as fact_rowid, f.facnumber as fact_facnumber, f.datef as fact_datef, f.date_lim_reglement as fact_date_lim_reglement, f.total_ttc as fact_total_ttc,"; 
		$sql.= " f.tva as fact_tva, f.type as fact_type,";
		$sql.= " fd.total_ht as ligne_total_ht, fd.tva as ligne_total_tva, fd.total_ttc as ligne_total_ttc,";
		$sql.= " p.accountancy_code_sell as prod_accountancy_code_sell, p.fk_product_type as prod_fk_product_type,";
		$sql.= " s.code_compta as client_code_compta, s.nom as client_nom";
		$sql.= " FROM llx_facture_fourn f";
		$sql.= " LEFT JOIN llx_facture_fourn_det fd ON fd.fk_facture_fourn = f.rowid";
		$sql.= " LEFT JOIN llx_product p ON p.rowid = fd.fk_product";
		$sql.= " LEFT JOIN llx_societe s ON s.rowid = f.fk_soc";
		$sql.= " WHERE f.datef BETWEEN '$dt_deb' AND '$dt_fin'";
		$sql.= " AND f.entity = {$conf->entity}";
		$sql.= " AND f.fk_statut IN (1,2,3)";
		$sql.= " AND fd.total_ht != 0";
		$sql.= " ORDER BY f.datef ASC";
		//echo $sql;
		$resql = $db->query($sql);
		
		// Construction du tableau de données
		$TFactures = array();
		while($obj = $db->fetch_object($resql)) {
			$idFact = $obj->fact_rowid;
			
			if(empty($TFactures[$idFact])) $TFactures[$idFact] = array();
			
			if(empty($TFactures[$idFact]['facture'])) {
				$TFactures[$idFact]['facture'] = array();
				$TFactures[$idFact]['facture']['facnumber'] = $obj->fact_facnumber;
				$TFactures[$idFact]['facture']['datef'] = $obj->fact_datef;
				$TFactures[$idFact]['facture']['date_lim_reglement'] = $obj->fact_date_lim_reglement;
				$TFactures[$idFact]['facture']['tva'] = $obj->fact_tva;
				$TFactures[$idFact]['facture']['total_ttc'] = $obj->fact_total_ttc;
				$TFactures[$idFact]['facture']['type'] = $obj->fact_type;
			}
			
			if(empty($TFactures[$idFact]['lignes'])) $TFactures[$idFact]['lignes'] = array();
			
			$codeComptableProduit = $obj->prod_accountancy_code_sell;
			if($codeComptableProduit == '') {
				if($obj->prod_fk_product_type == 0) {
					$codeComptableProduit = $conf->global->COMPTA_SERVICE_SOLD_ACCOUNT;
				} else {
					$codeComptableProduit = $conf->global->COMPTA_PRODUCT_SOLD_ACCOUNT;
				}
			}
			
			if(empty($TFactures[$idFact]['lignes'][$codeComptableProduit])) {
				$TFactures[$idFact]['lignes'][$codeComptableProduit] = array();
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['code_compta'] = $codeComptableProduit;
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['total_ht'] = floatval($obj->ligne_total_ht);
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['total_tva'] = floatval($obj->ligne_total_tva);
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['total_ttc'] = floatval($obj->ligne_total_ttc);
			} else {
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['total_ht'] += floatval($obj->ligne_total_ht);
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['total_tva'] += floatval($obj->ligne_total_tva);
				$TFactures[$idFact]['lignes'][$codeComptableProduit]['total_ttc'] += floatval($obj->ligne_total_ttc);
			}
			
			if(empty($TFactures[$idFact]['client'])) {
				$TFactures[$idFact]['client'] = array();
				$TFactures[$idFact]['client']['code_compta'] = $obj->client_code_compta == '' ? $conf->global->COMPTA_ACCOUNT_CUSTOMER : $obj->client_code_compta;
				$TFactures[$idFact]['client']['nom'] = $obj->client_nom;
			}

			if(empty($TFactures[$idFact]['divers'])) {
				$TFactures[$idFact]['divers'] = array();
				$TFactures[$idFact]['divers']['code_compta_tva'] = $conf->global->COMPTA_VAT_ACCOUNT;
			}
		}	
		
		return $TFactures;
	}

	/* 
	 * Récupération dans Dolibarr de la liste des règlements clients avec détails facture + ligne + produit + client
	 * Tous les règlement pour l'entité concernée, avec date de règlement entre les bornes sélectionnées
	 */
	function get_reglement_tiers(&$db, &$conf, $dt_deb, $dt_fin) {
		// Requête de récupération des règlements
		$sql = "SELECT r.amount as paiement_amount, r.fk_paiement as paiement_mode, r.datep as paiement_datep,"; 
		$sql.= " s.code_compta as client_code_compta, s.nom as client_nom";
		$sql.= " FROM llx_paiement r";
		$sql.= " LEFT JOIN llx_paiement_facture rf ON rf.fk_paiement = r.rowid";
		$sql.= " LEFT JOIN llx_facture f ON f.rowid = rf.fk_facture";
		$sql.= " LEFT JOIN llx_societe s ON s.rowid = f.fk_soc";
		$sql.= " WHERE r.datep BETWEEN '$dt_deb' AND '$dt_fin'";
		$sql.= " AND r.entity = {$conf->entity}";
		$sql.= " ORDER BY r.datep ASC";
		//echo $sql;
		$resql = $db->query($sql);
		
		// Construction du tableau de données
		$TReglements = array();
		while($obj = $db->fetch_object($resql)) {
			$rglt = array();
			
			$rglt['client'] = array(
				'code_compta' => $obj->client_code_compta == '' ? $conf->global->COMPTA_ACCOUNT_CUSTOMER : $obj->client_code_compta,
				'nom' => $obj->client_nom
			);
			
			$rglt['reglement'] = array(
				'amount' => $obj->paiement_amount,
				'mode' => $obj->paiement_mode,
				'datep' => $obj->paiement_datep
			);
			
			// Cas spécifique Axis : code comptables différents en fonction du mode de règlement
			// Voir si c'est systématique et proposer d'associer un code comptable à chaque mode de règlement
			if($rglt['reglement']['mode'] == 2)			$rglt['reglement']['code_compta'] = '58020000';
			else if($rglt['reglement']['mode'] == 7)	$rglt['reglement']['code_compta'] = '58010000';
			else if($rglt['reglement']['mode'] == 12)	$rglt['reglement']['code_compta'] = '58030000';
			else 										$rglt['reglement']['code_compta'] = '';
			
			$TReglements[] = $rglt;
		}	
		
		return $TReglements;
	}
	
	static function get_line(&$format, $ligneFichier) {
		/*$ligneFichierTxtFixe = '';
		foreach($ligneFichier as $i => $valeur) {
			if($valeur == '') $valeur = $format[$i]['default'];
			if($format[$i]['type'] == 'date') $valeur = date($format[$i]['format'], $valeur);
			if(strlen($valeur) < $format[$i]['length']) {
				$pad_string = ($format[$i]['default'] == '') ? ' ' : $format[$i]['default'];
				$valeur = str_pad($valeur, $format[$i]['length'], $pad_string, STR_PAD_LEFT);
			} else if(strlen($valeur) > $format[$i]['length']) {
				$valeur = substr($valeur, 0, $format[$i]['length']);
			}
			$ligneFichierTxtFixe .= $valeur;
		}
		return $ligneFichierTxtFixe;*/
		
		$ligneFichierTxtFixe = '';
		foreach($format as $fmt) {
			$valeur = empty($ligneFichier[$fmt['name']]) ? '' : $ligneFichier[$fmt['name']];
			if($valeur == '') $valeur = $fmt['default'];
			if($fmt['type'] == 'date' && !empty($valeur)) $valeur = date($fmt['format'], $valeur);
			if(strlen($valeur) < $fmt['length']) {
				$pad_string = ($fmt['default'] == '') ? ' ' : $fmt['default'];
				$valeur = str_pad($valeur, $fmt['length'], $pad_string, STR_PAD_LEFT);
			} else if(strlen($valeur) > $fmt['length']) {
				$valeur = substr($valeur, 0, $fmt['length']);
			}
			$ligneFichierTxtFixe .= $valeur;
		}
		return $ligneFichierTxtFixe;
	}
	
}

class TExportComptaQuadratus extends TExportCompta {
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