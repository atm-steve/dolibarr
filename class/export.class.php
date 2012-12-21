<?php 
/*************************************************************************************************************************************************
 * Classe permettant la récupération de données pour utilisation dans un export
 *  - EXPORT DES FACTURES VENTE
 *  - EXPORT DES REGLEMENTS
 *  - EXPORT DES FACTURES ACHAT + NOTE DE FRAIS
 *************************************************************************************************************************************************/
class ExportCompta {	
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
?>