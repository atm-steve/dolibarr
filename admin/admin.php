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
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/export-compta/lib/export-compta.lib.php');

$langs->load("admin");
$langs->load("errors");
$langs->load('export-compta@export-compta');

if (! $user->admin) accessforbidden();

$action = GETPOST('action','alpha');

if($action == 'setlogicielexport') {
	$logicielexport = GETPOST('logicielexport', 'alpha');
	dolibarr_set_const($db,'EXPORT_COMPTA_LOGICIEL_EXPORT',$logicielexport,'chaine',0,'',$conf->entity);
}
if($action == 'setallentites') {
	$allentites = GETPOST('allentites', 'alpha');
	dolibarr_set_const($db,'EXPORT_COMPTA_ALL_ENTITIES',$allentites,'chaine',0,'',$conf->entity);
}
if($action == 'setdateexportvente') {
	$dateexportvente = GETPOST('dateexportvente', 'alpha');
	dolibarr_set_const($db,'EXPORT_COMPTA_DATE_VENTES',$dateexportvente,'chaine',0,'',$conf->entity);
}
if($action == 'setdateexportachat') {
	$dateexportachat = GETPOST('dateexportachat', 'alpha');
	dolibarr_set_const($db,'EXPORT_COMPTA_DATE_ACHATS',$dateexportachat,'chaine',0,'',$conf->entity);
}


$head = exportcompta_admin_prepare_head();
llxHeader("",$langs->trans("ExportComptaSetup"));

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("ExportComptaSetup"),$linkback,'setup');

dol_fiche_head($head, 'config', $langs->trans("ExportCompta"));

$form=new Form($db);

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td align="center" width="60">'.$langs->trans("Value").'</td>';
print '<td width="80">&nbsp;</td>';
print "</tr>\n";
$var=true;

// Format d'export
$var=! $var;
print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'" />';
print '<input type="hidden" name="action" value="setlogicielexport" />';
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ExportFormat");
print '</td><td width="60" align="right">';
print '<select name="logicielexport" class="flat">';
print '<option value=""></option>';
print '<option value="quadratus"';
print $conf->global->EXPORT_COMPTA_LOGICIEL_EXPORT == 'quadratus' ? ' selected="selected"':'';
print '>'.$langs->trans("quadratus").'</option>';
print '<option value="sage"';
print $conf->global->EXPORT_COMPTA_LOGICIEL_EXPORT == 'sage' ? ' selected="selected"':'';
print '>'.$langs->trans("sage").'</option>';
print '</select>';
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
print '</form>';

// Export de toutes les entités
$var=!$var;
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setallentites">';
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("ExportAllEntities").'</td>';
print '<td width="60" align="right">';
print $form->selectyesno("allentites",$conf->global->EXPORT_COMPTA_ALL_ENTITIES,1);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print '</td>';
print '</tr>';
print '</form>';

// Champ date utilisé pour les bornes sur factures de vente
$var=! $var;
print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'" />';
print '<input type="hidden" name="action" value="setdateexportvente" />';
print '<tr '.$bc[$var].'><td>';
print $langs->trans("BaseDateForSellings");
print '</td><td width="60" align="right">';
print '<select name="dateexportvente" class="flat">';
print '<option value="datef"';
print $conf->global->EXPORT_COMPTA_DATE_VENTES == 'datef' ? ' selected="selected"':'';
print '>'.$langs->trans("DateFacture").'</option>';
print '<option value="date_valid"';
print $conf->global->EXPORT_COMPTA_DATE_VENTES == 'date_valid' ? ' selected="selected"':'';
print '>'.$langs->trans("DateValid").'</option>';
print '</select>';
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
print '</form>';

// Champ date utilisé pour les bornes sur factures d'achat
$var=! $var;
print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'" />';
print '<input type="hidden" name="action" value="setdateexportachat" />';
print '<tr '.$bc[$var].'><td>';
print $langs->trans("BaseDateForSellings");
print '</td><td width="60" align="right">';
print '<select name="dateexportachat" class="flat">';
print '<option value="datef"';
print $conf->global->EXPORT_COMPTA_DATE_ACHATS == 'datef' ? ' selected="selected"':'';
print '>'.$langs->trans("DateFacture").'</option>';
print '</select>';
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
print '</form>';

llxFooter();

?>