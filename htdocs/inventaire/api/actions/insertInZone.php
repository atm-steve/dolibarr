<?php

/**
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

require INVENTAIRE_PATH_ROOT . '/class/ZoneLine.php';
require INVENTAIRE_PATH_ROOT . '/class/Zone.php';
require INVENTAIRE_PATH_ROOT . '/class/Product.class.php';

$barcode        = GETPOST('barecode');
$zone           = GETPOST('zone');
$qty            = GETPOST('qty');
$product        = GETPOST('product');
$warehouse      = GETPOST('warehouse');
$comment = GETPOST('comment');
$inventory      = GETPOST('inventory');
$hasProduct     = isset($product) && !empty($product);
$productColumns = [
    'rowid',
    'ref',
    'entity',
    'ref_ext',
    'tms',
    'label',
    'description',
    'barcode',
    'fk_barcode_type',
];

$line = (new ZoneLine)->where('fk_zone', $zone)->where(function ($query) use ($barcode, $product) {
    $query->orWhere([
        'barcode'    => $barcode,
        'fk_product' => $product,
    ]);
})->first();

if ($hasProduct) {
    $product = (new ProductEloquent)->select($productColumns)->find($product);
    if (empty($product->barcode)) {
        $product->updateBarcode($barcode, $capsule->getDatabaseManager());

    }
} else {
    $product    = (new ProductEloquent)->select($productColumns)->where('barcode', $barcode)->first();
    $hasProduct = !empty($product);
}

if ($line) {
    if (
        $line->zone->statut == Zone::STATUT_ON_VERIF ||
        $line->zone->statut == Zone::STATUT_TO_VERIF
    ) {
        $line->zone->statut = Zone::STATUT_TO_VERIF;
        $line->zone->save();
        $line->qty_verified += $qty;
    } else {
        $line->qty_view += $qty;
    }
    if ($hasProduct) {
        $line->fk_product = $product->rowid;
    }
    $line->comment = $comment;
    $line->save();
} else {
    $newLine = [
        'qty_view'     => $qty,
        'barcode'      => $barcode,
        'fk_zone'      => $zone,
        'fk_inventory' => $inventory,
        'fk_warehouse' => $warehouse,
        'comment' => $comment
    ];
    if ($hasProduct) {
        $newLine['fk_product'] = $product->rowid;

    }
    $line = (new ZoneLine)->create($newLine);
    $line->refresh();
}
$line->product = $product;

return compact('line');
