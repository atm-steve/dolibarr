<?php 
/*************************************************************************************************************************************************
 * Classe permettant la récupération de données pour utilisation dans un export
 *  - EXPORT DES FACTURES VENTE
 *  - EXPORT DES FACTURES ACHAT
 *  - EXPORT DES NOTE DE FRAIS
 *  - EXPORT DES ÉCRITURES BANCAIRES
 *  - EXPORT DES REGLEMENTS
 *  - EXPORT DES TIERS
 *  - EXPORT DES PRODUITS 
 *************************************************************************************************************************************************/

dol_include_once('/compta/facture/class/facture.class.php');
dol_include_once('/compta/paiement/cheque/class/remisecheque.class.php');
dol_include_once('/fourn/class/fournisseur.facture.class.php');
dol_include_once('/societe/class/client.class.php');
dol_include_once('/product/class/product.class.php');
dol_include_once('/compta/bank/class/account.class.php');
dol_include_once('/compta/sociales/class/chargesociales.class.php');
dol_include_once('/compta/prelevement/class/bonprelevement.class.php');

class TExportCompta extends TObjetStd {
	
	function __construct(&$db, $exportAllreadyExported = false, $addExportTime = false) {
		global $conf;
		
		$this->db = $db;
		
		$this->dt_deb = strtotime('first day of last month');
		$this->dt_fin = strtotime('last day of last month');
		
		$this->exportAllreadyExported = $exportAllreadyExported;
		$this->addExportTime=$addExportTime;
		
		$this->TLogiciel = array(
			'quadratus' => 'Quadratus'
			,'sage' => 'Sage'
			,'sage30' => 'Sage 30'
			,'ciel' => 'Ciel'
			,'opensi' => 'Open SI'
			,'etag' => 'eTag'
			,'cegid' => 'Cegid'
			,'ebp' => 'EBP'
		);
		$this->TDatesFacCli = array(
			'datef' => 'Date de facture' 
			,'date_valid' => 'Date de validation'
		);
		$this->TDatesFacFourn = array(
			'datef' => 'Date de facture'
			,'datec' => 'Date de création'
		);
		$this->TDatesBank = array(
			'datev' => 'Date de valeur'
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
		if(!empty($conf->facture->enabled)) $this->TTypeExport['ecritures_comptables_vente'] = 'Ecritures comptables vente';
		if(!empty($conf->fournisseur->enabled)) $this->TTypeExport['ecritures_comptables_achat'] = 'Ecritures comptables achats';
		if(!empty($conf->banque->enabled)) $this->TTypeExport['ecritures_comptables_banque'] = 'Écritures comptables banque';
		if(!empty($conf->ndfp->enabled)) {
			dol_include_once('/ndfp/class/ndfp.class.php');
			$this->TTypeExport['ecritures_comptables_ndf'] = 'Ecritures comptables notes de frais';
		}
		if(!empty($conf->facture->enabled)) $this->TTypeExport['reglement_tiers'] = 'Règlements tiers';
		
		$this->TTypeExport['tiers'] = 'Tiers';
		$this->TTypeExport['produits'] = 'Produits';

		$this->fieldSeparator=$conf->global->EXPORT_COMPTA_DATASEPARATOR;
		$this->fieldPadding=empty($conf->global->EXPORT_COMPTA_DATASEPARATOR) ? true : false;
		
		$this->init_plan_comptable();
	}

	/*
	 * Initialisation d'un tableau qui contient tous les codes compta (tiers, produits, charges, TVA, etc.)
	 */
	function init_plan_comptable() {
		$this->TPlanComptable = array();
				
		// Requête de récupération des codes tva
		$this->TTVA = array();
		$sql = "SELECT t.fk_pays, t.taux, t.accountancy_code_sell, t.accountancy_code_buy";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_tva as t WHERE active=1";
		
		$resql = $this->db->query($sql);
		
		while($obj = $this->db->fetch_object($resql)) {
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
		$sql.= " FROM ".MAIN_DB_PREFIX."facture f LEFT JOIN ".MAIN_DB_PREFIX."facture_extrafields fex ON (fex.fk_object=f.rowid)";
		$sql.= " WHERE f.".$datefield." BETWEEN '$dt_deb' AND '$dt_fin'";
		if(!$allEntities) $sql.= " AND f.entity = {$conf->entity}";
		if(!empty($conf->global->FACTURE_DEPOSITS_ARE_JUST_PAYMENTS)) $sql.= " AND f.type <> 3";
		$sql.= " AND f.fk_statut IN (1,2)";
		if(!empty($conf->global->EXPORT_COMPTA_FACT_CLI_FILTER)) {
			$sql.= " AND f.facnumber LIKE '".$conf->global->EXPORT_COMPTA_FACT_CLI_FILTER."'";
		}
		
		if(!$this->exportAllreadyExported) {
			$sql.=" AND fex.date_compta IS NULL "; 
		}
		
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
			$facture->fetch($idFacture['rowid']);
			
			if($this->addExportTime) {
				$facture->array_options['options_date_compta'] = time(); 
				$facture->insertExtraFields();
				
			}
			
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
				if($ligne->special_code != 0) continue;
				if($ligne->total_ht == 0) continue;
				
				// Code compta produit
				$codeComptableProduit = ''; 
				if(!empty($ligne->fk_product)) {
					$produit = new Product($db);
					$produit->fetch($ligne->fk_product);
					$produit->fetch_optionals($ligne->fk_product);
					
					// Cas des DOM-TOM
					if($facture->thirdparty->country_code == 'PM'
							|| $facture->thirdparty->country_code == 'BL'
							|| $facture->thirdparty->country_code == 'SM'
							|| $facture->thirdparty->country_code == 'WF'
							|| $facture->thirdparty->country_code == 'PF'
							|| $facture->thirdparty->country_code == 'NC'
							|| ($facture->thirdparty->country_code == 'FR' && substr($facture->thirdparty->state_code, 0, 2) == '97'))
					{
						$codeComptableProduit = $produit->array_options['options_'.$conf->global->EXPORT_COMPTA_PRODUCT_FR_DOM_FIELD];
					}
					// Cas de la France
					else if($facture->thirdparty->country_code == 'FR') {
						// Client en france, code compta standard du produit ok
						$codeComptableProduit = $produit->accountancy_code_sell;
						
						// Cas de la société française exonérée
						if($facture->thirdparty->tva_assuj == 0) {
							$codeComptableProduit = $produit->array_options['options_'.$conf->global->EXPORT_COMPTA_PRODUCT_FR_SUSP_FIELD];
						}
					}
					// Cas de la vente CEE
					else if($facture->thirdparty->isInEEC()) { 
						$codeComptableProduit = $produit->array_options['options_'.$conf->global->EXPORT_COMPTA_PRODUCT_CEE_FIELD];
					}
					// Cas de la vente Export
					else {
						$codeComptableProduit = $produit->array_options['options_'.$conf->global->EXPORT_COMPTA_PRODUCT_EXPORT_FIELD];
					}
					
					// Sécurité au cas où non utilisation des comptes différents domtom, cee, export.
					if(empty($codeComptableProduit)) $codeComptableProduit = $produit->accountancy_code_sell;
				}

				// Compte spécifique pour les remises
				if(empty($codeComptableProduit)) {
					if($ligne->fk_remise_except !== 0) {
						$codeComptableProduit = $conf->global->EXPORT_COMPTA_REMISE;
					}
				}
				
				if(empty($codeComptableProduit)) {
					if($ligne->product_type == 0) {
						$codeComptableProduit = $conf->global->COMPTA_SERVICE_SOLD_ACCOUNT;
					} else if($ligne->product_type == 1) {
						$codeComptableProduit = $conf->global->COMPTA_PRODUCT_SOLD_ACCOUNT;
					}/* else {
						$codeComptableProduit = 'NOCODE';
					} Milestone ! */
				}
                
                if(!empty($facture->thirdparty->array_options['options_code_tva'])) {
                    $codeComptableTVA  = $facture->thirdparty->array_options['options_code_tva'];
                }
                else if(!empty($produit->array_options['options_code_tva'])) {
                    $codeComptableTVA  = $produit->array_options['options_code_tva'];
                }
                else{
				    // Code compta TVA
				    $codeComptableTVA = !empty($this->TTVA[$idpays][floatval($ligne->tva_tx)]['sell']) ? $this->TTVA[$idpays][floatval($ligne->tva_tx)]['sell'] : $conf->global->COMPTA_VAT_ACCOUNT;
                }
                
				if(empty($TFactures[$facture->id]['ligne_tiers'][$codeComptableClient])) $TFactures[$facture->id]['ligne_tiers'][$codeComptableClient] = 0;
				if(empty($TFactures[$facture->id]['ligne_produit'][$codeComptableProduit])) $TFactures[$facture->id]['ligne_produit'][$codeComptableProduit] = 0;
				if(empty($TFactures[$facture->id]['ligne_tva'][$codeComptableTVA]) && $ligne->total_tva > 0) $TFactures[$facture->id]['ligne_tva'][$codeComptableTVA] = 0;
				$TFactures[$facture->id]['ligne_tiers'][$codeComptableClient] += $ligne->total_ttc;
				$TFactures[$facture->id]['ligne_produit'][$codeComptableProduit] += $ligne->total_ht;
				if($ligne->total_tva != 0) $TFactures[$facture->id]['ligne_tva'][$codeComptableTVA] += $ligne->total_tva;
			}
			
			$i++;
		}
		$conf->entity = $trueEntity;
		
        TExportCompta::equilibreFacture($TFactures);
       
		return $TFactures;
	}

