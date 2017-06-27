<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012 ATM Consulting <developper@atm-consulting.fr
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

require('../config.php');
dol_include_once('/exportcompta/class/export.class.php');
dol_include_once('/core/lib/admin.lib.php');
dol_include_once('/exportcompta/lib/exportcompta.lib.php');

if (! $user->admin) accessforbidden();

$langs->load("admin");
$langs->load("errors");

$exp = new TExportCompta($db);

/**
 * Actions
 */
$action = GETPOST('action','alpha');

if($action == 'save') {
	
	$TTVA = GETPOST('TTVA');
	foreach($TTVA as $fk_tva=>$data) {
		
		$db->query("UPDATE ".MAIN_DB_PREFIX."c_tva SET 
			accountancy_code_sell=".(empty($data['accountancy_code_sell']) ? 'NULL' : "'".$data['accountancy_code_sell']."'")."
			, accountancy_code_buy=".(empty($data['accountancy_code_buy']) ? 'NULL' : "'".$data['accountancy_code_buy']."'")."
			,accountancy_code_sell_service=".(empty($data['accountancy_code_sell_service']) ? 'NULL' : "'".$data['accountancy_code_sell_service']."'")."
			,accountancy_code_buy_service=".(empty($data['accountancy_code_buy_service']) ? 'NULL' : "'".$data['accountancy_code_buy_service']."'")."
		WHERE rowid=".$fk_tva);
		 
	}
	
}

/**
 * View
 */
$head = exportcompta_admin_prepare_head();
llxHeader("",$langs->trans("ExportComptaSetup"));

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("ExportComptaSetup"),$linkback,'setup');

dol_fiche_head($head, 'tva', $langs->trans("ExportCompta"));

$formCore= new TFormCore('auto','formTVA');
echo $formCore->hidden('action', 'save');


print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("VAT").'</td>';
print '<td align="right" width="60">'.$langs->trans("Rate").'</td>';
print '<td>'.$langs->trans("AccountancySellCode").'</td>';
print '<td>'.$langs->trans("AccountancyBuyCode").'</td>';
print '<td>'.$langs->trans("AccountancySellCodeService").'</td>';
print '<td>'.$langs->trans("AccountancyBuyCodeService").'</td>';
print "</tr>\n";
$var=true;

$fk_country = $mysoc->country_id;

$res = $db->query("SELECT rowid, taux, note, accountancy_code_sell, accountancy_code_buy,accountancy_code_sell_service,accountancy_code_buy_service FROM ".MAIN_DB_PREFIX."c_tva WHERE active=1 AND fk_pays=".$fk_country);

while($tva = $db->fetch_object($res)) {
// Logiciel d'export
$var=! $var;
//var_dump($tva);
print '<tr '.$bc[$var].'><td>';
print $langs->trans($tva->note);
print '</td><td width="60" align="right">'.$tva->taux.'%</td>';
print '<td>'.$formCore->texte('', 'TTVA['.$tva->rowid.'][accountancy_code_sell]', $tva->accountancy_code_sell, 20).'</td>';
print '<td>'.$formCore->texte('', 'TTVA['.$tva->rowid.'][accountancy_code_buy]', $tva->accountancy_code_buy, 20).'</td>';
print '<td>'.$formCore->texte('', 'TTVA['.$tva->rowid.'][accountancy_code_sell_service]', $tva->accountancy_code_sell_service, 20).'</td>';
print '<td>'.$formCore->texte('', 'TTVA['.$tva->rowid.'][accountancy_code_buy_service]', $tva->accountancy_code_buy_service, 20).'</td>';
print "</tr>\n";

}
echo '</table>';

echo '<div class="tabsAction">';
echo $formCore->btsubmit($langs->trans('Save'), 'bt_save');
echo '</div>';

$formCore->end();

llxFooter();
