<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2019     Jean-François Ferry	<hello+jf@librethic.io>
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
 *	\file       htdocs/marketplace/template/marketplaceindex.php
 *	\ingroup    marketplace
 *	\brief      Home page of marketplace top menu
 */

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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
// Load translation files required by the page
$langs->loadLangs(array("marketplace@marketplace"));

$action=GETPOST('action', 'alpha');


// Securite acces client
if (! $user->rights->marketplace->read) {
    accessforbidden();
}
$socid=GETPOST('socid', 'int');
if (isset($user->societe_id) && $user->societe_id > 0) {
	$action = '';
	$socid = $user->societe_id;
}

$max=5;
$now=dol_now();


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("MarketplaceArea"));

print load_fiche_titre($langs->trans("MarketplaceArea"), '', 'marketplace.png@marketplace');

print '<div class="fichecenter"><div class="fichethirdleft">';


/* BEGIN MODULEBUILDER DRAFT MYOBJECT
*/
// Draft MyObject
if (! empty($conf->marketplace->enabled) && $user->rights->marketplace->read) {
	$langs->load("orders");

	$sql = "SELECT c.rowid, c.fk_seller, c.fk_product, c.price, c.tax_total as total_tva, c.collection_amount, s.rowid as socid, s.nom as name, s.client, s.canvas";
    $sql.= ", s.code_client";
    $sql.= ", p.label as product_label, p.ref as product_ref";
	$sql.= " FROM ".MAIN_DB_PREFIX."marketplace_sales as c";
    $sql.= ", ".MAIN_DB_PREFIX."societe as s";
    $sql.= " , ".MAIN_DB_PREFIX."product as p ";
	if (! $user->rights->societe->client->voir && ! $socid) {
        $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
    }
    $sql.= " WHERE c.fk_seller = s.rowid";
    $sql.= " AND p.rowid=c.fk_product";
	$sql.= " AND c.status = 0";
	//$sql.= " AND c.entity IN (".getEntity('marketplace_sales').")";
	if (! $user->rights->societe->client->voir && ! $socid) {
        $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
    }
	if ($socid) {
        $sql.= " AND c.fk_seller = ".$socid;
    }

    
    $sql.= $db->plimit(15, 0);
	$resql = $db->query($sql);
	if ($resql) {
		$total = 0;
		$num = $db->num_rows($resql);

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
        print '<th >'.$langs->trans("MarketPlaceLatestSales").($num?' <span class="badge">'.$num.'</span>':'').'</th>';
        print '<th>'.$langs->trans("MarketPlaceSeller").'</th>';
        print '<th class="right">'.$langs->trans("MarketPlaceSalePriceNet").'</th>';
        print '<th class="right">'.$langs->trans("MarketplaceSaleTaxTotal").'</th>';
        print '<th class="right" colspan="3">'.$langs->trans("MarketplaceSaleRetrocessionAmount").'</th>';
        print '</tr>';

		$var = true;
		if ($num > 0) {
			$i = 0;
			while ($i < $num) {
                $companyStatic = new Societe($db);
                $productStatic = new Product($db);

				$obj = $db->fetch_object($resql);
				print '<tr class="oddeven"><td class="nowrap">';
                
                $productStatic->id = $obj->fk_product;
                $productStatic->ref = $obj->product_ref;
                $productStatic->label = $obj->product_label;

                print $productStatic->getNomUrl(1);
                print '</td>';

				print '<td class="nowrap">';
                $companyStatic->id =  $obj->fk_seller;
                $companyStatic->name = $obj->name;
                print $companyStatic->getNomUrl();
                print '</td>';
                
                print '<td align="right" class="nowrap">'.price($obj->price).'</td>';
                
                print '<td align="right" class="nowrap">'.price($obj->total_tva).'</td>';

                print '<td align="right" class="nowrap">'.price($obj->collection_amount).'</td></tr>';

				$i++;
				$total += $obj->total_ttc;
			}
			if ($total>0) {
				print '<tr class="liste_total"><td>'.$langs->trans("Total").'</td><td colspan="2" align="right">'.price($total)."</td></tr>";
			}
		} else {
			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("NoOrder").'</td></tr>';
		}
		print "</table><br>";

		$db->free($resql);
	} else {
		dol_print_error($db);
	}
}
/* END MODULEBUILDER DRAFT MYOBJECT */


print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


$NBMAX=3;
$max=3;

/* BEGIN MODULEBUILDER LASTMODIFIED MYOBJECT
// Last modified myobject
if (! empty($conf->marketplace->enabled) && $user->rights->marketplace->read)
{
	$sql = "SELECT s.rowid, s.nom as name, s.client, s.datec, s.tms, s.canvas";
    $sql.= ", s.code_client";
	$sql.= " FROM ".MAIN_DB_PREFIX."societe as s";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql.= " WHERE s.client IN (1, 2, 3)";
	$sql.= " AND s.entity IN (".getEntity($companystatic->element).")";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($socid)	$sql.= " AND s.rowid = $socid";
	$sql .= " ORDER BY s.tms DESC";
	$sql .= $db->plimit($max, 0);

	$resql = $db->query($sql);
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<th colspan="2">';
		if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) print $langs->trans("BoxTitleLastCustomersOrProspects",$max);
        else if (! empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) print $langs->trans("BoxTitleLastModifiedProspects",$max);
		else print $langs->trans("BoxTitleLastModifiedCustomers",$max);
		print '</th>';
		print '<th align="right">'.$langs->trans("DateModificationShort").'</th>';
		print '</tr>';
		if ($num)
		{
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);
				$companystatic->id=$objp->rowid;
				$companystatic->name=$objp->name;
				$companystatic->client=$objp->client;
                $companystatic->code_client = $objp->code_client;
                $companystatic->code_fournisseur = $objp->code_fournisseur;
                $companystatic->canvas=$objp->canvas;
				print '<tr class="oddeven">';
				print '<td class="nowrap">'.$companystatic->getNomUrl(1,'customer',48).'</td>';
				print '<td align="right" nowrap>';
				print $companystatic->getLibCustProspStatut();
				print "</td>";
				print '<td align="right" nowrap>'.dol_print_date($db->jdate($objp->tms),'day')."</td>";
				print '</tr>';
				$i++;


			}

			$db->free($resql);
		}
		else
		{
			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
		}
		print "</table><br>";
	}
}
*/

print '</div></div></div>';

// End of page
llxFooter();
$db->close();
