<?php
/*************************************************************************************************************************************************
 * Format d'export comptable Diacompta
 *************************************************************************************************************************************************/


class TExportComptaDiacompta extends TExportCompta {

	public static $TCodeReglement = array(
		'VAD' => '1'
		,'CB' => 'B'
		,'CHQ' => 'R'
		,'LIQ' => 'E'
		,'VIR' => 'V'
		,'ANCV' => '2'
		,'CBVAD' => '3'
	);

	public $TCodeComptaRglt = array();

	function __construct($db, $exportAllreadyExported=false,$addExportTimeToBill=false) {
		global $db;

		parent::__construct($db, $exportAllreadyExported, $addExportTimeToBill);

		$this->_format_ecritures_comptables_vente = array(
			array('name' => 'code_journal',			'length' => 3,	'default' => ' VE',	'type' => 'text'),
			array('name' => 'numero_lot_ecriture',	'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_compte',		'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'sens',					'length' => 1,	'default' => 'C',	'type' => 'text'),
			array('name' => 'montant',				'length' => 15,	'default' => '',	'type' => 'text'),
			array('name' => 'code_libelle',			'length' => 1,	'default' => '',	'type' => 'text'),
			array('name' => 'libelle_ecriture',		'length' => 50,	'default' => '',	'type' => 'text', 'pad_type' => STR_PAD_RIGHT),
			array('name' => 'date_ecriture',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),
			array('name' => 'code_lettrage',		'length' => 2,	'default' => '',	'type' => 'text'),
			array('name' => 'date_echeance',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'Ymd'),
			array('name' => 'numero_piece16',		'length' => 16,	'default' => '',	'type' => 'text'),
			array('name' => 'quantite',				'length' => 15,	'default' => '',	'type' => 'text'),
			array('name' => 'code_reglement',		'length' => 3,	'default' => '',	'type' => 'text'), // TODO WTF ?
			array('name' => 'intitule_compte',		'length' => 40,	'default' => '',	'type' => 'text'), // TODO WTF ?
			array('name' => 'numero_compte_collectif','length' => 10,'default' => '',	'type' => 'text'), // TODO WTF ?
			array('name' => 'code_regroupement',	'length' => 10,	'default' => '',	'type' => 'text'), // TODO WTF ?
			array('name' => 'code_devise',			'length' => 3,	'default' => 'EUR',	'type' => 'text'),
			array('name' => 'montant_devise',		'length' => 15,	'default' => '',	'type' => 'text'),
			array('name' => 'code_tva',				'length' => 1,	'default' => '',	'type' => 'text'),

			/*array('name' => 'type',					'length' => 1,	'default' => 'M',	'type' => 'text'),
			array('name' => 'numero_folio',			'length' => 3,	'default' => '000',	'type' => 'text'),
			array('name' => 'libelle_libre',		'length' => 20,	'default' => '',	'type' => 'text'),
			array('name' => 'compte_contrepartie',	'length' => 8,	'default' => '',	'type' => 'text'),
			array('name' => 'code_lettrage',		'length' => 2,	'default' => '',	'type' => 'text'),
			array('name' => 'code_statistiques',	'length' => 3,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_piece5',		'length' => 5,	'default' => '',	'type' => 'text'),
			array('name' => 'code_affaire',			'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'quantite1',			'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_piece8',		'length' => 8,	'default' => '',	'type' => 'text'),
			array('name' => 'code_devise',			'length' => 3,	'default' => 'EUR',	'type' => 'text'),
			array('name' => 'code_journal2',		'length' => 3,	'default' => '',	'type' => 'text'),
			array('name' => 'flag_code_tva',		'length' => 1,	'default' => '',	'type' => 'text'),
			array('name' => 'code_tva1',			'length' => 1,	'default' => '',	'type' => 'text'),
			array('name' => 'methode_tva',			'length' => 1,	'default' => '',	'type' => 'text'),
			array('name' => 'code_tva2',			'length' => 2,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_piece10',		'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'reserve',				'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'montant_devise_signe',	'length' => 1,	'default' => '+',	'type' => 'text'),
			array('name' => 'montant_devise',		'length' => 12,	'default' => '0',	'type' => 'text'),
			array('name' => 'piece_jointe',			'length' => 12,	'default' => '',	'type' => 'text'),
			array('name' => 'quantite2',			'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'num_unique',			'length' => 10,	'default' => '0',	'type' => 'text'),
			array('name' => 'code_operateur',		'length' => 4,	'default' => '',	'type' => 'text'),
			array('name' => 'date_systeme',			'length' => 14,	'default' => '',	'type' => 'date',	'format' => 'dmYHis'),*/
		);

		$this->addExportTimeToBill = $addExportTimeToBill;

		$this->_format_ecritures_comptables_achat = $this->_format_ecritures_comptables_vente;
		//$this->_format_ecritures_comptables_achat[2] = array('name' => 'code_journal','length' => 2,'default' => 'AC',	'type' => 'text');
		$this->_format_ecritures_comptables_banque = $this->_format_ecritures_comptables_vente;
		//$this->_format_ecritures_comptables_banque[2] = array('name' => 'code_journal','length' => 2,'default' => 'BQ',	'type' => 'text');
		$this->_format_ecritures_comptables_ndf = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_ndf[2] = array('name' => 'code_journal','length' => 2,'default' => 'AC',	'type' => 'text');

		$this->_format_reglement_tiers=array(

			array('name' => 'type',					'length' => 1,	'default' => 'R',	'type' => 'text'),
			array('name' => 'date_ecriture',		'length' => 6,	'default' => '',	'type' => 'date',	'format' => 'dmy'),
			array('name' => 'montant',				'length' => 13,	'default' => '0',	'type' => 'text'),
			array('name' => 'mode_reglement',		'length' => 2,	'default' => 'CB',	'type' => 'text'),
			array('name' => 'code_journal',			'length' => 2,	'default' => 'RE',	'type' => 'text'),
			array('name' => 'reference',			'length' => 10,	'default' => '',	'type' => 'text'),
			array('name' => 'domiciliation',		'length' => 20,	'default' => '',	'type' => 'text'),
			array('name' => 'code_journal2',		'length' => 3,	'default' => '  ',	'type' => 'text'),
			array('name' => 'numero_compte',		'length' => 8,	'default' => ' ',	'type' => 'text'),
			array('name' => 'mode_reglement2',		'length' => 4,	'default' => 'CB',	'type' => 'text'),
			array('name' => 'bon_a_payer',			'length' => 1,	'default' => '1',	'type' => 'text'),
			array('name' => 'iban',					'length' => 4,	'default' => '',	'type' => 'text'),
			array('name' => 'bic',					'length' => 11,	'default' => '',	'type' => 'text'),

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


		$sql = 'SELECT code, accountancy_code FROM '.MAIN_DB_PREFIX.'c_paiement WHERE active = 1 AND accountancy_code IS NOT NULL';
		$resql = $db->query($sql);
		if ($resql) {
			while ($r = $db->fetch_object($resql)) {
				$this->TCodeComptaRglt[$r->code] = $r->accountancy_code;
			}
		}
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

		$TConfQuadra = parent::get_line_conf($format, 'code_journal');
		if(!empty($TConfQuadra['default'])) $codeJournal = $TConfQuadra['default'];

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
					'code_journal'					=> $codeJournal
					,'numero_lot_ecriture'			=> $numEcriture
					,'numero_compte'				=> parent::get_code_comptable($tiers['id'])
					,'sens'							=> ($facture['type'] == 2 ? 'C' : 'D')
					,'montant'						=> abs($montant*100)
					,'code_libelle'					=> ($facture['type']=='2' ? 'A' : 'F' )
					,'libelle_ecriture'				=> $tiers['nom']
					,'date_ecriture'				=> $facture['date']
					,'code_lettrage'				=> ''
					,'date_echeance'				=> $facture['date_lim_reglement']
					,'numero_piece16'				=> $facture['ref']
					,'quantite'						=> ''
					,'code_reglement'				=> ''
					,'intitule_compte'				=> ''
					,'numero_compte_collectif'		=> ''
					,'code_regroupement'			=> ''
					,'code_devise' 					=> ''
					,'montant_devise'				=> abs($montant*100)
					,'code_tva'						=> ''
					,'date_systeme'					=> time()
				);

				if(!empty($conf->global->EXPORTCOMPTA_AVOIRS_AVEC_SIGNE_PLUS)) {
					$ligneFichier['montant_devise_signe'] = '+';
					$ligneFichier['montant_signe'] = '+';
				}

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;

				$numLignes++;
			}

			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'code_journal'					=> $codeJournal
					,'numero_lot_ecriture'			=> $numEcriture
					,'numero_compte'				=> $code_compta
					,'sens'							=> ($montant<0 ) ? 'D' : 'C'
					,'montant'						=> abs($montant*100)
					,'code_libelle'					=> ($facture['type']=='2' ? 'A' : 'F' )
					,'libelle_ecriture'				=> $tiers['nom']
					,'date_ecriture'				=> $facture['date']
					,'code_lettrage'				=> ''
					,'date_echeance'				=> $facture['date_lim_reglement']
					,'numero_piece16'				=> $facture['ref']
					,'quantite'						=> ''
					,'code_reglement'				=> ''
					,'intitule_compte'				=> ''
					,'numero_compte_collectif'		=> ''
					,'code_regroupement'			=> ''
					,'code_devise' 					=> ''
					,'montant_devise'				=> abs($montant*100)
					,'code_tva'						=> ''
					,'date_systeme'					=> time()
				);

