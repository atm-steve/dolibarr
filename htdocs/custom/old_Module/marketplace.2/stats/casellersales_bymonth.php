<?php
/**
 * Copyright (C) 2012    Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2019    Jean-François Ferry    <hello@librethic.io>
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
 *       \file        htdocs/pos/backend/resultat/casoc.php
 *       \brief       Page ticket reporting  by customer
 */

$res=@require "../../main.inc.php";                                   // For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php")) {
    $res=@include $_SERVER['DOCUMENT_ROOT']."/main.inc.php"; // Use on dev env only
}
if (! $res) {
    $res=@include "../../../main.inc.php";                // For "custom" directory
}

require_once DOL_DOCUMENT_ROOT."/core/lib/report.lib.php";
require_once DOL_DOCUMENT_ROOT."/core/lib/tax.lib.php";
require_once DOL_DOCUMENT_ROOT."/core/lib/date.lib.php";

global $langs, $user,$conf,$db, $bc;

$langs->load("companies");

$sortorder = GETPOST('sortorder', 'string');
$sortfield = GETPOST('sortfield', 'string');
if (! $sortorder) {
    $sortorder="asc";
}
if (! $sortfield) {
    $sortfield="name";
}

// Security check
$socid = GETPOST("socid", "int");
if ($user->socid > 0) {
    $socid = $user->socid;
}
if (!$user->rights->marketplace->read) {
    accessforbidden();
}

// Date range
$year=GETPOST("year");
$month=GETPOST("month");
if (empty($year)) {
    $year_current = strftime("%Y", dol_now());
    $month_current = strftime("%m", dol_now());
    $year_start = $year_current;
} else {
    $year_current = $year;
    $month_current = strftime("%m", dol_now());
    $year_start = $year;
}
$dateStartFilter = GETPOST('dateStart');
$dateEndFilter = GETPOST('dateEnd');

$date_start=dol_mktime(0, 0, 0, GETPOST("date_startmonth"), GETPOST("date_startday"), GETPOST("date_startyear"));
$date_end=dol_mktime(23, 59, 59, GETPOST("date_endmonth"), GETPOST("date_endday"), GETPOST("date_endyear"));
if (! empty($dateStartFilter) && ! empty($dateEndFilter)) {
    $date_start = $dateStartFilter;
    $date_end = $dateEndFilter;
}
// Quarter
if (empty($date_start) || empty($date_end)) {
    // We define date_start and date_end
    $date_start=dol_get_first_day($year_current, $month_current, false);
    $date_end=dol_get_last_day($year_current, $month_current, false);
}


/*
 * View
 */
$helpurl='';
$title= $langs->trans("RapportSales"). ', '. $langs->trans("MarketPlaceBySeller");
llxHeader('', $title, $helpurl);

$html=new Form($db);

$period=$html->select_date($date_start, 'date_start', 0, 0, 0, '', 1, 0, 1).' - '.$html->select_date($date_end, 'date_end', 0, 0, 0, '', 1, 0, 1);
$description=$langs->trans("RulesResult");
$builddate=time();

report_header($title, $nomlink, $period, $periodlink, $description, $builddate, $exportlink);

// Load table
$catotal=0;

$sql = "SELECT s.rowid as socid, s.nom as name, sum(ms.price) as amount_ht, sum(ms.tax_total) as amount_tax, sum(ms.collection_amount) as amount_collection, sum(ms.retrocession_amount) as amount_retrocession";
    $sql.= " FROM ".MAIN_DB_PREFIX."societe as s";
    $sql.= ", ".MAIN_DB_PREFIX."marketplace_sales as ms";
    $sql.= " WHERE ms.status in (0,1,9)";
    //$sql.= " AND f.rowid = ms.fk_customer_invoice";
    $sql.= " AND ms.fk_seller = s.rowid";
if ($date_start && $date_end) {
    $sql.= " AND ms.date_creation >= '".$db->idate($date_start)."' AND ms.date_creation <= '".$db->idate($date_end)."'";
}

//$sql.= " AND f.entity = ".$conf->entity;
if ($socid) {
    $sql.= " AND ms.fk_seller = ".$socid;
}
$sql.= " GROUP BY s.rowid, s.nom";
$sql.= " ORDER BY s.rowid";

$result = $db->query($sql);

$amount = array();
$amountTax = array();
$amountCollection = array();
$amountRetrocession = array();

if ($result) {
    $num = $db->num_rows($result);
    $i=0;
    while ($i < $num) {
        $obj = $db->fetch_object($result);
        $amount[$obj->socid] += $obj->amount_ht;
        $amountTax[$obj->socid] += $obj->amount_tax;
        $amountCollection[$obj->socid] += $obj->amount_collection;
        $amountRetrocession[$obj->socid] += $obj->amount_retrocession;
        $name[$obj->socid] = $obj->name;
        $catotal+=$obj->amount_ht;
        $totalTax+= $obj->amount_tax;
        $totalCollection+= $obj->amount_collection;
        $totalRetrocession+= $obj->amount_retrocession;

        $i++;
    }
} else {
    dol_print_error($db);
}


$i = 0;
print "<table class=\"noborder\" width=\"100%\">";
print "<tr class=\"liste_titre\">";

