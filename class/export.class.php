<?php 
/*************************************************************************************************************************************************
 * Classe permettant la récupération de données pour utilisation dans un export
 *  - EXPORT DES FACTURES VENTE
 *  - EXPORT DES REGLEMENTS
 *  - EXPORT DES FACTURES ACHAT + NOTE DE FRAIS
 *************************************************************************************************************************************************/

require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/client.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

class ExportCompta {
	
	function __construct(&$db) {
		$this->TTVA = array();
		
		// Requête de récupération des codes tva
		//$sql = "SELECT t.fk_pays, t.taux, t.accountancy_code_sell, t.accountancy_code_buy";
		$sql = "SELECT t.fk_pays, t.taux, t.accountancy_code";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_tva as t";
		
		$resql = $db->query($sql);
		
		while($obj = $db->fetch_object($resql)) {
			$this->TTVA[$obj->fk_pays][floatval($obj->taux)]['sell'] = $obj->accountancy_code;
			//$this->TTVA[$obj->fk_pays][floatval($obj->taux)]['buy'] = $obj->accountancy_code_buy;
		}
	}
	
	/* 
	 * Récupération dans Dolibarr de la liste des factures clients avec détails ligne + produit + client
	 * Toutes les factures validées, payées, abandonnées, pour l'entité concernée, avec date de facture entre les bornes sélectionnées
	 */
	function get_journal_ventes($dt_deb, $dt_fin) {
		global $db, $conf, $user;
		
		$datefield=$conf->global->EXPORT_COMPTA_DATE_VENTES;
		$allEntities=$conf->global->EXPORT_COMPTA_ALL_ENTITIES;
		
		$p = explode(":", $conf->global->MAIN_INFO_SOCIETE_PAYS);
		$idpays = $p[0];
		
		// Requête de récupération des factures
		$sql = "SELECT f.rowid, f.entity";
		$sql.= " FROM ".MAIN_DB_PREFIX."facture f";
		$sql.= " WHERE f.".$datefield." BETWEEN '$dt_deb' AND '$dt_fin'";
		if(!$allEntities) $sql.= " AND f.entity = {$conf->entity}";
		$sql.= " AND f.fk_statut IN (1,2,3)";
		$sql.= " ORDER BY f.".$datefield." ASC";

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
			$facture->fetch($idFacture['rowid']); // TODO : le fetch ne fonctionnera pas pour du multi entité
			
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
				if(!empty($ligne->fk_product)) {
					$produit = new Product($db);
					$produit->fetch($ligne->fk_product);
					$codeComptableProduit = $produit->accountancy_code_sell;
				}
				
				if(empty($codeComptableProduit)) {
					if($ligne->fk_product_type == 0) {
						$codeComptableProduit = $conf->global->COMPTA_SERVICE_SOLD_ACCOUNT;
					} else if($ligne->fk_product_type == 1) {
						$codeComptableProduit = $conf->global->COMPTA_PRODUCT_SOLD_ACCOUNT;
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
	function get_journal_achats($dt_deb, $dt_fin) {
		global $db, $conf, $user;
		
		$datefield=$conf->global->EXPORT_COMPTA_DATE_ACHATS;
		$allEntities=$conf->global->EXPORT_COMPTA_ALL_ENTITIES;
		
		// Requête de récupération des factures
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
	 * Récupération dans Dolibarr de la liste des règlements clients avec détails facture + ligne + produit + client
	 * Tous les règlement pour l'entité concernée, avec date de règlement entre les bornes sélectionnées
	 */
	function get_reglement_tiers($dt_deb, $dt_fin) {
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
	}
	
	function get_line(&$format, $dataline) {		
		$ligneFichierTxtFixe = '';
		foreach($format as $fmt) {
			$valeur = '';
			if($fmt['type_value'] == 'data') {
				$valeur = $dataline[$fmt['name']];
			} else if($fmt['type_value'] == 'php') {
				$valeur = eval('return '.$fmt['value'].';');
			} else if($fmt['type_value'] == 'dur') {
				$valeur = $fmt['value'];
			}
			
			if($valeur == '') $valeur = $fmt['default'];
			if($fmt['type'] == 'date' && !empty($valeur) && !empty($fmt['format'])) $valeur = date($fmt['format'], $valeur);
			if(mb_strlen($valeur, 'UTF-8') < $fmt['length']) {
				$pad_string = ($fmt['default'] == '') ? ' ' : $fmt['default'];
				$pad_type = !empty($fmt['pad_type']) ? $fmt['pad_type'] : STR_PAD_LEFT;
				$valeur = str_pad($valeur, $fmt['length']+(strlen($valeur)-mb_strlen($valeur)), $pad_string, $pad_type);
			} else if(mb_strlen($valeur,'UTF-8') > $fmt['length']) {
				$valeur = mb_substr($valeur, 0, $fmt['length'],'UTF-8');
			}
			$ligneFichierTxtFixe .= $valeur;
		}
		return $ligneFichierTxtFixe;
	}
}
?>