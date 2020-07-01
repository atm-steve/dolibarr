#!/usr/bin/env php
<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018      Jean-François Ferry  <hello+jf@librethic.io>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
 *      \file       scripts/suppliersales.php
 *		\ingroup    marketplace
 *      \brief      This file is an example for a command line script to work on Sales
 */

$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path=dirname(__FILE__).'/';

// Test if batch mode
if (substr($sapi_type, 0, 3) == 'cgi') {
    echo "Error: You are using PHP for CGI. To execute ".$script_file." from command line, you must use PHP for CLI mode.\n";
    exit(-1);
}

// Global variables
$version='1.0';
$error=0;


// -------------------- START OF YOUR CODE HERE --------------------
@set_time_limit(0);							// No timeout for this script
define('EVEN_IF_ONLY_LOGIN_ALLOWED', 1);		// Set this define to 0 if you want to lock your script when dolibarr setup is "locked to admin user only".

// Load Dolibarr environment
$res=0;
// Try master.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];
$tmp2=realpath(__FILE__);
$i=strlen($tmp)-1;
$j=strlen($tmp2)-1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) {
    $i--;
    $j--;
}
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/master.inc.php")) {
    $res=@include substr($tmp, 0, ($i+1))."/master.inc.php";
}
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/master.inc.php")) {
    $res=@include dirname(substr($tmp, 0, ($i+1)))."/master.inc.php";
}
// Try master.inc.php using relative path
if (! $res && file_exists("../master.inc.php")) {
    $res=@include "../master.inc.php";
}
if (! $res && file_exists("../../master.inc.php")) {
    $res=@include "../../master.inc.php";
}
if (! $res && file_exists("../../../master.inc.php")) {
    $res=@include "../../../master.inc.php";
}
if (! $res) {
    die("Include of master fails");
}
// After this $db, $mysoc, $langs, $conf and $hookmanager are defined (Opened $db handler to database will be closed at end of file).
// $user is created but empty.

//$langs->setDefaultLang('en_US'); 	// To change default language of $langs
$langs->load("main");				// To load language file for default language

// Load user and its permissions
$result=$user->fetch('', 'admin');	// Load user for login 'admin'. Comment line to run as anonymous user.
if (! $result > 0) {
    dol_print_error('', $user->error);
    exit;
}
$user->getrights();


print "***** ".$script_file." (".$version.") pid=".dol_getmypid()." *****\n";
//if (! isset($argv[1])) {	// Check parameters
 //   print "Usage: ".$script_file." param1 param2 ...\n";
//	exit(-1);
//}
print '--- start'."\n";
print 'Argument 1='.$argv[1]."\n";
print 'Argument 2='.$argv[2]."\n";


// Start of transaction
$db->begin();


// Examples for manipulating class Sales
dol_include_once("/marketplace/class/sales.class.php");
$suppliersales=new Sales($db);

// Example for inserting creating object in database
/*
dol_syslog($script_file." CREATE", LOG_DEBUG);
$suppliersales->prop1='value_prop1';
$suppliersales->prop2='value_prop2';
$id=$suppliersales->create($user);
if ($id < 0) { $error++; dol_print_error($db,$suppliersales->error); }
else print "Object created with id=".$id."\n";
*/

// Example for reading object from database
/*
dol_syslog($script_file." FETCH", LOG_DEBUG);
$result=$suppliersales->fetch($id);
if ($result < 0) { $error; dol_print_error($db,$suppliersales->error); }
else print "Object with id=".$id." loaded\n";
*/

// Example for updating object in database ($suppliersales must have been loaded by a fetch before)
/*
dol_syslog($script_file." UPDATE", LOG_DEBUG);
$suppliersales->prop1='newvalue_prop1';
$suppliersales->prop2='newvalue_prop2';
$result=$suppliersales->update($user);
if ($result < 0) { $error++; dol_print_error($db,$suppliersales->error); }
else print "Object with id ".$suppliersales->id." updated\n";
*/

