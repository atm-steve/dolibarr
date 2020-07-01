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
$value = GETPOST('value');
$id    = GETPOST('id');
$type  = GETPOST('type');
switch ($type) {
    case 'user':
        include '../class/InventoryUser.php';
        $user       = (new InventoryUser)->find($id);
        $user->name = $value;
        $user->save();
        echo $value;
        break;
    case 'zoneUserVerif':
        include_once '../class/Zone.php';
        include '../class/InventoryUser.php';
        include INVENTAIRE_PATH_ROOT . '/class/Notify.class.php';
        $zone = (new Zone)->find($id);
        if ($zone->statut >= Zone::STATUT_COUNTED) {
            $zone->fk_user_verif = $value;
            $zone->statut        = Zone::STATUT_ON_VERIF;
            $zone->save();
            $users = (new InventoryUser)->all();

            $notify = new FCMNotify();
            $res    = $notify->sendTo($users)->withData([
                'action' => 'main.fetchZones',
            ])->send();
            echo json_encode(['statut' => $zone->statutLabel(0)]);
            return;
        }
        echo json_encode(['error' => $langs->trans('zoneNotCounted')]);
        break;
    case 'zoneUser':
        include_once '../class/Zone.php';
        include '../class/InventoryUser.php';
        include '../class/Inventaire.php';
        include INVENTAIRE_PATH_ROOT . '/class/Notify.class.php';
        $zone = (new Zone)->find($id);
        if ($zone->statut == Zone::STATUT_UNASSIGNED && !empty($value) && $value > 0) {
            $zone->statut = Zone::STATUT_ASSIGNED;
            $zone->inventaire->status = Inventaire::STATUT_ON_HOLD;
            $zone->inventaire->save();
        }
        if (
            $zone->statut > Zone::STATUT_UNASSIGNED &&
            $zone->statut < Zone::STATUT_COUNTED &&
            (empty($value) || intval($value) == 0)
        ) {
            $zone->statut = Zone::STATUT_UNASSIGNED;
        }
        $zone->fk_user = $value;
        $zone->save();
        $users = (new InventoryUser)->all();

        $notify = new FCMNotify();
        $res    = $notify->sendTo($users)->withData([
            'action' => 'main.fetchZones',
        ])->send();
        echo json_encode(['statut' => $zone->statutLabel(0), 'res' => $res, 'data' => $_POST]);
        break;

    default:
        return GETPOST('value');
        break;
}
