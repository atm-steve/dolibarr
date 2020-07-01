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

require INVENTAIRE_PATH_ROOT . '/class/Zone.php';
$dbi = $capsule->getDatabaseManager();
$tms = GETPOST('tms', 'int');
if (!isset($tms)) {
    return sendOutput(['code' => $errorCodes['missingArguments']]);
}
return (new Zone)
    ->select([
        "rowid",
        "fk_user",
        "fk_user_verif",
        "name",
        "comment",
        "fk_warehouse",
        "fk_inventory",
        "statut",
        "tms",
    ])
    ->where('tms', '>', $dbi->raw('FROM_UNIXTIME(' . $tms . ')'))
    ->get()->map(function ($zone) {
    $zone->statut_code = $zone->statut;
    $zone->statut      = html_entity_decode(Zone::getStatutLabel($zone->statut, 1, false));
    return $zone;
})->toArray();
