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
 *   	\file       suppliersales_list.php
 *		\ingroup    marketplace
 *		\brief      List page for suppliersales
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
    if ($action == 'export_sales') {
        $nbSupplierBills = 0;
        $arrayCreatedBills = array();
        
        $sql = "SELECT ";
        $sql.= " ts.rowid, ts.fk_seller, ts.fk_product, ts.price";
        $sql.= ", ts.discount_rate, ts.discount_amount";
        $sql. ", ts.care_amount, ts.care_rate";
        $sql.= ", ts.collection_rate, ts.collection_amount";
        $sql.= ", ts.tax_rate, ts.tax_total";
        $sql.= ", ts.retrocession_amount";
        $sql.= ", ts.fk_customer_invoice, ts.fk_customer_invoice_line";
        $sql.= ", ts.fk_seller_invoice, ts.fk_seller_invoice_line";
        $sql.= ", ts.date_creation";
        $sql.= ", i.qty";
        $sql .= ", f.facnumber";
        $sql.= " FROM ".MAIN_DB_PREFIX."marketplace_sales as ts";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX. "facturedet as i ON i.rowid=ts.fk_customer_invoice_line";
        $sql.= " INNER JOIN " . MAIN_DB_PREFIX . "facture as f ON f.rowid=ts.fk_customer_invoice";
        $sql.= " WHERE ts.date_creation BETWEEN '".$db->idate($dateStart)."'";
        $sql.= " AND '".$db->idate($dateEnd)."'";
        $sql.= " AND status = 0";
        if ($filter_soc > 0) {
            $sql.= " AND ts.fk_seller=".$filter_soc;
        }
        $sql.= " ORDER BY ts.date_creation ASC";

        $resql=$db->query($sql);
        if ($resql) {
            $num = $db->num_rows($resql);
            $i = 0;
           
            $arraySellerSales = array();
            $nbCreatedInvoice = 0;
            $amountCollectedHT = 0;

            // Build an array to group sales by seller
            while ($i < $num) {
                $obj = $db->fetch_object($resql);
                
                $sellerId = $obj->fk_seller;

                $retroSale = array(
                    'id' =>$obj->rowid,
                    'fk_product' => $obj->fk_product,
                    'fk_seller' => $obj->fk_seller,
                    'price' => $obj->price,
                    'qty' => $obj->qty,
                    'discount_rate' => $obj->discount_rate,
                    'discount_amount' => $obj->discount_amount,
                    'care_rate' => $obj->care_rate,
                    'care_amount' => $obj->care_amount,

                    'collection_amount' => $obj->collection_amount,
                    'collection_rate' => $obj->collection_rate,
                    'retrocession_amount' => $obj->retrocession_amount,

                    'tax_total' => $obj->tax_total,
                    'tax_rate' => $obj->tax_rate,

                    'fk_customer_invoice' => $obj->fk_customer_invoice,
                    'fk_customer_invoice_line' => $obj->fk_customer_invoice_line,

                    'date_creation' => $obj->date_creation,

                    'facnumber' => $obj->facnumber,
                );
                $arraySellerSales[$sellerId][] = $retroSale;
                
                $i++;
            }

            $arraySalesByProduct = array();
            $arrayTaxSaleByRate = array();
            $arrayCollectionByRate = array();

            $arraySalesByIds = array();

            $collectionAmountTotal = 0;

            // Loop on sales by seller
            foreach ($arraySellerSales as $sellerId => $sales) {
                $arraySalesByProduct = array();
                $arrayTaxSaleByRate = array();
                $arrayCollectionByRate = array();

                // create supplier invoice
                $supplierInvoice = new FactureFournisseur($db);
                
                $supplierInvoice->socid = $sellerId;
                $supplierInvoice->ref_supplier = dol_mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y'));
                $supplierInvoice->lines = array();
                
                $resCreate = $supplierInvoice->create($user);
                if ($resCreate > 0) {
                    $nbCreatedInvoice++;

                    
                    // Loop on sales and group by product
                    foreach ($sales as $lineSale) {
                        $productId = $lineSale['fk_product'];
                        //$arraySalesByProduct[$productId]['qty'] = 0;
                        $arraySalesByProduct[$productId]['price'] += $lineSale['price'];
                        $arraySalesByProduct[$productId]['qty'] += $lineSale['qty'];
                        $arraySalesByProduct[$productId]['discount_amount'] += $lineSale['discount_amount'];
                        $arraySalesByProduct[$productId]['care_amount'] += $lineSale['care_amount'];
                        $arraySalesByProduct[$productId]['collection_amount'] += $lineSale['collection_amount'];
                        $arraySalesByProduct[$productId]['retrocession_amount'] += $lineSale['retrocession_amount'];
                        $arraySalesByProduct[$productId]['tax_total'] += $lineSale['tax_total'];
                        $arraySalesByProduct[$productId]['tax_rate'] = $lineSale['tax_rate'];
                        //$arraySalesByProduct[$productId]['facnumbers'][] = $lineSale['facnumber'];

                        $arraySalesByProduct[$productId]['sales_id'][] = $lineSale['id']; // Sale ID
                        $taxRate = $lineSale['tax_rate'];
                        $arrayTaxSaleByRate[$taxRate] += $lineSale['tax_total'];

                        $arrayCollectionByRate[$taxRate] += $lineSale['collection_amount'];
                    }

                    foreach ($arraySalesByProduct as $idProduct => $lineSale) {
                        //var_dump($lineSale);
                        $line = new SupplierInvoiceLine($db);
                        $line->fk_product = $idProduct;
 
                        $line->pu_ht = $lineSale['price'] / $lineSale['qty'];
                        $line->description = 'Montant commission : ' . $lineSale['collection_amount'];
                        if ($lineSale['care_amount'] > 0) {
                            $line->description .= 'Montant pris en charge : ' . price2num($lineSale['care_amount']);
                        }
                        $line->product_type = 0;
                        $line->qty = $lineSale['qty'];
                        $line->tva_tx = $lineSale['tax_rate'];
                        $line->total_tva = $lineSale["tax_total"];
                        //$line->total_ht = $lineSale['price'] - $lineSale['care_amount'] - $lineSale['collection_amount'];
                        
                        $line->fk_retrosell = $lineSale['id'];
                        
                        $supplierInvoice->lines[] = $line;

                        $collectionAmountTotal += $lineSale['collection_amount'];
                    }
                    
                    // If this->lines is array of InvoiceLines (preferred mode)
                    if (count($supplierInvoice->lines) && is_object($supplierInvoice->lines[0])) {
                        dol_syslog("There is ".count($supplierInvoice->lines)." lines that are invoice lines objects");
                        foreach ($supplierInvoice->lines as $j => $val) {
                            $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'facture_fourn_det (fk_facture_fourn, special_code)';
                            $sql .= ' VALUES ('.$supplierInvoice->id.','.intval($supplierInvoice->line[$j]->special_code).')';
                            
                            $resql_insert=$db->query($sql);
                            if ($resql_insert) {
                                $idligne = $db->last_insert_id(MAIN_DB_PREFIX.'facture_fourn_det');

                                $supplierInvoice->updateline(
                                    $idligne,
                                    $supplierInvoice->lines[$j]->description,
                                    $supplierInvoice->lines[$j]->pu_ht,
                                    $supplierInvoice->lines[$j]->tva_tx,
                                    $supplierInvoice->lines[$j]->localtax1_tx,
                                    $supplierInvoice->lines[$j]->localtax2_tx,
                                    $supplierInvoice->lines[$j]->qty,
                                    $supplierInvoice->lines[$j]->fk_product,
                                    'HT',
                                    (! empty($supplierInvoice->lines[$j]->info_bits) ? $supplierInvoice->lines[$j]->info_bits : ''),
                                    $supplierInvoice->lines[$j]->product_type,
                                    $supplierInvoice->lines[$j]->remise_percent,
                                    false,
                                    $supplierInvoice->lines[$j]->date_start,
                                    $supplierInvoice->lines[$j]->date_end,
                                    $supplierInvoice->lines[$j]->array_options,
                                    $supplierInvoice->lines[$j]->fk_unit,
                                    $supplierInvoice->lines[$j]->pu_ht_devise
                                );

                                $idProduct = $supplierInvoice->lines[$j]->fk_product;
                                foreach ($arraySalesByProduct[$idProduct]['sales_id'] as $key => $saleId) {
                                    $supplierSale = new Sales($db);

                                    $supplierSale->id = $saleId;
                                    $resBilled = $supplierSale->setBilled($user, $supplierInvoice->id, $idligne);
                                    if ($resBilled) {
                                    }
                                }
                            } else {
                                $object->error=$db->lasterror();
                                $db->rollback();
                                return -5;
                            }
                        }
                    }

                    // Add negative line for commission
                    foreach ($arrayCollectionByRate as $tx => $value) {
                        // One service by vat rate
                        $idServiceCollection = $conf->global->MARKETPLACE_MAIN_COLLECTION_SERVICE;

                        // Test si produit existe avec
                        $productCollection = new Product($db);
                        $productCollection->fetch($idServiceCollection);

                        /*
                         *   One service by VAT rate
                         */
                        $tmpTx = str_replace('.', '', price($tx));
                        $tmpTx = price2num($tx);
                        $refServiceCollection = $productCollection->ref .'-'. $tmpTx;
                        if ($productCollection->fetch('', $refServiceCollection) > 0) {
                            $idServiceCollection = $productCollection->id;
                        }
                       
                        $sql = 'INSERT INTO ' . MAIN_DB_PREFIX . 'facture_fourn_det (fk_facture_fourn, special_code)';
                        $sql .= ' VALUES (' . $supplierInvoice->id . ', 0)';
    
                        $resql_insert = $db->query($sql);
                        if ($resql_insert) {
                            $idligne = $db->last_insert_id(MAIN_DB_PREFIX . 'facture_fourn_det');



                            $supplierInvoice->updateline(
                                $idligne,
                                'Commission TVA '.price($tx).'% - Période : '.dol_print_date($dateStart). ' au '.dol_print_date($dateEnd),
                                $value,
                                $tx,
                                0,
                                0,
                                -1,
                                $idServiceCollection,
                                'HT',
                                '',
                                1,
                                0,
                                false,
                                $dateStart,
                                $dateEnd,
                                $array_options,
                                $fk_unit,
                                $pu_ht_devise
                            );

                            $amountCollectedHT+= $value;
                        }
                    }
                    $supplierInvoice->validate($user);
                    $arrayCreatedBills[] = $supplierInvoice;

                    $amountBilledHT += $supplierInvoice->total_ht;
                    $amountBilledTTC += $supplierInvoice->total_ttc;

                    // Generate document
                    $supplierInvoice->generateDocument('absinthe', $langs);

                    // TODO: send invoice by email
                } else {
                    dol_print_error($db, '', $supplierInvoice->error);
                }
            } // END foreach invoices
        } else {
            print $db->lasterror();
            dol_print_error($db);
        }
    } elseif ($action == 'fixCollection') {
        $res = $object->fixNullCollections();
        if ($res < 0) {
            setEventMessage($langs->trans('MarketPlaceErrorWhenCalculCollectionAmount'), 'errors');
        } else {
            setEventMessage($langs->trans('MarketPlaceCollectionRecalculated', $res));
        }
    }
}



