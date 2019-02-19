<?php 
/*************************************************************************************************************************************************
 * Classe permettant la récupération de données pour utilisation dans un export
 *  - EXPORT DES FACTURES VENTE
 *  - EXPORT DES REGLEMENTS
 *  - EXPORT DES FACTURES ACHAT + NOTE DE FRAIS
 *************************************************************************************************************************************************/

require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/cheque/class/remisecheque.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/client.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';

class TExportCompta extends TObjetStd {
	
	function __construct(&$db) {
		global $conf;
		
		$this->dt_deb = strtotime('first day of last month');
		$this->dt_fin = strtotime('last day of last month');
		
		$this->TLogiciel = array(
			'quadratus' => 'Quadratus'
			,'sage' => 'Sage'
		);
		$this->TDatesFacCli = array(
			'datef' => 'Date de facture' 
			,'date_valid' => 'Date de validation'
		);
		$this->TDatesFacFourn = array(
			'datef' => 'Date de facture' 
		);
		$this->TDatesNDF = array(
			'dates' => 'Date de début'
			,'datee' => 'Date de fin'
			,'date_valid' => 'Date de validation'
		);
		$this->TDatesBANK = array(
			'datec' => 'Date de création'
			,'dateo' => 'Date opération'
			,'datev' => 'Date de valeur'
		);
		
		$this->TTypeExport = array();
		if($conf->facture->enabled) $this->TTypeExport['ecritures_comptables_vente'] = 'Ecritures comptables vente';
		if($conf->fournisseur->enabled) $this->TTypeExport['ecritures_comptables_achat'] = 'Ecritures comptables achats';
		if($conf->ndfp->enabled) {
			dol_include_once('/ndfp/class/ndfp.class.php');
			$this->TTypeExport['ecritures_comptables_ndf'] = 'Ecritures comptables notes de frais';
		}
		if($conf->banque->enabled) $this->TTypeExport['ecritures_bancaires'] = 'Ecritures bancaires';
		
		// Requête de récupération des codes tva
		$this->TTVA = array();
		$sql = "SELECT t.fk_pays, t.taux, t.accountancy_code_sell, t.accountancy_code_buy";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_tva as t";

		$resql = $db->query($sql);
		
		while($obj = $db->fetch_object($resql)) {
			$this->TTVA[$obj->fk_pays][floatval($obj->taux)]['sell'] = $obj->accountancy_code_sell;
			$this->TTVA[$obj->fk_pays][floatval($obj->taux)]['buy'] = $obj->accountancy_code_buy;
		}
	}
	
