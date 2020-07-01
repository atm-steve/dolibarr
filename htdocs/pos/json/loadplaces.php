<?php
/* Copyright (C) 2013 Andreu Bisquerra Gayà	<andreu@artadigital.com>
 * Released under the MIT license
 */
$res=@include("../../main.inc.php");
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../../main.inc.php");
$id = GETPOST('id');
$action = GETPOST('action');
$zone = GETPOST('zone');
$result=$user->fetch('','admin');
$user->getrights();


//Get records from database
$sql="DELETE from ".MAIN_DB_PREFIX."pos_places_bar where name=''";
$resql = $db->query($sql);
$sql="SELECT name as place, left_pos, top_pos from ".MAIN_DB_PREFIX."pos_places_bar where zone=$zone";
$resql = $db->query($sql);

//Add all records to an array
$rows = array();
while($row = $db->fetch_array ($resql))
{
    $rows[] = $row;
}


echo json_encode($rows);
