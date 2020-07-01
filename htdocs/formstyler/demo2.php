<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$res = 0;
if (!$res && file_exists("../main.inc.php")) {
    $res = @include '../main.inc.php';
}
// to work if your module directory is into dolibarr root htdocs directory
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include '../../main.inc.php';
}
// to work if your module directory is into a subdir of root htdocs directory
if (!$res) {
    die("Include of main fails");
}

// Change this following line to use the correct relative path from htdocs
include_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
include_once DOL_DOCUMENT_ROOT . '/core/class/html.formcontract.class.php';
include_once './class/formstyler.class.php';

include_once DOL_DOCUMENT_ROOT . "/core/lib/company.lib.php";

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("formstyler@formstyler");

llxHeader('', $langs->trans('FormStylerDemo'), '');

print_fiche_titre($langs->trans("FormStylerDemo"));

print '<p>'.$langs->trans('FormstylerDemoExplanationPhpDemo2');
print '<br /><a href="https://git.aternatik.net/dolibarr/formstyler/blob/develop/demo2.php" target="_blank">'.$langs->trans('FormstylerDemoSeePhpFile').'</a>';
print '</p>';
dol_fiche_head();

$form = new FormStyler($db, 'formstyler_demo2');
$params = array('action' => "add", 'backtopage' => $backtopage);
$form->printFormBegin('POST', $_SERVER['PHP_SELF'], $params);

$form->printBeginFieldset('', '', false);
$form->printFormLegend('Standard fields with method printInputField()', false);

// Simple text input
$name = 'inputOne';
$label = 'InputOne';
$value = GETPOST($name, 'alpha');
$form->printInputField($name, $label, $value);

// Simple text input, required
$name = 'inputTwo';
$label = 'InputTwoRequired';
$value = GETPOST($name, 'alpha');
$form->printInputField($name, $label, $value, true);

// Price input
$name = 'inputThree';
$label = 'InputThree (price)';
$value = GETPOST($name, 'alpha');
$type = 'price';
$form->printInputField($name, $label, $value, false, '', $type);

// Textarea
$name = 'inputFour';
$label = 'InputFour (textarea)';
$value = GETPOST($name);
$type = 'textarea';
$form->printInputField($name, $label, $value, false, '', $type);

// Note area
$name = 'inputFive';
$label = 'InputFive (note)';
$value = GETPOST($name);
$type = 'note';
$form->printInputField($name, $label, $value, false, '', $type);

// Date input
$name = 'inputSix';
$label = 'InputSix (date)';
$value = GETPOST($name);
$type = 'date';
$form->printInputField($name, $label, $value, false, '', $type);

// Yes/No select list
$name = 'inputSeven';
$label = 'InputSeven (yesno)';
$value = GETPOST($name);
$type = 'yesno';
$form->printInputField($name, $label, $value, false, '', $type);

// File input
$name = 'inputEight';
$label = 'InputEight (file)';
$value = GETPOST($name);
$type = 'file';
$form->printInputField($name, $label, $value, false, '', $type);

$form->printEndFieldset(false);

$form->printBeginFieldset('', '', false);
$form->printFormLegend('Checkbox / radio field', false);

// Checkbox input
$name = 'inputNine';
$label = 'InputNine (checkbox)';
$value = GETPOST($name);
$type = 'checkbox';
$form->getCheckBox($name, $label, $value, '', '', '', '', false);

// Checkbox list
$array_value = array(0 => 'test 0', 1 => 'test 1');
$name = 'inputTen';
$label = 'InputTen (checkbox list)';
$value = GETPOST($name);
$form->printCheckboxesListWithLabelField($name, $label, $array_value, $value, '', '', false);

// Radio buttons
$name = 'inputEleven';
$label = 'InputEleven (radio buttons)';
$value = GETPOST($name);
$array_value = array(0 => 'test radio 0', 1 => 'test radio 1');
$form->printRadiosFieldWithLabel($name, $label, $value, $array_value, true, '', false);

$form->printEndFieldset(false);

$form->printBeginFieldset('', '', false);
$form->printFormLegend('Select list', false);

$name = 'listOne';
$label = 'List One ';
$arrayListValue = array('key1' => 'Value 1', 'key2' => 'Value 2');
$type_select = '';
$form->printSelectList($name, $label, $arrayListValue, $selected, false, '', $type_select, '', false);

$name = 'listOne';
$label = 'List catégories ';
$arrayListValue = array();
$type_select = 'categories';
print $form->printSelectList($name, $label, $arrayListValue, $selected, false, '', $type_select, '', true);

$form->printEndFieldset(false);

$form->printFormSubmitButton('ok', 'Done !');
$form->printFormEnd();

dol_fiche_end();
