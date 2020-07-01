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
if (!defined('NOTOKENRENEWAL')) {
    define('NOTOKENRENEWAL', '1');
}

if (!defined('NOREQUIREMENU')) {
    define('NOREQUIREMENU', '1');
}

if (!defined('NOREQUIREHTML')) {
    define('NOREQUIREHTML', '1');
}

if (!defined('NOREQUIREAJAX')) {
    define('NOREQUIREAJAX', '1');
}

if (!defined('NOREQUIRESOC')) {
    define('NOREQUIRESOC', '1');
}

if (!defined('NOCSRFCHECK')) {
    define('NOCSRFCHECK', '1');
}

if (!defined('INVENTAIRE_API')) {
    define('INVENTAIRE_API', '1');
}
require '../../config.php';
require '../../class/datatable.class.php';
require '../../class/Zone.php';
$langs->load('inventaire@inventaire');
$inventory = GETPOST('inventory');
$statut = GETPOST('statut');
$columns   = [];
$filtered    = null;
if ($statut > -1) {
    $filtered = "zones.statut = $statut ";
}
$columns[] = [
    'db' => 'zones.rowid',
    'as' => 'id',
    'dt' => 'id',
];
$columns[] = [
    'db' => 'zones.fk_user',
    'as' => 'fk_user',
    'dt' => 'fk_user',
];
$columns[] = [
    'db' => 'zones.fk_warehouse',
    'as' => 'fk_warehouse',
    'dt' => 'fk_warehouse',
];

$columns[] = [
    'db' => $entrepotLabel,
    'as' => 'warehouse',
    'dt' => 'warehouse',
];

$columns[] = [
    'db' => 'zones.name',
    'as' => 'name',
    'dt' => 'name',
];
$columns[] = [
    'db' => 'comment',
    'dt' => 'comment',
];

$columns[] = [
    'db' => 'iuser.name',
    'as' => 'user',
    'dt' => 'user',
];

$columns[] = [
    'db'        => 'zones.statut',
    'as'        => 'statut',
    'dt'        => 'statut',
    'formatter' => function ($statut) {
        return Zone::getStatutLabel($statut, 1);
    },
];
$join = "LEFT OUTER JOIN " . MAIN_DB_PREFIX . "entrepot as entrepot on entrepot.rowid = zones.fk_warehouse\n";
$join .= ' LEFT OUTER JOIN ' . MAIN_DB_PREFIX . 'inventaire_user as iuser on iuser.rowid = zones.fk_user';

$conn = array(
    'user' => $dolibarr_main_db_user,
    'pass' => $dolibarr_main_db_pass,
    'db'   => $dolibarr_main_db_name,
    'host' => $dolibarr_main_db_host,
);

$results = Datatables::complex(
    $_GET,
    $conn,
    MAIN_DB_PREFIX . 'inventaire_zones',
    'zones',
    'rowid',
    $columns,
    $filtered,
    'zones.fk_inventory=' . $inventory,
    $join
);
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header('Content-Type: application/json;charset=UTF-8');
$json = json_encode($results);
if ($json) {
    print $json;
} else {
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = ' - Aucune erreur';
            break;
        case JSON_ERROR_DEPTH:
            $error = ' - Profondeur maximale atteinte';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = ' - Inadéquation des modes ou underflow';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = ' - Erreur lors du contrôle des caractères';
            break;
        case JSON_ERROR_SYNTAX:
            $error = ' - Erreur de syntaxe ; JSON malformé';
            break;
        case JSON_ERROR_UTF8:
            $error = " - Caractères UTF-8 malformés, probablement une erreur d'encodage";
            break;
        default:
            $error = ' - Erreur inconnue';
            break;
    }
    echo json_encode(array("error" => $error));
}
