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

include_once INVENTAIRE_PATH_ROOT . '/class/Zone.php';
include_once INVENTAIRE_PATH_ROOT . '/class/ZoneLine.php';
$zone = (new Zone)->find(GETPOST('zone'));
if ($zone->statut == Zone::STATUT_ON_VERIF || $zone->statut == Zone::STATUT_TO_VERIF) {
    $zone->statut = Zone::STATUT_VERIFIED;
    $zone->products()->where('statut', ZoneLine::STATUT_TO_VERIFIE)->update([
        'statut' => ZoneLine::STATUT_VERIFIED,
    ]);

} else {
    $zone->statut = Zone::STATUT_COUNTED;
    $zone->products()->update([
        'statut' => ZoneLine::STATUT_VALIDE,
    ]);
}
$zone->save();
$zone->statut_code = $zone->statut;
$zone->statut      = html_entity_decode(Zone::getStatutLabel($zone->statut, 1, false));
return compact('zone');