    static function equilibreFacture(&$TFactures) {
        
        foreach ($TFactures as $id_facture => &$infosFacture) {

            $montant_facture = 0;
            if(!empty($infosFacture['ligne_tiers'])) {
                foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
                    $montant_facture+=number_format($montant,2,'.','');
                }
                
            }
            
            $montant_produit = 0;
            // Lignes de produits
             if(!empty($infosFacture['ligne_produit'])) {
                foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
                        $montant_produit+=number_format($montant,2,'.','');
                }
             }
            
            $montant_tva=0;$cpt_tva=1;
            
            if(!empty($infosFacture['ligne_tva'])) {
                $nb_tva = count($infosFacture['ligne_tva']);
                // Lignes TVA
                foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
                	
                    if($cpt_tva == $nb_tva) {
                    	$montant = $montant_facture-$montant_produit-$montant_tva;
						$infosFacture['ligne_tva'][$code_compta] = $montant;
					}
                    // Ecriture générale
                    $cpt_tva++;
                    $montant_tva+=round($montant,2);
    
                }
                
            }
        	      
            
        }
        
		
		
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
		
		$p = explode(":", $conf->global->MAIN_INFO_SOCIETE_PAYS);
		$idpays = $p[0];
		
		// Requête de récupération des factures fournisseur
		$sql = "SELECT f.rowid, f.entity";
		$sql.= " FROM ".MAIN_DB_PREFIX."facture_fourn f LEFT JOIN ".MAIN_DB_PREFIX."facture_fourn_extrafields fex ON (fex.fk_object=f.rowid)";
		$sql.= " WHERE f.".$datefield." BETWEEN '$dt_deb' AND '$dt_fin'";
		if(!$allEntities) $sql.= " AND f.entity = {$conf->entity}";
		$sql.= " AND f.fk_statut IN (1,2,3)";

		if(!$this->exportAllreadyExported) {
			$sql.=" AND fex.date_compta IS NULL "; 
		}
		
		$sql.= " ORDER BY f.".$datefield." ASC";

		$resql = $db->query($sql);
		
		// Construction du tableau de données
		$i = 0;
		$TFactures = array();
		while($obj = $db->fetch_object($resql)) {
			$facture = new FactureFournisseur($db);
			$facture->fetch($obj->rowid);

			$facture->date_lim_reglement = $facture->date_echeance;

			if($this->addExportTime) {			
				 $facture->array_options['options_date_compta'] = time(); 
				 $facture->insertExtraFields();
			}
			
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
			$codeComptableFournisseur = !empty($facture->thirdparty->code_compta_fournisseur) ? $facture->thirdparty->code_compta_fournisseur : $conf->global->COMPTA_ACCOUNT_SUPPLIER;
			
			// Récupération lignes de facture
			$facture->fetch_lines();
			foreach ($facture->lines as $ligne) {
				// Code compta produit 
				if(!empty($ligne->fk_product)) {
					$produit = new Product($db);
					$produit->fetch($ligne->fk_product);
					
					// Cas des DOM-TOM
					if($facture->thirdparty->country_code == 'PM'
							|| $facture->thirdparty->country_code == 'BL'
							|| $facture->thirdparty->country_code == 'SM'
							|| $facture->thirdparty->country_code == 'WF'
							|| $facture->thirdparty->country_code == 'PF'
							|| $facture->thirdparty->country_code == 'NC'
							|| ($facture->thirdparty->country_code == 'FR' && substr($facture->thirdparty->state_code, 0, 2) == '97'))
					{
						$codeComptableProduit = $produit->array_options['options_'.$conf->global->EXPORT_COMPTA_PRODUCT_FR_DOM_FIELD_BUYING];
					}
					// Cas de la France
					else if($facture->thirdparty->country_code == 'FR') {
						// Client en france, code compta standard du produit ok
						$codeComptableProduit = $produit->accountancy_code_buy;
						
						// Cas de la société française exonérée
						if($facture->thirdparty->tva_assuj == 0) {
							$codeComptableProduit = $produit->array_options['options_'.$conf->global->EXPORT_COMPTA_PRODUCT_FR_SUSP_FIELD_BUYING];
						}
					}
					// Cas de la vente CEE
					else if($facture->thirdparty->isInEEC()) { 
						$codeComptableProduit = $produit->array_options['options_'.$conf->global->EXPORT_COMPTA_PRODUCT_CEE_FIELD_BUYING];
					}
					// Cas de la vente Export
					else {
						$codeComptableProduit = $produit->array_options['options_'.$conf->global->EXPORT_COMPTA_PRODUCT_EXPORT_FIELD_BUYING];
					}
					
					// Sécurité au cas où non utilisation des comptes différents domtom, cee, export.
					if(empty($codeComptableProduit)) $codeComptableProduit = $produit->accountancy_code_buy;
				}
				
				if(empty($codeComptableProduit)) {
					if($ligne->fk_product_type == 0) {
						$codeComptableProduit = $conf->global->COMPTA_SERVICE_BUY_ACCOUNT;
					} else if($ligne->fk_product_type == 1) {
						$codeComptableProduit = $conf->global->COMPTA_PRODUCT_BUY_ACCOUNT;
					}
				}
				
				// Code compta TVA
				
				if(!empty($facture->thirdparty->array_options['options_code_tva_achat'])) {
				    $codeComptableTVA  = $facture->thirdparty->array_options['options_code_tva_achat'];
				}
                else if(!empty($produit->array_options['options_code_tva_achat'])) {
                    $codeComptableTVA  = $produit->array_options['options_code_tva_achat'];
				}
                else{
                    $codeComptableTVA = !empty($this->TTVA[$idpays][floatval($ligne->tva_tx)]['buy']) ? $this->TTVA[$idpays][floatval($ligne->tva_tx)]['buy'] : $conf->global->COMPTA_VAT_ACCOUNT;    

                    // Spécifique Travail Associé : si facture réglé, mettre TVA dans un autre compte
                    if($facture->paye == 1) $codeComptableTVA = '44566200';
                }
				

				if(empty($TFactures[$facture->id]['ligne_tiers'][$codeComptableFournisseur])) $TFactures[$facture->id]['ligne_tiers'][$codeComptableFournisseur] = 0;
				if(empty($TFactures[$facture->id]['ligne_produit'][$codeComptableProduit])) $TFactures[$facture->id]['ligne_produit'][$codeComptableProduit] = 0;
				if(empty($TFactures[$facture->id]['ligne_tva'][$codeComptableTVA]) && $ligne->total_tva > 0) $TFactures[$facture->id]['ligne_tva'][$codeComptableTVA] = 0;
				$TFactures[$facture->id]['ligne_tiers'][$codeComptableFournisseur] += $ligne->total_ttc;
				$TFactures[$facture->id]['ligne_produit'][$codeComptableProduit] += $ligne->total_ht;
				if($ligne->total_tva != 0) $TFactures[$facture->id]['ligne_tva'][$codeComptableTVA] += $ligne->total_tva;
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
		$sql = 'SELECT rowid, accountancy_code FROM '.MAIN_DB_PREFIX.'c_exp';
		$TCodesCompta = TRequeteCore::get_keyval_by_sql($ATMdb, $sql, 'rowid', 'accountancy_code');
		
		if(!$conf->ndfp->enabled) return array();
		
		$datefield=$conf->global->EXPORT_COMPTA_DATE_NDF;
		$allEntities=$conf->global->EXPORT_COMPTA_ALL_ENTITIES;
		
		$p = explode(":", $conf->global->MAIN_INFO_SOCIETE_PAYS);
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
			
			// Récupération en-tête ndf
			$TNDF[$ndfp->id]['ndf'] = get_object_vars($ndfp);
			
			// Récupération client
			if($ndfp->fetch_thirdparty()) {
				$TNDF[$ndfp->id]['tiers'] = get_object_vars($ndfp->thirdparty);
			}
			
			// Récupération user
			if($ndfp->fetch_user($ndfp->fk_user)) {
				$TNDF[$ndfp->id]['user'] = get_object_vars($ndfp->user);
			}
			
			// Récupération entity
			if($conf->multicompany->enabled) {
				$entity = new DaoMulticompany($db);
				$entity->fetch($idNDF['entity']);
				$TNDF[$ndfp->id]['entity'] = get_object_vars($entity);
			}
			
			// Définition des codes comptables
			$codeComptableClient = !empty($ndfp->thirdparty->code_compta) ? $ndfp->thirdparty->code_compta : $conf->global->COMPTA_ACCOUNT_SUPPLIER;
			$codeCompta = $ndfp->user->array_options['options_COMPTE_TIERS'];
			
			// Récupération lignes de notes de frais
			
			foreach ($ndfp->lines as $ligne) {
				// Code compta produit 
				if(!empty($ligne->fk_exp)) {
					$codeComptableProduit = $TCodesCompta[$ligne->fk_exp];
				}
				
				if(empty($codeComptableProduit)) {
					$codeComptableProduit = $conf->global->COMPTA_EXP_ACCOUNT;
				}
				
				// Code compta TVA
				$codeComptableTVA = !empty($this->TTVA[$idpays][floatval($ligne->tva_tx)]['buy']) ? $this->TTVA[$idpays][floatval($ligne->tva_tx)]['buy'] : $conf->global->COMPTA_VAT_BUY_ACCOUNT;

				if(empty($TNDF[$ndfp->id]['ligne_tiers'][$codeCompta])) $TNDF[$ndfp->id]['ligne_tiers'][$codeCompta] = 0;
				if(empty($TNDF[$ndfp->id]['ligne_produit'][$codeComptableProduit])) $TNDF[$ndfp->id]['ligne_produit'][$codeComptableProduit] = 0;
				if(empty($TNDF[$ndfp->id]['ligne_tva'][$codeComptableTVA]) && $ligne->total_tva > 0) $TNDF[$ndfp->id]['ligne_tva'][$codeComptableTVA] = 0;
				$TNDF[$ndfp->id]['ligne_tiers'][$codeCompta] += $ligne->total_ttc;
				$TNDF[$ndfp->id]['ligne_produit'][$codeComptableProduit] += $ligne->total_ht;
				if($ligne->total_tva != 0) $TNDF[$ndfp->id]['ligne_tva'][$codeComptableTVA] += $ligne->total_tva;
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
	function get_reglement_tiers($dt_deb, $dt_fin) {
		global $db, $conf;

		// Requête de récupération des règlements
		$sql = "SELECT r.rowid, f.facnumber num_fact, r.amount as paiement_amount, r.fk_paiement as paiement_mode, r.datep as paiement_datep,"; 
		$sql.= " s.code_compta as client_code_compta, s.nom as client_nom, ba.account_number";
		$sql.= " FROM llx_paiement r";
		$sql.= " LEFT JOIN llx_paiement_facture rf ON rf.fk_paiement = r.rowid";
		$sql.= " LEFT JOIN llx_facture f ON f.rowid = rf.fk_facture";
		$sql.= " LEFT JOIN llx_societe s ON s.rowid = f.fk_soc";
		$sql.= " LEFT JOIN llx_bank bank ON bank.rowid = r.fk_bank";
		$sql.= " LEFT JOIN llx_bank_account ba ON ba.rowid = bank.fk_account";
		$sql.= " WHERE r.datep BETWEEN '$dt_deb' AND '$dt_fin'";
		$sql.= " AND r.entity = {$conf->entity}";
		$sql.= " GROUP BY r.rowid
					ORDER BY r.datep ASC 
				 ";
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
				'datep' => $obj->paiement_datep,
				'num_fact' => $obj->num_fact
			);

			$rglt['reglement']['code_compta'] = $obj->account_number;
			
			$TReglements[] = $rglt;
		}	
		
		return $TReglements;
	}
	function get_produits($dt_deb, $dt_fin) {
		global $db, $conf, $user;
	
		$sql="SELECT p.label, p.accountancy_code_sell, p.accountancy_code_buy
		FROM ".MAIN_DB_PREFIX."product p
		WHERE p.tms BETWEEN '".$dt_deb."' AND '".$dt_fin."'";
	
		$resql = $db->query($sql);
		// Construction du tableau de données
		$TProd = array();
		while($obj = $db->fetch_object($resql)) {
			
			$row=get_object_vars($obj);
			
			$code = $obj->accountancy_code_sell;
			
			$TProd[$code] = $row;
			
		}	
			
		return $TProd;
		
	}
	function get_tiers($dt_deb, $dt_fin) {
		global $db, $conf, $user;
	
		if((float)DOL_VERSION>=3.7) {
			$sql="SELECT s.nom,s.code_client,s.code_fournisseur,s.code_compta,s.code_compta_fournisseur, s.address, s.zip, s.fournisseur
			, s.town,s.phone,s.fax,s.email,s.tms,rglt.code as mode_reglement_code,p.label as 'pays',s.siret, rib.label as 'rib_label', rib.code_banque
			, rib.code_guichet, rib.number as 'compte_bancaire', rib.cle_rib, rib.bic, rib.iban_prefix as 'iban', rib.domiciliation, rib.proprio as 'rib_proprio'
			, ex.fk_soc_affacturage
			FROM ".MAIN_DB_PREFIX."societe s 
			LEFT JOIN ".MAIN_DB_PREFIX."societe_rib rib ON (s.rowid=rib.fk_soc AND rib.default_rib=1)
            LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields ex ON (s.rowid=ex.fk_object)
            LEFT JOIN ".MAIN_DB_PREFIX."c_country p ON (s.fk_pays=p.rowid)
			LEFT JOIN ".MAIN_DB_PREFIX."c_paiement rglt ON (s.mode_reglement=rglt.id)
			WHERE s.tms BETWEEN '".$dt_deb."' AND '".$dt_fin."'";
			
			
		}
		else {
			$sql="SELECT s.nom,s.code_client,s.code_fournisseur,s.code_compta,s.code_compta_fournisseur, s.address, s.zip
			, s.town,s.phone,s.fax,s.email,s.tms,rglt.code as mode_reglement_code,p.libelle as 'pays',s.siret, rib.label as 'rib_label', rib.code_banque
			, rib.code_guichet, rib.number as 'compte_bancaire', rib.cle_rib, rib.bic, rib.iban_prefix as 'iban', rib.domiciliation, rib.proprio as 'rib_proprio'
			FROM ".MAIN_DB_PREFIX."societe s 
			LEFT JOIN ".MAIN_DB_PREFIX."societe_rib rib ON (s.rowid=rib.fk_soc AND rib.default_rib=1)
			LEFT JOIN ".MAIN_DB_PREFIX."c_pays p ON (s.fk_pays=p.rowid)
			LEFT JOIN ".MAIN_DB_PREFIX."c_paiement rglt ON (s.mode_reglement=rglt.id)
			WHERE s.tms BETWEEN '".$dt_deb."' AND '".$dt_fin."'";
			
		}
	
		$resql = $db->query($sql);
		
		// Construction du tableau de données
		$TTier = array();
		while($obj = $db->fetch_object($resql)) {
				
			$obj->address = strtr($obj->address, array("\n"=>' ',"\r"=>''));
			
			if($obj->fk_soc_affacturage>0) {
                 $saffac = new Societe($db);
                 $saffac->fetch($obj->fk_soc_affacturage);
               
                 $obj->code_client_affacturage = $saffac->code_client;
                 $obj->code_fournisseur_affacturage = $saffac->code_fournisseur;

                 $obj->code_compta_affacturage = $saffac->code_compta;
                 $obj->code_compta_fournisseur_affacturage = $saffac->code_compta_fournisseur;
                 
  			}
            else{
                 $obj->code_client_affacturage = '';
                 $obj->code_fournisseur_affacturage = '';
                 $obj->code_compta_affacturage ='';
                 $obj->code_compta_fournisseur_affacturage ='';
            }
            
			$row=get_object_vars($obj);
			
			$code = $obj->code_compta;
			$code_fournisseur = $obj->code_compta_fournisseur;	
			
			if(!empty($code) && !is_null($code)){
				$row['client'] = 1;
				$row['fournisseur'] = 0;
				$TTier[$code] = $row ;
			}
			if(!empty($code_fournisseur) && !is_null($code)){
				$row['client'] = 0;
				$row['fournisseur'] = 1;
				$TTier[$code_fournisseur] = $row;
			} 
			
		}	
			
		return $TTier;
		
	}

	function get_banque($dt_deb, $dt_fin) {
		global $db, $conf, $user;
		
		if(!$conf->banque->enabled) return array();
		
		$datefield=$conf->global->EXPORT_COMPTA_DATE_BANK;
		$allEntities=$conf->global->EXPORT_COMPTA_ALL_ENTITIES;
		
		// Requête de récupération des écritures bancaires
		$sql = "SELECT b.rowid, ba.entity";
		$sql.= " FROM ".MAIN_DB_PREFIX."bank b";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."bank_account ba ON b.fk_account = ba.rowid";
		$sql.= " WHERE b.".$datefield." BETWEEN '$dt_deb' AND '$dt_fin'";
		if(!$allEntities) $sql.= " AND ba.entity = {$conf->entity}";
		$sql.= " ORDER BY b.".$datefield." ASC";
		
		//echo $sql;
		
		$resql = $db->query($sql);
		
		// Construction du tableau de données
		$TIdBank = array();
		while($obj = $db->fetch_object($resql)) {
			$TIdBank[] = array(
				'rowid' => $obj->rowid
				,'entity' => $obj->entity
			);
		}
		
		$i = 0;
		
		// Construction du tableau de données
		$TBank = array();
		$TBankAccount = array(); // Permet de stocket l'objet compte bancaire pour éviter de le fetcher à chaque écriture
		foreach($TIdBank as $idBank) {
			$bankline = new AccountLine($db);
			$bankline->fetch($idBank['rowid']);
			$bankline->datev = $db->jdate($bankline->datev);

			if(empty($TBankAccount[$bankline->fk_account])) {
				$TBankAccount[$bankline->fk_account] = new Account($db);
				$TBankAccount[$bankline->fk_account]->fetch($bankline->fk_account);
			}
			$bank = &$TBankAccount[$bankline->fk_account];
			
			// Récupération du tiers concerné, ou type de charge, ou user pour le code compta
			$codeCompta = '';
			$TCodeCompta = array();
			$links = $bank->get_url($bankline->id);
			$lineType = '';
			$object = new stdClass();
			foreach($links as $key => $val) {
				// On ne prend que les liens qui nous intéressent
				if(!in_array($links[$key]['type'], array('company','sc','withdraw','user','banktransfert'))) continue;
				$lineType = $links[$key]['type'];
				
				// Cas du tiers, type d'écriture = règlement client ou fournisseur
				if($lineType == 'company') {
					$tiers = new Societe($db);
					$tiers->fetch($links[$key]['url_id']);
					if($bankline->label == '(CustomerInvoicePayment)') {
						$codeCompta = !empty($tiers->code_compta) ? $tiers->code_compta : $conf->global->COMPTA_ACCOUNT_CUSTOMER;
					} else {
						$codeCompta = !empty($tiers->code_compta_fournisseur) ? $tiers->code_compta_fournisseur : $conf->global->COMPTA_ACCOUNT_SUPPLIER;
					}
					$TCodeCompta[$codeCompta] = $bankline->amount;
					$object = $tiers;
				}
				// Cas de la charge sociale
				if($lineType == 'sc') {
					$charge = new ChargeSociales($db);
					$charge->fetch($links[$key]['url_id']);
					
					$sql = "SELECT c.accountancy_code";
					$sql.= " FROM ".MAIN_DB_PREFIX."c_chargesociales as c";
					$sql.= " WHERE c.id = ".$charge->type;
					
					$resql=$this->db->query($sql);
					$obj = $this->db->fetch_object($resql);
					
					$codeCompta = $obj->accountancy_code;
					$TCodeCompta[$codeCompta] = $bankline->amount;
					$object = $charge;
				}
				// Cas du prélèvement
				if($lineType == 'withdraw') {
					$prel = new BonPrelevement($db);
					$prel->fetch($links[$key]['url_id']);
					
					$sql = "SELECT s.code_compta, pl.amount ";
					$sql.= "FROM ".MAIN_DB_PREFIX."prelevement_bons as p ";
					$sql.= "LEFT JOIN ".MAIN_DB_PREFIX."prelevement_lignes as pl ON pl.fk_prelevement_bons = p.rowid ";
					$sql.= "LEFT JOIN ".MAIN_DB_PREFIX."prelevement_facture as pf ON pf.fk_prelevement_lignes = pl.rowid ";
					$sql.= "LEFT JOIN ".MAIN_DB_PREFIX."facture as f ON pf.fk_facture = f.rowid ";
					$sql.= "LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON f.fk_soc = s.rowid ";
					$sql.= "WHERE f.entity = ".$conf->entity;
					$sql.= " AND p.rowid=".$links[$key]['url_id'];
					
					$resql=$this->db->query($sql);
					while($obj = $this->db->fetch_object($resql)) {
						if(empty($TCodeCompta[$obj->code_compta])) $TCodeCompta[$obj->code_compta] = 0;
						$TCodeCompta[$obj->code_compta]+= $obj->amount;
					}
					$object = $prel;
				}
				
				// Cas de l'utilisateur paiement NDF
				if($lineType == 'user') {
					$usr = new User($db);
					$usr->fetch($links[$key]['url_id']);
					
					$codeCompta = $usr->array_options['options_COMPTE_TIERS'];
					$TCodeCompta[$codeCompta] = $bankline->amount;
					$object = $usr;
				}
				
				// Cas du transfert de compte à compte
				if($lineType == 'banktransfert') {
					$codeCompta = $conf->global->EXPORT_COMPTA_BANK_TRANSFER_ACCOUNT;
					$TCodeCompta[$codeCompta] = $bankline->amount;
					$object = $bankline;
				}
			}
			
			$TBank[$bankline->id] = array();
			
			// Récupération entity
			if($conf->multicompany->enabled) {
				$entity = new DaoMulticompany($db);
				$entity->fetch($idBank['entity']);
				$TBank[$bankline->id]['entity'] = get_object_vars($entity);
			}
			
			// Définition du code comptable banque
			$codeComptableBank = !empty($bank->account_number) ? $bank->account_number : '512';
			
			$TBank[$bankline->id]['bank'] = get_object_vars($bank);
			$TBank[$bankline->id]['bankline'] = get_object_vars($bankline);
			$TBank[$bankline->id]['object'] = $object;
			
			foreach($TCodeCompta as $codeCompta => $amount) {
				if(empty($TBank[$bankline->id]['ligne_tiers'][$codeCompta])) $TBank[$bankline->id]['ligne_tiers'][$codeCompta] = 0;
				$TBank[$bankline->id]['ligne_tiers'][$codeCompta] += $amount;
			}
			if(empty($TBank[$bankline->id]['ligne_banque'][$codeComptableBank])) $TBank[$bankline->id]['ligne_banque'][$codeComptableBank] = 0;
			$TBank[$bankline->id]['ligne_banque'][$codeComptableBank] += $bankline->amount;
		}

		/*
		// Requête de récupération des écritures bancaires (CHQ)
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
		*/
		return $TBank;
	}
	
	function get_line(&$format, $dataline) {
		$ligneFichierTxtFixe = '';
		
		$TVal = array();
		foreach($format as $fmt) {
			// Récupération valeur
			$valeur = isset($dataline[$fmt['name']]) ? $dataline[$fmt['name']] : '';
			
			if($fmt['type_value'] == 'php') {
				$valeur = eval('return '.strtr($fmt['value'],array(
					'@val@'=>$valeur
				)).';');
			} else if($fmt['type_value'] == 'dur') {
				$valeur = $fmt['value'];
			}
			
			// Gestion du format de la valeur
			if($valeur == '') $valeur = $fmt['default'];
			if($fmt['type'] == 'date' && !empty($valeur) && !empty($fmt['format'])) {
				$valeur = date($fmt['format'], $valeur);
			}
			
			// Suppression de tous les caractères accentués pour compatibilités tous systèmes
			$valeur = $this->suppr_accents($valeur);
			
			// Ajout padding ou troncature
			$pad_type = !empty($fmt['pad_type']) ? $fmt['pad_type'] : STR_PAD_LEFT;
			if(strlen($valeur) < $fmt['length'] && $this->fieldPadding) {
				$pad_string = ($fmt['default'] == '') ? ' ' : $fmt['default'];
				$valeur = str_pad($valeur, $fmt['length'], $pad_string, $pad_type);
			} else if(mb_strlen($valeur,'UTF-8') > $fmt['length']) {
				if($pad_type == STR_PAD_RIGHT) {
					$valeur = substr($valeur, 0, $fmt['length']);
				} else {
					$valeur = substr($valeur, -1 * $fmt['length'], $fmt['length']);
				}
			}
			
			$TVal[] = $valeur;
			//$ligneFichierTxtFixe .= $valeur;
			
			//if(!empty($this->fieldSeparator)) $ligneFichierTxtFixe .= $this->fieldSeparator;
		}

		$ligneFichierTxtFixe = implode($this->fieldSeparator, $TVal);
		
		if(!empty($this->lineSeparator)) $ligneFichierTxtFixe .= $this->lineSeparator;
		
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
