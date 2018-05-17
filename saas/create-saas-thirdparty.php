#!/usr/bin/env php
<?php

    $company_code = '';

	require '../htdocs/master.inc.php';

	$sapi_type = php_sapi_name();
	$script_file = basename(__FILE__);
	$path=dirname(__FILE__).'/';

	// Test if batch mode
	if (substr($sapi_type, 0, 3) != 'cli') {
	    echo "Error: You are using PHP for CGI. To execute ".$script_file." from command line, you must use PHP for CLI mode.\n";
	    exit(-1);
	}

	//TODO $argv[1] securekey
	$fk_soc = $argv[1];

	if($fk_soc>0) {

		$user=new User($db);
		$user->fetch(1);

		$societe = new Societe($db);
		if($societe->fetch($fk_soc)>0) {

			if(empty($societe->array_options['options_saas_env'] )) {

				$societe->array_options['options_saas_env'] = _get_env_code($societe);
				$societe->array_options['options_saas_status'] = 'todo';

				$societe->update($societe->id, $user);
			}


		}


	}

function _get_env_code(&$societe,$randmax=999) {
	global $db;

    $code =strtoupper(dol_sanitizeFileName(substr(trim($societe->name),0,10).rand(100,$randmax)));

	$res = $db->query("SELECT COUNT(*) FROM ".MAIN_DB_PREFIX."societe_extrafields WHERE saas_env='".$code."'");
	$obj = $db->fetch_object($res);

	if($obj->nb == 0) {
		return $code;
	}
	else {
	    return _get_env_code($societe,$randmax*2);
	}


}