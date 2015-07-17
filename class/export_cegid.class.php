<?php
/*************************************************************************************************************************************************
 * Format d'export comptable CEGID
 *************************************************************************************************************************************************/

class TExportComptaCegid extends TExportCompta {
	
	function __construct($db, $exportAllreadyExported=false) {
		
		parent::__construct($db, $exportAllreadyExported);
		
		$this->_format_ecritures_comptables_vente = array(
			array('name' => 'date_ecriture',		'length' => 8,	'default' => '',	'type' => 'date',	'format' => 'dmY'),
			array('name' => 'code_journal',			'length' => 3,	'default' => 'VEN',	'type' => 'text'),
			array('name' => 'numero_compte',		'length' => 17,	'default' => '0',	'type' => 'text'),
			array('name' => 'numero_compte_aux',	'length' => 17,	'default' => '0',	'type' => 'text'),
			array('name' => 'sens',					'length' => 1,	'default' => 'D',	'type' => 'text',),
			array('name' => 'montant',				'length' => 20,	'default' => '0',	'type' => 'text',),
			array('name' => 'libelle',				'length' => 35,	'default' => '',	'type' => 'text'),
			array('name' => 'numero_piece',			'length' => 35,	'default' => '',	'type' => 'text')
		);
				
		$this->_format_ecritures_comptables_achat = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_achat[1]['default'] = 'AC';
		
		$this->_format_ecritures_comptables_banque = $this->_format_ecritures_comptables_vente;
		$this->_format_ecritures_comptables_banque[1]['default'] = '';
		
		$this->_format_reglement_tiers = $this->_format_ecritures_comptables_vente;
		
		$this->lineSeparator = "\r\n";
		$this->fieldSeparator = ';';
		$this->fieldPadding = false;
		
		unset($this->TTypeExport['produits']); // pas encore pris en charge
		unset($this->TTypeExport['reglement_tiers']); // pas encore pris en charge
		unset($this->TTypeExport['tiers']); // pas encore pris en charge
	}
	
	function get_file_ecritures_comptables_ventes($format, $dt_deb, $dt_fin) {
		global $conf;

		if(empty($format)) $format = $this->_format_ecritures_comptables_vente;

		$TabFactures = parent::get_factures_client($dt_deb, $dt_fin);
		
		$contenuFichier = '';

		$numEcriture = 1;
		$numLignes = 1;
		
		foreach ($TabFactures as $id_facture => $infosFacture) {
			$tiers = &$infosFacture['tiers'];
			$facture = &$infosFacture['facture'];

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> '41100000',
					'numero_compte_aux'				=> $code_compta,
					'libelle'						=> $tiers['nom'],
					'sens'							=> ($facture['type'] == 2 ? 'C' : 'D'),
					'montant'						=> number_format(abs($montant),2,',',''),
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_ecriture'					=> $facture['date'],
					'numero_piece'					=> $facture['ref'],
					'numero_compte'					=> $code_compta,
					'libelle'						=> $tiers['nom'],
					'sens'							=> (($facture['type'] == 2 || $montant < 0) ? 'D' : 'C'),
					'montant'						=> number_format(abs($montant),2,',',''),
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				
				$numLignes++;
			}

			// Lignes TVA
			if(!empty($infosFacture['ligne_tva'])) {
				foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
					$ligneFichier = array(
						'date_ecriture'					=> $facture['date'],
						'numero_piece'					=> $facture['ref'],
						'numero_compte'					=> $code_compta,
						'libelle'						=> $tiers['nom'],
						'sens'							=> ($facture['type'] == 2 ? 'D' : 'C'),
						'montant'						=> number_format(abs($montant),2,',',''),
					);
					
					// Ecriture générale
					$contenuFichier .= parent::get_line($format, $ligneFichier);
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

			// Lignes client
			foreach($infosFacture['ligne_tiers'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_ecriture'			=> $facture['date'],
					'code_journal'			=> $codeJournal,
					'numero_compte'			=> '40100000',
					'numero_compte_aux'		=> $code_compta,
					'sens'					=> ($montant > 0 ? 'C' : 'D'),
					'montant'				=> number_format(abs($montant),2,',',''),
					'libelle'				=> $label,
					'numero_piece'			=> $facture['ref']
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}
			
			// Lignes de produits
			foreach($infosFacture['ligne_produit'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_ecriture'			=> $facture['date'],
					'code_journal'			=> $codeJournal,
					'numero_compte'			=> $code_compta,
					'sens'					=> ($montant > 0 ? 'D' : 'C'),
					'montant'				=> number_format(abs($montant),2,',',''),
					'libelle'				=> $label,
					'numero_piece'			=> $facture['ref']
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				
				// Ecriture analytique
				$numLignes++;
			}

			// Lignes TVA
			if(!empty($infosFacture['ligne_tva'])) {
				foreach($infosFacture['ligne_tva'] as $code_compta => $montant) {
						$ligneFichier = array(
							'date_ecriture'					=> $facture['date'],
							'code_journal'					=> $codeJournal,
							'numero_compte'					=> $code_compta,
							'sens'							=> ($montant > 0 ? 'D' : 'C'),
							'montant'						=> number_format(abs($montant),2,',',''),
							'libelle'						=> $label,
							'numero_piece'					=> $facture['ref']
						);
					
					// Ecriture générale
					$contenuFichier .= parent::get_line($format, $ligneFichier);
					$numLignes++;
				}
			}
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
					'date_ecriture'			=> $bankline['datev'],
					'code_journal'			=> $bank['ref'],
					'numero_compte'			=> $code_compta,
					'sens'					=> ($montant < 0) ? 'D' : 'C',
					'montant'				=> number_format(abs($montant),2,',',''),
					'libelle'				=> $label
				);
				
				// Ecriture générale
				$contenuFichier .= parent::get_line($format, $ligneFichier);
				$numLignes++;
			}
			
			// Lignes banque
			foreach($infosBank['ligne_banque'] as $code_compta => $montant) {
				$ligneFichier = array(
					'date_ecriture'			=> $bankline['datev'],
					'code_journal'			=> $bank['ref'],
					'numero_compte'			=> $code_compta,
					'sens'					=> ($montant < 0) ? 'C' : 'D',
					'montant'				=> number_format(abs($montant),2,',',''),
					'libelle'				=> $label
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
				'date_ecriture'			=> $reglement['datep'],
				'code_journal'			=> 'M',
				'numero_compte'			=> $reglement['code_compta'],
				'sens'					=> 'D',
				'montant'				=> number_format(abs($montant),2,',',''),
				'libelle'				=> $tiers['nom'],
				'numero_piece'			=> $reglement['num_fact']
			);
			
			$contenuFichier .= parent::get_line($format, $ligneFichier);
			$numLignes++;

			$ligneFichier = array(
				'date_ecriture'		=> $reglement['datep'],
				'code_journal'		=> 'C',
				'numero_compte'		=> $tiers['code_compta'],
				'sens'				=> 'C',
				'montant'			=> number_format(abs($montant),2,',',''),
				'libelle'			=> $tiers['nom'],
				'numero_piece'		=> $reglement['num_fact']
			);

			$contenuFichier .= parent::get_line($format, $ligneFichier);
			$numLignes++;
			

			
			$numEcriture++;
		}

		return $contenuFichier;
	}	
}
