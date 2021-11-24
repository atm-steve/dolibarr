<?php

class TExportComptaSage1000_Koesio extends TExportCompta
{
	const CODE_KOESIO = '70000';
	public function __construct(&$db, $exportAllreadyExported = false, $addExportTime = false)
	{
		parent::__construct($db, $exportAllreadyExported, $addExportTime);
		global $conf;

		$this->_format_tiers = array(
			array('name' => 'entite',                      'length' => 11,  'default' => self::CODE_KOESIO, 'type' => 'text'),
			array('name' => 'type_tiers',                  'length' =>  1,  'default' => '', 'type' => 'text'),
			array('name' => 'code_aux_sage1000',           'length' => 16,  'default' => '', 'type' => 'text'),
			array('name' => 'raison_sociale',              'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'adresse1',                    'length' => 50,  'default' => '', 'type' => 'text'),
			array('name' => 'adresse2',                    'length' => 50,  'default' => '', 'type' => 'text'),
			array('name' => 'distributionBP',              'length' => 10,  'default' => '', 'type' => 'text'),
			array('name' => 'code_postal',                 'length' => 16,  'default' => '', 'type' => 'text'),
			array('name' => 'ville',                       'length' => 40,  'default' => '', 'type' => 'text'),
			array('name' => 'pays',                        'length' =>  2,  'default' => '', 'type' => 'text'),
			array('name' => 'forme_juridique',             'length' => 10,  'default' => '', 'type' => 'text'),
			array('name' => 'code_APE',                    'length' => 10,  'default' => '', 'type' => 'text'),
			array('name' => 'num_TVA_intracom',            'length' => 16,  'default' => '', 'type' => 'text'),
			array('name' => 'siret',                       'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'mode_rglt',                   'length' =>  8,  'default' => '', 'type' => 'text'),
			array('name' => 'condition_rglt',              'length' => 26,  'default' => '', 'type' => 'text'),
			array('name' => 'compte_collectif_privilegie', 'length' => 16,  'default' => '', 'type' => 'text'),
			array('name' => 'tel_societe',                 'length' => 20,  'default' => '', 'type' => 'text'),
			array('name' => 'fax_societe',                 'length' => 20,  'default' => '', 'type' => 'text'),
			array('name' => 'email_societe',               'length' => 60,  'default' => '', 'type' => 'text'),
			array('name' => 'intitule_compte',             'length' => 30,  'default' => '', 'type' => 'text'),
			array('name' => 'IBAN',                        'length' => 60,  'default' => '', 'type' => 'text'),
			array('name' => 'libelle_banque',              'length' => 50,  'default' => '', 'type' => 'text'),
			array('name' => 'code_BIC',                    'length' => 20,  'default' => '', 'type' => 'text'),
			array('name' => 'domiciliation',               'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'civilite_contact',            'length' =>  3,  'default' => '', 'type' => 'text'),
			array('name' => 'nom',                         'length' => 50,  'default' => '', 'type' => 'text'),
			array('name' => 'prenom',                      'length' => 50,  'default' => '', 'type' => 'text'),
			array('name' => 'tel_contact',                 'length' => 20,  'default' => '', 'type' => 'text'),
			array('name' => 'fax_contact',                 'length' => 20,  'default' => '', 'type' => 'text'),
			array('name' => 'email_contact',               'length' => 60,  'default' => '', 'type' => 'text'),
			array('name' => 'groupe_relance',              'length' => 10,  'default' => '', 'type' => 'text'),
			array('name' => 'membre_CPRO',                 'length' => 10,  'default' => '', 'type' => 'text'),
			array('name' => 'charge_de_recouvrement',      'length' => 10,  'default' => '', 'type' => 'text'),
		);
		$this->_format_ecritures_comptables_vente = array(
			array('name' => 'societe',             'length' => 32,  'default' => self::CODE_KOESIO, 'type' => 'text'),
			array('name' => 'code_etablissement',  'length' => 32,  'default' => self::CODE_KOESIO, 'type' => 'text'),
			array('name' => 'journal',             'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'type_piece',          'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'date',                'length' => 32,  'default' => '', 'type' => 'date', 'format' => 'd/m/Y'),
			array('name' => 'num_piece',           'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'type_compte',         'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'compte_comptable',    'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'tiers',               'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'libelle',             'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'sens',                'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'montant',             'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'devise_tenue',        'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'montant_devise',      'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'devise',              'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'date_echeance',       'length' => 32,  'default' => '', 'type' => 'date', 'format' => 'd/m/Y'),
			array('name' => 'mode_reglement',      'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'profil_tva',          'length' => 55,  'default' => '', 'type' => 'text'),
			array('name' => 'assujettissement_tva','length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'axe',                 'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'section_analytique',  'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'code_lettrage',       'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'code_rapprochement',  'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'ref_externe',         'length' => 32,  'default' => '', 'type' => 'text'),
			array('name' => 'type_location',       'length' => 32,  'default' => '', 'type' => 'text'),
		);
		$this->_format_ecritures_comptables_achat = $this->_format_ecritures_comptables_vente;
		$this->_format_infos_paiement = array(
			array('name' => 'reference_mandat',        'length' => 90,  'default' => '',                'type' => 'text'),
			array('name' => 'date_signature_mandat',   'length' => 10,  'default' => '',                'type' => 'date', 'format' => 'd/m/Y'),
			array('name' => 'lieu_signature',          'length' => 70,  'default' => '',                'type' => 'text'),
			array('name' => 'code_client',             'length' => 30,  'default' => '',                'type' => 'text'),
			array('name' => 'code_iban',               'length' => 70,  'default' => '',                'type' => 'text'),
			array('name' => 'code_bic',                'length' => 30,  'default' => '',                'type' => 'text'),
			array('name' => 'code_societe_creditrice', 'length' => 30,  'default' => self::CODE_KOESIO, 'type' => 'text'),
			array('name' => 'libelle_mandat',          'length' =>127,  'default' => '',                'type' => 'text'),
			array('name' => 'contrat_commercial',      'length' =>  2,  'default' => '',                'type' => 'text'),
			array('name' => 'type_mandat',             'length' =>  4,  'default' => 'CORE',            'type' => 'text'),
			array('name' => 'periodicite',             'length' =>  9,  'default' => 'Recurrent',       'type' => 'text'),
			array('name' => 'date_fin_mandat',         'length' => 10,  'default' => '',                'type' => 'date', 'format' => 'd/m/Y'),
		);
		$this->lineSeparator = "\r\n";
		$this->fieldSeparator = ';';
		$this->fieldPadding = false;
	}

	/**
	 * @param array $format
	 * @param string $dt_deb
	 * @param string $dt_fin
	 * @return string
	 */
	public function get_file_ecritures_comptables_ventes($format, $dt_deb, $dt_fin)
	{
		$TFactures = parent::get_factures_client($dt_deb, $dt_fin);
		$contenuFichier = '';
		foreach ( $TFactures as $id_facture => $infosFacture ) {
			$TLineArray = $this->_getLineArrayEcritures($infosFacture, 'C');
			foreach ($TLineArray->client as $lineArray)
				$contenuFichier .= parent::get_line($format, $lineArray);
			foreach ($TLineArray->produit as $lineArray)
				$contenuFichier .= parent::get_line($format, $lineArray);
			foreach ($TLineArray->TVA as $lineArray)
				$contenuFichier .= parent::get_line($format, $lineArray);
		}

		return $contenuFichier;
	}

	/**
	 * @param array $format
	 * @param string $dt_deb
	 * @param string $dt_fin
	 * @return string
	 */
	public function get_file_ecritures_comptables_achats($format, $dt_deb, $dt_fin)
	{
		$TabFactures = parent::get_factures_fournisseur($dt_deb, $dt_fin);
		$contenuFichier = '';
		foreach ( $TabFactures as $id_facture => $infosFacture ) {
			$TLineArray = $this->_getLineArrayEcritures($infosFacture, 'F');
			$contenuFichier .= parent::get_line($format, $TLineArray->client);
			$contenuFichier .= parent::get_line($format, $TLineArray->produit);
			$contenuFichier .= parent::get_line($format, $TLineArray->TVA);
		}

		return $contenuFichier;
	}

	public function get_file_tiers($format, $dt_deb, $dt_fin)
	{
		$TTiers = parent::get_tiers($dt_deb, $dt_fin);
		$contenuFichier = '';

		foreach ( $TTiers as $code_compta => $tiers ) {
			// si tiers à la fois client et fournisseur, on lui crée deux lignes, une pour chaque "rôle"
			if ($tiers['client']) {
				$contenuFichier .= parent::get_line($format, $this->_getLineArrayTiers($tiers, 'C'));
			}
			if ($tiers['fournisseur']) {
				$contenuFichier .= parent::get_line($format, $this->_getLineArrayTiers($tiers, 'F'));
			}
		}

		return $contenuFichier;
	}

	/**
	 * @param array[] $format
	 * @param string $dt_deb
	 * @param string $dt_fin
	 */
	public function get_file_infos_paiement($format, $dt_deb, $dt_fin)
	{
		$filecontent = '';
		$TInfosPaiement = parent::get_infos_paiement($dt_deb, $dt_fin);
		/**
		 * @var int $id
		 * @var array $infosPaiement
		 */
		foreach ($TInfosPaiement as $id => $infosPaiement) {
			$lineArray = array();
			$directKeys = array(
				'reference_mandat',
				'date_signature_mandat',
				'lieu_signature',
				'code_client',
				'code_iban',
				'code_bic',
				'date_fin_mandat',
			);
			// clés directement reprises:
			foreach ($directKeys as $k) $lineArray[$k] = $infosPaiement[$k];
			// autres clés
//			$lineArray['code_societe_creditrice'] = self::CODE_KOESIO;
			$lineArray['libelle_mandat'] = 'en attente';
			$lineArray['contrat_commercial'] = '';
			$lineArray['periodicite'] = 'Recurrent';
			$lineArray['type_mandat'] = 'CORE';
			$filecontent .= parent::get_line($format, $lineArray);
		}
		return $filecontent;
	}

	/* Les 4 fonctions ci-dessous sont là pour éviter d'avoir une fatal si on choisit un des exports non définis */
	public function get_file_produits($format, $dt_deb, $dt_fin) { return ''; }
	public function get_file_ecritures_comptables_banque($format, $dt_deb, $dt_fin) { return ''; }
	public function get_file_reglement_tiers($format, $dt_deb, $dt_fin) { return ''; }
	public function get_file_ecritures_comptables_ndf($format, $dt_deb, $dt_fin) { return ''; }

	/**
	 * @param $address
	 * @return string[]  Address split in 2: the first string holds the first line, the second string holds the rest.
	 */
	private function _splitAddress($address)
	{
		$address = html_entity_decode($address, ENT_QUOTES, 'UTF-8');
		$address = preg_replace("/(\s*\r?\n\s*)+/", "\n", $address);
		return array_map(
			function ($l) {
				return str_replace("\n", " ", $l);
			},
			explode("\n", $address, 2)
		);
	}

	/**
	 * @param array $tiers
	 * @param string $type_tiers  "C" ou "F"
	 */
	private function _getLineArrayTiers($tiers, $type_tiers = 'C') {
		foreach ( $tiers as $key => $value ) {
			$tiers[$key] = strtr($value, array(
				'amp;' => '&',
				'gt;' => '>'
			));
		}
		$membre_CPRO = 'Non';
		if ($type_tiers === 'C') {
			$compte_collectif_privilegie = $tiers['soc_ext_compte_collectif_client'];
			$code_compta = $tiers['code_compta'];
			if (!preg_match('/^C/', $tiers['code_client'])) $membre_CPRO = 'Oui';
		} elseif ($type_tiers === 'F') {
			$compte_collectif_privilegie = $tiers['soc_ext_compte_collectif_fournisseur'];
			$code_compta = $tiers['code_compta_fournisseur'];
			if (!preg_match('/^F/', $tiers['code_fournisseur'])) $membre_CPRO = 'Oui';
		} else {
			$compte_collectif_privilegie = '';
		}

		$address = $this->_splitAddress($tiers['address']);
		// cf. spec https://docs.google.com/spreadsheets/d/16cBacx7fbzmPgFBbNmvg57t0Yn6i42nh
		return array(
//			'entite' => self::CODE_KOESIO,
			'type_tiers' => $type_tiers,
			'code_aux_sage1000' => preg_replace('^411', '', $code_compta), // "ne pas avoir le 411 devant"
			'raison_sociale' => $tiers['nom'],
			'adresse1' => $address[0],
			'adresse2' => $address[1],
			'distributionBP' => '',
			'code_postal' => $tiers['zip'],
			'ville' => html_entity_decode($tiers['town'], ENT_QUOTES, 'UTF-8'),
			'pays' => $tiers['code_pays'],
			'forme_juridique' => $tiers['soctype_libelle'], // TODO: correspondance à faire
			'code_APE' => $tiers['ape'],
			'num_TVA_intracom' => $tiers['tva_intra'],
			'siret' => $tiers['siret'],
			'mode_rglt' => $tiers['mode_reglement_code'],
			'condition_rglt' => $tiers['cond_rglt_code'],
			// si à la fois client et fournisseur, ça prendra le compte collectif fournisseur
			'compte_collectif_privilegie' => $compte_collectif_privilegie,
			'tel_societe' => $this->_formatTelNumKoesio($tiers['phone']),
			'fax_societe' => $this->_formatTelNumKoesio($tiers['fax']),
			'email_societe' => $tiers['email'],
			'intitule_compte' => $tiers['libelle_banque'],
			'IBAN' => $tiers['iban'],
			'libelle_banque' => $tiers['libelle_banque'], // "libelle de la Banque du compte privilégié" → pas la même chose que 'intitule_compte' mais je ne vois pas ce que je dois mettre
			'code_BIC' => $tiers['bic'],
			'domiciliation' => html_entity_decode($tiers['domiciliation'], ENT_QUOTES, 'UTF-8'),
			'civilite_contact' => $tiers['contacts'] ? $tiers['contacts'][0]['civility'] : '',
			'nom' => $tiers['contacts'] ? $tiers['contacts'][0]['lastname'] : '',
			'prenom' => $tiers['contacts'] ? $tiers['contacts'][0]['firstname'] : '',
			'tel_contact' => $tiers['contacts'] ? ($tiers['contacts'][0]['phone']) : '',
			'fax_contact' => $tiers['contacts'] ? $tiers['contacts'][0]['fax'] : '',
			'email_contact' => $tiers['contacts'] ? $tiers['contacts'][0]['email'] : '',
			'groupe_relance' => '', // toujours vide
			'membre_CPRO' => $membre_CPRO,
			'charge_de_recouvrement' => '', // Pour les clients, valeur dans la classification "CHARGE RECOUVREMENT CLIENT". Non renseigné si la classification n’existe pas. → je n'ai pas trouvé la classification
		);
	}

	/**
	 * @param array $infosFacture
	 * @param string $type_facture  "C" ou "F"
	 * @return stdClass  Attributes of returned object: client / fournisseur, TVA, produit
	 */
	private function _getLineArrayEcritures($infosFacture, $type_facture = 'C')
	{
		$mappingSens = [ // selon export_sage1000
		                 'client'  => ['C' => 'D', 'F' => 'C'], // vente => débit, achat => crédit
		                 'TVA'     => ['C' => 'C', 'F' => 'D'], // vente => crédit, achat => débit
		                 'produit' => ['C' => 'C', 'F' => 'D'], // vente => crédit, achat => débit
		];

		$retLineArray = array(
			'client' => array(),
			'produit' => array(),
			'TVA' => array(),
		);
		$tiers = &$infosFacture['tiers'];
		$facture = &$infosFacture['facture'];
		if ($type_facture === 'C') {
			$cle_compte_collectif = 'compte_collectif_client';
			$journal = 'VEN1';
		} elseif ($type_facture === 'F') {
			$cle_compte_collectif = 'compte_collectif_fournisseur';
			$journal = 'ACH1';
		} else {
			// error
			return null;
		}

		switch(intval($facture['type'])) {
			case 0: // facture
				$type_piece = 'F' . $type_facture; break;
			case 2: // avoir
				$type_piece = 'A' . $type_facture; break;
			default:
				$type_piece = 'ERROR'; break;
		}
//		if (! empty($infosFacture['entity'])) {
//			$tmp = explode(";", $infosFacture['entity']['description']);
//			$codeCompteTiers = !empty($tmp[0]) ? $tmp[0] : '';
//			$codeAnalytique =  !empty($tmp[1]) ? $tmp[1] : '';
//		}
		$isTiersCEE = isInEEC((object) $infosFacture['tiers']);
		$isTiersFrance = $infosFacture['tiers']['country_code'] === 'FR';

		$codes_lignes = array_keys($infosFacture['ligne_produit']);
		$type_location = '';
		if (array_intersect(['607999', '706106'], $codes_lignes)) {
			$type_location .= 'M';
		} elseif (array_intersect(['607998', '706103'], $codes_lignes)) {
			$type_location .= 'A';
		}

		$commonLineArray = array(
//			'societe'              => self::CODE_KOESIO,
//			'code_etablissement'   => self::CODE_KOESIO,
			'journal'              => $journal, // ACH1 ou VEN1
			'type_piece'           => $type_piece,
			'date'                 => $facture['date'],
			'num_piece'             => $facture['ref'],
			'type_compte'          => 'G',
			'compte_comptable'     => $tiers['array_options']["options_{$cle_compte_collectif}"],
			'tiers'                => null, // défini plus bas
			'libelle'              => $tiers['nom'],
			'sens'                 => null, // défini plus bas
			'montant'              => null, // défini plus bas
			'devise_tenue'         => 'EUR',
			'montant_devise'       => null, // défini plus bas
			'devise'               => 'EUR',
			'date_echeance'        => $facture['date_lim_reglement'],
			'mode_reglement'       => $facture['mode_reglement_code'],
			'profil_tva'           => '',
			'assujettissement_tva' => $isTiersFrance ? 'Taxable' : ($isTiersCEE ? 'Intracommunautaire' : ''),
			'axe'                  => 'AXE 1',
			'section_analytique'   => '',
			'code_lettrage'        => '', // non renseigné
			'code_rapprochement'   => '', // non renseigné
			'ref_externe'          => $facture['ref'], // TODO vérifier, c'est la même que 'no_piece'
			'type_location'        => $type_location ?: "(type location vide)",
			'profil_tva'           => $this->_getProfilTVA($tiers['tva_intra'], $type_facture, $isTiersCEE),
		);

		// Lignes client
		foreach ($infosFacture['ligne_tiers'] as $code_compta => $montant) {
			$retLineArray['client'][] = array_merge($commonLineArray, array(
				'tiers'                => $code_compta,
				'sens'                 => $mappingSens['client'][$type_facture],
				'montant'              => $this->_numberFormat($montant),
				'montant_devise'       => $this->_numberFormat($montant),
			));
		}

		// Lignes de produits
		foreach ($infosFacture['ligne_produit'] as $code_compta => $montant) {
			$retLineArray['produit'][] = array_merge($commonLineArray, array(
				'tiers'                => $code_compta,
				'sens'                 => $mappingSens['produit'][$type_facture],
				'montant'              => $this->_numberFormat($montant),
				'montant_devise'       => $this->_numberFormat($montant),
			));
		}

		// Lignes TVA
		foreach ($infosFacture['ligne_tva'] as $code_compta => $montant) {
			$retLineArray['TVA'][] = array_merge($commonLineArray, array(
				'tiers'                => $code_compta,
				'sens'                 => $mappingSens['TVA'][$type_facture],
				'montant'              => $this->_numberFormat($montant),
				'montant_devise'       => $this->_numberFormat($montant),
			));
		}

		return (object) $retLineArray;
	}

	/**
	 * @param string $code_compta
	 * @param string $type_facture  "C" ou "F"
	 * @return string
	 */
	private function _getProfilTVA($code_compta, $type_facture, $isTiersCEE = true)
	{
		$mappingTVA = [
			'other'           => "2100 - Collectée débits exonéré de TVA (France)",
			'44562000'        => "1121 - Déductible immobilisations taux normal",
			'44560000'        => "1111 - Déductible débit autoliquidation TN",
			'44566100'        => "1101 - Déductible débits taux normal",
			'44566101'        => "1102 - Déductible débits taux intermédiaire réduit",
			'44566102'        => "1103 - Déductible débits taux normal spécifique",
			'44566103'        => "1104 - Déductible débits taux réduit",
			'44574000'        => "1141 - Déductible débits créances irrécouvrables",
			'44520000'        => "3131 - TVA due intracomm. taux normal",
			'44566500'        => "1131 - Déductible intracomm taux normal",
			'44566110'        => "1201 - Déductible encaissements taux normal",
			'44571200'        => "2101 - Collectée débits taux normal",
			'44571201'        => "2102 - Collectée débits taux intermédiaire réduit",
			'44571202'        => "2103 - Collectée débits taux normal spécifique",
			'44571203'        => "2104 - Collectée débits taux réduit",
			'44571210'        => "2201 - Collectée encaissements taux normal",
			'44521000'        => "3111 - TVA due autoliquidation",
			'44566100'        => "3122 - TVA due intracomm. taux normal – Immo",
			'clientCEE'       => "2131 - Collectée débits intracomm taux normal",
			'fournisseurCEE'  => "1131 - Déductible débits intracomm taux normal",
		];

		if (isset($mappingTVA[$code_compta])) {
			return $mappingTVA[$code_compta];
		}

		if ($type_facture === 'F' && $isTiersCEE) {
			return $mappingTVA['fournisseurCEE'];
		}

		if ($type_facture === 'C' && $isTiersCEE) {
			return $mappingTVA['clientCEE'];
		}

		return $mappingTVA['other'];
	}

	/**
	 * Supprime les blancs, puis reformate avec un blanc tous les 2 caractères.
	 * Exemple: '+33601-22-31-31' → '+3 36 01 22 31 31' sans l'option $splitIndicatifInternational
	 *                              '+33 60 12 23 13 1' avec l'option $splitIndicatifInternational
	 * @param string $telNum
	 */
	private function _formatTelNumKoesio($telNum)
	{
		if (empty($telNum)) return '';
		// suppression des espaces, tirets, points, etc.
		$telNum = preg_replace('/[\s_.-]/', '', $telNum);

		$ret = implode(' ', str_split($telNum, 2));
		return $ret;
	}

	/**
	 * @param int $n  number
	 * @return string  Representation of the number using ',' as a decimal separator
	 */
	private function _numberFormat($n) {
		// TODO: ce n'est pas le rôle de cette fonction de faire ça, ça devrait faire partie du format et être dans parent::get_line.
		return strtr($n, ['.' => ',', ',' => '', ' ' => '']);
	}
}
