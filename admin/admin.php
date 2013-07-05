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
require('../class/export.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/export-compta/lib/export-compta.lib.php');

if (! $user->admin) accessforbidden();

$langs->load("admin");
$langs->load("errors");

$exp = new TExportCompta($db);

/**
 * Actions
 */
$action = GETPOST('action','alpha');

if($action == 'setconst') {
	$const = GETPOST('const', 'alpha');
	dolibarr_set_const($db,$const,GETPOST($const,'alpha'),'chaine',0,'',$conf->entity);
}

/**
 * View
 */
$head = exportcompta_admin_prepare_head();
llxHeader("",$langs->trans("ExportComptaSetup"));

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("ExportComptaSetup"),$linkback,'setup');

dol_fiche_head($head, 'config', $langs->trans("ExportCompta"));

$formDoli=new Form($db);

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td align="center" width="60">'.$langs->trans("Value").'</td>';
print '<td width="80">&nbsp;</td>';
print "</tr>\n";
$var=true;

// Logiciel d'export
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_logiciel');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_LOGICIEL_EXPORT');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ExportFormat");
print '</td><td width="60" align="right">';
print $form->combo('', 'EXPORT_COMPTA_LOGICIEL_EXPORT', $exp->TLogiciel, $conf->global->EXPORT_COMPTA_LOGICIEL_EXPORT);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Export de toutes les entités
$var=!$var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_allentities');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_ALL_ENTITIES');
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("ExportAllEntities").'</td>';
print '<td width="60" align="right">';
print $formDoli->selectyesno("EXPORT_COMPTA_ALL_ENTITIES",$conf->global->EXPORT_COMPTA_ALL_ENTITIES,1);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print "</td></tr>\n";
$form->end();

// Champ date utilisé pour les bornes sur factures client
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_datefaccli');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_DATE_FACTURES_CLIENT');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("BaseDateForFacCli");
print '</td><td width="60" align="right">';
print $form->combo('', 'EXPORT_COMPTA_DATE_FACTURES_CLIENT', $exp->TDatesFacCli, $conf->global->EXPORT_COMPTA_DATE_FACTURES_CLIENT);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Champ date utilisé pour les bornes sur factures fournisseur
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_datefacfourn');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_DATE_FACTURES_FOURNISSEUR');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("BaseDateForFacFourn");
print '</td><td width="60" align="right">';
print $form->combo('', 'EXPORT_COMPTA_DATE_FACTURES_FOURNISSEUR', $exp->TDatesFacFourn, $conf->global->EXPORT_COMPTA_DATE_FACTURES_FOURNISSEUR);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Champ date utilisé pour les bornes sur notes de frais
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_datendf');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_DATE_NDF');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("BaseDateForNDF");
print '</td><td width="60" align="right">';
print $form->combo('', 'EXPORT_COMPTA_DATE_NDF', $exp->TDatesNDF, $conf->global->EXPORT_COMPTA_DATE_NDF);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

print "</table>";

llxFooter();

?>