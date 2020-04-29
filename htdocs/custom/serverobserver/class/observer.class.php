<?php 

class ServerObserver {
	
	public static function getAllThirdparty() {
		
		global $db;
		
		$res = $db->query("SELECT s.rowid 
				FROM ".MAIN_DB_PREFIX."societe_extrafields sex
					LEFT JOIN ".MAIN_DB_PREFIX."societe s ON (sex.fk_object = s.rowid)
				WHERE sex.serverobserverchecker IS NOT NULL
				ORDER BY s.nom");
		
		$Tab=array();
		while($obj=$db->fetch_object($res)) {
			
			$Tab[] = $obj->rowid;
			
		}
	
		return $Tab;
	}
	
}