// Example for deleting object in database ($suppliersales must have been loaded by a fetch before)
/*
dol_syslog($script_file." DELETE", LOG_DEBUG);
$result=$suppliersales->delete($user);
if ($result < 0) { $error++; dol_print_error($db,$suppliersales->error); }
else print "Object with id ".$suppliersales->id." deleted\n";
*/

$mpSales = new Sales($db);

// An example of a direct SQL read without using the fetch method
$sql = "SELECT rowid, price, collection_rate, collection_amount, tax_rate, care_rate, discount_rate, discount_amount, retrocession_amount";
$sql.= " FROM ".MAIN_DB_PREFIX."marketplace_sales";
$sql.= " WHERE (discount_rate > 0 OR care_rate > 0)";
$sql.= " AND (fk_seller_invoice IS NULL OR fk_seller_invoice=0)";
$sql.= " ORDER BY rowid ASC";

//print $sql; exit;
dol_syslog($script_file, LOG_DEBUG);
$resql=$db->query($sql);
if ($resql) {
    $num = $db->num_rows($resql);
    $i = 0;
    if ($num) {
        while ($i < $num) {
            $obj = $db->fetch_object($resql);
            if ($obj) {
                $mpSales->fetch($obj->id);

                // Calculate price brut
                //$totalPrice = $obj->price + ($obj->price * $obj->discount_rate / 100);

                $mpSales->discount_rate = $obj->discount_rate;
                $mpSales->collection_rate = $obj->collection_rate;
                $mpSales->care_rate = $obj->care_rate;
                $mpSales->tax_rate = $obj->tax_rate;

                if (!empty($conf->global->MARKETPLACE_USE_DISCOUNT_AS_CARE_RATE)) {
                    $mpSales->care_rate = $mpSales->discount_rate;
                    $mpSales->discount_rate = 0;
                }
                $newRetroAmount = $mpSales->calculateRetrocessionAmount($obj->price);
                $newCollectionAmount = $mpSales->calculateCollectionAmount($obj->price);

                $priceBrut = $mpSales->calculatePriceBrut($obj->price);
                $careAmount = $mpSales->calculateCareAmount($priceBrut);

                print "line ".$obj->rowid .":: Price : ". $obj->price . " / brut : ". $priceBrut." / collection rate : " . $mpSales->collection_rate . " / Discount : ". $mpSales->discount_rate. " / Care rate : " . $mpSales->care_rate . " / Old retro amount : ".$obj->retrocession_amount ." / New retro amount : ". $newRetroAmount." / coll amount ". $newCollectionAmount ." / Diff : ". ( $newRetroAmount - $obj->retrocession_amount )."\n";

                // Update line
                $sql = 'UPDATE ' . MAIN_DB_PREFIX . 'marketplace_sales';
                $sql .= ' SET retrocession_amount="' . $newRetroAmount.'"';
                $sql .= ", collection_amount='" . $newCollectionAmount . "'";
                $sql .= ", discount_rate='" . $mpSales->discount_rate . "'";
                $sql .= ", care_rate='" . $mpSales->care_rate . "'";
                $sql .= ", care_amount='" . $careAmount . "'";
                $sql .= ' WHERE rowid =' . $obj->rowid;
                $resql2 = $db->query($sql);
                if ($resql2) {
                    // Call trigger
                    //$result=$this->call_trigger('BILL_SUPPLIER_PAYED',$user);
                    //if ($result < 0) $error++;
                    // End call triggers

                    $nbUpdated++;
                } else {
                    $error++;
                }
            }
            $i++;
        }
    }
} else {
    $error++;
    dol_print_error($db);
}



// -------------------- END OF YOUR CODE --------------------

if (! $error) {
    $db->commit();
    print '--- end ok'."\n";
    
    print $nbUpdated . " lines updated \n";
} else {
    print '--- end error code='.$error."\n";
    $db->rollback();
}

$db->close();	// Close $db database opened handler

exit($error);
