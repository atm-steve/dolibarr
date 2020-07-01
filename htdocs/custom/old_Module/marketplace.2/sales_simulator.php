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
 *   	\file       sales_simulator.php
 *		\ingroup    marketplace
 *		\brief      Form to calculate sales value (collection / retrocession)
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB','1');					// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER','1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC','1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN','1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION','1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION','1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK','1');					// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL','1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK','1');				// Do not check style html tag into posted data
//if (! defined('NOIPCHECK'))                define('NOIPCHECK','1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU','1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML','1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX','1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN",'1');						// If this page is public (can be called outside logged session)
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT','auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE','aloginmodule');		// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN',1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
    $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];
$tmp2=realpath(__FILE__);
$i=strlen($tmp)-1;
$j=strlen($tmp2)-1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) {
    $i--;
    $j--;
}
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) {
    $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
}
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) {
    $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) {
    $res=@include "../main.inc.php";
}
if (! $res && file_exists("../../main.inc.php")) {
    $res=@include "../../main.inc.php";
}
if (! $res && file_exists("../../../main.inc.php")) {
    $res=@include "../../../main.inc.php";
}
if (! $res) {
    die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
dol_include_once('/marketplace/class/sales.class.php');
dol_include_once('/formstyler/class/formstyler.class.php');

// Load translation files required by the page
$langs->loadLangs(array("marketplace@marketplace","other"));

$action     = GETPOST('action', 'aZ09')?GETPOST('action', 'aZ09'):'view';				// The action 'add', 'create', 'edit', 'update', 'view', ...
$confirm    = GETPOST('confirm', 'alpha');												// Result of a confirmation
$cancel     = GETPOST('cancel', 'alpha');												// We click on a Cancel button
$contextpage= GETPOST('contextpage', 'aZ')?GETPOST('contextpage', 'aZ'):'marketplacesalesexport';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');											// Go back to a dedicated page
$optioncss  = GETPOST('optioncss', 'aZ');												// Option for the css output (always '' except when 'print')

$id			= GETPOST('id', 'int');
$filter_soc = GETPOST('filter_soc', 'int');

$dateStartday   = GETPOST('dateStartday', 'int');
$dateStartmonth = GETPOST('dateStartmonth', 'int');
$dateStartyear  = GETPOST('dateStartyear', 'int');

$dateEndday     = GETPOST('dateEndday', 'int');
$dateEndmonth   = GETPOST('dateEndmonth', 'int');
$dateEndyear    = GETPOST('dateEndyear', 'int');

$productPrice = GETPOST('product_price', 'int');
$vatRate = GETPOST('vat_rate', 'int');
$discountRate = GETPOST('discount_rate', 'int');
$collectionRate = GETPOST('collection_rate', 'int');
$careRate = GETPOST('care_rate', 'int');


$dateStart      = dol_mktime(0, 0, 0, $dateStartmonth, $dateStartday, $dateStartyear);
$dateEnd        = dol_mktime('23', '59', '59', $dateEndmonth, $dateEndday, $dateEndyear);

//if (! $sortfield) $sortfield="p.date_fin";
//if (! $sortorder) $sortorder="DESC";

// Initialize technical objects
$object=new Sales($db);
$extrafields = new ExtraFields($db);
$hookmanager->initHooks(array('marketplacesuppliersalesexport'));     // Note that conf->hooks_modules contains array

// Security check
$socid=0;
// Protection if not admin
if (!$user->admin) {
    //$socid = $user->societe_id;
    accessforbidden();
}
//$result = restrictedArea($user, 'marketplace', $id, '');

/*
 * Actions
 */

if (GETPOST('cancel', 'alpha')) {
    $action='list';
    $massaction='';
}
if (! GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') {
    $massaction='';
}

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
    if ($action == 'calculate') {
    
    }
}



/*
 * View
 */

$form=new Form($db);
$formstyler = new FormStyler($db, 'sales_simulator');

$now=dol_now();

//$help_url="EN:Module_Sales|FR:Module_Sales_FR|ES:Módulo_Sales";
$help_url='';
$title = $langs->trans('MarketplaceSalesSimulator');


// Output page
// --------------------------------------------------------------------

llxHeader('', $title, $help_url);

$newcardbutton='';
//if ($user->rights->marketplace->creer)
//{
//    $newcardbutton='<a class="butActionNew" href="suppliersales_card.php?action=create&backtopage='.urlencode($_SERVER['PHP_SELF']).'"><span class="valignmiddle">'.$langs->trans('New').'</span>';
//    $newcardbutton.= '<span class="fa fa-plus-circle valignmiddle"></span>';
//    $newcardbutton.= '</a>';
//}
print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, 0, '', 'title_accountancy', 0, $newcardbutton);

// Form parameters
$params = array(
    'action' => 'calculate'
);
if ($socid) {
    $params['filter_soc'] = $socid;
}
$formstyler->printFormBegin('POST', $url_page_current, $params);

print FormStyler::getInputField('product_price', $langs->trans('ProductPriceWithoutTax'), $productPrice, 1, '', 'price');

