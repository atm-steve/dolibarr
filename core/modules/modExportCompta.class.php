<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2009 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010 Regis Houssin        <regis@dolibarr.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 		\defgroup   asset     Module Asset
 *      \brief      Asset management (Products related to companies
 */

/**
 *      \file       htdocs/includes/modules/modOdtdocs.class.php
 *      \ingroup    asset
 *      \brief      Description and activation file for module Asset
 *		\version	$Id: modOdtdocs.class.php,v 1.67 2011/11/08 15:58:32 atm-maxime Exp $
 */
include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");



/**
 * 		\class      modOdtdocs
 *      \brief      Description and activation class for module Asset
 */
class modExportCompta extends DolibarrModules
{
	/**
	 *   \brief      Constructor. Define names, constants, directories, boxes, permissions
	 *   \param      DB      Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;


		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 104180;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'exportcompta';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "ATM";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Export Comptabilité format propriétaires";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '1.1.1';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='generic';

		// Defined if the directory /mymodule/includes/triggers/ contains triggers or not

        $this->module_parts = array(
                    // Set this to 1 if module has its own trigger directory
                    'triggers' => 1,
                    // Set this to 1 if module has its own login method directory
                    //'login' => 0,
                    // Set this to 1 if module has its own substitution function file
                    //'substitutions' => 0,
                    // Set this to 1 if module has its own menus handler directory
                    //'menus' => 0,
                    // Set this to 1 if module has its own barcode directory
                    //'barcode' => 0,
                    // Set this to 1 if module has its own models directory
                    //'models' => 0,
                    // Set this to relative path of css if module has its own css file
                    //'css' => '/scrumboard/css/mycss.css.php',
                    // Set here all hooks context managed by module
                    'hooks' => array('invoicecard', 'invoicesuppliercard')
                    // Set here all workflow context managed by module
                    //'workflow' => array('order' => array('WORKFLOW_ORDER_AUTOCREATE_INVOICE'))
        );
		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array();
		$r=0;

		// Relative path to module style sheet if exists. Example: '/mymodule/css/mycss.css'.
//		$this->style_sheet = '/css/style.css';

		// Config pages. Put here list of php page names stored in admmin directory used to setup module.
		$this->config_page_url = 'admin.php@exportcompta';

		// Dependencies
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->phpmin = array(5,0);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,0);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("exportcompta@exportcompta");

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0) );
		//                             2=>array('MAIN_MODULE_MYMODULE_NEEDSMARTY','chaine',1,'Constant to say module need smarty',1)
		$this->const = array(
			0=>array('EXPORT_COMPTA_ALL_ENTITIES','chaine','0','Acccountancy export made across multi-companies', 1, 'current', 1),
			1=>array('EXPORT_COMPTA_DATE_FACTURES_CLIENT','chaine','datef','Date on which acccountancy export will be based for sales', 1, 'current', 1),
			2=>array('EXPORT_COMPTA_DATE_FACTURES_FOURNISSEUR','chaine','datef','Date on which acccountancy export will be based for purchases', 1, 'current', 1),
			3=>array('EXPORT_COMPTA_DATE_FACTURES_NDF','chaine','datee','Date on which acccountancy export will be based for ndf', 1, 'current', 1),
			4=>array('EXPORT_COMPTA_DATE_BANK','chaine','datev','Date on which acccountancy export will be based for bank', 1, 'current', 1)
		);

		// Array to add new pages in new tabs
		// Example: $this->tabs = array('objecttype:+tabname1:Title1:@mymodule:$user->rights->mymodule->read:/mymodule/mynewtab1.php?id=__ID__',  // To add a new tab identified by code tabname1
        //                              'objecttype:+tabname2:Title2:@mymodule:$user->rights->othermodule->read:/mymodule/mynewtab2.php?id=__ID__',  // To add another new tab identified by code tabname2
        //                              'objecttype:-tabname');                                                     // To remove an existing tab identified by code tabname
		// where objecttype can be
		// 'thirdparty'       to add a tab in third party view
		// 'intervention'     to add a tab in intervention view
		// 'order_supplier'   to add a tab in supplier order view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'invoice'          to add a tab in customer invoice view
		// 'order'            to add a tab in customer order view
		// 'product'          to add a tab in product view
		// 'stock'            to add a tab in stock view
		// 'propal'           to add a tab in propal view
		// 'member'           to add a tab in fundation member view
		// 'contract'         to add a tab in contract view
		// 'user'             to add a tab in user view
		// 'group'            to add a tab in group view
		// 'contact'          to add a tab in contact view
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
        $this->tabs = array(
			'categories_2:+exportcompta:Comptabilité:exportcompta@exportcompta:$user->rights->exportcompta->linkcat:/exportcompta/linkcategory.php?id=__ID__&type=customer',
			'categories_customer:+exportcompta:Comptabilité:exportcompta@exportcompta:$user->rights->exportcompta->linkcat:/exportcompta/linkcategory.php?id=__ID__&type=customer'
		);


        // Dictionnaries
        $this->dictionnaries=array();
        /*
        $this->dictionnaries=array(
            'langs'=>'cabinetmed@cabinetmed',
            'tabname'=>array(MAIN_DB_PREFIX."cabinetmed_diaglec",MAIN_DB_PREFIX."cabinetmed_examenprescrit",MAIN_DB_PREFIX."cabinetmed_motifcons"),
            'tablib'=>array("DiagnostiqueLesionnel","ExamenPrescrit","MotifConsultation"),
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'cabinetmed_diaglec as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'cabinetmed_examenprescrit as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'cabinetmed_motifcons as f'),
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),
            'tabfield'=>array("code,label","code,label","code,label"),
            'tabfieldvalue'=>array("code,label","code,label","code,label"),
            'tabfieldinsert'=>array("code,label","code,label","code,label"),
            'tabrowid'=>array("rowid","rowid","rowid"),
            'tabcond'=>array($conf->cabinetmed->enabled,$conf->cabinetmed->enabled,$conf->cabinetmed->enabled)
        );
        */

        // Boxes
		// Add here list of php file(s) stored in includes/boxes that contains class to show a box.
        $this->boxes = array();			// List of boxes
		$r=0;
		// Example:
		/*
		$this->boxes[$r][1] = "myboxa.php";
		$r++;
		$this->boxes[$r][1] = "myboxb.php";
		$r++;
		*/

		// Permissions
		$this->rights = array();		// Permission array used by this module
		$r=0;
		$this->rights[$r][0] = 10050; 				// Permission id (must not be already used)
		$this->rights[$r][1] = 'Générer des export comptables';	// Permission label
		$this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'generate';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;

		$this->rights[$r][0] = $this->numero.$r; 				// Permission id (must not be already used)
		$this->rights[$r][1] = 'Lier une catégorie client à un code comptable produit';	// Permission label
		$this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'linkcat';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;



		// Main menu entries
		$this->menus = array();			// List of menus to add

		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=accountancy',		// Use r=value where r is index key used for the parent menu entry (higher parent must be a top menu entry)
			'type'=>'left',			// This is a Left menu entry
			'titre'=>'Exports comptables',
			'mainmenu'=>'accountancy',
			'leftmenu'=>'export',
			'url'=>'/exportcompta/export.php',
			'langs'=>'exportcompta@exportcompta',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>100,
			'enabled'=>'$conf->exportcompta->enabled && $user->rights->exportcompta->generate',			// Define condition to show or hide menu entry. Use '$conf->monmodule->enabled' if entry must be visible if module is enabled.
			'perms'=>'1',			// Use 'perms'=>'$user->rights->monmodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'level'=>1,
			'user'=>0);

		$r++;
		// Exports
		$r=1;

		// Example:
		// $this->export_code[$r]=$this->rights_class.'_'.$r;
		// $this->export_label[$r]='CustomersInvoicesAndInvoiceLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
        // $this->export_enabled[$r]='1';                               // Condition to show export in list (ie: '$user->id==3'). Set to 1 to always show when module is enabled.
		// $this->export_permission[$r]=array(array("facture","facture","export"));
		// $this->export_fields_array[$r]=array('s.rowid'=>"IdCompany",'s.nom'=>'CompanyName','s.address'=>'Address','s.cp'=>'Zip','s.ville'=>'Town','s.fk_pays'=>'Country','s.tel'=>'Phone','s.siren'=>'ProfId1','s.siret'=>'ProfId2','s.ape'=>'ProfId3','s.idprof4'=>'ProfId4','s.code_compta'=>'CustomerAccountancyCode','s.code_compta_fournisseur'=>'SupplierAccountancyCode','f.rowid'=>"InvoiceId",'f.facnumber'=>"InvoiceRef",'f.datec'=>"InvoiceDateCreation",'f.datef'=>"DateInvoice",'f.total'=>"TotalHT",'f.total_ttc'=>"TotalTTC",'f.tva'=>"TotalVAT",'f.paye'=>"InvoicePaid",'f.fk_statut'=>'InvoiceStatus','f.note'=>"InvoiceNote",'fd.rowid'=>'LineId','fd.description'=>"LineDescription",'fd.price'=>"LineUnitPrice",'fd.tva_tx'=>"LineVATRate",'fd.qty'=>"LineQty",'fd.total_ht'=>"LineTotalHT",'fd.total_tva'=>"LineTotalTVA",'fd.total_ttc'=>"LineTotalTTC",'fd.date_start'=>"DateStart",'fd.date_end'=>"DateEnd",'fd.fk_product'=>'ProductId','p.ref'=>'ProductRef');
		// $this->export_entities_array[$r]=array('s.rowid'=>"company",'s.nom'=>'company','s.address'=>'company','s.cp'=>'company','s.ville'=>'company','s.fk_pays'=>'company','s.tel'=>'company','s.siren'=>'company','s.siret'=>'company','s.ape'=>'company','s.idprof4'=>'company','s.code_compta'=>'company','s.code_compta_fournisseur'=>'company','f.rowid'=>"invoice",'f.facnumber'=>"invoice",'f.datec'=>"invoice",'f.datef'=>"invoice",'f.total'=>"invoice",'f.total_ttc'=>"invoice",'f.tva'=>"invoice",'f.paye'=>"invoice",'f.fk_statut'=>'invoice','f.note'=>"invoice",'fd.rowid'=>'invoice_line','fd.description'=>"invoice_line",'fd.price'=>"invoice_line",'fd.total_ht'=>"invoice_line",'fd.total_tva'=>"invoice_line",'fd.total_ttc'=>"invoice_line",'fd.tva_tx'=>"invoice_line",'fd.qty'=>"invoice_line",'fd.date_start'=>"invoice_line",'fd.date_end'=>"invoice_line",'fd.fk_product'=>'product','p.ref'=>'product');
		// $this->export_sql_start[$r]='SELECT DISTINCT ';
		// $this->export_sql_end[$r]  =' FROM ('.MAIN_DB_PREFIX.'facture as f, '.MAIN_DB_PREFIX.'facturedet as fd, '.MAIN_DB_PREFIX.'societe as s)';
		// $this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'product as p on (fd.fk_product = p.rowid)';
		// $this->export_sql_end[$r] .=' WHERE f.fk_soc = s.rowid AND f.rowid = fd.fk_facture';
		// $r++;
	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories.
	 *      @return     int             1 if OK, 0 if KO
	 */
	function init($options = '')
	{
		$sql = array();

		define('INC_FROM_DOLIBARR',true);
		 dol_include_once('/exportcompta/config.php');
        dol_include_once('/exportcompta/script/create-maj-base.php');

		dol_include_once('/core/class/extrafields.class.php');
        $extrafields=new ExtraFields($this->db);
		$res = $extrafields->addExtraField('date_compta', 'Comptabilisé le', 'datetime', 0, '', 'facture');

        $res = $extrafields->addExtraField('date_compta', 'Comptabilisé le', 'datetime', 0, '', 'facture_fourn');

        $res = $extrafields->addExtraField('code_tva_achat', 'Compte de TVA spécifique (achat)', 'varchar', 0, 30, 'product',0,0,'');
        $res = $extrafields->addExtraField('code_tva', 'Compte de TVA spécifique (vente)', 'varchar', 0, 30, 'product',0,0,'');

        $res = $extrafields->addExtraField('code_tva_achat', 'Compte de TVA spécifique (achat)', 'varchar', 0, 30, 'societe',0,0,'');
        $res = $extrafields->addExtraField('code_tva', 'Compte de TVA spécifique (vente)', 'varchar', 0, 30, 'societe',0,0,'');

        $res = $extrafields->addExtraField('fk_soc_affacturage', 'Tiers pour  affacturage', 'sellist', 0, '', 'societe',0,0,'',serialize(array('options'=>array('societe:nom:rowid'=>null))));


        $this->db->query("ALTER TABLE ".MAIN_DB_PREFIX."c_tva ADD accountancy_code_sell_service varchar(32) NULL");
        $this->db->query("ALTER TABLE ".MAIN_DB_PREFIX."c_tva ADD accountancy_code_buy_service varchar(32) NULL");

        $result=$this->load_tables();

		return $this->_init($sql);
	}

	/**
	 *		Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted.
	 *      @return     int             1 if OK, 0 if KO
	 */
	function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql);
	}


	/**
	 *		\brief		Create tables, keys and data required by module
	 * 					Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * 					and create data commands must be stored in directory /mymodule/sql/
	 *					This function is called by this->init.
	 * 		\return		int		<=0 if KO, >0 if OK
	 */
	function load_tables()
	{
		return $this->_load_tables( '/exportcompta/sql/');
	}


}

?>
