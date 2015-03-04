<?php
/*************************************************************************************************************************************************
 * Format d'export comptable Quadratus
 *************************************************************************************************************************************************/

dol_include_once("/exportcompta/class/export.class.php");

class TExportComptaSage30 extends TExportCompta {
	function __construct(&$db, $exportAllreadyExported=false) {
		
		parent::__construct($db, $exportAllreadyExported);
		
		$this->_format_ecritures_comptables_vente = array(
			array('name' => 'code_journal',			'length' => 2,	'default' => 'VT',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'date_piece',			'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'dmy',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'date_piece',			'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'dmy',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'numero_piece',			'length' => 15,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'code_client',			'length' => 10,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'numero_compte_general','length' => 10,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'numero_compte_tiers',	'length' => 10,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'libelle',				'length' => 30,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'mode_rglt',			'length' => 2,	'default' => '10',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'date_echeance',		'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'dmy',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '0',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			
			array('name' => 'sens',					'length' => 1,	'default' => '0',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => 'montant',				'length' => 12,	'default' => '0',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			
			array('name' => '(vide)',				'length' => 1,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '0',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '0',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
			array('name' => '(vide)',				'length' => 1,	'default' => '0',	'type' => 'text',	'pad_type' => STR_PAD_RIGHT),
		);
		
		$this->fieldSeparator="\r\n";
		
        $this->TModeRglt = array(
            'LCR' => 1,
            'B03000' => 2,
            'CHQ' => 3,
            'LCRD' => 6,
            'VIR' => 10
        );
	}
	
	function get_file_ecritures_comptables_ventes($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabFactures = parent::get_factures_client($dt_deb, $dt_fin);
		
		$contenuFichier = "#VER 5\r\n";
		$separateurLigne = "#MECG\r\n";

		$numEcriture = 1;
		$numLignes = 1;
		
		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];
            $label = ($facture['type'] == 2 ? 'Avoir' : 'Facture');
            $label.= ' '.$facture['ref'].' C '.$tiers['code_client'];
            $facture['ref'] = substr($facture['ref'],0,2).substr($facture['ref'],-4);

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
					'code_client'					=> $tiers['code_client'],
					'numero_compte_general'			=> "41100000",
					'numero_compte_tiers'			=> $code_compta,
	
					'libelle'						=> $label,
					'mode_rglt'						=> $this->TModeRglt[$facture['mode_reglement_code']],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'sens'							=> ($facture['type'] == 2 ? '1' : '0'),
					'montant'						=> abs($montant),
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= $separateurLigne . parent::get_line($format, $ligneFichier);
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'code_client'					=> $tiers['code_client'],
					'numero_compte_general'			=> $code_compta,
					
					'libelle'						=> $tiers['nom'],
					'mode_rglt'						=> $this->TModeRglt[$facture['mode_reglement_code']],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'sens'							=> ($facture['type'] == 2 ? '0' : '1'),
					'montant'						=> abs($montant),
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= $separateurLigne . parent::get_line($format, $ligneFichier);
				
				// Ecriture analytique
				//$ligneFichier['type_ecriture'] = 'A';
				//$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}

			// Lignes TVA
			if(!empty($infosFacture['ligne_tva'])) {
				foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
					$ligneFichier = array(
						'date_piece'					=> $facture['date'],
						'numero_piece'					=> $facture['ref'],
						'code_client'					=> $tiers['code_client'],
						'numero_compte_general'			=> $code_compta,
						
						'libelle'						=> $tiers['nom'],
						'mode_rglt'						=> $this->TModeRglt[$facture['mode_reglement_code']],
						'date_echeance'					=> $facture['date_lim_reglement'],
						'sens'							=> ($facture['type'] == 2 ? '0' : '1'),
						'montant'						=> abs($montant),
						'type_ecriture'					=> 'G'
					);
					
					// Ecriture générale
					$contenuFichier .= $separateurLigne . parent::get_line($format, $ligneFichier);
					$numLignes++;
				}
			}
			
			$numEcriture++;
		}

		return $contenuFichier . "\r\n#FIN";
	}

	/*********************************************************
	 * FONCTIONS NON ENCORE DEVELOPPEES / TESTEE POUR SAGE 30
	 *********************************************************/

	/*function get_file_ecritures_comptables_achats($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabFactures = parent::get_factures_fournisseur($dt_deb, $dt_fin);
		
		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numEcriture = 1;
		$numLignes = 1;
		
		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref_supplier'],
					'numero_compte_general'			=> "40100000",
					'numero_compte_tiers'			=> $code_compta,
	
					'libelle'						=> $tiers['nom'],
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref_supplier'],
					'numero_compte_general'			=> $code_compta,
					
					'libelle'						=> $tiers['nom'],
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
					'type_ecriture'					=> 'G'
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
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref_supplier'],
					'numero_compte_general'			=> $code_compta,
					
					'libelle'						=> $tiers['nom'],
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			$numEcriture++;
		}

		return $contenuFichier;
	}*/

