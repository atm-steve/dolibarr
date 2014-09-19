<?php
/*************************************************************************************************************************************************
 * Format d'export comptable Quadratus
 *************************************************************************************************************************************************/


class TExportComptaCiel extends TExportCompta {
	
	function __construct($db) {
		
		parent::__construct($db);
		
		$this->_format_ecritures_comptables_vente = array(
			array('name' => 'num_unique',			'length' => 5,	'default' => '0',	'type' => 'text'),
			array('name' => 'code_journal',			'length' => 2,	'default' => 'VE',	'type' => 'text'),
			array('name' => 'date_ecriture',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),
			array('name' => 'date_echeance',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),
			array('name' => 'numero_piece',		'length' => 12,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_compte',		'length' => 11,	'default' => '0',	'type' => 'text'),
			array('name' => 'libelle_libre',		'length' => 25,	'default' => '',	'type' => 'text'),
			array('name' => 'montant',				'length' => 13,	'default' => '0',	'type' => 'text'),
			array('name' => 'sens',					'length' => 1,	'default' => 'C',	'type' => 'text'),
			array('name' => 'numero_pointage',		'length' => 12,	'default' => '',	'type' => 'text'),
			array('name' => 'compte_contrepartie',	'length' => 6,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle_compte',		'length' => 34,	'default' => '',	'type' => 'text'),
			array('name' => 'code_devise',			'length' => 1,	'default' => 'E',	'type' => 'text'),
			array('name' => 'version',				'length' => 4,	'default' => '',	'type' => 'text'),
		);
	
		$this->_format_ecritures_comptables_achat = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_achat['code_journal']='AC';
	
		$this->_format_reglement_tiers=array(
	
			array('name' => 'num_unique',			'length' => 5,	'default' => '0',	'type' => 'text'),
			array('name' => 'code_journal',			'length' => 2,	'default' => 'RG',	'type' => 'text'),
			array('name' => 'date_ecriture',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),
			array('name' => 'date_echeance',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),
			array('name' => 'numero_piece',			'length' => 12,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_compte',		'length' => 11,	'default' => '0',	'type' => 'text'),
			array('name' => 'libelle_libre',		'length' => 25,	'default' => '',	'type' => 'text'),
			array('name' => 'montant',				'length' => 13,	'default' => '0',	'type' => 'text'),
			array('name' => 'sens',					'length' => 1,	'default' => 'C',	'type' => 'text'),
			array('name' => 'numero_pointage',		'length' => 12,	'default' => '',	'type' => 'text'),
			array('name' => 'compte_contrepartie',	'length' => 6,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle_compte',		'length' => 34,	'default' => '',	'type' => 'text'),
			array('name' => 'code_devise',			'length' => 1,	'default' => 'E',	'type' => 'text'),
			array('name' => 'version',				'length' => 4,	'default' => '',	'type' => 'text'),
		
		);
		
		$this->_format_tiers=array(
	
			array('name' => 'type',					'length' => 1,	'default' => 'C',	'type' => 'text'),
			array('name' => 'numero_compte',		'length' => 8,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle',				'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'clef',					'length' => 7,	'default' => ' ',	'type' => 'text'),
			array('name' => 'debit_N1',				'length' => 13,	'default' => '0',	'type' => 'text'),
			array('name' => 'credit_N1',			'length' => 13,	'default' => '0',	'type' => 'text'),
			array('name' => 'debit_N2',				'length' => 13,	'default' => '0',	'type' => 'text'),
			array('name' => 'credit_N2',			'length' => 13,	'default' => '0',	'type' => 'text'),
			array('name' => 'compte_collectif',		'length' => 8,	'default' => '',	'type' => 'text'),
			array('name' => 'adresse1',		'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'adresse2',		'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'ville',		'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'telephone',		'length' => 20,	'default' => '',	'type' => 'text'),
			array('name' => 'flag',		'length' => 1,	'default' => ' ',	'type' => 'text'),
			array('name' => 'type_compte',		'length' => 1,	'default' => 'C',	'type' => 'text'),
			array('name' => 'centraliser_compte',		'length' => 1,	'default' => 'N',	'type' => 'text'),
			array('name' => 'domiciliation',		'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'rib',		'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'mode_reglement',		'length' => 2,	'default' => 'CB',	'type' => 'text'),
			array('name' => 'nb_jour_echeance',		'length' => 2,	'default' => '0',	'type' => 'text'),
			array('name' => 'term_echeance',		'length' => 2,	'default' => '31',	'type' => 'text'),
			array('name' => 'depart_calcul_echeance',		'length' => 2,	'default' => '01',	'type' => 'text'),
			array('name' => 'code_tva',		'length' => 2,	'default' => '',	'type' => 'text'),
			array('name' => 'compte_contrepartie',		'length' => 8,	'default' => '0',	'type' => 'text'),
			array('name' => 'nb_jour_echeance2',		'length' => 3,	'default' => '0',	'type' => 'text'),
			array('name' => 'flag_tva',		'length' => 1,	'default' => '0',	'type' => 'text'),
			array('name' => 'fax',		'length' => 20,	'default' => '',	'type' => 'text'),
			array('name' => 'mode_reglement2',		'length' => 4,	'default' => 'CB',	'type' => 'text'),
			array('name' => 'groupe4',		'length' => 8,	'default' => '',	'type' => 'text'),
			array('name' => 'siret',		'length' => 14,	'default' => '',	'type' => 'text'),
			array('name' => 'edit_m2',		'length' => 1,	'default' => '',	'type' => 'text'),
			array('name' => 'profession',		'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'pays',		'length' => 50,	'default' => '',	'type' => 'text'),
			array('name' => 'code_journal_treso',		'length' => 3,	'default' => '',	'type' => 'text'),
			array('name' => 'personne_morale',		'length' => 1,	'default' => '0',	'type' => 'text'),
			array('name' => 'bon_a_payer',			'length' => 1,	'default' => '1',	'type' => 'text'),
			array('name' => 'iban',					'length' => 4,	'default' => '',	'type' => 'text'),
			array('name' => 'bic',					'length' => 11,	'default' => '',	'type' => 'text'),
			array('name' => 'code_imputation',		'length' => 2,	'default' => '14',	'type' => 'text'),
			
		);
		
		$this->_format_produits=array(
	
			array('name' => 'type',					'length' => 1,	'default' => 'N',	'type' => 'text'),
			array('name' => 'code',		'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle',		'length' => 30,	'default' => '',	'type' => 'text'),
			
		);
		
		unset($this->TTypeExport['ecritures_bancaires']); // pas encore pris en charge
		
		$this->filename = 'XIMPORT.TXT';
		
	}

	function get_file_produits($format, $dt_deb, $dt_fin) {
		global $conf;

		$separateurLigne="\r\n";

		if(empty($format)) $format = $this->_format_produits;
	
		$Tab = parent::get_produits($dt_deb, $dt_fin);
	
		foreach($Tab as $code_compta=>$prod) {
			
			$ligneFichier=array(
				'code'=>$code_compta,
				'libelle'=>$prod['label'], 
			);
			
			$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;	
		}
	
		return $contenuFichier;
	
	}
	

	function get_file_tiers($format, $dt_deb, $dt_fin) {
		global $conf;

		$separateurLigne="\r\n";

		if(empty($format)) $format = $this->_format_tiers;
	
		$Tab = parent::get_tiers($dt_deb, $dt_fin);
	
		foreach($Tab as $code_compta=>$tiers) {
			
			$ligneFichier=array(
				'numero_compte'=>$code_compta,
				'libelle'=>$tiers['nom'], 
				'compte_collectif'=>$conf->global->COMPTA_ACCOUNT_CUSTOMER,
				'adresse1'>$tiers['address'],
				'ville'=>$tiers['town'],
				'telephone'=>$tiers['phone'],
				'domiciliation'=>$tiers['domiciliation'],
				'rib'=>$tiers['code_banque'].$tiers['code_quichet'].$tiers['code_banque'].$tiers['compte_bancaire'].$tiers['cle_rib'],
				'fax'=>$tiers['fax'],
				'siret'=>$tiers['siret'],
				'pays'=>$tiers['pays'],
				'iban'=>$tiers['iban'],
				'bic'=>$tiers['bic'],
			);
			
			$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;	
		}
	
		return $contenuFichier;
	
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
//var_dump($infosFacture);exit;
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
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $codeJournal,
					'date_ecriture'					=> $facture['date'],
					'libelle_libre'					=> $tiers['nom'].' - '.$facture['ref_client'],
					'sens'							=> ($facture['type'] == 2 ? 'C' : 'D'),
					
					'montant'						=> $montant,
					'date_echeance'					=> $facture['date_lim_reglement'],
					'numero_piece'					=> $facture['ref'],
					
					'num_unique'					=> $numEcriture,
				
					
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $codeJournal,
					'date_ecriture'					=> $facture['date'],
					'libelle_libre'					=> $tiers['nom'].' - '.$facture['ref_client'],
					'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
					//'montant_signe'					=> floatval($facture['total_ttc']) < 0 ? '-' : '+',
					'montant'						=> $montant ,
					'date_echeance'					=> $facture['date_lim_reglement'],
					'numero_piece'					=> $facture['ref'],
					'num_unique'					=> $numEcriture,
					
					
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
						'numero_compte'					=> $code_compta,
						'code_journal'					=> $codeJournal,
						'date_ecriture'					=> $facture['date'],
						'libelle_libre'					=> $tiers['nom'],
						'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
						//'montant_signe'					=> floatval($facture['tva']) < 0 ? '-' : '+',
						'montant'						=> $montant,
						'date_echeance'					=> $facture['date_lim_reglement'],
						'numero_piece'					=> $facture['ref'],
						'num_unique'					=> $numEcriture,
					
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
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $codeJournal,
					'date_ecriture'					=> $facture['date'],
					'libelle_libre'					=> $tiers['nom'].(!empty($facture['ref_client']) ? ' - '.$facture['ref_client']:''),
					'sens'							=> ($facture['type'] == 2 ? 'C' : 'D'),
					
					'montant'						=> $montant,
					'date_echeance'					=> $facture['date_lim_reglement'],
					'numero_piece'					=> $facture['facnumber'],
					
					'num_unique'					=> $numEcriture,
				
					
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $codeJournal,
					'date_ecriture'					=> $facture['date'],
					'libelle_libre'					=> $tiers['nom'].(!empty($facture['ref_client']) ? ' - '.$facture['ref_client']:''),
					'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
					//'montant_signe'					=> floatval($facture['total_ttc']) < 0 ? '-' : '+',
					'montant'						=> $montant,
					'date_echeance'					=> $facture['date_lim_reglement'],
					'numero_piece'					=> $facture['facnumber'],
					'num_unique'					=> $numEcriture,
					
					
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
						'numero_compte'					=> $code_compta,
						'code_journal'					=> $codeJournal,
						'date_ecriture'					=> $facture['date'],
						'libelle_libre'					=> $tiers['nom'],
						'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
						//'montant_signe'					=> floatval($facture['tva']) < 0 ? '-' : '+',
						'montant'						=> $montant,
						'date_echeance'					=> $facture['date_lim_reglement'],
						'numero_piece'					=> $facture['facnumber'],
						'num_unique'					=> $numEcriture,
					
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
		
			// Ligne Banque
			$ligneFichier = array(
				'type'							=> 'M',
				'numero_compte'					=> $reglement['code_compta'],
				'numero_piece'					=> $reglement['num_fact'],
				'date_ecriture'					=> strtotime($reglement['datep']),
				'libelle_libre'					=> $tiers['nom'],
				'montant'						=> $reglement['amount'] ,
				'num_unique'=>$numEcriture,
				'sens'=>'D',
				
			);
			
			$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
			$numLignes++;

			$ligneFichier = array(
				'type'							=> 'R',
				'numero_compte'					=> $tiers['code_compta'],
				'numero_piece'					=> $reglement['num_fact'],
				'date_ecriture'					=> strtotime($reglement['datep']),
				'libelle_libre'					=> $tiers['nom'],
				'montant'						=> $reglement['amount'],
				'num_unique'=>$numEcriture,
				'sens'=>'C',
				
			);
			
			$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
			$numLignes++;
			

			
			$numEcriture++;
		}

		return $contenuFichier;
	}	
	
}