	/* 
	 * Récupération dans Dolibarr de la liste des factures clients avec détails ligne + produit + client
	 * Toutes les factures validées, payées, abandonnées, pour l'entité concernée, avec date de facture entre les bornes sélectionnées
	 */
	function get_factures_client($dt_deb, $dt_fin) {
		global $db, $conf, $user;
		
		if(!$conf->facture->enabled) return array();
		
		$datefield=$conf->global->EXPORT_COMPTA_DATE_FACTURES_CLIENT;
		$allEntities=$conf->global->EXPORT_COMPTA_ALL_ENTITIES;

		$p = explode(":", $conf->global->MAIN_INFO_SOCIETE_COUNTRY);
		$idpays = $p[0];
		
		// Requête de récupération des factures
		$sql = "SELECT f.rowid, f.entity";
		$sql.= " FROM ".MAIN_DB_PREFIX."facture f";
		$sql.= " WHERE f.".$datefield." BETWEEN '$dt_deb' AND '$dt_fin'";
		if(!$allEntities) $sql.= " AND f.entity = {$conf->entity}";
		if(!empty($conf->global->FACTURE_DEPOSITS_ARE_JUST_PAYMENTS)) $sql.= " AND f.type <> 3";
		$sql.= " AND f.fk_statut IN (1,2,3)";
		$sql.= " ORDER BY f.".$datefield." ASC";
//echo $sql;exit;
		$resql = $db->query($sql);
		
		// Construction du tableau de données
		$TIdFactures = array();
		while($obj = $db->fetch_object($resql)) {
			$TIdFactures[] = array(
				'rowid' => $obj->rowid
				,'entity' => $obj->entity
			);
		}
		$trueEntity = $conf->entity;
		
		$i = 0;
		$TFactures = array();
		foreach($TIdFactures as $idFacture) {
			$conf->entity = $idFacture['entity'];
			$facture = new Facture($db);
			$facture->fetch($idFacture['rowid']);
			
			$TFactures[$facture->id] = array();
			$TFactures[$facture->id]['compteur']['piece'] = $i;
			
			// Récupération en-tête facture
			$TFactures[$facture->id]['facture'] = get_object_vars($facture);

			// Récupération client
			$facture->fetch_thirdparty();
			$TFactures[$facture->id]['tiers'] = get_object_vars($facture->thirdparty);
			
			// Récupération entity
			if($conf->multicompany->enabled) {
				$entity = new DaoMulticompany($db);
				$entity->fetch($idFacture['entity']);
				$TFactures[$facture->id]['entity'] = get_object_vars($entity);
			}
			
			// Définition des codes comptables
			$codeComptableClient = !empty($facture->thirdparty->code_compta) ? $facture->thirdparty->code_compta : $conf->global->COMPTA_ACCOUNT_CUSTOMER;
			
			// Récupération lignes de facture
			$facture->fetch_lines();
			foreach ($facture->lines as $ligne) {
				// Code compta produit
				$codeComptableProduit = ''; 
				if(!empty($ligne->fk_product)) {
					$produit = new Product($db);
					$produit->fetch($ligne->fk_product);
					$codeComptableProduit = $produit->accountancy_code_sell;
				}
				
				if(empty($codeComptableProduit)) {
					if($ligne->product_type == 0) {
						$codeComptableProduit = $conf->global->COMPTA_SERVICE_SOLD_ACCOUNT;
					} else if($ligne->product_type == 1) {
						$codeComptableProduit = $conf->global->COMPTA_PRODUCT_SOLD_ACCOUNT;
					} else {
						$codeComptableProduit = 'NOCODE';
					}
				}
				
				// Code compta TVA
				$codeComptableTVA = !empty($this->TTVA[$idpays][floatval($ligne->tva_tx)]['sell']) ? $this->TTVA[$idpays][floatval($ligne->tva_tx)]['sell'] : $conf->global->COMPTA_VAT_ACCOUNT;

				if(empty($TFactures[$facture->id]['ligne_tiers'][$codeComptableClient])) $TFactures[$facture->id]['ligne_tiers'][$codeComptableClient] = 0;
				if(empty($TFactures[$facture->id]['ligne_produit'][$codeComptableProduit])) $TFactures[$facture->id]['ligne_produit'][$codeComptableProduit] = 0;
				if(empty($TFactures[$facture->id]['ligne_tva'][$codeComptableTVA])) $TFactures[$facture->id]['ligne_tva'][$codeComptableTVA] = 0;
				$TFactures[$facture->id]['ligne_tiers'][$codeComptableClient] += $ligne->total_ttc;
				$TFactures[$facture->id]['ligne_produit'][$codeComptableProduit] += $ligne->total_ht;
				$TFactures[$facture->id]['ligne_tva'][$codeComptableTVA] += $ligne->total_tva;
			}
			
			$i++;
		}
		$conf->entity = $trueEntity;
		
		return $TFactures;
	}

