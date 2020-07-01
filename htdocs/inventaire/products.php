<?php

/*
 * Copyright (C) 2017-2018  <dev2a> contact@dev2a.pro
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

require './config.php';
include './class/Inventaire.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';

$id = GETPOST('id');

$formproduct = new FormProduct($db);
$inventaire  = (new Inventaire)->withCount('lines')->find($id);

$css = [
    'inventaire/includes/datatables/media/css/jquery.dataTables.css',
    'inventaire/css/datatable.css',
];
$js = [
    'inventaire/includes/datatables/media/js/jquery.dataTables.js',
    'inventaire/js/datatable.editable.js',
];

llxheader('', 'Products', '', '', 0, 0, $js, $css);
$head = inventairePrepareHead($inventaire);
dol_fiche_head(
    $head,
    'products',
    $langs->trans("inventaire"),
    -1,
    "inventaire@inventaire"
);
$view->display('products/index.html.twig', compact('inventaire', 'formproduct'));
