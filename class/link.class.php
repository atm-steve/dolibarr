<?php

class TExportComptaLinkCat extends TObjetStd {
	
	function __construct() {
		 
        parent::set_table(MAIN_DB_PREFIX.'exportcompta_link_category');
        parent::add_champs('fk_category,fk_category_product',array('type'=>'int','index'=>true));
		parent::add_champs('type_category',array('type'=>'string', 'length'=>30,'index'=>true));
		
        parent::_init_vars('code_compta');
        parent::start();    
		
	}

	static function getCategoryProduct(&$PDOdb, $fk_category, $type) {
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."exportcompta_link_category
			WHERE fk_category=".$fk_category_customer." AND type_category='".$type."' 
		";
		$Tab = $PDOdb->ExecuteAsArray($sql);
		
		$TLink = array();
		foreach($Tab as $row) {
			
			$l=new TExportComptaLinkCat;
			$l->load($PDOdb, $row->rowid);
			
			$TLink[] = $l;
		}
		
		return $TLink;
		
	}
	
}