	/* 
	 * Récupération dans Dolibarr de la liste des factures fournisseur avec détails ligne + produit + fournisseur
	 * Toutes les factures validées, payées, abandonnées, pour l'entité concernée, avec date entre les bornes sélectionnées
	 */
	function get_factures_fournisseur($dt_deb, $dt_fin) {
		global $db, $conf, $user;
		
		if(!$conf->fournisseur->enabled) return array();
		
		$datefield=$conf->global->EXPORT_COMPTA_DATE_FACTURES_FOURNISSEUR;
		$allEntities=$conf->global->EXPORT_COMPTA_ALL_ENTITIES;
		
		$p = explode(":", $conf->global->MAIN_INFO_SOCIETE_COUNTRY);
		$idpays = $p[0];
		
		// Requête de récupération des factures fournisseur
		$sql = "SELECT f.rowid, f.entity";
		$sql.= " FROM ".MAIN_DB_PREFIX."facture_fourn f";
		$sql.= " WHERE f.".$datefield." BETWEEN '$dt_deb' AND '$dt_fin'";
		if(!$allEntities) $sql.= " AND f.entity = {$conf->entity}";
		$sql.= " AND f.fk_statut IN (1,2,3)";
		$sql.= " ORDER BY f.".$datefield." ASC";

		$resql = $db->query($sql);
		
		// Construction du tableau de données
		$i = 0;
		$TFactures = array();
		while($obj = $db->fetch_object($resql)) {
			$facture = new FactureFournisseur($db);
			$facture->fetch($obj->rowid);
			
			$TFactures[$facture->id] = array();
			$TFactures[$facture->id]['compteur']['piece'] = $i;
			
			// Récupération en-tête facture
			$TFactures[$facture->id]['facture'] = get_object_vars($facture);

			// Récupération client
			$facture->fetch_thirdparty();
			$idpays = $facture->thirdparty->country_id;
			$TFactures[$facture->id]['tiers'] = get_object_vars($facture->thirdparty);
			
			// Récupération entity
			if($conf->multicompany->enabled) {
				$entity = new DaoMulticompany($db);
				$entity->fetch($obj->entity);
				$TFactures[$facture->id]['entity'] = get_object_vars($entity);
			}
			
			// Définition des codes comptables
			$codeComptableClient = !empty($facture->thirdparty->code_compta) ? $facture->thirdparty->code_compta : $conf->global->COMPTA_ACCOUNT_SUPPLIER;
			
			// Récupération lignes de facture
			$facture->fetch_lines();
			foreach ($facture->lines as $ligne) {
				// Code compta produit 
				if(!empty($ligne->fk_product)) {
					$produit = new Product($db);
					$produit->fetch($ligne->fk_product);
					$codeComptableProduit = $produit->accountancy_code_sell;
				}
				
				if(empty($codeComptableProduit)) {
					if($ligne->fk_product_type == 0) {
						$codeComptableProduit = $conf->global->COMPTA_SERVICE_BUY_ACCOUNT;
					} else if($ligne->fk_product_type == 1) {
						$codeComptableProduit = $conf->global->COMPTA_PRODUCT_BUY_ACCOUNT;
					}
				}
				
				// Code compta TVA
				$codeComptableTVA = !empty($this->TTVA[$idpays][floatval($ligne->tva_tx)]['buy']) ? $this->TTVA[$idpays][floatval($ligne->tva_tx)]['buy'] : $conf->global->COMPTA_VAT_ACCOUNT;

				if(empty($TFactures[$facture->id]['ligne_tiers'][$codeComptableClient])) $TFactures[$facture->id]['ligne_tiers'][$codeComptableClient] = 0;
				if(empty($TFactures[$facture->id]['ligne_produit'][$codeComptableProduit])) $TFactures[$facture->id]['ligne_produit'][$codeComptableProduit] = 0;
				if(empty($TFactures[$facture->id]['ligne_tva'][$codeComptableTVA])) $TFactures[$facture->id]['ligne_tva'][$codeComptableTVA] = 0;
				$TFactures[$facture->id]['ligne_tiers'][$codeComptableClient] += $ligne->total_ttc;
				$TFactures[$facture->id]['ligne_produit'][$codeComptableProduit] += $ligne->total_ht;
				$TFactures[$facture->id]['ligne_tva'][$codeComptableTVA] += $ligne->total_tva;
			}
			
			$i++;
		}
		
		return $TFactures;
	}

