<?php
/*************************************************************************************************************************************************
 * Format d'export comptable Sage
 *************************************************************************************************************************************************/

dol_include_once("/exportcompta/class/export.class.php");

class TExportComptaWinfic extends TExportCompta {
	function __construct(&$db, $exportAllreadyExported=false, $addExportTime=false) {

		parent::__construct($db, $exportAllreadyExported, $addExportTime);

		$this->_format_ecritures_comptables_vente = array(
				array('name' => 'code_journal',			'length' => 2,	'default' => 'VE',	'type' => 'text'),
				array('name' => 'date_piece',			'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'dmY'),
				array('name' => 'numero_folio',			'length' => 6,	'default' => '     1',	'type' => 'text'),
				array('name' => 'numero_ecriture',		'length' => 6,	'default' => '     1',	'type' => 'text'),
				array('name' => 'jour_ecriture',		'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'N'),
				array('name' => 'numero_compte_general','length' => 6,	'default' => '0',	'type' => 'text',	'pad_type'=>0),
				array('name' => 'montant_debit',		'length' => 13,	'default' => '',	'type' => 'numeric',	'format'=>'2,'),
				array('name' => 'montant_credit',		'length' => 13,	'default' => '',	'type' => 'numeric',	'format'=>'2,'),
				array('name' => 'libelle',				'length' => 30,	'default' => '',	'type' => 'text',	'pad_type'=>1),
				array('name' => 'lettrage',				'length' => 2,	'default' => '',	'type' => 'text'),
				array('name' => 'numero_piece',			'length' => 5,	'default' => '',	'type' => 'text'),
				array('name' => 'code_stat',			'length' => 4,	'default' => '',	'type' => 'text'),
				array('name' => 'date_echeance',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'dmY'),
				array('name' => 'monnaie',				'length' => 1,	'default' => '1',	'type' => 'text'),
				array('name' => '(vide)',				'length' => 1,	'default' => '',	'type' => 'text'),
				array('name' => 'indice_compteur',		'length' => 1,	'default' => '',	'type' => 'text'),
				array('name' => 'quantite',				'length' => 11,	'default' => '',	'type' => 'numeric',	'format'=>'3,'),
				array('name' => '(vide)',				'length' => 3,	'default' => '  |',	'type' => 'text'),

		);
		//array('name' => 'numero_compte_tiers',	'length' => 17,	'default' => '0',	'type' => 'text'),

		$this->_format_ecritures_comptables_achat = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_achat[1]['default'] = 'AC';


		$this->_format_tiers = array(
				array('name' => 'type',				'length' => 1,	'default' => 'C',	'type' => 'text'),
				array('name' => 'numero_compte',	'length' => 18,	'default' => '',	'type' => 'text'),
				array('name' => 'civilite',			'length'=>3,	'default' => '', 	'type'=>'text'),
				array('name' => 'prenom',			'length'=>3,	'default' => '', 	'type'=>'text'),
				array('name' => 'libelle',			'length' => 40,	'default' => '',	'type' => 'text'),
				array('name' => 'adresse1',			'length' => 30,	'default' => '',	'type' => 'text'),
				array('name' => 'adresse2',			'length' => 30,	'default' => '',	'type' => 'text'),
				array('name' => 'zip',				'length' => 5,	'default' => '',	'type' => 'text'),
				array('name' => 'ville',			'length' => 25,	'default' => '',	'type' => 'text'),
				array('name' => 'pays',				'length' => 50,	'default' => '',	'type' => 'text'),
				array('name' => 'gsm',				'length' => 20,	'default' => '',	'type' => 'text'),
				array('name' => 'phone',			'length' => 20,	'default' => '',	'type' => 'text'),
				array('name' => 'phone2',			'length' => 20,	'default' => '',	'type' => 'text'),
				array('name' => 'fax',				'length' => 20,	'default' => '',	'type' => 'text'),
				array('name' => 'email',			'length' => 20,	'default' => '',	'type' => 'text'),
				array('name' => 'tva',				'length' => 20,	'default' => '',	'type' => 'text'),
				array('name' => 'numero_compte',	'length' => 6,	'default' => '',	'type' => 'text'),
				array('name' => 'commentaire',		'length' => 80,	'default' => '',	'type' => 'text'),
		);

		$this->lineSeparator = "\r\n";
		$this->fieldSeparator = '|';
		$this->fieldPadding = true;

		unset($this->TTypeExport['reglement_tiers'], $this->TTypeExport['produits'], $this->TTypeExport['ecritures_comptables_achat'], $this->TTypeExport['ecritures_comptables_banque'], $this->TTypeExport['ecritures_comptables_ndf']); // pas encore pris en charge

		$this->filename = 'ECRITURE.WIN';
	}

	function get_file_ecritures_comptables_ventes($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabFactures = parent::get_factures_client($dt_deb, $dt_fin);

		$contenuFichier = '';

		$numEcriture = 1;
		$numLignes = 1;

		$compte_general_client = $conf->global->EXPORT_COMPTA_GENERAL_CUSTOMER_ACCOUNT;
		if(empty($compte_general_client)) $compte_general_client = '41100000';

		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];

			if(!empty($infosFacture['entity'])) {
				$entity = $infosFacture['entity'];
				$tmp = explode(";", $entity['description']);
				$codeCompteTiers = !empty($tmp[0]) ? $tmp[0] : '';
				$codeAnalytique = !empty($tmp[1]) ? $tmp[1] : '';
			}

			$libelle = $tiers['nom'];
			if(!empty($facture['ref_client'])) $libelle.= ' - '.$facture['ref_client'];

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
//var_dump($facture);exit;
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'jour_ecriture'					=> $facture['date'],
					'numero_compte_general'			=> $compte_general_client,
					'numero_compte_tiers'			=> $code_compta,

					'libelle'						=> $libelle,
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
					'type_ecriture'					=> 'G',
					'numero_ecriture'				=> $numLignes,
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}

			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte_general'			=> $code_compta,
					'jour_ecriture'					=> $facture['date'],

					'libelle'						=> $libelle,
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
					'type_ecriture'					=> 'G',
					'numero_ecriture'				=> $numLignes,
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);

