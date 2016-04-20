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

// Autoriser la modification (repassage en brouillon) des factures lorsqu'elle ont été comptabilisés
$var=!$var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_reopeninvoice');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_ALLOW_UNVALIDATE');
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("AllowModifyInvoiceWhenTransfered").'</td>';
print '<td width="60" align="right">';
print $formDoli->selectyesno("EXPORT_COMPTA_ALLOW_UNVALIDATE",$conf->global->EXPORT_COMPTA_ALLOW_UNVALIDATE,1);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print "</td></tr>\n";
$form->end();

// Empêcher la réouverture des factures lorsqu'elle ont été comptabilisé
$var=!$var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_reopeninvoice');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_HIDE_REOPEN_INVOICE');
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("ReopenInvoice").'</td>';
print '<td width="60" align="right">';
print $formDoli->selectyesno("EXPORT_COMPTA_HIDE_REOPEN_INVOICE",$conf->global->EXPORT_COMPTA_HIDE_REOPEN_INVOICE,1);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print "</td></tr>\n";
$form->end();

// Empêcher la regénération des factures lorsqu'elle ont été comptabilisé
$var=!$var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_reopeninvoice');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_HIDE_GENERATE_FACTURE');
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("RegenerateInvoice").'</td>';
print '<td width="60" align="right">';
print $formDoli->selectyesno("EXPORT_COMPTA_HIDE_GENERATE_FACTURE",$conf->global->EXPORT_COMPTA_HIDE_GENERATE_FACTURE,1);
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

// Champ date utilisé pour les bornes sur écritures bancaires
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_datefacfourn');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_DATE_BANK');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("BaseDateForBank");
print '</td><td width="60" align="right">';
print $form->combo('', 'EXPORT_COMPTA_DATE_BANK', $exp->TDatesBank, $conf->global->EXPORT_COMPTA_DATE_BANK);
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


// Champ date utilisé pour les bornes sur sur les tiers
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_datetiers');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_DATE_TIERS');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("BaseDateForThirdParties");
print '</td><td width="60" align="right">';
print $form->combo('', 'EXPORT_COMPTA_DATE_TIERS', $exp->TDateTiers, $conf->global->EXPORT_COMPTA_DATE_TIERS);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Logiciel d'export
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_justmm');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_TIERS_JUSTMM');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ExportJustMM");
print '</td><td width="60" align="right">';
print $form->combo('', 'EXPORT_COMPTA_TIERS_JUSTMM', array(0=>$langs->trans('No'), 1=>$langs->trans('Yes')), $conf->global->EXPORT_COMPTA_TIERS_JUSTMM);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Export uniquement de la banque si rapprochée
$var=!$var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_allentities');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_BANK_ONLY_RECONCILED');
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("ExportOnlyReconciled").'</td>';
print '<td width="60" align="right">';
print $formDoli->selectyesno("EXPORT_COMPTA_BANK_ONLY_RECONCILED",$conf->global->EXPORT_COMPTA_BANK_ONLY_RECONCILED,1);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print "</td></tr>\n";
$form->end();

