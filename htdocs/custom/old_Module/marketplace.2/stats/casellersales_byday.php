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
$title= $langs->trans("RapportSales"). ', '. $langs->trans("MarketPlaceByDay");
llxHeader('', $title, $helpurl);

$html=new Form($db);

$period=$html->select_date($date_start, 'date_start', 0, 0, 0, '', 1, 0, 1).' - '.$html->select_date($date_end, 'date_end', 0, 0, 0, '', 1, 0, 1);
$description=$langs->trans("RulesResult");
$builddate=time();

report_header($title, $nomlink, $period, $periodlink, $description, $builddate, $exportlink);

// Load table
$catotal=0;

$sql = "SELECT MONTH(date_creation) as monthno, WEEK(date_creation) as weekno, DAY(date_creation) as dayno, SUM(price) as amount_ht, SUM(tax_total) as amount_tax, SUM(collection_amount) as amount_collection, SUM(retrocession_amount) as amount_retrocession";
    $sql.= " FROM ".MAIN_DB_PREFIX."marketplace_sales as ms";
    $sql.= " WHERE ms.status in (0,1,9)";
    //$sql.= " AND f.rowid = ms.fk_customer_invoice";
    //$sql.= " AND ms.fk_seller = s.rowid";
if ($date_start && $date_end) {
    $sql.= " AND ms.date_creation >= '".$db->idate($date_start)."' AND ms.date_creation <= '".$db->idate($date_end)."'";
}

//$sql.= " AND f.entity = ".$conf->entity;
if ($socid) {
    $sql.= " AND ms.fk_seller = ".$socid;
}
$sql.= " GROUP BY monthno, weekno, dayno";
$sql.= " ORDER BY date_creation ASC";

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

        $lines[] = $obj;

        $linesPerMonth[$obj->monthno] = array();
        $linesPerMonth[$obj->monthno][$obj->weekno][$obj->dayno]['amount_ht'] = $obj->amount_ht;
        $linesPerMonth[$obj->monthno][$obj->weekno][$obj->dayno]['amount_tax'] = $obj->amount_tax;
        $linesPerMonth[$obj->monthno][$obj->weekno][$obj->dayno]['amount_collection'] = $obj->amount_collection;
        $linesPerMonth[$obj->monthno][$obj->weekno][$obj->dayno]['amount_retrocession'] = $obj->amount_retrocession;


        
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
print_liste_field_titre($langs->trans("Month"), $_SERVER["PHP_SELF"], "", "", $params, "");
//print_liste_field_titre($langs->trans("Week"), $_SERVER["PHP_SELF"], "weekno", "", $params, "", $sortfield, $sortorder);
print_liste_field_titre($langs->trans("Day"), $_SERVER["PHP_SELF"], "", "", $params, "");
print_liste_field_titre($langs->trans("AmountHT"), $_SERVER["PHP_SELF"], "", "", $params, 'align="right"');
print_liste_field_titre($langs->trans("AmountTax"), $_SERVER["PHP_SELF"], "", '', '', ' align="right"');
print_liste_field_titre($langs->trans("AmountCollection"), $_SERVER["PHP_SELF"], "", '', '', ' align="right"');
print_liste_field_titre($langs->trans("AmountRetrocession"), $_SERVER["PHP_SELF"], "", '', '', ' align="right"');
print_liste_field_titre($langs->trans("EvolPreviousDay"), $_SERVER["PHP_SELF"], "", "", $params, 'align="right"');
print_liste_field_titre($langs->trans("Percentage"), $_SERVER["PHP_SELF"], "", "", "", 'align="right"');
print "</tr>\n";
$var=true;
//arsort($lines);

//var_dump($lines);

if (count($lines)) {
    $oldAmount = 0;
    foreach ($lines as $key => $objsql) {
        $var=!$var;
        print "<tr ".$bc[$var].">";

        

        print "<td>". $objsql->monthno."</td>\n";
        print "<td>". $objsql->dayno."</td>\n";

        // Amount HT
        print '<td align="right">';
        print price($objsql->amount_ht);
        
        print '</td>';

        // Amount Tax
        print '<td align="right">';
        
        print price($objsql->amount_tax);
        print '</td>';

        // Collection amount
        print '<td align="right">';
        print price($objsql->amount_collection);
        print '</td>';

        // Retrocession amount
        print '<td align="right">';
        print price($objsql->amount_retrocession);
        print '</td>';

        // Evolution
        $evolVeille = (($objsql->amount_ht - $oldAmount) / $objsql->amount_ht) * 100;
        print '<td align="right">'.($oldAmount  > 0 ? round($evolVeille, 2) .'%' : '&nbsp;').'</td>';

        // Percent;
        print '<td align="right">'.($catotal > 0 ? round(100 * $objsql->amount_ht / $catotal, 2).'%' : '&nbsp;').'</td>';

        //
        //print '<td align="center">';
        
        //print '</td>';

        print "</tr>\n";

        $oldAmount = $objsql->amount_ht;

        $i++;
    }

    // Total
    print '<tr class="liste_total">';
    print '<td>&nbsp;</td>';
    print '<td>'.$langs->trans("Total").'</td><td align="right">'.price($catotal).'</td>';
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