$params = '';
if (!empty($date_start) && !empty($date_end)) {
    $params .= '&amp;dateStart='.$date_start.'&amp;dateEnd='.$date_end;
}
//$params = '&amp;year='.($year).'&modecompta='.$modecompta;
print_liste_field_titre($langs->trans("Company"), $_SERVER["PHP_SELF"], "name", "", $params, "", $sortfield, $sortorder);
print_liste_field_titre($langs->trans("AmountHT"), $_SERVER["PHP_SELF"], "amount_ttc", "", $params, 'align="right"', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("AmountTax"), $_SERVER["PHP_SELF"], "", '', '', ' align="right"');
print_liste_field_titre($langs->trans("AmountCollection"), $_SERVER["PHP_SELF"], "", '', '', ' align="right"');
print_liste_field_titre($langs->trans("AmountRetrocession"), $_SERVER["PHP_SELF"], "", '', '', ' align="right"');
print_liste_field_titre($langs->trans("Percentage"), $_SERVER["PHP_SELF"], "amount_ttc", "", $params, 'align="right"', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("OtherStatistics"), $_SERVER["PHP_SELF"], "", "", "", 'align="center" width="20%"');
print "</tr>\n";
$var=true;

if (count($amount)) {
    $arrayforsort=$name;

    if ($sortfield == 'name' && $sortorder == 'asc') {
        asort($name);
        $arrayforsort=$name;
    }
    if ($sortfield == 'name' && $sortorder == 'desc') {
        arsort($name);
        $arrayforsort=$name;
    }
    if ($sortfield == 'amount_ttc' && $sortorder == 'asc') {
        asort($amount);
        $arrayforsort=$amount;
    }
    if ($sortfield == 'amount_ttc' && $sortorder == 'desc') {
        arsort($amount);
        $arrayforsort=$amount;
    }

    foreach ($arrayforsort as $key => $value) {
        $var=!$var;
        print "<tr ".$bc[$var].">";

        // Third party
        $fullname=$name[$key];
        if ($key > 0) {
            $url = dol_buildpath('/marketplace/sales_list.php', 1) . '?fk_seller='.$key. '&amp;dateStart='.$date_start.'&amp;dateEnd='.$date_end;
            $linkname='<a href="'.$url.'">'.img_object($langs->trans("ShowCompany"), 'company').' '.$fullname.'</a>';
        }

        print "<td>".$linkname."</td>\n";

        // Amount HT
        print '<td align="right">';
        $url = dol_buildpath('/pos/backend/listefac.php?socid='.$key.'', 1);
        if ($key > 0) {
            print '<a href="'.$url.'">';
        } else {
            print '<a href="#">';
        }

        print price($amount[$key]);
        print '</a>';
        print '</td>';

        // Amount Tax
        print '<td align="right">';
        $url = dol_buildpath('/pos/backend/listefac.php?socid='.$key.'', 1);
        if ($key > 0) {
            print '<a href="'.$url.'"> ' ;
        } else {
            print '<a href="#">';
        }
        print price($amountTax[$key]);
        print '</a>';
        print '</td>';

        // Collection amount
        print '<td align="right">';
        $url = dol_buildpath('/pos/backend/listefac.php?socid='.$key.'', 1);
        if ($key > 0) {
            print '<a href="'.$url.'">   ';
        } else {
            print '<a href="#">';
        }
        print price($amountCollection[$key]);
        print '</a>';
        print '</td>';

        // Retrocession amount
        print '<td align="right">';
        $url = dol_buildpath('/pos/backend/listefac.php?socid='.$key.'', 1);
        if ($key > 0) {
            print '<a href="'.$url.'">     ';
        } else {
            print '<a href="#">';
        }
        print price($amountRetrocession[$key]);
        print '</a>';
        print '</td>';

        // Percent;
        print '<td align="right">'.($catotal > 0 ? round(100 * $amount[$key] / $catotal, 2).'%' : '&nbsp;').'</td>';

        // Other stats
        print '<td align="center">';
        if ($conf->propal->enabled && $key>0) {
            print '&nbsp;<a href="'.DOL_URL_ROOT.'/comm/propal/stats/index.php?socid='.$key.'">'.img_picto($langs->trans("ProposalStats"), "stats").'</a>&nbsp;';
        }
        if ($conf->commande->enabled && $key>0) {
            print '&nbsp;<a href="'.DOL_URL_ROOT.'/commande/stats/index.php?socid='.$key.'">'.img_picto($langs->trans("OrderStats"), "stats").'</a>&nbsp;';
        }
        if ($conf->facture->enabled && $key>0) {
            print '&nbsp;<a href="'.DOL_URL_ROOT.'/compta/facture/stats/index.php?socid='.$key.'&mode=supplier">'.img_picto($langs->trans("BillsStatisticsSuppliers"), "stats").'</a>&nbsp;';
        }
        if ($conf->facture->enabled && $key>0) {
            print '&nbsp;<a href="'.DOL_URL_ROOT.'/compta/facture/stats/index.php?socid='.$key.'">'.img_picto($langs->trans("InvoiceStats"), "stats").'</a>&nbsp;';
        }
        print '</td>';

        print "</tr>\n";
        $i++;
    }

    // Total
    print '<tr class="liste_total"><td>'.$langs->trans("Total").'</td><td align="right">'.price($catotal).'</td>';
    print '<td align="right">'.price($totalTax).'</td> ';
    print '<td align="right">'.price($totalCollection).'</td>';
    print '<td align="right">'.price($totalRetrocession).'</td> ';
    print '<td>&nbsp;</td>';
    print '<td>&nbsp;</td>';
    print '</tr>';

    $db->free($result);
}

print "</table>";
print '<br>';

llxFooter();

$db->close();