	/*function get_file_ecritures_comptables_ndf($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabNDF = parent::get_notes_de_frais($dt_deb, $dt_fin);
		
		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numEcriture = 1;
		$numLignes = 1;
		
		foreach ($TabNDF as $id_ndf => $infosNDF) {
			$tiers = &$infosNDF['tiers'];
			$ndf = &$infosNDF['ndf'];

			if(!empty($infosNDF['entity'])) {
				$entity = $infosNDF['entity'];
				$tmp = explode(";", $entity['description']);
				$codeCompteTiers = !empty($tmp[2]) ? $tmp[2] : '';
				$codeAnalytique = !empty($tmp[1]) ? $tmp[1] : '';
			}

			// Lignes client
			foreach($infosNDF['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> mktime(0,0,0,date('m', $ndf['datee']), date('t', $ndf['datee']), date('Y', $ndf['datee'])),
					'numero_piece'					=> $ndf['ref'],
					'numero_plan'					=> '0',
					'numero_compte_general'			=> "40100000",
					'numero_compte_tiers'			=> empty($code_compta) ? (isset($codeCompteTiers) ? $codeCompteTiers : '') : $code_compta,
	
					'libelle'						=> isset($entity) ? 'NF '.mb_substr($entity['label'],0,15,'UTF-8') : $tiers['nom'],
					'date_echeance'					=> '',
					'montant_debit'					=> 0,
					'montant_credit'				=> abs($montant),
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosNDF['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> mktime(0,0,0,date('m', $ndf['datee']), date('t', $ndf['datee']), date('Y', $ndf['datee'])),
					'numero_compte_general'			=> $code_compta,
					'numero_piece'					=> $ndf['ref'],
					'numero_plan'					=> '2',
					'numero_section'				=> $codeAnalytique,
					
					'libelle'						=> isset($entity) ? 'NF '.mb_substr($entity['label'],0,15,'UTF-8') : $tiers['nom'],
					'date_echeance'					=> '',
					'montant_debit'					=> abs($montant),
					'montant_credit'				=> 0,
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				
				// Ecriture analytique
				//$ligneFichier['type_ecriture'] = 'A';
				//$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}

			// Lignes TVA
			foreach($infosNDF['ligne_tva'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> mktime(0,0,0,date('m', $ndf['datee']), date('t', $ndf['datee']), date('Y', $ndf['datee'])),
					'numero_compte_general'			=> $code_compta,
					'numero_piece'					=> $ndf['ref'],
					'numero_plan'					=> '0',
					
					'libelle'						=> isset($entity) ? 'NF '.mb_substr($entity['label'],0,15,'UTF-8') : $tiers['nom'],
					'date_echeance'					=> '',
					'montant_debit'					=> abs($montant),
					'montant_credit'				=> 0,
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			$numEcriture++;
		}

		return $contenuFichier;
	}*/

	/*function get_file_reglement_tiers($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabReglement = parent::get_reglement_tiers($dt_deb, $dt_fin);
		
		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numEcriture = 1;
		$numLignes = 1;
		
		foreach ($TabReglement as $infosReglement) {
			$tiers = &$infosReglement['client'];
			$reglement = &$infosReglement['reglement'];
			
			// Ligne client
			$ligneFichier = array(
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
			
			$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
			$numLignes++;
			
			// Ligne Banque
			$ligneFichier = array(
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
			
			$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
			$numLignes++;
			
			$numEcriture++;
		}

		return $contenuFichier;
	}/*

	/*function get_file_ecritures_comptables_banque($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabBank = parent::get_banque($dt_deb, $dt_fin);
		//pre($TabBank);return;
		
		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numEcriture = 1;
		$numLignes = 1;
		
		foreach ($TabBank as $id_bank => $infosBank) {
			$tiers = &$infosBank['tiers'];
			$banque = &$infosBank['bank'];
			$banqueligne = &$infosBank['bankline'];

			if(!empty($infosBank['entity'])) {
				$entity = $infosBank['entity'];
				$tmp = explode(";", $entity['description']);
				$codeCompteTiers = !empty($tmp[0]) ? $tmp[0] : '';
				$codeAnalytique = !empty($tmp[1]) ? $tmp[1] : '';
			}

			$label = $banqueligne['fk_type'].' '.$tiers['nom'];
			$datepiece = $banqueligne['datev'];

			// Lignes client
			foreach($infosBank['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'code_journal'					=> $banque['ref'],
					'date_piece'					=> $datepiece,
					'numero_piece'					=> 'BK'.str_pad($banqueligne['id'],6,'0',STR_PAD_LEFT),
					'numero_plan'					=> '0',
					'numero_compte_general'			=> $banqueligne['label'] == '(SupplierInvoicePayment)' ? '40100000' : '41100000',
					'numero_compte_tiers'			=> empty($code_compta) ? (isset($codeCompteTiers) ? $codeCompteTiers : '') : $code_compta,
	
					'libelle'						=> $label,
					'montant_debit'					=> ($montant < 0) ? abs($montant) : 0,
					'montant_credit'				=> ($montant < 0) ? 0 : abs($montant),
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}
			
			// Lignes de banque
			foreach($infosBank['ligne_banque'] as $code_compta => $montant) {
				$ligneFichier = array(
					'code_journal'					=> $banque['ref'],
					'date_piece'					=> $datepiece,
					'numero_compte_general'			=> $code_compta,
					'numero_piece'					=> 'BK'.str_pad($banqueligne['id'],6,'0',STR_PAD_LEFT),
					'numero_plan'					=> '2',
					'numero_section'				=> $codeAnalytique,
					
					'libelle'						=> $label,
					'montant_debit'					=> ($montant < 0) ? 0 : abs($montant),
					'montant_credit'				=> ($montant < 0) ? abs($montant) : 0,
					'type_ecriture'					=> 'G'
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
			}
			
			$numEcriture++;
		}

		return $contenuFichier;
	}*/
}
?>