				// Ecriture analytique
				//$ligneFichier['type_ecriture'] = 'A';
				//$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}

			// Lignes TVA
			foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte_general'			=> $code_compta,
					'jour_ecriture'					=> $facture['date'],

					'libelle'						=> $libelle,
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
					'type_ecriture'					=> 'G',
					'numero_ecriture'				=> $numLignes,
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}

			$numEcriture++;
		}

		return $contenuFichier;
	}

	function get_file_ecritures_comptables_achats($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabFactures = parent::get_factures_fournisseur($dt_deb, $dt_fin);

		$contenuFichier = '';

		$numEcriture = 1;
		$numLignes = 1;

		$compte_general_fournisseur = $conf->global->EXPORT_COMPTA_GENERAL_SUPPLIER_ACCOUNT;
		if(empty($compte_general_fournisseur)) $compte_general_fournisseur = '40100000';

		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];


			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'jour_ecriture'					=> $facture['date'],
					'numero_ecriture'				=> $numLignes,
					'numero_piece_fournisseur'		=> $facture['ref_supplier'],
					'numero_compte_general'			=> $compte_general_fournisseur,
					'numero_compte_tiers'			=> $code_compta,

//					'libelle'						=> (!empty($facture['libelle']) ? $tiers['nom'].' '.$facture['libelle'] : $tiers['nom']),
					'libelle'						=> $tiers['nom'],
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
					'type_ecriture'					=> 'G'
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}

			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'jour_ecriture'					=> $facture['date'],
					'numero_ecriture'				=> $numLignes,
					'numero_piece_fournisseur'		=> $facture['ref_supplier'],
					'numero_compte_general'			=> $code_compta,

