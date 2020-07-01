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

/**
 * Product Eloquent class
 */
class ProductEloquent extends \Illuminate\Database\Eloquent\Model
{
    protected $table      = 'product';
    protected $primaryKey = 'rowid';
    public $stock         = 0;
    const CREATED_AT      = 'datec';
    const UPDATED_AT      = 'tms';

    protected $fillable = [
        'pmp',
    ];

    protected $casts = [
        'entity'          => 'int',
        'fk_barcode_type' => 'int',
        'fk_parent'       => 'int',
        'fk_country'      => 'int',
        'fk_product_type' => 'int',
    ];

    public function loadStock($entrepot)
    {
        $stock = Illuminate\Database\Capsule\Manager::table('product_stock')
            ->where('fk_entrepot', $entrepot)
            ->where('fk_product', $this->attributes['rowid'])
            ->first();
        if ($stock) {
            $this->stock = $stock->reel;
        }
    }

    public function updateBarcode($barcode, $dbi)
    {

        $bcValidator = new \violuke\Barcodes\BarcodeValidator($barcode);
        $barcodeType = [
            \violuke\Barcodes\BarcodeValidator::TYPE_EAN_8 => 'EAN8',
            \violuke\Barcodes\BarcodeValidator::TYPE_GTIN  => 'EAN8',
            \violuke\Barcodes\BarcodeValidator::TYPE_EAN   => 'EAN13',
            \violuke\Barcodes\BarcodeValidator::TYPE_UPC   => 'UPC',
        ];
        if ($bcValidator->isValid()) {
            $res = $dbi->table('c_barcode_type')
                ->select('rowid')
                ->where('code', $barcodeType[$bcValidator->getType()])->first();
            $this->barcode         = $barcode;
            $this->fk_barcode_type = $res->rowid;
            return $this->save();
        }
    }
}
