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
$langs->load('exportcompta@exportcompta');

$exp = new TExportCompta($db);

$action = GETPOST('action','alpha');

$logiciel_export = GETPOST('logiciel_export', 'alpha');
$type_export = GETPOST('type_export', 'alpha');
$formatvar = 'EXPORT_COMPTA_FORMAT_'.$type_export.'_'.$logiciel_export;

if($action == 'saveformat') {
	$format = GETPOST('format');
	foreach($format as $i => $ligne) {
		if($ligne['name'] == '') unset($format[$i]);
	}
	$format = serialize($format);
	dolibarr_set_const($db,$formatvar,$format,'array',0,'',$conf->entity);
}

if(!empty($conf->global->{$formatvar})) $format = unserialize($conf->global->{$formatvar});
if(empty($logiciel_export)) $logiciel_export = $conf->global->EXPORT_COMPTA_LOGICIEL_EXPORT;
if(empty($format)) {

	$format = array();

	if(!empty($logiciel_export)) {
		dol_include_once('/exportcompta/class/export_'.$logiciel_export.'.class.php');
		
		$className = 'TExportCompta'.ucfirst($logiciel_export);
		$o=new $className($db);
		//var_dump($o);
		if(isset($o->{'_format_'.$type_export})) {
			$format = $o->{'_format_'.$type_export};
			//var_dump($format);
		}
		
	}
		
	
	
}

/**
 * View
 */
$head = exportcompta_admin_prepare_head();
llxHeader("",$langs->trans("ExportComptaSetup"));

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("ExportComptaSetup"),$linkback,'setup');

dol_fiche_head($head, 'format', $langs->trans("ExportCompta"));

$formDoli=new Form($db);

$form = new TFormCore($_SERVER["PHP_SELF"],'export_format_choice');
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("LogicielExport").'</td>';
print '<td>';
print $form->combo('', 'logiciel_export', $exp->TLogiciel, $logiciel_export);
print '</td>';
print '<td>'.$langs->trans("TypeExport").'</td>';
print '<td>';
print $form->combo('', 'type_export', $exp->TTypeExport, $type_export);
print '</td>';
print '<td><input type="submit" class="button" value="'.$langs->trans("Display").'" /></td>';
print "</tr>\n";
print '</table>';
$form->end();

if(!empty($logiciel_export) && !empty($type_export)) {
	$form = new TFormCore($_SERVER["PHP_SELF"],'export_format_data');
	print $form->hidden('action','saveformat');
	print $form->hidden('type_export',$type_export);
	print $form->hidden('logiciel_export',$logiciel_export);
	
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
		print '<td><input type="text" name="format['.$i.'][value]" class="flat" value="'.htmlentities($colonne['value']).'" /></td>';
		print '<td><input type="text" name="format['.$i.'][default]" class="flat" value="'.htmlentities($colonne['default']).'" /></td>';
		print '<td><input type="text" name="format['.$i.'][type]" class="flat" value="'.$colonne['type'].'" /></td>';
		print '<td><input type="text" name="format['.$i.'][format]" class="flat" value="'.htmlentities($colonne['format']).'" /></td>';
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
	$form->end();
}

llxFooter();

?>