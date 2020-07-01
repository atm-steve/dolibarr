<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018      Jean-François Ferry  <hello+jf@librethic.io>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    marketplace/admin/setup.php
 * \ingroup marketplace
 * \brief   Marketplace setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
    $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--;
    $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
    $res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
    $res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}
if (!$res) {
    die("Include of main fails");
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/marketplace.lib.php';
require_once DOL_DOCUMENT_ROOT . "/product/class/product.class.php";
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';

// Translations
$langs->loadLangs(array("admin", "marketplace@marketplace"));

// Access control
if (!$user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

$arrayofparameters = array(
    //'MARKETPLACE_MAIN_SUPPLIER_CATEGORY' => array('css' => 'minwidth200', 'enabled' => 1),
    //'MARKETPLACE_MYPARAM2'=>array('css'=>'minwidth500','enabled'=>1)
);


// Fields too complex for standard system
if ($action == 'update') {
    $result = dolibarr_set_const($db, 'MARKETPLACE_MAIN_SUPPLIER_CATEGORY', GETPOST('MARKETPLACE_MAIN_SUPPLIER_CATEGORY', 'alpha'), 'chaine', 0, '', $conf->entity);
    if ($result < 0) {
        $ok = false;
    }
    $result = dolibarr_set_const($db, 'MARKETPLACE_MAIN_COLLECTION_SERVICE', GETPOST('MARKETPLACE_MAIN_COLLECTION_SERVICE', 'alpha'), 'chaine', 0, '', $conf->entity);
    if ($result < 0) {
        $ok = false;
    }

    $result = dolibarr_set_const($db, 'MARKETPLACE_USE_DISCOUNT_AS_CARE_RATE', GETPOST('MARKETPLACE_USE_DISCOUNT_AS_CARE_RATE', 'alpha'), 'chaine', 0, '', $conf->entity);
    if ($result < 0) {
        $ok = false;
    }
}

/*
 * Actions
 */
if ((float)DOL_VERSION >= 6) {
    include DOL_DOCUMENT_ROOT . '/core/actions_setmoduleoptions.inc.php';
}


/*
 * View
 */
$formOther = new FormOther($db);
$page_name = "MarketplaceSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_marketplace@marketplace');

// Configuration header
$head = marketplaceAdminPrepareHead();
dol_fiche_head($head, 'settings', '', -1, "marketplace@marketplace");

// Setup page goes here
echo $langs->trans("MarketplaceSetupPage") . '<br><br>';


if ($action == 'edit') {
    print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
    print '<input type="hidden" name="action" value="update">';

    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td class="titlefield">' . $langs->trans("Parameter") . '</td><td>' . $langs->trans("Value") . '</td></tr>';

    print '<tr class="oddeven"><td>';
    print $form->textwithpicto($langs->trans('MARKETPLACE_MAIN_SUPPLIER_CATEGORY'), $langs->trans('MARKETPLACE_MAIN_SUPPLIER_CATEGORYTooltip'));
    print '</td><td>';
    print $formOther->select_categories('supplier', $conf->global->MARKETPLACE_MAIN_SUPPLIER_CATEGORY, 'MARKETPLACE_MAIN_SUPPLIER_CATEGORY');
    print '</tr>';

    print '<tr class="oddeven"><td>';
    print $form->textwithpicto($langs->trans('MARKETPLACE_MAIN_COLLECTION_SERVICE'), $langs->trans('MARKETPLACE_MAIN_COLLECTION_SERVICETooltip'));
    print '</td><td>';
    print $form->select_produits($conf->global->MARKETPLACE_MAIN_COLLECTION_SERVICE, 'MARKETPLACE_MAIN_COLLECTION_SERVICE', 1);
    print '</tr>';

    print '<tr class="oddeven"><td>';
    print $form->textwithpicto($langs->trans('MARKETPLACE_USE_DISCOUNT_AS_CARE_RATE'), $langs->trans('MARKETPLACE_USE_DISCOUNT_AS_CARE_RATETooltip'));
    print '</td><td>';
    print $form->selectyesno('MARKETPLACE_USE_DISCOUNT_AS_CARE_RATE', $conf->global->MARKETPLACE_USE_DISCOUNT_AS_CARE_RATE, 1);
    print '</tr>';

    foreach ($arrayofparameters as $key => $val) {
        print '<tr class="oddeven"><td>';
        print $form->textwithpicto($langs->trans($key), $langs->trans($key . 'Tooltip'));
        print '</td><td><input name="' . $key . '"  class="flat ' . (empty($val['css']) ? 'minwidth200' : $val['css']) . '" value="' . $conf->global->$key . '"></td></tr>';
    }
    print '</table>';

    print '<br><div class="center">';
    print '<input class="button" type="submit" value="' . $langs->trans("Save") . '">';
    print '</div>';

    print '</form>';
    print '<br>';
} else {
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td class="titlefield">' . $langs->trans("Parameter") . '</td><td>' . $langs->trans("Value") . '</td></tr>';

    print '<tr class="oddeven"><td>';
    print $form->textwithpicto($langs->trans('MARKETPLACE_MAIN_SUPPLIER_CATEGORY'), $langs->trans('MARKETPLACE_MAIN_SUPPLIER_CATEGORYTooltip'));
    print '</td><td>';
    $catStatic = new Categorie($db);
    if ($catStatic->fetch($conf->global->MARKETPLACE_MAIN_SUPPLIER_CATEGORY) > 0) {
        print $catStatic->getNomUrl(2) . ' ' . $catStatic->label;
    } else {
        print $langs->trans('None');
    }
    
    print '</td></tr>';

    print '<tr class="oddeven"><td>';
    print $form->textwithpicto($langs->trans('MARKETPLACE_MAIN_COLLECTION_SERVICE'), $langs->trans('MARKETPLACE_MAIN_COLLECTION_SERVICETooltip'));
    print '</td><td>';
    $serviceStatic = new Product($db);
    if ($serviceStatic->fetch($conf->global->MARKETPLACE_MAIN_COLLECTION_SERVICE) > 0) {
        print $serviceStatic->getNomUrl(2) . ' ' . $serviceStatic->label;
    } else {
        print $langs->trans('None');
    }
    print '</td></tr>';

    print '<tr class="oddeven"><td>';
    print $form->textwithpicto($langs->trans('MARKETPLACE_USE_DISCOUNT_AS_CARE_RATE'), $langs->trans('MARKETPLACE_USE_DISCOUNT_AS_CARE_RATETooltip'));
    print '</td><td>';
    $serviceStatic = new Product($db);
    if ($serviceStatic->fetch($conf->global->MARKETPLACE_USE_DISCOUNT_AS_CARE_RATE) > 0) {
        print $langs->trans('Yes');
    } else {
        print $langs->trans('No');
    }
    print '</td></tr>';


    if (!empty($arrayofparameters)) {
        foreach ($arrayofparameters as $key => $val) {
            print '<tr class="oddeven"><td>';
            print $form->textwithpicto($langs->trans($key), $langs->trans($key . 'Tooltip'));
            print '</td><td>' . $conf->global->$key . '</td></tr>';
        }
    }
    print '</table>';
    print '<div class="tabsAction">';
    print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?action=edit">' . $langs->trans("Modify") . '</a>';
    print '</div>';
}


// Page end
dol_fiche_end();

llxFooter();
$db->close();
