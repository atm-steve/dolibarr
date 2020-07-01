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
require '../../class/ZoneLine.php';
require DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
$langs->load('inventaire@inventaire');
$product     = new Product($db);
$inventory   = GETPOST('inventory', 'int');
$zone        = GETPOST('zone', 'int');
$statut = GETPOST('statut');
$as          = 'line';
$globalSql   = [];
$filtered    = null;
if ($statut > -1) {
    $filtered = "line.statut = $statut ";
}
$globalSql[] = " $as.fk_inventory = $inventory ";
if (!empty($zone)) {
    $globalSql[] = " $as.fk_zone = $zone";
}

$columns[] = [
    'db' => $as . '.rowid',
    'as' => 'id',
    'dt' => 'id',
];

$columns[] = [
    'db' => 'product.label',
    'as' => 'label',
    'dt' => 'label',
];

$columns[] = [
    'db' => 'product.ref',
    'as' => 'ref',
    'dt' => 'ref',
];
$columns[] = [
    'db' => $as . '.barcode',
    'as' => 'barcode',
    'dt' => 'barcode',
];

$columns[] = [
  'db' => $as.'.comment',
  'as' => 'comment',
  'dt' => 'comment'
];

$columns[] = [
    'db' => 'product.rowid',
    'as' => 'productId',
    'dt' => 'productId',
];

$columns[] = [
    'dt'        => 'product',
    'formatter' => function ($data, $row) use ($product) {
        if (isset($row['productId'])) {
            $product->id    = $row['productId'];
            $product->label = $row['label'];
            $product->type  = Product::TYPE_PRODUCT;
            $product->ref   = $row['ref'];
            return $product->getNomUrl(1, 'stock') . '<br />' . $row['label'];
        }
        $form = new Form($db);
        ob_start();
        $form->select_produits('', 'idAssignProduct', 0, $conf->product->limit_size, 0, -1, 2, '', 2, [], 0, '1', 1, '', 1);
        $select = ob_get_clean();
        return $row['barcode'] . '
  <div class="assignProductContainer" >
    <button class="assignProduct butAction">Assigner un produit</button>

    <div class="assignProductform" style="display: none">
    <form class="formProduct" action="./api/front.php" method="post">
      <input type="hidden" name="action" value="assignProduct" />
      <input type="hidden" name="barcode" value="'.$row['barcode'].'" />
      <input type="hidden" name="id" value="' . $row['id'] . '"/>
      ' . $select . '
      <button>Valider</button>
    </form>
    </div>
  </div>
  ';
    },
];

$columns[] = [
    'db' => $as . '.fk_warehouse',
    'as' => 'fk_warehouse',
    'dt' => 'fk_warehouse',
];

$columns[] = [
    'db' => $as . '.fk_inventory',
    'as' => 'fk_inventory',
    'dt' => 'fk_inventory',
];
$columns[] = [
    'db' => $as . '.fk_zone',
    'as' => 'fk_zone',
    'dt' => 'fk_zone',
];

$columns[] = [
    'db' => $entrepotLabel,
    'as' => 'warehouse',
    'dt' => 'warehouse',
];

$columns[] = [
    'db' => 'zone.name',
    'as' => 'zone',
    'dt' => 'zone',
];

$columns[] = [
    'db' => 'qty_view',
    'dt' => 'qty_view',
];
$columns[] = [
    'db' => 'qty_verified',
    'dt' => 'qty_verified',
];
$columns[] = [
    'db' => 'qty_stock',
    'dt' => 'qty_stock',
];
$columns[] = [
    'db' => $as . '.statut',
    'as' => 'statut',
    'dt' => 'statut',
];
$columns[] = [
    'dt'        => 'statutHtml',
    'formatter' => function ($statut, $row) {
        return ZoneLine::getStatutLabel($row['statut'], 1);
    },
];

$join = "LEFT OUTER JOIN " . MAIN_DB_PREFIX . "entrepot as entrepot on entrepot.rowid = $as.fk_warehouse\n";
$join .= " LEFT OUTER JOIN " . MAIN_DB_PREFIX . "product as product on product.rowid = $as.fk_product\n";
$join .= " LEFT OUTER JOIN " . MAIN_DB_PREFIX . "inventaire_zones as zone on zone.rowid = $as.fk_zone\n";

$conn = array(
    'user' => $dolibarr_main_db_user,
    'pass' => $dolibarr_main_db_pass,
    'db'   => $dolibarr_main_db_name,
    'host' => $dolibarr_main_db_host,
);
$results = Datatables::complex(
    $_GET,
    $conn,
    MAIN_DB_PREFIX . 'inventaire_zonedet',
    $as,
    'rowid',
    $columns,
    $filtered,
    implode(' AND ', $globalSql),
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
