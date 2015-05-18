<?php

//$file = file('XIMPORT - copie Ticket 1408.txt');
/*$file = file('XIMPORT-1.TXT');

foreach($file as $row) {
	$piece = substr($row,23,7);
	$amount = (double)substr($row,74,12);
	$type = substr($row,86,1);

	if($type == 'D')@$Tab[$piece]-=$amount;
	else @$Tab[$piece]+=$amount;

}

var_dump($Tab);

*/



$file = file('sageecritures_comptables_vente20150121095411.txt');

foreach($file as $row) {
	$piece = substr($row,25,9);
	$amount = (double)substr($row,96,14);
	$amount2 = (double)substr($row,110,14);
	$type = $row[155];
	
	if($type == 'G') {

//var_dump($piece,$amount,$amount2);
	@$Tab[$piece]-=$amount2;
	@$Tab[$piece]+=$amount;

	}
}

var_dump($Tab);

