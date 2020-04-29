<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require('../config.php');

}

global $db;

dol_include_once('/core/class/extrafields.class.php');
$extrafields=new ExtraFields($db);
$res = $extrafields->addExtraField('serverobserverchecker', 'CheckerURL', 'varchar', 0, 255, 'societe',0, 0, '', 0, 1);
	
//var_dump($res,$extrafields);exit;

/* uncomment


dol_include_once('/mymodule/class/xxx.class.php');

$PDOdb=new TPDOdb;

$o=new TXXX($db);
$o->init_db_by_vars($PDOdb);
*/