//					'libelle'						=> $tiers['nom'],
					'libelle'						=> (!empty($facture['libelle']) ? $tiers['nom'].' '.$facture['libelle'] : $tiers['nom']),
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
					'type_ecriture'					=> 'G'
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);

				// Ecriture analytique
				//$ligneFichier['type_ecriture'] = 'A';
				//$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}

			// Lignes TVA
			foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_piece'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'jour_ecriture'					=> $facture['date'],
					'numero_ecriture'				=> $numLignes,
					'numero_piece_fournisseur'		=> $facture['ref_supplier'],
					'numero_compte_general'			=> $code_compta,

					'libelle'						=> $tiers['nom'],
					'mode_rglt'						=> $facture['mode_reglement'],
					'date_echeance'					=> $facture['date_lim_reglement'],
					'montant_debit'					=> ($facture['type'] == 2 || $montant < 0) ? 0 : abs($montant),
					'montant_credit'				=> ($facture['type'] == 2 || $montant < 0) ? abs($montant) : 0,
					'type_ecriture'					=> 'G'
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}

			$numEcriture++;
		}

		return $contenuFichier;
	}

	function get_file_ecritures_comptables_ndf($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabNDF = parent::get_notes_de_frais($dt_deb, $dt_fin);

		$contenuFichier = '';

		$numEcriture = 1;
		$numLignes = 1;

		$compte_general_user = $conf->global->EXPORT_COMPTA_GENERAL_USER_ACCOUNT;
		if(empty($compte_general_user)) $compte_general_user = '40100000';

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
					'numero_compte_general'			=> $compte_general_user,
					'numero_compte_tiers'			=> empty($code_compta) ? (isset($codeCompteTiers) ? $codeCompteTiers : '') : $code_compta,

					'libelle'						=> isset($entity) ? 'NF '.mb_substr($entity['label'],0,15,'UTF-8') : $tiers['nom'],
					'date_echeance'					=> '',
					'montant_debit'					=> 0,
					'montant_credit'				=> abs($montant),
					'type_ecriture'					=> 'G'
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
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
				$contenuFichier .= parent::get_line($format, $ligneFichier);

				// Ecriture analytique
				//$ligneFichier['type_ecriture'] = 'A';
				//$contenuFichier .= parent::get_line($format, $ligneFichier);
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
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}

			$numEcriture++;
		}

		return $contenuFichier;
	}

	function get_file_reglement_tiers($format, $dt_deb, $dt_fin) {
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

			$contenuFichier .= parent::get_line($format, $ligneFichier);
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

			$contenuFichier .= parent::get_line($format, $ligneFichier);
			$numLignes++;

			$numEcriture++;
		}

		return $contenuFichier;
	}

	function get_file_ecritures_comptables_banque($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabBank = parent::get_banque($dt_deb, $dt_fin);
		//pre($TabBank, true);exit;

		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numEcriture = 1;
		$numLignes = 1;

		$compte_general_client = $conf->global->EXPORT_COMPTA_GENERAL_CUSTOMER_ACCOUNT;
		if(empty($compte_general_client)) $compte_general_client = '41100000';

		$compte_general_fournisseur = $conf->global->EXPORT_COMPTA_GENERAL_SUPPLIER_ACCOUNT;
		if(empty($compte_general_fournisseur)) $compte_general_fournisseur = '40100000';

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
				if(get_class($object) == 'BonPrelevement')	$label = $object->ref;
			}

			if(!empty($infosBank['entity'])) {
				$entity = $infosBank['entity'];
				$tmp = explode(";", $entity['description']);
				$codeCompteTiers = !empty($tmp[0]) ? $tmp[0] : '';
				$codeAnalytique = !empty($tmp[1]) ? $tmp[1] : '';
			}

			$datepiece = $bankline['datev'];

			// Lignes client
			foreach($infosBank['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'code_journal'					=> $bank['ref'],
					'date_piece'					=> $datepiece,
					'numero_piece'					=> 'BK'.str_pad($bankline['id'],6,'0',STR_PAD_LEFT),
					'numero_plan'					=> '0',
					'numero_compte_general'			=> $bankline['label'] == '(SupplierInvoicePayment)' ? $compte_general_fournisseur : $compte_general_client,
					'numero_compte_tiers'			=> empty($code_compta) ? (isset($codeCompteTiers) ? $codeCompteTiers : '') : $code_compta,

					'libelle'						=> $label,
					'montant_debit'					=> ($montant < 0) ? abs($montant) : 0,
					'montant_credit'				=> ($montant < 0) ? 0 : abs($montant),
					'type_ecriture'					=> 'G'
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}

			// Lignes de banque
			foreach($infosBank['ligne_banque'] as $code_compta => $montant) {
				$ligneFichier = array(
					'code_journal'					=> $bank['ref'],
					'date_piece'					=> $datepiece,
					'numero_compte_general'			=> $code_compta,
					'numero_piece'					=> 'BK'.str_pad($bankline['id'],6,'0',STR_PAD_LEFT),
					'numero_plan'					=> '2',
					'numero_section'				=> $codeAnalytique,

					'libelle'						=> $label,
					'montant_debit'					=> ($montant < 0) ? 0 : abs($montant),
					'montant_credit'				=> ($montant < 0) ? abs($montant) : 0,
					'type_ecriture'					=> 'G'
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
			}

			// Ajout de ligne de transfert de TVA lorsqu'une facture d'achat est totalement payée
			if(in_array($bankline['label'], array('(SupplierInvoicePayment)','Règlement fournisseur'))) {
				$TOD = $this->getODVATTransfer($infosBank);
				foreach ($TOD as $ligneFichier) {
					$contenuFichier .= parent::get_line($format, $ligneFichier);
				}
			}

			$numEcriture++;
		}

		return $contenuFichier;
	}

	function get_file_tiers($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabTiers = parent::get_tiers($dt_deb, $dt_fin);
		$numEcriture = 1;
		$numLignes = 1;

		$this->lineSeparator = "\r\n";
		$this->fieldSeparator = ';';
		$this->fieldPadding = false;
		$this->filename = 'INFTIERS.TXT';

		$compte_general_client = $conf->global->EXPORT_COMPTA_GENERAL_CUSTOMER_ACCOUNT;
		if(empty($compte_general_client)) $compte_general_client = '41100000';

		$compte_general_fournisseur = $conf->global->EXPORT_COMPTA_GENERAL_SUPPLIER_ACCOUNT;
		if(empty($compte_general_fournisseur)) $compte_general_fournisseur = '40100000';

		foreach($TabTiers as $code_compta=>$tiers) {

			foreach($tiers as $key=>$value){
				$tiers[$key] = strtr($value,array('amp;'=>'&','gt;'=>'>'));
			}

			$ligneFichier=array_merge($tiers, array(
				'numero_compte'=>$code_compta,
				'numero_compte_general'	=> ($tiers['fournisseur']) ? $compte_general_fournisseur : $compte_general_client,
				'libelle'=>$tiers['nom'],
				'type_tiers'=>($tiers['fournisseur']) ? 'F' : 'C',
				'compte_collectif'=>$conf->global->COMPTA_ACCOUNT_CUSTOMER,
				'adresse1'=>html_entity_decode($tiers['address'],ENT_QUOTES,'UTF-8'),
				'zip'=>$tiers['zip'],
				'ville'=>html_entity_decode($tiers['town'],ENT_QUOTES,'UTF-8'),
				'telephone'=>$tiers['phone'],
				'domiciliation'=>html_entity_decode($tiers['domiciliation'],ENT_QUOTES,'UTF-8'),
				'rib'=>$tiers['code_banque'].$tiers['code_quichet'].$tiers['code_banque'].$tiers['compte_bancaire'].$tiers['cle_rib'],
				'phone'=>$tiers['phone'],
				'fax'=>$tiers['fax'],
				'email'=>$tiers['email'],
				'siret'=>$tiers['siret'],
				'pays'=>$tiers['pays'],
				'iban'=>$tiers['iban'],
				'bic'=>$tiers['bic'],
				'mode_rglt'	=> $this->TModeRglt[$tiers['mode_reglement_code']],
				'tms'=>strtotime($tiers['tms']),
				'commentaire'=>trim($code_compta).' '.trim(strtr(strip_tags($tiers['note_public']),array("\n"=>' ',"\r"=>''))),
			));

			$contenuFichier .= parent::get_line($format, $ligneFichier);
		}

		return $contenuFichier;
	}

}
?>
