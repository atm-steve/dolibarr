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

$type_export = GETPOST('type_export', 'alpha');
$logiciel_export = GETPOST('logiciel_export', 'alpha');
$formatvar = 'EXPORT_COMPTA_FORMAT_'.$type_export.'_'.$logiciel_export;

if($action == 'saveformat') {
	$format = GETPOST('format');
	foreach($format as $i => $ligne) {
		if($ligne['name'] == '') unset($format[$i]);
	}
	$format = serialize($format);
	dolibarr_set_const($db,$formatvar,$format,'array',0,'',$conf->entity);
}

$format = unserialize($conf->global->{$formatvar});
if(empty($format)) $format = array();


$head = exportcompta_admin_prepare_head();
llxHeader("",$langs->trans("ExportComptaSetup"));

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("ExportComptaSetup"),$linkback,'setup');

dol_fiche_head($head, 'format', $langs->trans("ExportCompta"));

$form=new Form($db);

print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("LogicielExport").'</td>';
print '<td>';
print '<select name="logiciel_export" class="flat">';
print '<option value=""></option>';
print '<option value="quadratus"';
print $conf->global->EXPORT_COMPTA_LOGICIEL_EXPORT == 'quadratus' ? ' selected="selected"':'';
print '>'.$langs->trans("quadratus").'</option>';
print '<option value="sage"';
print $conf->global->EXPORT_COMPTA_LOGICIEL_EXPORT == 'sage' ? ' selected="selected"':'';
print '>'.$langs->trans("sage").'</option>';
print '</select>';
print '</td>';
print '<td>'.$langs->trans("TypeExport").'</td>';
print '<td>';
print '<select name="type_export" class="flat">';
print '<option value=""></option>';
print '<option value="ecritures_comptables_vente"'.($type_export == 'ecritures_comptables_vente' ? ' selected' : '').'>'.$langs->trans("ecritures_comptables_vente").'</option>';
print '<option value="ecritures_comptables_achat"'.($type_export == 'ecritures_comptables_achat' ? ' selected' : '').'>'.$langs->trans("ecritures_comptables_achat").'</option>';
print '<option value="reglement_tiers"'.($type_export == 'reglement_tiers' ? ' selected' : '').'>'.$langs->trans("reglement_tiers").'</option>';
print '</select>';
print '</td>';
print '<td><input type="submit" class="button" value="'.$langs->trans("Display").'" /></td>';
print "</tr>\n";
print '</table>';
print '</form>';

if(!empty($logiciel_export) && !empty($type_export)) {
	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'" />';
	print '<input type="hidden" name="action" value="saveformat" />';
	print '<input type="hidden" name="type_export" value="'.$type_export.'" />';
	print '<input type="hidden" name="logiciel_export" value="'.$logiciel_export.'" />';
	
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Name").'</td>';
	print '<td>'.$langs->trans("Length").'</td>';
	print '<td>'.$langs->trans("TypeValue").'</td>';
	print '<td>'.$langs->trans("Value").'</td>';
	print '<td>'.$langs->trans("Default").'</td>';
	print '<td>'.$langs->trans("Type").'</td>';
	print '<td>'.$langs->trans("Format").'</td>';
	print '<td>'.$langs->trans("Padding").'</td>';
	print "</tr>\n";
	$var=true;
	$i = 0;
	
	
	foreach($format as $colonne) {
		$var=! $var;
		print '<tr '.$bc[$var].'>';
		print '<td><input type="text" name="format['.$i.'][name]" class="flat" value="'.$colonne['name'].'" /></td>';
		print '<td><input type="text" name="format['.$i.'][length]" class="flat" value="'.$colonne['length'].'" /></td>';
		print '<td>';
		print '<select name="format['.$i.'][type_value]" class="flat">';
		print '<option value="data"'.($colonne['type_value'] == 'data' ? ' selected' : '').'>Data</option>';
		print '<option value="php"'.($colonne['type_value'] == 'php' ? ' selected' : '').'>PHP</option>';
		print '<option value="dur"'.($colonne['type_value'] == 'dur' ? ' selected' : '').'>dur</option>';
		print '</select>';
		print '</td>';
		print '<td><input type="text" name="format['.$i.'][value]" class="flat" value="'.$colonne['value'].'" /></td>';
		print '<td><input type="text" name="format['.$i.'][default]" class="flat" value="'.$colonne['default'].'" /></td>';
		print '<td><input type="text" name="format['.$i.'][type]" class="flat" value="'.$colonne['type'].'" /></td>';
		print '<td><input type="text" name="format['.$i.'][format]" class="flat" value="'.$colonne['format'].'" /></td>';
		print '</td>';
		print '<td>';
		print '<select name="format['.$i.'][pad_type]" class="flat">';
		print '<option value="0"'.($colonne['pad_type'] == '0' ? ' selected' : '').'>Gauche</option>';
		print '<option value="1"'.($colonne['pad_type'] == '1' ? ' selected' : '').'>Droite</option>';
		print '<option value="2"'.($colonne['pad_type'] == '2' ? ' selected' : '').'>Les deux</option>';
		print '</select>';
		print '</td>';
		print '</tr>';
		
		$i++;
	}
	
	$var=! $var;
	print '<tr '.$bc[$var].'>';
	print '<td><input type="text" name="format['.$i.'][name]" class="flat" /></td>';
	print '<td><input type="text" name="format['.$i.'][length]" class="flat" /></td>';
	print '<td><select name="format['.$i.'][type_value]" class="flat">';
	print '<option value="data"'.($colonne['type_value'] == 'data' ? ' selected' : '').'>Data</option>';
	print '<option value="php"'.($colonne['type_value'] == 'php' ? ' selected' : '').'>PHP</option>';
	print '<option value="dur"'.($colonne['type_value'] == 'dur' ? ' selected' : '').'>dur</option>';
	print '</select></td>';
	print '<td><input type="text" name="format['.$i.'][value]" class="flat" /></td>';
	print '<td><input type="text" name="format['.$i.'][default]" class="flat" /></td>';
	print '<td><input type="text" name="format['.$i.'][type]" class="flat" /></td>';
	print '<td><input type="text" name="format['.$i.'][format]" class="flat" /></td>';
	print '<td>';
	print '<select name="format['.$i.'][pad_type]" class="flat">';
	print '<option value="0"'.($colonne['pad_type'] == 'data' ? ' selected' : '').'>Gauche</option>';
	print '<option value="1"'.($colonne['pad_type'] == 'php' ? ' selected' : '').'>Droite</option>';
	print '<option value="2"'.($colonne['pad_type'] == 'dur' ? ' selected' : '').'>Les deux</option>';
	print '</select>';
	print '</td>';
	print '</tr>';
	
	print '</table>';
	
	print '<input type="submit" class="button" value="'.$langs->trans("Save").'" />';
	print '</form>';
}

llxFooter();

?>