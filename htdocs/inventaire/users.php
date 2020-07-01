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
require './class/phpqrcode.class.php';
require './class/UUID.class.php';

$action    = GETPOST('action');
$inventory = GETPOST('inventaire', 'int');
$name      = GETPOST('name');
$errors    = [];

if ($action == 'add_intern_users') {
    $dolUsers = GETPOST('dolusers');
    foreach ($dolUsers as $id => $name) {
        addUser(trim($name), $inventory, $id);
    }
    $name = '';
}

if ($action == 'add_user') {
    $validate = $validator->make(
        compact('name'),
        array('name' => 'required'),
        [],
        array('name' => $langs->trans('name'))
    );
    if ($validate->passes()) {

        addUser($name, $inventory);

        header('location:' . INVENTAIRE_URL_ROOT . '/users.php?inventaire=' . $inventory);
    }
    $errors = $validate->errors()->toArray();
}

$inventaire = (new Inventaire)->with('users')->find($inventory);
$css        = [
    'inventaire/css/modal.css',
];
llxHeader('', 'utilisateur inventaire', '', '', 0, 0, [], $css);
$head = inventairePrepareHead($inventaire);
dol_fiche_head(
    $head,
    'user',
    $langs->trans("inventaire"),
    -1,
    "inventaire@inventaire"
);

$sql      = "SELECT rowid, CONCAT(firstname, ' ', lastname) as name from " . MAIN_DB_PREFIX . "user where statut=1";
$res      = $db->query($sql);
$dolUsers = [];
if ($res) {
    while ($obj = $db->fetch_object($res)) {
        $dolUsers[] = [
            'id'     => $obj->rowid,
            'htmlId' => 'user' . $obj->rowid,
            'name'   => $obj->name,
        ];
    }
}
$view->display('users/index.html.twig', compact('inventaire', 'errors', 'name', 'dolUsers'));

function addUser($name, $inventory, $fkUser = null)
{
    global $conf;
    $iUser = (new InventoryUser)->create(
        [
            'name'         => $name,
            'fk_inventory' => $inventory,
            'fk_user'      => $fkUser,
            'uuid'         => UUID::v4(),
        ]
    );
    $folder = $conf->inventaire->dir_output . '/' . $iUser->rowid;
    if (!file_exists($folder)) {
        mkdir($folder);
    }
    $data = array(
        'url'       => INVENTAIRE_URL_ROOT . '/',
        'name'      => $name,
        'id'        => $iUser->rowid,
        'inventory' => $inventory,
        'uuid'      => $iUser->uuid,
    );
    QRcode::png(base64_encode(json_encode($data)), $folder . '/qrcode.png', 'H', 7, 5);

}
