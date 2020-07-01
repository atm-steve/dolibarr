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

require '../config.php';
require '../class/phpqrcode.class.php';
require '../class/InventoryUser.php';

$action = GETPOST('action');

if ($action == 'qrcode') {
    $iUser  = (new InventoryUser)->find(GETPOST('id'));
    $folder = $conf->inventaire->dir_output . '/' . $iUser->rowid;
    if (!file_exists($folder)) {
        mkdir($folder);
    }
    $data = array(
        'url'       => INVENTAIRE_URL_ROOT . '/',
        'name'      => $iUser->name,
        'id'        => $iUser->rowid,
        'inventory' => $iUser->fk_inventory,
        'uuid'      => $iUser->uuid,
    );
    QRcode::png(base64_encode(json_encode($data)), $folder . '/qrcode.png', 'H', 5, 3);
}

if ($action == 'delete_user') {
    // TODO: gcm clean
    $res = (new InventoryUser)->where('rowid', GETPOST('user', 'int'))->delete();
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    header('Content-Type: application/json;charset=UTF-8');
    print json_encode(['res' => $res]);
}
