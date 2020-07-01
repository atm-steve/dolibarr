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

require INVENTAIRE_PATH_ROOT . '/class/Product.class.php';
$barcode = GETPOST('barcode');
$product = GETPOST('id');

try {
    $product = (new ProductEloquent)->findOrFail($product);
    $success = $product->updateBarcode($barcode, $capsule->getDatabaseManager());
    return [
        'success' => $success,
    ];
} catch (Exception $e) {
    return [
        'success' => false,

        'errors'  => 'Produits non trouvé' . $e->getMessage(),
    ];
}
return [
    'errors' => 'invalide',
];