	/* 
	 * Récupération dans Dolibarr de la liste des notes de frais
	 */
	function get_notes_de_frais($dt_deb, $dt_fin) {
		global $db, $conf, $user;
		
		$ATMdb = new TPDOdb();
		$sql = 'SELECT rowid, accountancy_code FROM '.MAIN_DB_PREFIX.'c_ndfp_exp';
		$TCodesCompta = TRequeteCore::get_keyval_by_sql($ATMdb, $sql, 'rowid', 'accountancy_code');
		
		if(!$conf->ndfp->enabled) return array();
		
		$datefield=$conf->global->EXPORT_COMPTA_DATE_NDF;
		$allEntities=$conf->global->EXPORT_COMPTA_ALL_ENTITIES;
		
		$p = explode(":", $conf->global->MAIN_INFO_SOCIETE_COUNTRY);
		$idpays = $p[0];
		
		// Requête de récupération des notes de frais
		$sql = "SELECT n.rowid, n.entity";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp n";
		$sql.= " WHERE n.".$datefield." BETWEEN '$dt_deb' AND '$dt_fin'";
		if(!$allEntities) $sql.= " AND n.entity = {$conf->entity}";
		$sql.= " AND n.statut IN (1,2,3)";
		$sql.= " ORDER BY n.".$datefield." ASC";
		
		$resql = $db->query($sql);
		
		// Construction du tableau de données
		$TIdNDF = array();
		while($obj = $db->fetch_object($resql)) {
			$TIdNDF[] = array(
				'rowid' => $obj->rowid
				,'entity' => $obj->entity
			);
		}
		$trueEntity = $conf->entity;
		
		// Construction du tableau de données
		$i = 0;
		$TNDF = array();
		foreach($TIdNDF as $idNDF) {
			$conf->entity = $idNDF['entity']; // Le fetch ne marche pas si pas dans la bonne entity
			$ndfp = new Ndfp($db);
			$ndfp->fetch($idNDF['rowid']);
			$ndfp->fetch_lines();
			
			if(empty($ndfp->lines)) continue;
			
			$TNDF[$ndfp->id] = array();
			$TNDF[$ndfp->id]['compteur']['piece'] = $i;
			
			// Récupération en-tête facture
			$TNDF[$ndfp->id]['ndf'] = get_object_vars($ndfp);
			
			// Récupération client
			if($ndfp->fetch_thirdparty()) {
				$TNDF[$ndfp->id]['tiers'] = get_object_vars($ndfp->thirdparty);
			}
			
			// Récupération entity
			if($conf->multicompany->enabled) {
				$entity = new DaoMulticompany($db);
				$entity->fetch($idNDF['entity']);
				$TNDF[$ndfp->id]['entity'] = get_object_vars($entity);
			}
			
			// Définition des codes comptables
			$codeComptableClient = !empty($ndfp->thirdparty->code_compta) ? $ndfp->thirdparty->code_compta : $conf->global->COMPTA_ACCOUNT_SUPPLIER;
			
			// Récupération lignes de notes de frais
			
			foreach ($ndfp->lines as $ligne) {
				// Code compta produit 
				if(!empty($ligne->fk_exp)) {
					$codeComptableProduit = $TCodesCompta[$ligne->fk_exp];
				}
				
				if(empty($codeComptableProduit)) {
					$codeComptableProduit = $conf->global->COMPTA_EXP_ACCOUNT;
				}
				
				$codeComptableProduit = trim($codeComptableProduit);

				// Code compta TVA
				$codeComptableTVA = !empty($this->TTVA[$idpays][floatval($ligne->tva_tx)]['buy']) ? $this->TTVA[$idpays][floatval($ligne->tva_tx)]['buy'] : $conf->global->COMPTA_VAT_ACCOUNT;

				if(empty($TNDF[$ndfp->id]['ligne_tiers'][$codeComptableClient])) $TNDF[$ndfp->id]['ligne_tiers'][$codeComptableClient] = 0;
				if(empty($TNDF[$ndfp->id]['ligne_produit'][$codeComptableProduit])) $TNDF[$ndfp->id]['ligne_produit'][$codeComptableProduit] = 0;
				if(empty($TNDF[$ndfp->id]['ligne_tva'][$codeComptableTVA])) $TNDF[$ndfp->id]['ligne_tva'][$codeComptableTVA] = 0;
				$TNDF[$ndfp->id]['ligne_tiers'][$codeComptableClient] += $ligne->total_ttc;
				$TNDF[$ndfp->id]['ligne_produit'][$codeComptableProduit] += $ligne->total_ht;
				$TNDF[$ndfp->id]['ligne_tva'][$codeComptableTVA] += $ligne->total_tva;
				
				// Ticket 3313 : TVA spécifique pour 2 types de frais
				if($codeComptableProduit == '60709000') {
					$TNDF[$ndfp->id]['ligne_tva']['44526900'] += round($ligne->total_ht * .2,2);
					$TNDF[$ndfp->id]['ligne_tva']['44527900'] -= round($ligne->total_ht * .2,2);
				}
				if($codeComptableProduit == '60400000') {
					$TNDF[$ndfp->id]['ligne_tva']['44562200'] += round($ligne->total_ht * .2,2);
					$TNDF[$ndfp->id]['ligne_tva']['44571070'] -= round($ligne->total_ht * .2,2);
				}
			}
			
			$i++;
		}
		$conf->entity = $trueEntity;
		
		return $TNDF;
	}

	/* 
	 * Récupération dans Dolibarr de la liste des règlements clients avec détails facture + ligne + produit + client
	 * Tous les règlement pour l'entité concernée, avec date de règlement entre les bornes sélectionnées
	 */
	/*function get_reglement_tiers($dt_deb, $dt_fin) {
		global $db, $conf;

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
	}*/