				if(!empty($conf->global->EXPORTCOMPTA_AVOIRS_AVEC_SIGNE_PLUS)) {
					$ligneFichier['montant_devise_signe'] = '+';
					$ligneFichier['montant_signe'] = '+';
				}

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
							'code_journal'					=> $codeJournal
							,'numero_lot_ecriture'			=> $numEcriture
							,'numero_compte'				=> $code_compta
							,'sens'							=> (( $montant>0 && $facture['type'] == 2 ) || ($montant<0 ) ? 'D' : 'C')
							,'montant'						=> abs($montant*100)
							,'code_libelle'					=> ($facture['type']=='2' ? 'A' : 'F' )
							,'libelle_ecriture'				=> $tiers['nom']
							,'date_ecriture'				=> $facture['date']
							,'code_lettrage'				=> ''
							,'date_echeance'				=> $facture['date_lim_reglement']
							,'numero_piece16'				=> $facture['ref']
							,'quantite'						=> ''
							,'code_reglement'				=> ''
							,'intitule_compte'				=> ''
							,'numero_compte_collectif'		=> ''
							,'code_regroupement'			=> ''
							,'code_devise' 					=> ''
							,'montant_devise'				=> abs($montant*100)
							,'code_tva'						=> ''
							,'date_systeme'					=> time()


						);

						if(!empty($conf->global->EXPORTCOMPTA_AVOIRS_AVEC_SIGNE_PLUS)) {
							$ligneFichier['montant_devise_signe'] = '+';
							$ligneFichier['montant_signe'] = '+';
						}

					// Ecriture générale
					$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
					$numLignes++;
				}
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
		$codeJournal='ACH';


		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];

			$label = $tiers['nom'];
			if(!empty($conf->global->EXPORT_COMPTA_FOURN_INVOICE_REF_AND_REFSUPPLIER_ON_LABEL) && !empty($facture['ref_supplier'])) $label = $facture['ref_supplier'].' - '.$label;

			// Configuration permettant d'afficher la ligne tiers au crédit et les lignes complémentaires au débit. si non renseigné : tout au crédit
			$sens = array();
			$sens[] = 'D';
			$sens[] = 'C';

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
			/*	var_dump($code_compta);
				exit;*/
				$ligneFichier = array(
					'code_journal'					=> $codeJournal
					,'numero_lot_ecriture'			=> $numEcriture
					,'numero_compte'				=> $code_compta
					,'sens'							=> ($facture['type'] == 2 || $montant < 0) ? 'D' : 'C'
					,'montant'						=> abs($montant*100)
					,'code_libelle'					=> $facture['type'] == 2 ? 'A' : 'F'
					,'libelle_ecriture'				=> $label
					,'date_ecriture'				=> $facture['date']
					,'code_lettrage'				=> ''
					,'date_echeance'				=> $facture['date_lim_reglement']
					,'numero_piece16'				=> $facture['ref']
					,'quantite'						=> ''
					,'code_reglement'				=> ''
					,'intitule_compte'				=> ''
					,'numero_compte_collectif'		=> ''
					,'code_regroupement'			=> ''
					,'code_devise' 					=> ''
					,'montant_devise'				=> abs($montant*100)
					,'code_tva'						=> ''
					,'date_systeme'					=> time()
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}

			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'code_journal'					=> $codeJournal
					,'numero_lot_ecriture'			=> $numEcriture
					,'numero_compte'				=> $code_compta
					,'sens'							=> ($facture['type'] == 2 || $montant < 0) ? 'C' : 'D'
					,'montant'						=> abs($montant*100)
					,'code_libelle'					=> $facture['type'] == 2 ? 'A' : 'F'
					,'libelle_ecriture'				=> $label
					,'date_ecriture'				=> $facture['date']
					,'code_lettrage'				=> ''
					,'date_echeance'				=> $facture['date_lim_reglement']
					,'numero_piece16'				=> $facture['ref']
					,'quantite'						=> ''
					,'code_reglement'				=> ''
					,'intitule_compte'				=> ''
					,'numero_compte_collectif'		=> ''
					,'code_regroupement'			=> ''
					,'code_devise' 					=> ''
					,'montant_devise'				=> abs($montant*100)
					,'code_tva'						=> ''
					,'date_systeme'					=> time()
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
						'code_journal'					=> $codeJournal
						,'numero_lot_ecriture'			=> $numEcriture
						,'numero_compte'				=> $code_compta
						,'sens'							=> ($facture['type'] == 2 || $montant < 0) ? 'C' : 'D'
						,'montant'						=> abs($montant*100)
						,'code_libelle'					=> $facture['type'] == 2 ? 'A' : 'F'
						,'libelle_ecriture'				=> $label
						,'date_ecriture'				=> $facture['date']
						,'code_lettrage'				=> ''
						,'date_echeance'				=> $facture['date_lim_reglement']
						,'numero_piece16'				=> $facture['ref']
						,'quantite'						=> ''
						,'code_reglement'				=> ''
						,'intitule_compte'				=> ''
						,'numero_compte_collectif'		=> ''
						,'code_regroupement'			=> ''
						,'code_devise' 					=> ''
						,'montant_devise'				=> abs($montant*100)
						,'code_tva'						=> ''
						,'date_systeme'					=> time()
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

	function get_file_ecritures_comptables_ndf($format, $dt_deb, $dt_fin) {
		global $conf;

		$TabNDF = parent::get_notes_de_frais($dt_deb, $dt_fin);

		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numEcriture = 1;
		$numLignes = 1;

		$type = 'M';
		$codeJournal='AC';

		foreach ($TabNDF as $id_ndf => $infosNDF) {
			$tiers = &$infosNDF['tiers'];
			$ndf = &$infosNDF['ndf'];
			$user = &$infosNDF['user'];

			// Lignes client
			foreach($infosNDF['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'type'							=> $type,
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $codeJournal,
					'date_ecriture'					=> $ndf['datee'],
					'libelle_libre'					=> $user['firstname'].' '.$user['lastname'],
					'sens'							=> 'C',
					'montant'						=> abs($montant * 100),
					'date_echeance'					=> $ndf['datee'],
					'numero_piece5'					=> $ndf['ref'],
					'numero_piece8'					=> $ndf['ref'],
					'numero_piece10'				=> $ndf['ref'],
					'montant_devise'				=> abs($montant * 100),
					'num_unique'					=> $numLignes,
					'date_systeme'					=> time(),
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}

			// Lignes de produits
			foreach($infosNDF['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'type'							=> $type,
					'numero_compte'					=> $code_compta,
					'code_journal'					=> $codeJournal,
					'date_ecriture'					=> $ndf['datee'],
					'libelle_libre'					=> $user['firstname'].' '.$user['lastname'],
					'sens'							=> 'D',
					'montant'						=> abs($montant * 100),
					'date_echeance'					=> $ndf['datee'],
					'numero_piece5'					=> $ndf['ref'],
					'numero_piece8'					=> $ndf['ref'],
					'numero_piece10'				=> $ndf['ref'],
					'montant_devise'				=> abs($montant * 100),
					'num_unique'					=> $numLignes,
					'date_systeme'					=> time(),
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;

				// Ecriture analytique
				//$ligneFichier['type_ecriture'] = 'A';
				//$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}

			// Lignes TVA
			if(!empty($infosNDF['ligne_tva'])) {
				foreach($infosNDF['ligne_tva'] as $code_compta => $montant) {
					$ligneFichier = array(
						'type'							=> $type,
						'numero_compte'					=> $code_compta,
						'code_journal'					=> $codeJournal,
						'date_ecriture'					=> $ndf['datee'],
						'libelle_libre'					=> $user['firstname'].' '.$user['lastname'],
						'sens'							=> 'D',
						'montant'						=> abs($montant * 100),
						'date_echeance'					=> $ndf['datee'],
						'numero_piece5'					=> $ndf['ref'],
						'numero_piece8'					=> $ndf['ref'],
						'numero_piece10'				=> $ndf['ref'],
						'montant_devise'				=> abs($montant * 100),
						'num_unique'					=> $numLignes,
						'date_systeme'					=> time(),
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

	function getAmount(&$TInfo)
	{
		$amount_tiers = $amount_banque = 0;
		foreach ($TInfo['ligne_tiers'] as $code_compta => $amount)
		{
			$amount_tiers += $amount;
		}

		foreach ($TInfo['ligne_banque'] as $code_compta => $amount)
		{
			$amount_banque += $amount;
		}

		return array('montant_tiers' => $amount_tiers, 'montant_banque' => $amount_banque);
	}

	function regroupLinesByDate($Tab, $fk_type)
	{
		$TRes = $TResSupplier = $TResCustomer = $Tab2 = array();
		if (empty($Tab))  return $TRes;
		$i=0;
		// Groupement par date et par client
		foreach ($Tab as &$TInfo)
		{
			$i++;
			if(in_array($TInfo['bankline']['label'], array('(SupplierInvoicePayment)', 'Règlement fournisseur'))) {
				$TResSupplier['@supplierPayment'.$i] = $TInfo;
				continue; // Pas de groupement fournisseur on les ajoute tel quel
			} elseif (in_array($TInfo['bankline']['label'], array('(CustomerInvoicePaymentBack)', 'Remboursement client'))) {
				$TResCustomer['@customerPaymentBack'.$i] = $TInfo;
				continue; // Pas de groupement pour les remboursement client
			}

			$Tab2[date('Y-m-d', $TInfo['bankline']['datev'])][$TInfo['object']->id][] = $TInfo;
		}

		foreach ($Tab2 as $date => &$T)
		{
			$i=0;
			$total_banque = 0;
			foreach ($T as $fk_user => &$row)
			{
				// Somme pour 1 client
				$total_tiers = 0;
				foreach ($row as &$TInfo)
				{
					$TAmount = $this->getAmount($TInfo);
					$total_tiers += $TAmount['montant_tiers'];
					$total_banque += $TAmount['montant_banque'];
				}

				// Get first key - Récupération des codes comptables
				reset($TInfo['ligne_tiers']);
				$code_comptable_tiers = key($TInfo['ligne_tiers']);


				// Affectation au nouveau tableau pour respecter le format utilisé à l'origine
				$TRes[$fk_type.$date.$i] = $TInfo;
				$TRes[$fk_type.$date.$i]['ligne_tiers'] = array($code_comptable_tiers => $total_tiers);
				// Je veux pas de ligne banque maintenant
				$TRes[$fk_type.$date.$i]['ligne_banque'] = array();

				$i++;
			}

			// Si mon total_banque > 0 alors c'est que j'ai trouvé des paiement client avant
			if ($total_banque > 0)
			{
				reset($TInfo['ligne_banque']);
				$code_comptable_banque = key($TInfo['ligne_banque']);

				$TRes[$fk_type.$date.'@total'] = $TInfo;

				// Je veux le total banque qui été associé à user dans la bloucle du dessus
				$TRes[$fk_type.$date.'@total']['ligne_tiers'] = array();
				$TRes[$fk_type.$date.'@total']['ligne_banque'] = array($code_comptable_banque => $total_banque);

				// Je fait un clone car l'affectation d'objet à une variable le passe en référence et si j'efface le nom alors la ligne qui précède ma ligne banque aura pas de nom
				$TRes[$fk_type.$date.'@total']['object'] = clone $TRes[$fk_type.$date.'@total']['object'];
				$TRes[$fk_type.$date.'@total']['object']->nom = '';
			}

		}

		$TRes = $TRes + $TResSupplier + $TResCustomer;

		return $TRes;
	}

	function get_file_ecritures_comptables_banque($format, $dt_deb, $dt_fin) {
		global $conf;

		if(empty($format)) $format = $this->_format_ecritures_comptables_banque;

		$TabBank = parent::get_banque($dt_deb, $dt_fin);

		$type = 'M';
		$codeJournal='BQ';

		$contenuFichier = '';
		$separateurLigne = "\r\n";

		$numEcriture = 1;
		$numLignes = 1;

		// Comportement ajouté après dev pour reze
		// CHQ,CB,CBVAD,VAD,ANCV
		if (!empty($conf->global->EXPORT_COMPTA_DIAFORMAT_GROUP_BY_TYPE_RGLT))
		{
			$Tab = array('other'=>array());
			$TType = explode(',', $conf->global->EXPORT_COMPTA_DIAFORMAT_GROUP_BY_TYPE_RGLT);

			foreach ($TabBank as $id_bank => $infosBank) {
				if (in_array($infosBank['bankline']['fk_type'], $TType))
				{
					$Tab[$infosBank['bankline']['fk_type']][$id_bank] = $infosBank;
				}
				else
				{
					$Tab['other'][$id_bank] = $infosBank;
				}
			}

			$TabBank = array();
			foreach ($TType as $fk_type)
			{
				$Tab[$fk_type] = $this->regroupLinesByDate($Tab[$fk_type], $fk_type);
				$TabBank += $Tab[$fk_type];
			}

			$TabBank += $Tab['other'];
		}

		foreach ($TabBank as $id_bank => $infosBank) {
			$bankline = &$infosBank['bankline'];
			$numchq = $bankline['num_chq'];
			$bank = &$infosBank['bank'];
			$object = &$infosBank['object'];

			// Comportement ajouté après dev pour reze
			if (!empty($conf->global->EXPORT_COMPTA_DIAFORMAT_REPLACE_NUMCHQ_BY_NUM_BORDEREAU) && !empty($bankline['fk_bordereau']))
			{
				$bordereau = new RemiseCheque($this->db);
				if ($bordereau->fetch($bankline['fk_bordereau']) > 0)
				{
					$numchq = $bordereau->number;
				}
			}

			$label = $bankline['label'];
			//pre($object, true);exit;
			if(!empty($object)) {
				if($object->element == 'societe')			$label = $object->name;
				if($object->element == 'chargesociales')	$label = $object->type_libelle;
				if($object->element == 'user')				$label = $object->firstname.' '.$object->lastname;
				if(get_class($object) == 'BonPrelevement')	$label = $object->ref;
			}

			$nom_tiers = '';
			if (!empty($object) && $object->element== 'societe') $nom_tiers = $object->nom;

			// Lignes tiers
			if (!empty($infosBank['ligne_tiers']))
			{
				foreach($infosBank['ligne_tiers'] as $code_compta => $montant) {

					$code_reglement = !empty(self::$TCodeReglement[$bankline['fk_type']]) ? self::$TCodeReglement[$bankline['fk_type']] : '';
					if(in_array($bankline['label'], array('(SupplierInvoicePayment)', 'Règlement fournisseur')) && $code_reglement == 'R') $code_reglement = 'C';

					$ligneFichier = array(
						'code_journal'					=> $bank['ref']
						,'numero_lot_ecriture'			=> $numEcriture
						,'numero_compte'				=> $code_compta
						,'sens'							=> ($montant < 0) ? 'D' : 'C'
						,'montant'						=> abs($montant*100)
						,'code_libelle'					=> ''
						,'libelle_ecriture'				=> $nom_tiers
						,'date_ecriture'				=> $bankline['datev']
						,'code_lettrage'				=> ''
						,'date_echeance'				=> ''
						,'numero_piece16'				=> empty($numchq) ? $bankline['ref'] : $numchq
						,'quantite'						=> ''
						,'code_reglement'				=> $code_reglement
						,'intitule_compte'				=> $bank['label']
						,'numero_compte_collectif'		=> ''
						,'code_regroupement'			=> ''
						,'code_devise' 					=> ''
						,'montant_devise'				=> abs($montant*100)
						,'code_tva'						=> ''
						,'date_systeme'					=> time()
					);

					// Ecriture générale
					$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
					$numLignes++;
				}
			}

			// Lignes banque
			foreach($infosBank['ligne_banque'] as $code_compta => $montant) {

				$code_reglement = !empty(self::$TCodeReglement[$bankline['fk_type']]) ? self::$TCodeReglement[$bankline['fk_type']] : '';
				if(in_array($bankline['label'], array('(SupplierInvoicePayment)', 'Règlement fournisseur')) && $code_reglement == 'R') $code_reglement = 'C';

				$ligneFichier = array(
					'code_journal'					=> $bank['ref']
					,'numero_lot_ecriture'			=> $numEcriture
					,'numero_compte'				=> (!empty($this->TCodeComptaRglt[$bankline['fk_type']]) && empty($conf->global->EXPORT_COMPTA_DIAFORMAT_FORCE_CODE_COMPTA_BANK)) ? $this->TCodeComptaRglt[$bankline['fk_type']] : $code_compta
					,'sens'							=> ($montant < 0) ? 'C' : 'D'
					,'montant'						=> abs($montant*100)
					,'code_libelle'					=> ''
					,'libelle_ecriture'				=> !empty($conf->global->EXPORT_COMPTA_DIAFORMAT_FORCE_LABEL_LIGNE_BANK) ? $conf->global->EXPORT_COMPTA_DIAFORMAT_FORCE_LABEL_LIGNE_BANK : $nom_tiers
					,'date_ecriture'				=> $bankline['datev']
					,'code_lettrage'				=> ''
					,'date_echeance'				=> ''
					,'numero_piece16'				=> $numchq
					,'quantite'						=> ''
					,'code_reglement'				=> $code_reglement
					,'intitule_compte'				=> $bank['label']
					,'numero_compte_collectif'		=> ''
					,'code_regroupement'			=> ''
					,'code_devise' 					=> ''
					,'montant_devise'				=> abs($montant*100)
					,'code_tva'						=> ''
					,'date_systeme'					=> time()
				);

				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
				$numLignes++;
			}

			if (!empty($conf->global->EXPORT_COMPTA_DIAFORMAT_GROUP_BY_TYPE_RGLT))
			{
				if (!empty($infosBank['ligne_banque'])) $numEcriture++;
			}
			else $numEcriture++;
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
				'code_journal'					=>  $reglement['code_compta'],
				'date_ecriture'					=> strtotime($reglement['datep']),
				'libelle_libre'					=> $tiers['nom'],
				'montant'						=> abs($reglement['amount'] * 100),
				'mode_reglement'				=>$reglement['paiement_mode'],
				'sens'							=>($reglement['amount'] > 0 ? 'C' : 'D'),
			);

			$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
			$numLignes++;

			$ligneFichier = array(
				'type'							=> 'R',
				'numero_compte'					=> $tiers['code_compta'],
				'code_journal'					=> $tiers['code_compta'],
				'date_ecriture'					=> strtotime($reglement['datep']),
				'reference'						=> $tiers['nom'],
				'montant'						=> abs($reglement['amount'] * 100),
				'mode_reglement'				=>$reglement['paiement_mode'],
				'sens'							=>($reglement['amount'] > 0 ? 'C' : 'D'),
			);

			$contenuFichier .= parent::get_line($format, $ligneFichier) . $separateurLigne;
			$numLignes++;



			$numEcriture++;
		}

		return $contenuFichier;
	}

}
