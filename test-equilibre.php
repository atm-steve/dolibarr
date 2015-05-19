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



$file = file('ebpecritures_comptables_vente20150518171714.txt');

if(isset($_REQUEST['SAGE'])){
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
}

if(isset($_REQUEST['EBP'])){
	$filename = 'ebpecritures_comptables_vente20150518171714.txt';
	while ($line = fgetcsv($filename,1024,',','"')) {
		var_dump($line);
	}
}

var_dump($Tab);

