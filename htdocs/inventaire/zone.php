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
require DOL_DOCUMENT_ROOT . '/product/stock/class/entrepot.class.php';
require './class/Inventaire.php';
require './class/InventoryUser.php';
require './class/Zone.php';

$css = [
    'inventaire/includes/datatables/media/css/jquery.dataTables.css',
    'inventaire/css/datatable.css',
];
$js = [
    'inventaire/includes/datatables/media/js/jquery.dataTables.js',
    'inventaire/js/datatable.editable.js',
];

$id     = GETPOST('id');
$object = (new Zone)->with('user', 'userVerif')->find($id);
llxheader('', 'Zone '.$object->name, '', '', 0, 0, $js, $css);
$users  = (new InventoryUser)->where('fk_inventory', $object->fk_inventory)
    ->get(['rowid', 'name'])
    ->mapWithKeys(
        function ($user) {
            return [
                'id_' . $user->rowid => $user->name,
            ];
        }
    )->prepend('', 'id_0');

$head = inventaireZonePrepareHead($object);
print_fiche_titre('', '<a href="'.INVENTAIRE_URL_ROOT.'/inventaire.php?id='.$object->fk_inventory.'">retour a l inventaire</a>');
dol_fiche_head(
    $head,
    'inventaire',
    $langs->trans("Zone"),
    -1,
    "inventaire@inventaire"
);
$user      = $object->user;
$userVerif = $object->userVerif;
$view->display('zones/index.html.twig', compact('object', 'user', 'userVerif', 'users'));

llxFooter();
$db->close();