//print FormStyler::getInputField('vat_rate', $langs->trans('MarketplaceSaleTaxRate'), $vatRate);
print FormStyler::fieldEditBegin('vat_rate', $more_class, true);

print FormStyler::labelField('vat_rate', $langs->trans('MarketplaceSaleTaxRate'), 1, true);

print FormStyler::fieldValueBegin($name, true);

$defaultva=get_default_tva($mysoc, $mysoc);
$selected = ( GETPOST('vat_rate', 'int') ? GETPOST('vat_rate', 'int') : $defaultva);
print $form->load_tva("vat_rate", $selected, $mysoc, $mysoc, 0, 0, '', false, 1);
print FormStyler::fieldValueEnd();

print FormStyler::fieldEditEnd();

print FormStyler::getInputField('collection_rate', $langs->trans('MarketplaceCollectionRate'), $collectionRate);

print FormStyler::getInputField('discount_rate', $langs->trans('MarketplaceDiscountRate'), $discountRate);

print FormStyler::getInputField('care_rate', $langs->trans('MarketplaceCareRate'), $careRate);

$formstyler->printFormSubmitButton('confirm_build', $langs->trans('CalculateMarketPlaceSale'));

$formstyler->printFormEnd();


// Show results 
if ($action == 'calculate') {
    $sales = new Sales($db);

    print load_fiche_titre($langs->trans('MarketPlaceSimulatorResults'));

    $sales->discount_rate = $discountRate;
    $sales->collection_rate = $collectionRate;
    $sales->care_rate = $careRate;
    $sales->tax_rate = $vatRate;

    $paidPrice = $productPrice;
    if ($sales->discount_rate > 0) {
        $paidPrice = $productPrice - ( $productPrice * $sales->discount_rate / 100);
    }

    if ($sales->care_rate > 0) {
        $paidPrice = $productPrice - ( $productPrice * $sales->care_rate / 100);
    }

    $paidPriceTTC = $paidPrice + ($paidPrice * $sales->tax_rate / 100);

    print '<div class="fichecenter">';
    print '<div class="fichehalfleft">';

	print '<table class="border liste" width="100%">';
    //print '<tr class="oddeven"><td>Prix payé </td><td> '.price($paidPrice, '', $langs, 1, -1, 2).'</td></tr>';
    print '<tr class="oddeven"><td>' . $langs->trans('PaidPriceTTC') .'</td><td> '.price($paidPriceTTC, '', $langs, 1, -1, 2).'</td></tr>';
    print '<tr class="oddeven"><td>' . $langs->trans('AmountVAT') .' </td><td> '.price($paidPriceTTC - $paidPrice, '', $langs, 1, -1, 2).'</td></tr>';
 
    $collectionAmount = $sales->calculateCollectionAmount($paidPrice);
    $collectionAmountVat = $collectionAmount * $sales->tax_rate / 100;
    $collectionAmountTTC = $collectionAmount + $collectionAmountVat;
    print '<tr class="oddeven"><td><strong>' . $langs->trans('AmountCollectionHT') .'</strong></td><td><strong>'.price($collectionAmount, '', $langs, 1, -1, 2).'</strong></td></tr>';
    print '<tr class="oddeven"><td>' . $langs->trans('AmountVATCollection') .'</td><td> '.price($collectionAmountVat, '', $langs, 1, -1, 2).'</td></tr>';
    print '<tr class="oddeven"><td>' . $langs->trans('AmountCollectionTTC') .'</td><td> '.price($collectionAmountTTC, '', $langs, 1, -1, 2).'</td></tr>';

    $retrocessionAmount = $sales->calculateRetrocessionAmount($paidPrice);
    $retrocessionAmountVat = $retrocessionAmount * $sales->tax_rate / 100;
    $retrocessionAmountTTC = $retrocessionAmount + $retrocessionAmountVat;
    print '<tr class="oddeven"><td><strong>' . $langs->trans('AmountRetrocessionHT') .'</strong></td><td><strong>'.price($retrocessionAmount, '', $langs, 1, -1, 2).'</strong></td></tr>';
    print '<tr class="oddeven"><td>' . $langs->trans('AmountVATRetrocession') .'</td><td> '.price($retrocessionAmountVat, '', $langs, 1, -1, 2).'</td></tr>';
    print '<tr class="oddeven"><td>' . $langs->trans('AmountRetrocessionTTC') .'</td><td> '.price($retrocessionAmountTTC, '', $langs, 1, -1, 2).'</td></tr>';
    print '</table>';


    print '</div>';
    print '</div>';

}


// Check if sales with collection rate = 0
// and show button to recalculate lines if so
$res = $object->checkSalesWithNoCollection();
if ($res > 0) {
    print '<div class="error">' . $langs->trans('MarketPlacesalesWithNoCollectionAmount') . '<div>';
    print '<a class="butAction" href="?action=fixCollection">'.$langs->trans('MarketPlaceFixCollectionAmount') . '</a>';
}

// End of page
llxFooter();
$db->close();
