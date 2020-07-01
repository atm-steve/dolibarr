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
require '../class/Zone.php';
require INVENTAIRE_PATH_ROOT . '/class/InventoryUser.php';
require INVENTAIRE_PATH_ROOT. '/class/Inventaire.php';
require INVENTAIRE_PATH_ROOT . '/class/Notify.class.php';
$langs->load('inventaire@inventaire');
$id        = GETPOST('id');
$name      = GETPOST('name');
$comment   = GETPOST('comment');
$warehouse = GETPOST('warehouse');
$user      = GETPOST('user');

$zone = (new Zone)->find($id);
if ($zone->statut == Zone::STATUT_UNASSIGNED && !empty($user) && $user > 0) {
    $zone->statut = Zone::STATUT_ASSIGNED;
    $zone->inventaire->status = Inventaire::STATUT_ON_HOLD;
    $zone->inventaire->save();
}
if ($zone->statut > Zone::STATUT_UNASSIGNED && (empty($user) || $user == 0)) {
    $zone->statut = Zone::STATUT_UNASSIGNED;
}
$zone->name         = $name;
$zone->comment      = $comment;
$zone->fk_warehouse = $warehouse;
$zone->fk_user      = $user;
$zone->save();

$users = (new InventoryUser)->all();

$notify = new FCMNotify();
$res    = $notify->sendTo($users)->withData([
    'action' => 'main.fetchZones',
])->send();
