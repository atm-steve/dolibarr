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

$res=@include("../../main.inc.php");					// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

$langs->load("admin");
$langs->load("errors");
$langs->load('export-compta@export-compta');

if (! $user->admin) accessforbidden();

$action = GETPOST('action','alpha');

if($action == 'setexporttype') {
	$exporttype = GETPOST('exporttype', 'alpha');
	dolibarr_set_const($db,'EXPORT_COMPTA_FORMAT_EXPORT',$exporttype,'chaine',0,'',$conf->entity);
}
 

llxHeader("",$langs->trans("ExportComptaSetup"));

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("ExportComptaSetup"),$linkback,'setup');

print "<br>";
print_titre($langs->trans("Options"));

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td align="center" width="60">'.$langs->trans("Value").'</td>';
print '<td width="80">&nbsp;</td>';
print "</tr>\n";
$var=true;

// Force date validation
$var=! $var;
print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'" />';
print '<input type="hidden" name="action" value="setexporttype" />';
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ExportFormat");
print '</td><td width="60" align="center">';
print '<select name="exporttype" class="flat">';
print '<option value=""></option>';
print '<option value="quadratus"';
print $conf->global->EXPORT_COMPTA_FORMAT_EXPORT == 'quadratus' ? ' selected="selected"':'';
print '>'.$langs->trans("quadratus").'</option>';
print '<option value="sage"';
print $conf->global->EXPORT_COMPTA_FORMAT_EXPORT == 'sage' ? ' selected="selected"':'';
print '>'.$langs->trans("sage").'</option>';
print '</select>';
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
print '</form>';

llxFooter();

?>