	function get_banque($dt_deb, $dt_fin) {
		global $db, $conf, $user;
		
		if(!$conf->banque->enabled) return array();
		
		$datefield=$conf->global->EXPORT_COMPTA_DATE_BANK;
		$allEntities=$conf->global->EXPORT_COMPTA_ALL_ENTITIES;
		/*
        $sql = 'SELECT';
        $sql .= ' DATE_FORMAT(b.dateo, \'' . $this->date_format . '\') as date,';
        $sql .= ' t.libelle as op_type,';
        $sql .= ' b.num_chq as op_id,';
        $sql .= ' concat_ws(\'\', c.code_compta, s.code_compta_fournisseur) AS tp_account_code,';
        $sql .= ' ba.account_number as bank_account_code,';
        $sql .= ' b.label as bank_label,';
        $sql .= ' group_concat(concat_ws(\' \', soc.nom, f.facnumber, si.rowid) SEPARATOR \', \') as label,';
        $sql .= ' concat_ws(\' \', tc.libelle, ch.libelle) as payroll_label,';
        $sql .= ' b.amount as amount';
        $sql .= ' FROM llx_bank AS b';
        $sql .= ' JOIN llx_bank_account AS ba ON ba.rowid = b.fk_account';
        $sql .= ' JOIN llx_c_paiement AS t ON b.fk_type = t.code';
        $sql .= ' LEFT JOIN llx_paiement AS p ON p.fk_bank = b.rowid';
        $sql .= ' LEFT JOIN llx_paiement_facture AS pf ON pf.fk_paiement = p.rowid';
        $sql .= ' LEFT JOIN llx_facture AS f ON f.rowid = pf.fk_facture';
        $sql .= ' LEFT JOIN llx_paiementfourn AS sp ON sp.fk_bank = b.rowid';
        $sql .= ' LEFT JOIN llx_paiementfourn_facturefourn AS sip ON sip.fk_paiementfourn = sp.rowid';
        $sql .= ' LEFT JOIN llx_facture_fourn AS si ON si.rowid = sip.fk_facturefourn';
        $sql .= ' LEFT JOIN llx_paiementcharge AS pc ON  pc.fk_bank = b.rowid';
        $sql .= ' LEFT JOIN llx_chargesociales AS ch ON ch.rowid = pc.fk_charge';
        $sql .= ' LEFT JOIN llx_c_chargesociales AS tc ON ch.fk_type = tc.id';
        $sql .= ' LEFT JOIN llx_societe AS soc ON soc.rowid = f.fk_soc OR soc.rowid = si.fk_soc';
        $sql .= ' LEFT JOIN llx_societe AS c ON c.rowid = f.fk_soc';
        $sql .= ' LEFT JOIN llx_societe AS s ON s.rowid = si.fk_soc';
        $sql.= " WHERE n.".$datefield." BETWEEN '$dt_deb' AND '$dt_fin'";
        if(!$allEntities) $sql.= " AND n.entity = {$conf->entity} ";
        $sql .= ' AND ';
        $sql .= 'b.dateo >= \'' . $this->start_time . '\'';
        $sql .= ' AND ';
        $sql .= 'b.dateo <= \'' . $this->end_time . '\'';
        $sql .= ' GROUP BY';
        $sql .= ' b.rowid,';
        $sql .= ' b.dateo,';
        $sql .= ' t.libelle,';
        $sql .= ' b.num_chq,';
        $sql .= ' c.code_compta,';
        $sql .= ' s.code_compta_fournisseur,';
        $sql .= ' ba.account_number,';
        $sql .= ' b.label,';
        $sql .= ' tc.libelle,';
        $sql .= ' ch.libelle,';
        $sql .= ' b.amount';
        $sql .= ' ORDER BY date';
		*/
		// Requête de récupération des écritures bancaires
		// Spécifique Arcoop, on va chercher tout sauf les chèques qui sont traités par la suite pas bordereau de remise de chèque
		$sql = "SELECT b.rowid, p.entity, p.rowid as 'id_paiement'";
		$sql.= " FROM ".MAIN_DB_PREFIX."bank b";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."bank_account ba ON b.fk_account = ba.rowid";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."paiement p ON p.fk_bank = b.rowid";
		$sql.= " WHERE b.".$datefield." BETWEEN '$dt_deb' AND '$dt_fin'";
		$sql.= " AND p.fk_paiement <> 7";
		if(!$allEntities) $sql.= " AND p.entity = {$conf->entity}";
		$sql.= " ORDER BY b.".$datefield." ASC";
		
		//echo $sql;
		
		$resql = $db->query($sql);
		
		// Construction du tableau de données
		$TIdBank = array();
		while($obj = $db->fetch_object($resql)) {
			$TIdBank[] = array(
				'rowid' => $obj->rowid
				,'entity' => $obj->entity
				,'id_paiement' => $obj->id_paiement
			);
		}
		
		$i = 0;
		
		// Construction du tableau de données
		$TBank = array();
		foreach($TIdBank as $idBank) {
			$bankline = new AccountLine($db);
			$bankline->fetch($idBank['rowid']);
			$bankline->datev = $db->jdate($bankline->datev);
			
			$bank = new Account($db);
			$bank->fetch($bankline->fk_account);
			
			$links = $bank->get_url($bankline->id);
			foreach($links as $key => $val) {
				if($links[$key]['type'] == 'company') $client = $links[$key]['label'];
			}
			
			$TBank[$bankline->id] = array();
			
			// Récupération entity
			if($conf->multicompany->enabled) {
				$entity = new DaoMulticompany($db);
				$entity->fetch($idBank['entity']);
				$TBank[$bankline->id]['entity'] = get_object_vars($entity);
			}
			
			// Définition des codes comptables
			$codeComptableClient = 0;
			$codeComptableBank = !empty($bank->account_number) ? $bank->account_number : '51200000';
			
			$TBank[$bankline->id]['bank'] = get_object_vars($bank);
			$TBank[$bankline->id]['bankline'] = get_object_vars($bankline);
			$TBank[$bankline->id]['tiers'] = array('nom' => $client);
			
			if(empty($TBank[$bankline->id]['ligne_tiers'][$codeComptableClient])) $TBank[$bankline->id]['ligne_tiers'][$codeComptableClient] = 0;
			if(empty($TBank[$bankline->id]['ligne_banque'][$codeComptableBank])) $TBank[$bankline->id]['ligne_banque'][$codeComptableBank] = 0;
			$TBank[$bankline->id]['ligne_tiers'][$codeComptableClient] += $bankline->amount;
			$TBank[$bankline->id]['ligne_banque'][$codeComptableBank] += $bankline->amount;
		}

		// Requête de récupération des écritures bancaires (CHQ)
		// Spécifique Arcoop, on va chercher les remises de chèques et pour chacune la liste des chèques
		$sql = "SELECT bc.rowid";
		$sql.= " FROM ".MAIN_DB_PREFIX."bordereau_cheque bc";
		$sql.= " WHERE bc.date_bordereau BETWEEN '$dt_deb' AND '$dt_fin'";
		$sql.= " ORDER BY bc.rowid, bc.date_bordereau ASC";
		
		//echo $sql;
		
		$resql = $db->query($sql);
		
		// Construction du tableau de données
		$TIdRC = array();
		while($obj = $db->fetch_object($resql)) {
			$TIdRC[] = $obj->rowid;
		}
		
		$i = 0;
		
		// Construction du tableau de données
		foreach($TIdRC as $idRC) {
			$sql = "SELECT b.rowid, p.entity, p.rowid as 'id_paiement'";
			$sql.= " FROM ".MAIN_DB_PREFIX."bank b";
			$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."bank_account ba ON b.fk_account = ba.rowid";
			$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."paiement p ON p.fk_bank = b.rowid";
			$sql.= " WHERE b.fk_bordereau = ".$idRC;
			$sql.= " ORDER BY b.".$datefield." ASC";
			
			$resql = $db->query($sql);
			
			$TIdBank = array();
			while($obj = $db->fetch_object($resql)) {
				$TIdBank[] = array(
					'rowid' => $obj->rowid
					,'entity' => $obj->entity
					,'id_paiement' => $obj->id_paiement
				);
			}
			
			$bordereau = new RemiseCheque($db);
			$bordereau->fetch($idRC);
			
			foreach($TIdBank as $idBank) {
				$bankline = new AccountLine($db);
				$bankline->fetch($idBank['rowid']);
				$bankline->datev = $bordereau->date_bordereau;
				
				$bank = new Account($db);
				$bank->fetch($bankline->fk_account);
				
				$links = $bank->get_url($bankline->id);
				foreach($links as $key => $val) {
					if($links[$key]['type'] == 'company') $client = $links[$key]['label'];
				}
				
				$TBank[$bankline->id] = array();
				
				// Récupération entity
				if($conf->multicompany->enabled) {
					$entity = new DaoMulticompany($db);
					$entity->fetch($idBank['entity']);
					$TBank[$bankline->id]['entity'] = get_object_vars($entity);
				}
				
				// Définition des codes comptables
				$codeComptableClient = 0;
				$codeComptableBank = !empty($bank->account_number) ? $bank->account_number : '51200000';
				
				$TBank[$bankline->id]['bank'] = get_object_vars($bank);
				$TBank[$bankline->id]['bankline'] = get_object_vars($bankline);
				$TBank[$bankline->id]['tiers'] = array('nom' => $client);
				
				if(empty($TBank[$bankline->id]['ligne_tiers'][$codeComptableClient])) $TBank[$bankline->id]['ligne_tiers'][$codeComptableClient] = 0;
				$TBank[$bankline->id]['ligne_tiers'][$codeComptableClient] += $bankline->amount;
				$TBank[$bankline->id]['ligne_banque'] = array();
				$TBank[$bankline->id]['total_bordereau'] = $bordereau->amount;
			}

			$bankline->amount = $bordereau->amount;
			$TBank['RC'.$bordereau->id]['bank'] = get_object_vars($bank);
			$TBank['RC'.$bordereau->id]['bankline'] = get_object_vars($bankline);
			$TBank['RC'.$bordereau->id]['tiers'] = array('nom' => '('.$bordereau->number.' - '.date('d/m/Y',$bordereau->date_bordereau).')');
			$TBank['RC'.$bordereau->id]['ligne_banque'][$codeComptableBank] = $bordereau->amount;
			$TBank['RC'.$bordereau->id]['ligne_tiers'] = array();
		}
		
		return $TBank;
	}
	