/*
 * View
 */

$form=new Form($db);
$formstyler = new FormStyler($db, 'consumption_rapport');

$now=dol_now();

//$help_url="EN:Module_Sales|FR:Module_Sales_FR|ES:Módulo_Sales";
$help_url='';
$title = $langs->trans('MarketplaceSalesExport2Invoice');


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
    'action' => 'export_sales'
);
if ($socid) {
    $params['filter_soc'] = $socid;
}
$formstyler->printFormBegin('POST', $url_page_current, $params);


// Thirdparty select list
if (!$socid) {
    FormStyler::printSelectList('filter_soc', $langs->trans('Choosethirdparty'), array(), $filter_soc, $required = 0, 'filter_soc', 'suppliers', $more_class = '', $return_html = false);
}

// Date start input
$name = 'dateStart';
$label = $langs->trans('ChooseDateStart');
$value = GETPOST($name, 'alpha');
$type = 'date';
$formstyler->printInputField($name, $label, $value, true, '', $type);

// Date end input
$name = 'dateEnd';
$label = $langs->trans('ChooseDateEnd');
$value = GETPOST($name, 'alpha');
$type = 'date';
$formstyler->printInputField($name, $label, $value, true, '', $type);

$formstyler->printFormSubmitButton('confirm_build', $langs->trans('LaunchMarketplaceInvoicing'));
$formstyler->printFormEnd();


// Show results 
if ($action == 'export_sales') {
    if (is_array($arrayCreatedBills) && count($arrayCreatedBills) > 0) {
        print '<div class="info">';
        print $langs->trans('NumberOfBillsCreated', $nbCreatedInvoice);
        print '<br />';
        print $langs->trans('AmountHTOfBillsCreated', price($amountBilledHT));
        print '<br />';
        print $langs->trans('AmountTTCOfBillsCreated', price($amountBilledTTC));

        print '<br />';
        print $langs->trans('AmountHTOfCollection', price($amountCollectedHT));

        print '<ul>';
        foreach ($arrayCreatedBills as $index => $invoice) {
            print '<li>' . $invoice->getNomUrl(1) . '</li>';
        }
        print '</ul>';
        print '</div>';
    }

    if ($nbCreatedInvoice == 0) {
        print '<div class="info">';
        print $langs->trans('NoSalesForMarketplace');
        print '</div>';
    }
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
