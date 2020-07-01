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
$columns = [
    'rowid',
    'ref',
    'entity',
    'ref_ext',
    'tms',
    // 'fk_parent',
    'label',
    'description',
    'barcode',
    'fk_barcode_type',
];
$dbi = $capsule->getDatabaseManager();

return (new ProductEloquent)
    ->where('fk_product_type', 0)
    ->where('tms', '>', $dbi->raw('FROM_UNIXTIME(' . GETPOST('tms', 'int') . ')'))
    ->paginate(100, $columns, 'page', GETPOST('page'))
    ->toArray();
