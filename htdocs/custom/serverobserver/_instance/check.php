<?php

	if(is_file('../main.inc.php'))$dir = '../';
	else  if(is_file('../../../main.inc.php'))$dir = '../../../';
	else  if(is_file('../../../../main.inc.php'))$dir = '../../../../';
	else  if(is_file('../../../../../main.inc.php'))$dir = '../../../../../';
	else $dir = '../../';

	require $dir.'master.inc.php';

	$result = new stdClass;
	
	$result->dolibarr = new stdClass;
	$result->dolibarr->version = DOL_VERSION;
	$result->dolibarr->version1 = $conf->global->MAIN_VERSION_LAST_INSTALL;
	$result->dolibarr->theme = $conf->theme;
	
	$result->dolibarr->path=new stdClass;
	$result->dolibarr->path->http = dol_buildpath('/',2); 
	$result->dolibarr->path->relative = dol_buildpath('/var/www/client/',1);
	$result->dolibarr->path->absolute = dol_buildpath('/var/www/client/',0);
	
	$result->dolibarr->data = new stdClass;
	$result->dolibarr->data->path = DOL_DATA_ROOT;
	$result->dolibarr->data->size = _dir_size(DOL_DATA_ROOT);
	
	$result->dolibarr->htdocs=new stdClass;
	$result->dolibarr->htdocs->path = DOL_DOCUMENT_ROOT;
	$result->dolibarr->htdocs->size = _dir_size(DOL_DOCUMENT_ROOT);
	
	$result->dolibarr->repertoire_client=new stdClass;
	$result->dolibarr->repertoire_client->path = dirname(dirname(DOL_DOCUMENT_ROOT));
	$result->dolibarr->repertoire_client->size = _dir_size($result->dolibarr->repertoire_client->path);
	
	$result->db=new stdClass;
	$result->db->host = $dolibarr_main_db_host;
	$result->db->name = $dolibarr_main_db_name;
	$result->db->user = $dolibarr_main_db_user;
	$result->db->type = $dolibarr_main_db_type;
	
	$result->user=new stdClass;
	$result->user->all = _nb_user();
	$result->user->active = _nb_user(true);
	$result->user->date_last_login = _last_login() ;
	
	$result->module = new stdClass;
	
	$result->module = _module_active();
	
	$result->server_info = _getServerInfo();
	//var_dump($result);
	
	echo json_encode($result);
	
function _module_active() {
	
	global $db;
	
	$sql="SELECT name FROM ".MAIN_DB_PREFIX."const WHERE name LIKE 'MAIN_MODULE_%' AND value=1 ORDER BY name";
	
	$res = $db->query($sql);
	$Tab=array();
	while($obj = $db->fetch_object($res)) {
		if(preg_match('/^MAIN_MODULE_([0-9A-Z]+)$/i',$obj->name,$reg)) {
			$name =ucfirst(strtolower($reg[1]));
			
			if(!in_array($name,$Tab)) $Tab[] = $name;
		}
	}
	
	return $Tab;
}	
	
function _dir_size($dir) {
	
	// taile en Mo
	
	$io = popen ( 'du -sm ' . $dir, 'r' );
    $size = fgets ( $io, 4096);
    $size = substr ( $size, 0, strpos ( $size, "\t" ) );
    pclose ( $io );
	
	return (int)$size;
}

function _last_login() {
	global $db;

        $sql = "SELECT MAX(datelastlogin) as datelastlogin FROM ".MAIN_DB_PREFIX."user WHERE 1 ";
        $sql.=" AND statut=1 AND rowid>1"; // pas l'admin

        $res = $db->query($sql);

        $obj = $db->fetch_object($res);

        return $obj->datelastlogin;

}
	
function _nb_user($just_actif = false) {
	global $db;
	
	$sql = "SELECT count(*) as nb FROM ".MAIN_DB_PREFIX."user WHERE 1 ";
	
	if($just_actif) {
		$sql.=" AND statut=1 ";
	}
	
	$res = $db->query($sql);
	
	$obj = $db->fetch_object($res);
	
	return (int)$obj->nb;
	
	
}	


function _getServerInfo()
{
        $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
        $base = 1024;

        $root = DOL_DOCUMENT_ROOT.'/../../../';

        $bytes_total = disk_total_space($root);
        $bytes_left = disk_free_space($root);
        $bytes_used = $bytes_total - $bytes_left;

        $class = min((int)log($bytes_total , $base) , count($si_prefix) - 1);
        $espace_total = sprintf('%1.2f' , $bytes_total / pow($base,$class)) . ' ' . $si_prefix[$class];

        $class = min((int)log($bytes_left , $base) , count($si_prefix) - 1);
        $espace_left = sprintf('%1.2f' , $bytes_left / pow($base,$class)) . ' ' . $si_prefix[$class];

        $class = min((int)log($bytes_used , $base) , count($si_prefix) - 1);
        $espace_used = sprintf('%1.2f' , $bytes_used / pow($base,$class)) . ' ' . $si_prefix[$class];

        $percent_used = sprintf('%1.2f %%' , $bytes_used * 100 / $bytes_total, true);

        return array(
                'espace_total' => $espace_total
                ,'espace_restant' => $espace_left
                ,'espace_used' => $espace_used
                ,'percent_used' => $percent_used
        );

}

