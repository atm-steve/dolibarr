<?php
/* Copyright (C) 2001-2005  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2019  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2005       Marc Barilley / Ocebo   <marc@ocebo.com>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2012       Juanjo Menent           <jmenent@2byte.es>
 * Copyright (C) 2013       Christophe Battarel     <christophe.battarel@altairis.fr>
 * Copyright (C) 2013       Cédric Salvador         <csalvador@gpcsolutions.fr>
 * Copyright (C) 2015-2018  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2015       Marcos García           <marcosgdf@gmail.com>
 * Copyright (C) 2015       Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2016       Ferran Marcet           <fmarcet@2byte.es>
 * Copyright (C) 2018       Charlene Benke	        <charlie@patas-monkey.com>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *    \file       htdocs/commande/list.php
 *    \ingroup    commande
 *    \brief      Page to list orders
 */


require '../main.inc.php';
//require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
//require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
//require_once DOL_DOCUMENT_ROOT.'/core/class/discount.class.php';
//require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
//require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
//require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
//require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
//require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
//require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
//require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

/**
 * @var User $user
 * @var Translate $langs
 * @var array $conf
 * @var HookManager $hookmanager
 * @var DoliDB $db
 */
// Load translation files required by the page
$langs->loadLangs(array('multicurrency'));

$action = GETPOST('action', 'aZ09');
$massaction = GETPOST('massaction', 'alpha');
$confirm = GETPOST('confirm', 'alpha');

$toselect = GETPOST('toselect', 'array');

$search_date = dol_mktime(0, 0, 0, GETPOST('search_date_month', 'int'), GETPOST('search_date_startday', 'int'), GETPOST('search_date_startyear', 'int'));
$search_rate = GETPOST('search_rate', 'int');
$search_code = GETPOST('search_code', 'aZ09');

// Security check
$id = intval(GETPOST('id', 'int'));

$result = restrictedArea($user, 'facture', $id, '');

$diroutputmassaction = $conf->commande->multidir_output[$conf->entity] . '/temp/massgeneration/' . $user->id;

// Load variable for pagination
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page == -1 || !empty($search_btn) || !empty($search_remove_btn) || (empty($toselect) && $massaction === '0')) {
	$page = 0;
}     // If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortfield) $sortfield = 'c.ref';
if (!$sortorder) $sortorder = 'DESC';

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$object = new Commande($db);
$hookmanager->initHooks(array('orderlist'));
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);
$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

$checkedtypetiers = 0;
$arrayfields = array(
	'rate.date_sync' => array('label' => 'DateSync', 'checked' => 1),
	'rate.rate'      => array('label' => 'Rate',     'checked' => 1),
	'currency.code'  => array('label' => 'Code',     'checked' => 1),
);

$arrayfields = dol_sort_array($arrayfields, 'position');


/*
 * Actions
 */

if (GETPOST('cancel', 'alpha')) {
	$action = 'list';
	$massaction = '';
}

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook)) {
	// Selection of new fields
	include DOL_DOCUMENT_ROOT . '/core/actions_changeselectedfields.inc.php';

	// Purge search criteria
	if (
		GETPOST('button_removefilter_x', 'alpha')
		|| GETPOST('button_removefilter.x', 'alpha')
		|| GETPOST('button_removefilter', 'alpha')
	) // All tests are required to be compatible with all browsers
	{
		$search_date = '';
		$search_rate = '';
		$search_code = '';
	}
	if (
		GETPOST('button_removefilter_x', 'alpha')
		|| GETPOST('button_removefilter.x', 'alpha')
		|| GETPOST('button_removefilter', 'alpha')
		|| GETPOST('button_search_x', 'alpha')
		|| GETPOST('button_search.x', 'alpha')
		|| GETPOST('button_search', 'alpha')
	) {
		$massaction = ''; // Protection to avoid mass action if we force a new search during a mass action confirmation
	}

	// Mass actions
	$objectclass = 'CurrencyRate';
	$objectlabel = 'Orders';
	include DOL_DOCUMENT_ROOT . '/core/actions_massactions.inc.php';
}


/*
 * View
 */

//$now = dol_now();

$form = new Form($db);
//$formother = new FormOther($db);
//$formfile = new FormFile($db);
//$companystatic = new Societe($db);
//$formcompany = new FormCompany($db);
//$projectstatic = new Project($db);

$title = $langs->trans("ExchangeRates");


$sqlSelect = /** @lang SQL */
	'SELECT rate.rowid, rate.date_sync, currency.code';
// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters);
$sqlSelect .= $hookmanager->resPrint;
if ($reshook == 0)    $sqlSelect .= $hookmanager->resPrint;
elseif ($reshook > 0) $sqlSelect = $hookmanager->resPrint;
else setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

$sqlFrom = /** @lang SQL */
	' FROM ' . MAIN_DB_PREFIX . 'multicurrency_rate rate'
	. ' LEFT JOIN ' . MAIN_DB_PREFIX . 'multicurrency currency ON rate.fk_multicurrency = currency.rowid';
// join tables from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListFrom', $parameters);
if ($reshook == 0)    $sqlFrom .= $hookmanager->resPrint;
elseif ($reshook > 0) $sqlFrom = $hookmanager->resPrint;
else setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

$sqlWhere = /** @lang SQL */
	' WHERE rate.entity IN (' . getEntity('multicurrency') . ')';
if (!empty($search_rate)) $sqlWhere .= natural_search('rate.rate', $search_rate, 1);
// TODO add date filter and code filter

// Add filters from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters);
if ($reshook == 0)    $sqlWhere .= $hookmanager->resPrint;
elseif ($reshook > 0) $sqlWhere = $hookmanager->resPrint;
else setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

$sql = $sqlSelect . $sqlFrom . $sqlWhere . ' ';

$sql .= $db->order($sortfield, $sortorder);



// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$resql = $db->query($sql);
	if (!$resql) {
		dol_print_error($db);
		exit;
	}
	$nbtotalofrecords = $db->num_rows($resql);

	if (($page * $limit) > $nbtotalofrecords)    // if total resultset is smaller then paging size (filtering), goto and load page 0
	{
		$page = 0;
		$offset = 0;
	}
}

$sql .= $db->plimit($limit + 1, $offset);
$resql = $db->query($sql);

if ($resql) {
	$title = $langs->trans('CurrencyRates');
	$num = $db->num_rows($resql);

	$arrayofselected = is_array($toselect) ? $toselect : array();

	llxHeader('', $title);

	// array of URL parameters with pagination, filter values, etc.
	$param = array();
	if ($search_code) $param['search_code'] = $search_code; // urlencode not necessary (handled by http_build_query)

	$param = http_build_query($param);

	// List of mass actions available
	$arrayofmassactions = array(
//		'generate_doc' => $langs->trans("ReGeneratePDF"),
//		'builddoc' => $langs->trans("PDFMerge"),
//		'cancelorders' => $langs->trans("Cancel"),
//		'presend' => $langs->trans("SendByMail"),
	);
	$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

	$newcardbutton = '';
	if ($user->rights->multicurrency->create) {
		$newcardbutton .= dolGetButtonTitle(
			$langs->trans('NewRate'),
			'',
			'fa fa-plus-circle',
			DOL_URL_ROOT . '/commande/card.php?action=create'
		);
	}

	// Lines of title fields
	echo '<form method="POST" id="searchFormList" action="' . $_SERVER["PHP_SELF"] . '">';
//	if ($optioncss != '') echo '<input type="hidden" name="optioncss" value="' . $optioncss . '">';
	echo '<input type="hidden" name="token" value="' . newToken() . '">';
	echo '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
	echo '<input type="hidden" name="action" value="list">';
	echo '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
	echo '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
	echo '<input type="hidden" name="id" value="' . $id . '">';

	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, '', 0, $newcardbutton, '', $limit, 0, 0, 1);

	$topicmail = "SendOrderRef";
	$modelmail = "order_send";
	$objecttmp = new Commande($db);
	$trackid = 'ord' . $object->id;
	include DOL_DOCUMENT_ROOT . '/core/tpl/massactions_pre.tpl.php';

	if ($massaction == 'createbills') {
		//var_dump($_REQUEST);
		print '<input type="hidden" name="massaction" value="confirm_createbills">';

		print '<table class="noborder" width="100%" >';
		print '<tr>';
		print '<td class="titlefield">';
		print $langs->trans('DateInvoice');
		print '</td>';
		print '<td>';
		print $form->selectDate('', '', '', '', '', '', 1, 1);
		print '</td>';
		print '</tr>';
		print '<tr>';
		print '<td>';
		print $langs->trans('CreateOneBillByThird');
		print '</td>';
		print '<td>';
		print $form->selectyesno('createbills_onebythird', '', 1);
		print '</td>';
		print '</tr>';
		print '<tr>';
		print '<td>';
		print $langs->trans('ValidateInvoices');
		print '</td>';
		print '<td>';
		if (!empty($conf->stock->enabled) && !empty($conf->global->STOCK_CALCULATE_ON_BILL)) {
			print $form->selectyesno('validate_invoices', 0, 1, 1);
			print ' (' . $langs->trans("AutoValidationNotPossibleWhenStockIsDecreasedOnInvoiceValidation") . ')';
		} else {
			print $form->selectyesno('validate_invoices', 0, 1);
		}
		if (!empty($conf->workflow->enabled) && !empty($conf->global->WORKFLOW_INVOICE_AMOUNT_CLASSIFY_BILLED_ORDER)) print ' &nbsp; &nbsp; <span class="opacitymedium">' . $langs->trans("IfValidateInvoiceIsNoOrderStayUnbilled") . '</span>';
		else print ' &nbsp; &nbsp; <span class="opacitymedium">' . $langs->trans("OptionToSetOrderBilledNotEnabled") . '</span>';
		print '</td>';
		print '</tr>';
		print '</table>';

		print '<br>';
		print '<div class="center">';
		print '<input type="submit" class="button" id="createbills" name="createbills" value="' . $langs->trans('CreateInvoiceForThisCustomer') . '">  ';
		print '<input type="submit" class="button" id="cancel" name="cancel" value="' . $langs->trans('Cancel') . '">';
		print '</div>';
		print '<br>';
	}

	if ($sall) {
		foreach ($fieldstosearchall as $key => $val) $fieldstosearchall[$key] = $langs->trans($val);
		print '<div class="divsearchfieldfilter">' . $langs->trans("FilterOnInto", $sall) . join(', ', $fieldstosearchall) . '</div>';
	}

	$moreforfilter = '';

	// If the user can view prospects other than his'
	if ($user->rights->societe->client->voir || $socid) {
		$langs->load("commercial");
		$moreforfilter .= '<div class="divsearchfield">';
		$moreforfilter .= $langs->trans('ThirdPartiesOfSaleRepresentative') . ': ';
		$moreforfilter .= $formother->select_salesrepresentatives($search_sale, 'search_sale', $user, 0, 1, 'maxwidth200');
		$moreforfilter .= '</div>';
	}
	// If the user can view other users
	if ($user->rights->user->user->lire) {
		$moreforfilter .= '<div class="divsearchfield">';
		$moreforfilter .= $langs->trans('LinkedToSpecificUsers') . ': ';
		$moreforfilter .= $form->select_dolusers($search_user, 'search_user', 1, '', 0, '', '', 0, 0, 0, '', 0, '', 'maxwidth200');
		$moreforfilter .= '</div>';
	}
	// If the user can view prospects other than his'
	if ($conf->categorie->enabled && ($user->rights->produit->lire || $user->rights->service->lire)) {
		include_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
		$moreforfilter .= '<div class="divsearchfield">';
		$moreforfilter .= $langs->trans('IncludingProductWithTag') . ': ';
		$cate_arbo = $form->select_all_categories(Categorie::TYPE_PRODUCT, null, 'parent', null, null, 1);
		$moreforfilter .= $form->selectarray('search_product_category', $cate_arbo, $search_product_category, 1, 0, 0, '', 0, 0, 0, 0, 'maxwidth300', 1);
		$moreforfilter .= '</div>';
	}
	if (!empty($conf->categorie->enabled)) {
		require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
		$moreforfilter .= '<div class="divsearchfield">';
		$moreforfilter .= $langs->trans('CustomersProspectsCategoriesShort') . ': ';
		$moreforfilter .= $formother->select_categories('customer', $search_categ_cus, 'search_categ_cus', 1);
		$moreforfilter .= '</div>';
	}
	if (!empty($conf->expedition->enabled) && !empty($conf->global->WAREHOUSE_ASK_WAREHOUSE_DURING_ORDER)) {
		require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';
		$formproduct = new FormProduct($db);
		$moreforfilter .= '<div class="divsearchfield">';
		$moreforfilter .= $langs->trans('Warehouse') . ': ';
		$moreforfilter .= $formproduct->selectWarehouses($search_warehouse, 'search_warehouse', '', 1);
		$moreforfilter .= '</div>';
	}
	$parameters = array();
	$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) $moreforfilter .= $hookmanager->resPrint;
	else $moreforfilter = $hookmanager->resPrint;

	if (!empty($moreforfilter)) {
		print '<div class="liste_titre liste_titre_bydiv centpercent">';
		print $moreforfilter;
		print '</div>';
	}

	$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
	$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields
	$selectedfields .= $form->showCheckAddButtons('checkforselect', 1);

	print '<div class="div-table-responsive">';
	print '<table class="tagtable liste' . ($moreforfilter ? " listwithfilterbefore" : "") . '">' . "\n";

	print '<tr class="liste_titre_filter">';
	// Ref
	if (!empty($arrayfields['c.ref']['checked'])) {
		print '<td class="liste_titre">';
		print '<input class="flat" size="6" type="text" name="search_ref" value="' . dol_escape_htmltag($search_ref) . '">';
		print '</td>';
	}
	// Ref customer
	if (!empty($arrayfields['c.ref_client']['checked'])) {
		print '<td class="liste_titre" align="left">';
		print '<input class="flat" type="text" size="6" name="search_ref_customer" value="' . dol_escape_htmltag($search_ref_customer) . '">';
		print '</td>';
	}
	// Project ref
	if (!empty($arrayfields['p.ref']['checked'])) {
		print '<td class="liste_titre"><input type="text" class="flat" size="6" name="search_project_ref" value="' . dol_escape_htmltag($search_project_ref) . '"></td>';
	}
	// Project title
	if (!empty($arrayfields['p.title']['checked'])) {
		print '<td class="liste_titre"><input type="text" class="flat" size="6" name="search_project" value="' . dol_escape_htmltag($search_project) . '"></td>';
	}
	// Thirpdarty
	if (!empty($arrayfields['s.nom']['checked'])) {
		print '<td class="liste_titre" align="left">';
		print '<input class="flat" type="text" name="search_company" value="' . dol_escape_htmltag($search_company) . '">';
		print '</td>';
	}
	// Town
	if (!empty($arrayfields['s.town']['checked'])) print '<td class="liste_titre"><input class="flat" type="text" size="4" name="search_town" value="' . $search_town . '"></td>';
	// Zip
	if (!empty($arrayfields['s.zip']['checked'])) print '<td class="liste_titre"><input class="flat" type="text" size="4" name="search_zip" value="' . $search_zip . '"></td>';
	// State
	if (!empty($arrayfields['state.nom']['checked'])) {
		print '<td class="liste_titre">';
		print '<input class="flat" size="4" type="text" name="search_state" value="' . dol_escape_htmltag($search_state) . '">';
		print '</td>';
	}
	// Country
	if (!empty($arrayfields['country.code_iso']['checked'])) {
		print '<td class="liste_titre" align="center">';
		print $form->select_country($search_country, 'search_country', '', 0, 'minwidth100imp maxwidth100');
		print '</td>';
	}
	// Company type
	if (!empty($arrayfields['typent.code']['checked'])) {
		print '<td class="liste_titre maxwidthonsmartphone" align="center">';
		print $form->selectarray("search_type_thirdparty", $formcompany->typent_array(0), $search_type_thirdparty, 0, 0, 0, '', 0, 0, 0, (empty($conf->global->SOCIETE_SORT_ON_TYPEENT) ? 'ASC' : $conf->global->SOCIETE_SORT_ON_TYPEENT));
		print '</td>';
	}
	// Date order
	if (!empty($arrayfields['c.date_commande']['checked'])) {
		print '<td class="liste_titre center">';
		print '<div class="nowrap">';
		print $form->selectDate($search_dateorder_start ? $search_dateorder_start : -1, 'search_dateorder_start', 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('From'));
		print '</div>';
		print '<div class="nowrap">';
		print $form->selectDate($search_dateorder_end ? $search_dateorder_end : -1, 'search_dateorder_end', 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('to'));
		print '</div>';
		print '</td>';
	}
	if (!empty($arrayfields['c.date_delivery']['checked'])) {
		print '<td class="liste_titre center">';
		print '<div class="nowrap">';
		print $form->selectDate($search_datedelivery_start ? $search_datedelivery_start : -1, 'search_datedelivery_start', 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('From'));
		print '</div>';
		print '<div class="nowrap">';
		print $form->selectDate($search_datedelivery_end ? $search_datedelivery_end : -1, 'search_datedelivery_end', 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('to'));
		print '</div>';
		print '</td>';
	}
	if (!empty($arrayfields['c.total_ht']['checked'])) {
		// Amount
		print '<td class="liste_titre right">';
		print '<input class="flat" type="text" size="4" name="search_total_ht" value="' . dol_escape_htmltag($search_total_ht) . '">';
		print '</td>';
	}
	if (!empty($arrayfields['c.total_vat']['checked'])) {
		// Amount
		print '<td class="liste_titre right">';
		print '<input class="flat" type="text" size="4" name="search_total_vat" value="' . dol_escape_htmltag($search_total_vat) . '">';
		print '</td>';
	}
	if (!empty($arrayfields['c.total_ttc']['checked'])) {
		// Amount
		print '<td class="liste_titre right">';
		print '<input class="flat" type="text" size="5" name="search_total_ttc" value="' . $search_total_ttc . '">';
		print '</td>';
	}
	if (!empty($arrayfields['c.multicurrency_code']['checked'])) {
		// Currency
		print '<td class="liste_titre">';
		print $form->selectMultiCurrency($search_multicurrency_code, 'search_multicurrency_code', 1);
		print '</td>';
	}
	if (!empty($arrayfields['c.multicurrency_tx']['checked'])) {
		// Currency rate
		print '<td class="liste_titre">';
		print '<input class="flat" type="text" size="4" name="search_multicurrency_tx" value="' . dol_escape_htmltag($search_multicurrency_tx) . '">';
		print '</td>';
	}
	if (!empty($arrayfields['c.multicurrency_total_ht']['checked'])) {
		// Amount
		print '<td class="liste_titre right">';
		print '<input class="flat" type="text" size="4" name="search_multicurrency_montant_ht" value="' . dol_escape_htmltag($search_multicurrency_montant_ht) . '">';
		print '</td>';
	}
	if (!empty($arrayfields['c.multicurrency_total_vat']['checked'])) {
		// Amount VAT
		print '<td class="liste_titre right">';
		print '<input class="flat" type="text" size="4" name="search_multicurrency_montant_vat" value="' . dol_escape_htmltag($search_multicurrency_montant_vat) . '">';
		print '</td>';
	}
	if (!empty($arrayfields['c.multicurrency_total_ttc']['checked'])) {
		// Amount
		print '<td class="liste_titre right">';
		print '<input class="flat" type="text" size="4" name="search_multicurrency_montant_ttc" value="' . dol_escape_htmltag($search_multicurrency_montant_ttc) . '">';
		print '</td>';
	}
	if (!empty($arrayfields['u.login']['checked'])) {
		// Author
		print '<td class="liste_titre" align="center">';
		print '<input class="flat" size="4" type="text" name="search_login" value="' . dol_escape_htmltag($search_login) . '">';
		print '</td>';
	}
	// Extra fields
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_search_input.tpl.php';
	// Fields from hook
	$parameters = array('arrayfields' => $arrayfields);
	$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	// Date creation
	if (!empty($arrayfields['c.datec']['checked'])) {
		print '<td class="liste_titre">';
		print '</td>';
	}
	// Date modification
	if (!empty($arrayfields['c.tms']['checked'])) {
		print '<td class="liste_titre">';
		print '</td>';
	}
	// Date cloture
	if (!empty($arrayfields['c.date_cloture']['checked'])) {
		print '<td class="liste_titre">';
		print '</td>';
	}
	// Status
	if (!empty($arrayfields['c.fk_statut']['checked'])) {
		print '<td class="liste_titre maxwidthonsmartphone right">';
		$liststatus = array(
			Commande::STATUS_DRAFT => $langs->trans("StatusOrderDraftShort"),
			Commande::STATUS_VALIDATED => $langs->trans("StatusOrderValidated"),
			Commande::STATUS_SHIPMENTONPROCESS => $langs->trans("StatusOrderSentShort"),
			Commande::STATUS_CLOSED => $langs->trans("StatusOrderDelivered"),
			-3 => $langs->trans("StatusOrderValidatedShort") . '+' . $langs->trans("StatusOrderSentShort") . '+' . $langs->trans("StatusOrderDelivered"),
			Commande::STATUS_CANCELED => $langs->trans("StatusOrderCanceledShort")
		);
		print $form->selectarray('search_status', $liststatus, $search_status, -4, 0, 0, '', 0, 0, 0, '', 'maxwidth100');
		print '</td>';
	}
	// Status billed
	if (!empty($arrayfields['c.facture']['checked'])) {
		print '<td class="liste_titre maxwidthonsmartphone" align="center">';
		print $form->selectyesno('search_billed', $search_billed, 1, 0, 1);
		print '</td>';
	}
	// Action column
	print '<td class="liste_titre" align="middle">';
	$searchpicto = $form->showFilterButtons();
	print $searchpicto;
	print '</td>';

	print "</tr>\n";

	// Fields title
	print '<tr class="liste_titre">';
	if (!empty($arrayfields['c.ref']['checked'])) print_liste_field_titre($arrayfields['c.ref']['label'], $_SERVER["PHP_SELF"], 'c.ref', '', $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields['c.ref_client']['checked'])) print_liste_field_titre($arrayfields['c.ref_client']['label'], $_SERVER["PHP_SELF"], 'c.ref_client', '', $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields['p.ref']['checked'])) print_liste_field_titre($arrayfields['p.ref']['label'], $_SERVER["PHP_SELF"], "p.ref", "", $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields['p.title']['checked'])) print_liste_field_titre($arrayfields['p.title']['label'], $_SERVER["PHP_SELF"], "p.title", "", $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields['s.nom']['checked'])) print_liste_field_titre($arrayfields['s.nom']['label'], $_SERVER["PHP_SELF"], 's.nom', '', $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields['s.town']['checked'])) print_liste_field_titre($arrayfields['s.town']['label'], $_SERVER["PHP_SELF"], 's.town', '', $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields['s.zip']['checked'])) print_liste_field_titre($arrayfields['s.zip']['label'], $_SERVER["PHP_SELF"], 's.zip', '', $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields['state.nom']['checked'])) print_liste_field_titre($arrayfields['state.nom']['label'], $_SERVER["PHP_SELF"], "state.nom", "", $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields['country.code_iso']['checked'])) print_liste_field_titre($arrayfields['country.code_iso']['label'], $_SERVER["PHP_SELF"], "country.code_iso", "", $param, '', $sortfield, $sortorder, 'center ');
	if (!empty($arrayfields['typent.code']['checked'])) print_liste_field_titre($arrayfields['typent.code']['label'], $_SERVER["PHP_SELF"], "typent.code", "", $param, '', $sortfield, $sortorder, 'center ');
	if (!empty($arrayfields['c.date_commande']['checked'])) print_liste_field_titre($arrayfields['c.date_commande']['label'], $_SERVER["PHP_SELF"], 'c.date_commande', '', $param, '', $sortfield, $sortorder, 'center ');
	if (!empty($arrayfields['c.date_delivery']['checked'])) print_liste_field_titre($arrayfields['c.date_delivery']['label'], $_SERVER["PHP_SELF"], 'c.date_livraison', '', $param, '', $sortfield, $sortorder, 'center ');
	if (!empty($arrayfields['c.total_ht']['checked'])) print_liste_field_titre($arrayfields['c.total_ht']['label'], $_SERVER["PHP_SELF"], 'c.total_ht', '', $param, '', $sortfield, $sortorder, 'right ');
	if (!empty($arrayfields['c.total_vat']['checked'])) print_liste_field_titre($arrayfields['c.total_vat']['label'], $_SERVER["PHP_SELF"], 'c.tva', '', $param, '', $sortfield, $sortorder, 'right ');
	if (!empty($arrayfields['c.total_ttc']['checked'])) print_liste_field_titre($arrayfields['c.total_ttc']['label'], $_SERVER["PHP_SELF"], 'c.total_ttc', '', $param, '', $sortfield, $sortorder, 'right ');
	if (!empty($arrayfields['c.multicurrency_code']['checked'])) print_liste_field_titre($arrayfields['c.multicurrency_code']['label'], $_SERVER['PHP_SELF'], 'c.multicurrency_code', '', $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields['c.multicurrency_tx']['checked'])) print_liste_field_titre($arrayfields['c.multicurrency_tx']['label'], $_SERVER['PHP_SELF'], 'c.multicurrency_tx', '', $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields['c.multicurrency_total_ht']['checked'])) print_liste_field_titre($arrayfields['c.multicurrency_total_ht']['label'], $_SERVER['PHP_SELF'], 'c.multicurrency_total_ht', '', $param, 'class="right"', $sortfield, $sortorder);
	if (!empty($arrayfields['c.multicurrency_total_vat']['checked'])) print_liste_field_titre($arrayfields['c.multicurrency_total_vat']['label'], $_SERVER['PHP_SELF'], 'c.multicurrency_total_tva', '', $param, 'class="right"', $sortfield, $sortorder);
	if (!empty($arrayfields['c.multicurrency_total_ttc']['checked'])) print_liste_field_titre($arrayfields['c.multicurrency_total_ttc']['label'], $_SERVER['PHP_SELF'], 'c.multicurrency_total_ttc', '', $param, 'class="right"', $sortfield, $sortorder);
	if (!empty($arrayfields['u.login']['checked'])) print_liste_field_titre($arrayfields['u.login']['label'], $_SERVER["PHP_SELF"], 'u.login', '', $param, 'align="center"', $sortfield, $sortorder);

	// Extra fields
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_search_title.tpl.php';
	// Hook fields
	$parameters = array('arrayfields' => $arrayfields, 'param' => $param, 'sortfield' => $sortfield, 'sortorder' => $sortorder);
	$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	if (!empty($arrayfields['c.datec']['checked'])) print_liste_field_titre($arrayfields['c.datec']['label'], $_SERVER["PHP_SELF"], "c.date_creation", "", $param, '', $sortfield, $sortorder, 'center nowrap ');
	if (!empty($arrayfields['c.tms']['checked'])) print_liste_field_titre($arrayfields['c.tms']['label'], $_SERVER["PHP_SELF"], "c.tms", "", $param, '', $sortfield, $sortorder, 'center nowrap ');
	if (!empty($arrayfields['c.date_cloture']['checked'])) print_liste_field_titre($arrayfields['c.date_cloture']['label'], $_SERVER["PHP_SELF"], "c.date_cloture", "", $param, '', $sortfield, $sortorder, 'center nowrap ');
	if (!empty($arrayfields['c.fk_statut']['checked'])) print_liste_field_titre($arrayfields['c.fk_statut']['label'], $_SERVER["PHP_SELF"], "c.fk_statut", "", $param, '', $sortfield, $sortorder, 'right ');
	if (!empty($arrayfields['c.facture']['checked'])) print_liste_field_titre($arrayfields['c.facture']['label'], $_SERVER["PHP_SELF"], 'c.facture', '', $param, '', $sortfield, $sortorder, 'center ');
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], "", '', $param, '', $sortfield, $sortorder, 'maxwidthsearch center ');
	print '</tr>' . "\n";

	$total = 0;
	$subtotal = 0;
	$productstat_cache = array();
	$getNomUrl_cache = array();

	$generic_commande = new Commande($db);
	$generic_product = new Product($db);
	$userstatic = new User($db);
	$i = 0;
	$totalarray = array();
	while ($i < min($num, $limit)) {
		$obj = $db->fetch_object($resql);

		$notshippable = 0;
		$warning = 0;
		$text_info = '';
		$text_warning = '';
		$nbprod = 0;

		$companystatic->id = $obj->socid;
		$companystatic->code_client = $obj->code_client;
		$companystatic->name = $obj->name;
		$companystatic->client = $obj->client;
		$companystatic->email = $obj->email;
		if (!isset($getNomUrl_cache[$obj->socid])) {
			$getNomUrl_cache[$obj->socid] = $companystatic->getNomUrl(1, 'customer');
		}

		$generic_commande->id = $obj->rowid;
		$generic_commande->ref = $obj->ref;
		$generic_commande->statut = $obj->fk_statut;
		$generic_commande->billed = $obj->billed;
		$generic_commande->date = $db->jdate($obj->date_commande);
		$generic_commande->date_livraison = $db->jdate($obj->date_delivery);
		$generic_commande->ref_client = $obj->ref_client;
		$generic_commande->total_ht = $obj->total_ht;
		$generic_commande->total_tva = $obj->total_tva;
		$generic_commande->total_ttc = $obj->total_ttc;
		$generic_commande->note_public = $obj->note_public;
		$generic_commande->note_private = $obj->note_private;

		$projectstatic->id = $obj->project_id;
		$projectstatic->ref = $obj->project_ref;
		$projectstatic->title = $obj->project_label;

		print '<tr class="oddeven">';

		// Ref
		if (!empty($arrayfields['c.ref']['checked'])) {
			print '<td class="nowraponall">';

			$generic_commande->getLinesArray(); // This set ->lines

			print $generic_commande->getNomUrl(1, ($search_status != 2 ? 0 : $obj->fk_statut), 0, 0, 0, 1, 1);

			// Show shippable Icon (create subloop, so may be slow)
			if ($conf->stock->enabled) {
				$langs->load("stocks");
				if (($obj->fk_statut > 0) && ($obj->fk_statut < 3)) {
					$numlines = count($generic_commande->lines); // Loop on each line of order
					for ($lig = 0; $lig < $numlines; $lig++) {
						if ($generic_commande->lines[$lig]->product_type == 0 && $generic_commande->lines[$lig]->fk_product > 0)  // If line is a product and not a service
						{
							$nbprod++; // order contains real products
							$generic_product->id = $generic_commande->lines[$lig]->fk_product;

							// Get local and virtual stock and store it into cache
							if (empty($productstat_cache[$generic_commande->lines[$lig]->fk_product])) {
								$generic_product->load_stock('nobatch');
								//$generic_product->load_virtual_stock();   Already included into load_stock
								$productstat_cache[$generic_commande->lines[$lig]->fk_product]['stock_reel'] = $generic_product->stock_reel;
								$productstat_cachevirtual[$generic_commande->lines[$lig]->fk_product]['stock_reel'] = $generic_product->stock_theorique;
							} else {
								$generic_product->stock_reel = $productstat_cache[$generic_commande->lines[$lig]->fk_product]['stock_reel'];
								$generic_product->stock_theorique = $productstat_cachevirtual[$generic_commande->lines[$lig]->fk_product]['stock_reel'] = $generic_product->stock_theorique;
							}

							if (empty($conf->global->SHIPPABLE_ORDER_ICON_IN_LIST))  // Default code. Default is when this option is not set, setting it create strange result
							{
								$text_info .= $generic_commande->lines[$lig]->qty . ' X ' . $generic_commande->lines[$lig]->ref . '&nbsp;' . dol_trunc($generic_commande->lines[$lig]->product_label, 25);
								$text_info .= ' - ' . $langs->trans("Stock") . ': ' . $generic_product->stock_reel;
								$text_info .= ' - ' . $langs->trans("VirtualStock") . ': ' . $generic_product->stock_theorique;
								$text_info .= '<br>';

								if ($generic_commande->lines[$lig]->qty > $generic_product->stock_reel) {
									$notshippable++;
								}
							} else {  // Detailed code, looks bugged
								// stock order and stock order_supplier
								$stock_order = 0;
								$stock_order_supplier = 0;
								if (!empty($conf->global->STOCK_CALCULATE_ON_SHIPMENT) || !empty($conf->global->STOCK_CALCULATE_ON_SHIPMENT_CLOSE))    // What about other options ?
								{
									if (!empty($conf->commande->enabled)) {
										if (empty($productstat_cache[$generic_commande->lines[$lig]->fk_product]['stats_order_customer'])) {
											$generic_product->load_stats_commande(0, '1,2');
											$productstat_cache[$generic_commande->lines[$lig]->fk_product]['stats_order_customer'] = $generic_product->stats_commande['qty'];
										} else {
											$generic_product->stats_commande['qty'] = $productstat_cache[$generic_commande->lines[$lig]->fk_product]['stats_order_customer'];
										}
										$stock_order = $generic_product->stats_commande['qty'];
									}
									if (!empty($conf->fournisseur->enabled)) {
										if (empty($productstat_cache[$generic_commande->lines[$lig]->fk_product]['stats_order_supplier'])) {
											$generic_product->load_stats_commande_fournisseur(0, '3');
											$productstat_cache[$generic_commande->lines[$lig]->fk_product]['stats_order_supplier'] = $generic_product->stats_commande_fournisseur['qty'];
										} else {
											$generic_product->stats_commande_fournisseur['qty'] = $productstat_cache[$generic_commande->lines[$lig]->fk_product]['stats_order_supplier'];
										}
										$stock_order_supplier = $generic_product->stats_commande_fournisseur['qty'];
									}
								}
								$text_info .= $generic_commande->lines[$lig]->qty . ' X ' . $generic_commande->lines[$lig]->ref . '&nbsp;' . dol_trunc($generic_commande->lines[$lig]->product_label, 25);
								$text_stock_reel = $generic_product->stock_reel . '/' . $stock_order;
								if ($stock_order > $generic_product->stock_reel && !($generic_product->stock_reel < $generic_commande->lines[$lig]->qty)) {
									$warning++;
									$text_warning .= '<span class="warning">' . $langs->trans('Available') . '&nbsp;:&nbsp;' . $text_stock_reel . '</span>';
								}
								if ($generic_product->stock_reel < $generic_commande->lines[$lig]->qty) {
									$notshippable++;
									$text_info .= '<span class="warning">' . $langs->trans('Available') . '&nbsp;:&nbsp;' . $text_stock_reel . '</span>';
								} else {
									$text_info .= '<span class="ok">' . $langs->trans('Available') . '&nbsp;:&nbsp;' . $text_stock_reel . '</span>';
								}
								if (!empty($conf->fournisseur->enabled)) {
									$text_info .= '&nbsp;' . $langs->trans('SupplierOrder') . '&nbsp;:&nbsp;' . $stock_order_supplier . '<br>';
								} else {
									$text_info .= '<br>';
								}
							}
						}
					}
					if ($notshippable == 0) {
						$text_icon = img_picto('', 'dolly', '', false, 0, 0, '', 'green paddingleft');
						$text_info = $langs->trans('Shippable') . '<br>' . $text_info;
					} else {
						$text_icon = img_picto('', 'dolly', '', false, 0, 0, '', 'error paddingleft');
						$text_info = $langs->trans('NonShippable') . '<br>' . $text_info;
					}
				}

				if ($nbprod) {
					print $form->textwithtooltip('', $text_info, 2, 1, $text_icon, '', 2);
				}
				if ($warning) {     // Always false in default mode
					print $form->textwithtooltip('', $langs->trans('NotEnoughForAllOrders') . '<br>' . $text_warning, 2, 1, img_picto('', 'error'), '', 2);
				}
			}

			// Warning late icon and note
			if ($generic_commande->hasDelay()) {
				print img_picto($langs->trans("Late") . ' : ' . $generic_commande->showDelay(), "warning");
			}

			$filename = dol_sanitizeFileName($obj->ref);
			$filedir = $conf->commande->multidir_output[$conf->entity] . '/' . dol_sanitizeFileName($obj->ref);
			$urlsource = $_SERVER['PHP_SELF'] . '?id=' . $obj->rowid;
			print $formfile->getDocumentsLink($generic_commande->element, $filename, $filedir);

			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}

		// Ref customer
		if (!empty($arrayfields['c.ref_client']['checked'])) {
			print '<td class="nowrap tdoverflowmax200">' . $obj->ref_client . '</td>';
			if (!$i) $totalarray['nbfield']++;
		}

		// Project ref
		if (!empty($arrayfields['p.ref']['checked'])) {
			print '<td class="nowrap">';
			if ($obj->project_id > 0) {
				print $projectstatic->getNomUrl(1);
			}
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}

		// Project label
		if (!empty($arrayfields['p.title']['checked'])) {
			print '<td class="nowrap">';
			if ($obj->project_id > 0) {
				print $projectstatic->title;
			}
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}

		// Third party
		if (!empty($arrayfields['s.nom']['checked'])) {
			print '<td class="tdoverflowmax200">';
			print $getNomUrl_cache[$obj->socid];

			// If module invoices enabled and user with invoice creation permissions
			if (!empty($conf->facture->enabled) && !empty($conf->global->ORDER_BILLING_ALL_CUSTOMER)) {
				if ($user->rights->facture->creer) {
					if (($obj->fk_statut > 0 && $obj->fk_statut < 3) || ($obj->fk_statut == 3 && $obj->billed == 0)) {
						print '&nbsp;<a href="' . DOL_URL_ROOT . '/commande/orderstoinvoice.php?socid=' . $companystatic->id . '">';
						print img_picto($langs->trans("CreateInvoiceForThisCustomer") . ' : ' . $companystatic->name, 'object_bill', 'hideonsmartphone') . '</a>';
					}
				}
			}
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}
		// Town
		if (!empty($arrayfields['s.town']['checked'])) {
			print '<td class="nocellnopadd">';
			print $obj->town;
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}
		// Zip
		if (!empty($arrayfields['s.zip']['checked'])) {
			print '<td class="nocellnopadd">';
			print $obj->zip;
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}
		// State
		if (!empty($arrayfields['state.nom']['checked'])) {
			print "<td>" . $obj->state_name . "</td>\n";
			if (!$i) $totalarray['nbfield']++;
		}
		// Country
		if (!empty($arrayfields['country.code_iso']['checked'])) {
			print '<td class="center">';
			$tmparray = getCountry($obj->fk_pays, 'all');
			print $tmparray['label'];
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}
		// Type ent
		if (!empty($arrayfields['typent.code']['checked'])) {
			print '<td class="center">';
			if (empty($typenArray)) $typenArray = $formcompany->typent_array(1);
			print $typenArray[$obj->typent_code];
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}

		// Order date
		if (!empty($arrayfields['c.date_commande']['checked'])) {
			print '<td class="center">';
			print dol_print_date($db->jdate($obj->date_commande), 'day');
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}
		// Plannned date of delivery
		if (!empty($arrayfields['c.date_delivery']['checked'])) {
			print '<td class="center">';
			print dol_print_date($db->jdate($obj->date_delivery), 'dayhour');
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}
		// Amount HT
		if (!empty($arrayfields['c.total_ht']['checked'])) {
			print '<td class="nowrap right">' . price($obj->total_ht) . "</td>\n";
			if (!$i) $totalarray['nbfield']++;
			if (!$i) $totalarray['pos'][$totalarray['nbfield']] = 'c.total_ht';
			$totalarray['val']['c.total_ht'] += $obj->total_ht;
		}
		// Amount VAT
		if (!empty($arrayfields['c.total_vat']['checked'])) {
			print '<td class="nowrap right">' . price($obj->total_tva) . "</td>\n";
			if (!$i) $totalarray['nbfield']++;
			if (!$i) $totalarray['pos'][$totalarray['nbfield']] = 'c.total_tva';
			$totalarray['val']['c.total_tva'] += $obj->total_tva;
		}
		// Amount TTC
		if (!empty($arrayfields['c.total_ttc']['checked'])) {
			print '<td class="nowrap right">' . price($obj->total_ttc) . "</td>\n";
			if (!$i) $totalarray['nbfield']++;
			if (!$i) $totalarray['pos'][$totalarray['nbfield']] = 'c.total_ttc';
			$totalarray['val']['c.total_ttc'] += $obj->total_ttc;
		}

		// Currency
		if (!empty($arrayfields['c.multicurrency_code']['checked'])) {
			print '<td class="nowrap">' . $obj->multicurrency_code . ' - ' . $langs->trans('Currency' . $obj->multicurrency_code) . "</td>\n";
			if (!$i) $totalarray['nbfield']++;
		}

		// Currency rate
		if (!empty($arrayfields['c.multicurrency_tx']['checked'])) {
			print '<td class="nowrap">';
			$form->form_multicurrency_rate($_SERVER['PHP_SELF'] . '?id=' . $obj->rowid, $obj->multicurrency_tx, 'none', $obj->multicurrency_code);
			print "</td>\n";
			if (!$i) $totalarray['nbfield']++;
		}
		// Amount HT
		if (!empty($arrayfields['c.multicurrency_total_ht']['checked'])) {
			print '<td class="right nowrap">' . price($obj->multicurrency_total_ht) . "</td>\n";
			if (!$i) $totalarray['nbfield']++;
		}
		// Amount VAT
		if (!empty($arrayfields['c.multicurrency_total_vat']['checked'])) {
			print '<td class="right nowrap">' . price($obj->multicurrency_total_vat) . "</td>\n";
			if (!$i) $totalarray['nbfield']++;
		}
		// Amount TTC
		if (!empty($arrayfields['c.multicurrency_total_ttc']['checked'])) {
			print '<td class="right nowrap">' . price($obj->multicurrency_total_ttc) . "</td>\n";
			if (!$i) $totalarray['nbfield']++;
		}

		$userstatic->id = $obj->fk_user_author;
		$userstatic->login = $obj->login;

		// Author
		if (!empty($arrayfields['u.login']['checked'])) {
			print '<td align="center">';
			if ($userstatic->id) print $userstatic->getLoginUrl(1);
			else print '&nbsp;';
			print "</td>\n";
			if (!$i) $totalarray['nbfield']++;
		}

		// Extra fields
		include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_print_fields.tpl.php';
		// Fields from hook
		$parameters = array('arrayfields' => $arrayfields, 'obj' => $obj, 'i' => $i, 'totalarray' => &$totalarray);
		$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters); // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;
		// Date creation
		if (!empty($arrayfields['c.datec']['checked'])) {
			print '<td align="center" class="nowrap">';
			print dol_print_date($db->jdate($obj->date_creation), 'dayhour', 'tzuser');
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}
		// Date modification
		if (!empty($arrayfields['c.tms']['checked'])) {
			print '<td align="center" class="nowrap">';
			print dol_print_date($db->jdate($obj->date_update), 'dayhour', 'tzuser');
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}
		// Date cloture
		if (!empty($arrayfields['c.date_cloture']['checked'])) {
			print '<td align="center" class="nowrap">';
			print dol_print_date($db->jdate($obj->date_cloture), 'dayhour', 'tzuser');
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
		}
		// Status
		if (!empty($arrayfields['c.fk_statut']['checked'])) {
			print '<td class="nowrap right">' . $generic_commande->LibStatut($obj->fk_statut, $obj->billed, 5, 1) . '</td>';
			if (!$i) $totalarray['nbfield']++;
		}
		// Billed
		if (!empty($arrayfields['c.facture']['checked'])) {
			print '<td class="center">' . yn($obj->billed) . '</td>';
			if (!$i) $totalarray['nbfield']++;
		}

		// Action column
		print '<td class="nowrap" align="center">';
		if ($massactionbutton || $massaction)   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
		{
			$selected = 0;
			if (in_array($obj->rowid, $arrayofselected)) $selected = 1;
			print '<input id="cb' . $obj->rowid . '" class="flat checkforselect" type="checkbox" name="toselect[]" value="' . $obj->rowid . '"' . ($selected ? ' checked="checked"' : '') . '>';
		}
		print '</td>';
		if (!$i) $totalarray['nbfield']++;

		print "</tr>\n";

		$total += $obj->total_ht;
		$subtotal += $obj->total_ht;
		$i++;
	}

	// Show total line
	include DOL_DOCUMENT_ROOT . '/core/tpl/list_print_total.tpl.php';

	$db->free($resql);

	$parameters = array('arrayfields' => $arrayfields, 'sql' => $sql);
	$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;

	print '</table>' . "\n";
	print '</div>';

	print '</form>' . "\n";

	$hidegeneratedfilelistifempty = 1;
	if ($massaction == 'builddoc' || $action == 'remove_file' || $show_files) $hidegeneratedfilelistifempty = 0;

	// Show list of available documents
	$urlsource = $_SERVER['PHP_SELF'] . '?sortfield=' . $sortfield . '&sortorder=' . $sortorder;
	$urlsource .= str_replace('&amp;', '&', $param);

	$filedir = $diroutputmassaction;
	$genallowed = $user->rights->commande->lire;
	$delallowed = $user->rights->commande->creer;

	print $formfile->showdocuments('massfilesarea_orders', '', $filedir, $urlsource, 0, $delallowed, '', 1, 1, 0, 48, 1, $param, $title, '', '', '', null, $hidegeneratedfilelistifempty);
} else {
	dol_print_error($db);
}

// End of page
llxFooter();
$db->close();
