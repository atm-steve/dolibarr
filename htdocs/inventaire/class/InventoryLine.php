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

class InventoryLine extends \Illuminate\Database\Eloquent\Model
{
    protected $table      = 'inventairedet';
    protected $primaryKey = 'rowid';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'tms';

    protected $fillable = [
        'qty_view',
        'qty_stock',
        'barcode',
        'statut',
        'fk_inventory',
        'fk_warehouse',
        'fk_product',
        'pa',
        'pmp',
    ];

    protected $hidden = [
        'pmp',
        'new_pmp',
        'pa',
    ];

    const STATUT_DRAFT  = 0;
    const STATUT_VALIDE = 1;
    const STATUT_MERGED = 4;

    private static $statutLabel = [
        0 => [
            self::STATUT_DRAFT  => 'statutInventaireDaft',
            self::STATUT_VALIDE => 'statutInventaireValide',
            self::STATUT_MERGED => 'statutInventaireTerminate',
        ],
        1 => [
            self::STATUT_DRAFT  => 'statutInventaireDaftShort',
            self::STATUT_VALIDE => 'statutInventaireValide',
            self::STATUT_MERGED => 'statutInventaireTerminate',
        ],
    ];

    public static $statutPicto = [
        self::STATUT_DRAFT  => 'statut9',
        self::STATUT_VALIDE => 'statut4',
        self::STATUT_MERGED => 'tick',
    ];

    /**
     * Related inventory
     *
     * @return Inventaire
     */
    public function inventaire()
    {
        include_once INVENTAIRE_PATH_ROOT . '/class/Inventaire.php';
        return $this->hasOne(Inventaire::class, 'rowid', 'fk_inventory');
    }

    /**
     * Related warehouse
     *
     * @return WareHouse
     */
    public function warehouse()
    {
        include_once INVENTAIRE_PATH_ROOT . '/class/WareHouse.php';
        return $this->hasOne(WareHouse::class, 'rowid', 'fk_warehouse');
    }

    public function product()
    {
        include_once INVENTAIRE_PATH_ROOT . '/class/Product.class.php';
        return $this->hasOne(ProductEloquent::class, 'rowid', 'fk_product');
    }

    /**
     * Get statut label
     *
     * @param int $short 0 = long, 1=short
     * @param int $picto show picto null= no picto 1 = label + picto 2 = only picto
     *
     * @return void
     */
    public function statutLabel($short = 1, $picto = 1)
    {
        return self::getStatutLabel($this->statut, $short, $picto);
    }

    /**
     * Get statut label
     *
     * @param int $statut Zone statut
     * @param int $short  0 = long, 1=short
     * @param int $picto  show picto null= no picto 1 = label + picto 2 = only picto
     *
     * @return string
     */
    public static function getStatutLabel($statut, $short = 1, $picto = 1)
    {
        global $langs;

        if (isset(self::$statutLabel[$short][$statut])) {
            $label = $langs->trans(self::$statutLabel[$short][$statut]);
        }
        if ($picto && isset(self::$statutPicto[$statut])) {
            $img .= img_picto($label, self::$statutPicto[$statut]) . ' ';
        }

        if ($picto == 2) {
            return $img;
        }
        return $img . $label;
    }

}
