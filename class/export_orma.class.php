<?php
/*************************************************************************************************************************************************
 * Format d'export comptable Quadratus
 *************************************************************************************************************************************************/


class TExportComptaOrma extends TExportCompta {
	
	function __construct($db, $exportAllreadyExported=false,$addExportTime=false) {
		
		parent::__construct($db, $exportAllreadyExported, $addExportTime);
		
		$this->_format_ecritures_comptables_vente = array(
			array('name' => 'id_ligne',					'length' => 1,	'default' => '',	'type' => 'text'),
			array('name' => 'code_journal',				'length' => 3,	'default' => 'VEN',	'type' => 'text'),
			array('name' => 'date_ecriture',			'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),
			array('name' => 'numero_piece',				'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_compte',			'length' => 10,	'default' => '0',	'type' => 'text'),
			array('name' => 'code_operation',			'length' => 4,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle_libre',			'length' => 34,	'default' => '',	'type' => 'text'),
			array('name' => 'mode_reglement',			'length' => 4,	'default' => '',	'type' => 'text'),
			array('name' => 'date_echeance',			'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),
			array('name' => 'sens',						'length' => 1,	'default' => 'C',	'type' => 'text'),
			array('name' => 'montant',					'length' => 11,	'default' => '0',	'type' => 'text'),
			array('name' => 'code_devise',				'length' => 1,	'default' => 'E',	'type' => 'text'),
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
		
		$this->_format_compte=array(
	
			array('name' => 'id_ligne',					'length' => 1,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_compte',			'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_compte_collectif',	'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle',					'length' => 25,	'default' => '',	'type' => 'text'),
			
		);
		
		$this->_format_tiers=array(
	
			array('name' => 'id_ligne',				'length' => 1,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_compte',		'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'adresse1',				'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'adresse2',				'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'adresse3',				'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'adresse4',				'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'cp_ville',				'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'pays',					'length' => 30,	'default' => '',	'type' => 'text'),
			array('name' => 'contact',				'length' => 25,	'default' => '',	'type' => 'text'),
			array('name' => 'contact_tel',			'length' => 25,	'default' => '',	'type' => 'text'),
			array('name' => 'contact_mobile',		'length' => 20,	'default' => '',	'type' => 'text'),
			array('name' => 'telecopie',			'length' => 20,	'default' => '',	'type' => 'text'),
			array('name' => 'messagerie',			'length' => 25,	'default' => '',	'type' => 'text'),
			array('name' => 'telex',				'length' => 20,	'default' => '',	'type' => 'text'),
			array('name' => 'mode_reglement',		'length' => 4,	'default' => '',	'type' => 'text'),
			array('name' => 'credit',				'length' => 3,	'default' => '',	'type' => 'text'),
			array('name' => 'jour_echeance',		'length' => 2,	'default' => '',	'type' => 'text'),
			
		);
		
		$this->_format_produits=array(
	
			array('name' => 'type',					'length' => 1,	'default' => 'N',	'type' => 'text'),
			array('name' => 'code',		'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle',		'length' => 30,	'default' => '',	'type' => 'text'),
			
		);
		
		$this->_format_ecritures_comptables_banque = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_banque['code_journal']='BQ';
		
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
	

	function get_file_tiers($format, $dt_deb, $dt_fin, $id_ligne='C') {
		global $conf;

		$separateurLigne="\r\n";

		if(empty($format)) $format = $this->_format_tiers;
	
		$Tab = parent::get_tiers($dt_deb, $dt_fin);
	
		foreach($Tab as $code_compta=>$tiers) {
			
			$ligneFichier=array_merge($tiers, array(
				'id_ligne'=>$id_ligne,
				'numero_compte'=>$code_compta,
				'libelle'=>$tiers['nom'], 
				'numero_compte_collectif'=>$conf->global->ACCOUNTING_ACCOUNT_CUSTOMER,
				'adresse1'=>$tiers['address'],
				'cp_ville'=>$tiers['zip'].' '.$tiers['town'],
				'pays'=>$tiers['pays'],
				'telephone'=>$tiers['phone'],
				'domiciliation'=>$tiers['domiciliation'],
				'rib'=>$tiers['code_banque'].$tiers['code_quichet'].$tiers['code_banque'].$tiers['compte_bancaire'].$tiers['cle_rib'],
				'fax'=>$tiers['fax'],
				'siret'=>$tiers['siret'],
				'pays'=>$tiers['pays'],
				'iban'=>$tiers['iban'],
				'bic'=>$tiers['bic'],
			));
			
			$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;	
		}
	
		return $contenuFichier;
	
	}
	
	function get_file_ecritures_comptables_ventes($format, $dt_deb, $dt_fin) {
		global $conf;

		if(empty($format)) $format = $this->_format_ecritures_comptables_vente;

		$TabFactures = parent::get_factures_client($dt_deb, $dt_fin);
		
		$type = 'E';
		//$codeJournal='VEN';
		
		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numEcriture = 1;
		$numLignes = 1;
		
		$contenuFichier.= $this->get_file_tiers($this->_format_compte, $dt_deb, $dt_fin);
		$contenuFichier.= $this->get_file_tiers($this->_format_tiers, $dt_deb, $dt_fin, 'A');
		
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
			$label = $tiers['nom'];
			$mode_reglement = parent::_get_mode_reglement_label($infosFacture['facture']['mode_reglement_id']);
			$label.= (!empty($facture['ref_client']) ? ' - '.$facture['ref_client']:'');
//var_dump($infosFacture);exit;
			// Lignes client

			$montant_facture = 0;

			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
			
				$ligneFichier = array(
					'id_ligne'						=> 'E',
					'numero_compte'					=> $code_compta,
					//'code_journal'					=> $codeJournal,
					'date_ecriture'					=> $facture['date'],
					'libelle_libre'					=> $label,
					'sens'							=> ($facture['type'] == 2 || $montant<0 ? 'C' : 'D'),
					'code_operation'				=> '', // TODO ???
					'montant'						=> abs(number_format($montant,2,'.','')),
					//'montant'						=> abs($montant),
					'date_echeance'					=> $facture['date_lim_reglement'],
					'numero_piece'					=> $facture['ref'],
					
					'num_unique'					=> $numEcriture,
					'mode_reglement'				=> $mode_reglement,
					
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;

				$montant_facture+=number_format($montant,2,'.','');
			}
			
			$montant_produit = 0;
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				if($montant!=0) {

				$ligneFichier = array(
					'id_ligne'						=> 'E',
					'numero_compte'					=> $code_compta,
					//'code_journal'					=> $codeJournal,
					'date_ecriture'					=> $facture['date'],
					'libelle_libre'					=> $label,
					'sens'							=> ($facture['type'] == 2 || $montant<0 ? 'D' : 'C'),
					'code_operation'				=> '', // TODO ???
					'montant'						=>  abs(number_format($montant,2,'.','')),
					//'montant'						=> abs($montant),
					'date_echeance'					=> $facture['date_lim_reglement'],
					'numero_piece'					=> $facture['ref'],
					'num_unique'					=> $numEcriture,
					'mode_reglement'				=> $mode_reglement,
					
					
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				
				// Ecriture analytique
				//$ligneFichier['type_ecriture'] = 'A';
				//$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;

				$montant_produit+=number_format($montant,2,'.','');

				}

				
			}

			$montant_tva=0;$cpt_tva=1;
			$nb_tva = count($infosFacture['ligne_tva']);
			// Lignes TVA
			foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
					if($cpt_tva == $nb_tva) $montant = $montant_facture-$montant_produit-$montant_tva;
					
					if($montant!=0) {
					$ligneFichier = array(
						'id_ligne'						=> 'E',
						'numero_compte'					=> $code_compta,
						//'code_journal'					=> $codeJournal,
						'date_ecriture'					=> $facture['date'],
						'libelle_libre'					=> $label,
						'sens'							=> ($facture['type'] == 2 || $montant<0 ? 'D' : 'C'),
						'code_operation'				=> '', // TODO ???
					//	'montant'						=> abs($montant),
						'montant'						=>  abs(number_format($montant,2,'.','')),
						'date_echeance'					=> $facture['date_lim_reglement'],
						'numero_piece'					=> $facture['ref'],
						'num_unique'					=> $numEcriture,
						'mode_reglement'				=> $mode_reglement,
					
					);
					}
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;$cpt_tva++;

				$montant_tva+=number_format($montant,2,'.','');

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
			$label = $tiers['nom'];
			$label.= (!empty($facture['ref_client']) ? ' - '.$facture['ref_client']:'');
//var_dump($infosFacture);exit;
			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				
				$ligneFichier = array(
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $codeJournal,
					'date_ecriture'					=> $facture['date'],
					'libelle_libre'					=> $label,
					'sens'							=> ($montant > 0 ? 'C' : 'D'),
					'montant'						=> abs($montant),
					'date_echeance'					=> $facture['date_echeance'],
					'numero_piece'					=> $facture['ref'],
					'numero_piece_fourn'			=> $facture['ref_supplier'],
					
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
					'libelle_libre'					=> $label,
					'sens'							=> ($montant > 0 ? 'D' : 'C'),
					'montant'						=> abs($montant),
					'date_echeance'					=> $facture['date_echeance'],
					'numero_piece'					=> $facture['ref'],
					'numero_piece_fourn'			=> $facture['ref_supplier'],
					
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
			if(!empty($infosFacture['ligne_tva'])) {
				foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
						$ligneFichier = array(
							'numero_compte'					=> $code_compta,
							'code_journal'					=> $codeJournal,
							'date_ecriture'					=> $facture['date'],
							'libelle_libre'					=> $label,
							'sens'							=> ($montant > 0 ? 'D' : 'C'),
							'montant'						=> abs($montant),
							'date_echeance'					=> $facture['date_echeance'],
							'numero_piece'					=> $facture['ref'],
							'numero_piece_fourn'			=> $facture['ref_supplier'],
							
							'num_unique'					=> $numEcriture,
						);
					
					// Ecriture générale
					$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
					$numLignes++;
				}
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
				'montant'						=> abs($reglement['amount']),
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
				'montant'						=> abs($reglement['amount']),
				'num_unique'=>$numEcriture,
				'sens'=>'C',
				
			);

			$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
			$numLignes++;
			

			
			$numEcriture++;
		}

		return $contenuFichier;
	}	
	
	function get_file_ecritures_comptables_banque($format, $dt_deb, $dt_fin) {
		global $conf;

		if(empty($format)) $format = $this->_format_ecritures_comptables_banque;

		$TabBank = parent::get_banque($dt_deb, $dt_fin);
		
		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numEcriture = 1;
		$numLignes = 1;
		
		foreach ($TabBank as $id_bank => $infosBank) {
			$bankline = &$infosBank['bankline'];
			$bank = &$infosBank['bank'];
			$object = &$infosBank['object'];
			
			$label = $bankline['label'];
			//pre($object, true);exit;
			if(!empty($object)) {
				if($object->element == 'societe')			$label = $object->name;
				if($object->element == 'chargesociales')	$label = $object->type_libelle;
				if($object->element == 'user')				$label = $object->firstname.' '.$object->lastname;
			}
			
			// Lignes tiers
			foreach($infosBank['ligne_tiers'] as $code_compta => $montant) {
			
				$ligneFichier = array(
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $bank['ref'],
					'date_ecriture'					=> $bankline['datev'],
					'libelle_libre'					=> $label,
					'sens'							=> ($montant < 0) ? 'D' : 'C',
					'montant'						=> abs(number_format($montant,2,'.','')),
					'num_unique'					=> $numLignes,
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			// Lignes banque
			foreach($infosBank['ligne_banque'] as $code_compta => $montant) {
				$ligneFichier = array(
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $bank['ref'],
					'date_ecriture'					=> $bankline['datev'],
					'libelle_libre'					=> $label,
					'sens'							=> ($montant < 0) ? 'C' : 'D',
					'montant'						=> abs(number_format($montant,2,'.','')),
					'num_unique'					=> $numLignes,
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			$numEcriture++;
		}

		return $contenuFichier;
	}
}
