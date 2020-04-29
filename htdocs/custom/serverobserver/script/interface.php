<?php

	require '../config.php';
	ini_set('default_socket_timeout', 5);

	$get=GETPOST('get');
	
	
	switch($get) {
		
		case 'status':
			
			echo json_encode(_status(GETPOST('fk_soc')));
			
			break;
		
	}
	
function _status($fk_soc) {
	global $db;
	
	$societe = new Societe($db);
	
	if($societe->fetch($fk_soc)>0) {
		$res = @file_get_contents($societe->array_options['options_serverobserverchecker']);
		if($res !== false) $data = json_decode($res);
		
		if(!empty($data)) {
			
			$data->ok=1;
			$data->fk_soc = $fk_soc;
		
			return $data;
		}
	}
	
	$data=new stdClass();
	$data->ok=0;
	$data->fk_soc = $fk_soc;
	
	return $data;
}