	function get_line(&$format, $dataline) {		
		$ligneFichierTxtFixe = '';
		foreach($format as $fmt) {
			// Récupération valeur
			$valeur = '';
			if($fmt['type_value'] == 'data') {
				$valeur = $dataline[$fmt['name']];
			} else if($fmt['type_value'] == 'php') {
				$valeur = eval('return '.$fmt['value'].';');
			} else if($fmt['type_value'] == 'dur') {
				$valeur = $fmt['value'];
			}
			
			// Gestion du format de la valeur
			if($valeur == '') $valeur = $fmt['default'];
			if($fmt['type'] == 'date' && !empty($valeur) && !empty($fmt['format'])) $valeur = date($fmt['format'], $valeur);
			
			// Suppression de tous les caractères accentués pour compatibilités tous systèmes
			$valeur = $this->suppr_accents($valeur);
			
			// Ajout padding ou troncature
			if(strlen($valeur) < $fmt['length']) {
				$pad_string = ($fmt['default'] == '') ? ' ' : $fmt['default'];
				$pad_type = !empty($fmt['pad_type']) ? $fmt['pad_type'] : STR_PAD_LEFT;
				$valeur = str_pad($valeur, $fmt['length'], $pad_string, $pad_type);
			} else if(mb_strlen($valeur,'UTF-8') > $fmt['length']) {
				$valeur = substr($valeur, 0, $fmt['length']);
			}
			$ligneFichierTxtFixe .= $valeur;
		}
		return $ligneFichierTxtFixe;
	}
	
	/**
	 * Supprimer les accents
	 * 
	 * @param string $str chaîne de caractères avec caractères accentués
	 * @param string $encoding encodage du texte (exemple : utf-8, ISO-8859-1 ...)
	 */
	function suppr_accents($str, $encoding='utf-8')
	{
		// transformer les caractères accentués en entités HTML
		$str = htmlentities($str, ENT_NOQUOTES, $encoding);
	
		// remplacer les entités HTML pour avoir juste le premier caractères non accentués
		// Exemple : "&ecute;" => "e", "&Ecute;" => "E", "Ã " => "a" ...
		$str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
	
		// Remplacer les ligatures tel que : Œ, Æ ...
		// Exemple "Å“" => "oe"
		$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
		// Supprimer tout le reste
		$str = preg_replace('#&[^;]+;#', '', $str);
	
		return $str;
	}
}
?>