// Champ date utilisé pour les bornes sur notes de frais
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_factclifilter');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_FACT_CLI_FILTER');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("FactureClientFilter");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_FACT_CLI_FILTER',$conf->global->EXPORT_COMPTA_FACT_CLI_FILTER,10);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Champ supplémentaire contenant le code comptable produit pour les ventes CEE
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_product_cee_field');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_PRODUCT_CEE_FIELD');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ProductCEEField");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_PRODUCT_CEE_FIELD',$conf->global->EXPORT_COMPTA_PRODUCT_CEE_FIELD,30,255);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Champ supplémentaire contenant le code comptable produit pour les ventes export
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_product_export_field');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_PRODUCT_EXPORT_FIELD');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ProductExportField");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_PRODUCT_EXPORT_FIELD',$conf->global->EXPORT_COMPTA_PRODUCT_EXPORT_FIELD,30,255);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Champ supplémentaire contenant le code comptable produit pour les ventes France Dom / Tom
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_product_dom_field');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_PRODUCT_FR_DOM_FIELD');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ProductFRDomTomField");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_PRODUCT_FR_DOM_FIELD',$conf->global->EXPORT_COMPTA_PRODUCT_FR_DOM_FIELD,30,255);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Champ supplémentaire contenant le code comptable produit pour les ventes France suspension TVA
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_product_susp_field');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_PRODUCT_FR_SUSP_FIELD');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ProductFRSuspField");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_PRODUCT_FR_SUSP_FIELD',$conf->global->EXPORT_COMPTA_PRODUCT_FR_SUSP_FIELD,30,255);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Champ supplémentaire contenant le code comptable produit pour les achats CEE
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_product_cee_field_buying');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_PRODUCT_CEE_FIELD_BUYING');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ProductCEEFieldBuying");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_PRODUCT_CEE_FIELD_BUYING',$conf->global->EXPORT_COMPTA_PRODUCT_CEE_FIELD_BUYING,30,255);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Champ supplémentaire contenant le code comptable produit pour les achats export
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_product_export_field_buying');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_PRODUCT_EXPORT_FIELD_BUYING');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ProductExportFieldBuying");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_PRODUCT_EXPORT_FIELD_BUYING',$conf->global->EXPORT_COMPTA_PRODUCT_EXPORT_FIELD_BUYING,30,255);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Champ supplémentaire contenant le code comptable produit pour les achats France Dom / Tom
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_product_dom_field_buying');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_PRODUCT_FR_DOM_FIELD_BUYING');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ProductFRDomTomFieldBuying");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_PRODUCT_FR_DOM_FIELD_BUYING',$conf->global->EXPORT_COMPTA_PRODUCT_FR_DOM_FIELD_BUYING,30,255);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Champ supplémentaire contenant le code comptable produit pour les achats France suspension TVA
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_product_susp_field_buying');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_PRODUCT_FR_SUSP_FIELD_BUYING');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ProductFRSuspFieldBuying");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_PRODUCT_FR_SUSP_FIELD_BUYING',$conf->global->EXPORT_COMPTA_PRODUCT_FR_SUSP_FIELD_BUYING,30,255);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Extension des fichiers générés
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_product_export_field');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_EXTENSION');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("FileExtension");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_EXTENSION',$conf->global->EXPORT_COMPTA_EXTENSION,5,255);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Séparateur de données
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_product_export_field');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_DATASEPARATOR');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("DataSeparator");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_DATASEPARATOR',$conf->global->EXPORT_COMPTA_DATASEPARATOR,5,5);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Code compta pour les transfert banque à banque
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_product_export_field');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_BANK_TRANSFER_ACCOUNT');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("AccountForBankTransfer");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_BANK_TRANSFER_ACCOUNT',$conf->global->EXPORT_COMPTA_BANK_TRANSFER_ACCOUNT,30,255);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Code compta pour les remises
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_product_export_field');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_REMISE');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("AccountForDiscountExcept");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_REMISE',$conf->global->EXPORT_COMPTA_REMISE,30,255);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();

// Comptes bancaires contenant les écritures à exclure de l'export du journal de banque
$var=! $var;
$form = new TFormCore($_SERVER["PHP_SELF"],'const_bank_excluded');
print $form->hidden('action','setconst');
print $form->hidden('const','EXPORT_COMPTA_EXCLUDED_BANK_ACOUNT');
print '<tr '.$bc[$var].'><td>';
print $langs->trans("ExcludedBank");
print '</td><td width="60" align="right">';
print $form->texte('', 'EXPORT_COMPTA_EXCLUDED_BANK_ACOUNT',$conf->global->EXPORT_COMPTA_EXCLUDED_BANK_ACOUNT,30,255);
print '</td><td align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'" />';
print "</td></tr>\n";
$form->end();


print "</table>";

llxFooter();

?>