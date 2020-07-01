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
 * Zone class
 *
 * classe de gestion des zone de l'inventaire
 */
class Zone extends \Illuminate\Database\Eloquent\Model
{
    protected $table      = 'inventaire_zones';
    protected $primaryKey = 'rowid';
    const CREATED_AT      = 'date_created';
    const UPDATED_AT      = 'tms';
    protected $fillable   = array('name', 'comment', 'fk_warehouse', 'fk_inventory');

    protected $casts = [
        'fk_user'       => 'int',
        'fk_user_verif' => 'int',
        'fk_warehouse'  => 'int',
        'fk_inventory'  => 'int',
        'statut_code'   => 'int',
        'active'        => 'boolean',
    ];

    const STATUT_UNASSIGNED = 0;
    const STATUT_ASSIGNED   = 1;
    const STATUT_ON_HOLD    = 2;
    const STATUT_COUNTED    = 3;
    const STATUT_TO_VERIF   = 40;
    const STATUT_ON_VERIF   = 4;
    const STATUT_VERIFIED   = 5;
    const STATUT_MERGED     = 6;

    public static $statutLabel = [
        // long label
        0 => [
            self::STATUT_UNASSIGNED => 'statutInventaireUnassignedLong',
            self::STATUT_ASSIGNED   => 'statutInventaireAssignedLong',
            self::STATUT_ON_HOLD    => 'statutInventaireOnHoldLong',
            self::STATUT_COUNTED    => 'statutInventaireCountedLong',
            self::STATUT_TO_VERIF   => 'statutInventaireToVerifLong',
            self::STATUT_ON_VERIF   => 'statutInventaireOnVerifLong',
            self::STATUT_VERIFIED   => 'statutInventaireVerifiedLong',
            self::STATUT_MERGED     => 'statutInventaireMerged',
        ],
        // short label
        1 => [
            self::STATUT_UNASSIGNED => 'statutInventaireUnassigned',
            self::STATUT_ASSIGNED   => 'statutInventaireAssigned',
            self::STATUT_ON_HOLD    => 'statutInventaireOnHold',
            self::STATUT_COUNTED    => 'statutInventaireCounted',
            self::STATUT_TO_VERIF   => 'statutInventaireToVerif',
            self::STATUT_ON_VERIF   => 'statutInventaireOnVerif',
            self::STATUT_VERIFIED   => 'statutInventaireVerified',
            self::STATUT_MERGED     => 'statutInventaireMerged',
        ],

    ];

    public static $statutPicto = [
        self::STATUT_UNASSIGNED => 'statut9',
        self::STATUT_ASSIGNED   => 'statut1',
        self::STATUT_ON_HOLD    => 'statut3',
        self::STATUT_COUNTED    => 'statut4',
        self::STATUT_TO_VERIF   => 'statut3',
        self::STATUT_ON_VERIF   => 'statut3',
        self::STATUT_VERIFIED   => 'statut4',
        self::STATUT_MERGED     => 'tick',
    ];

    /**
     * Nested user
     *
     * @return InventoryUser
     */
    public function user()
    {
        include_once INVENTAIRE_PATH_ROOT . '/class/InventoryUser.php';
        return $this->hasOne(InventoryUser::class, 'rowid', 'fk_user');
    }
    /**
     * User verif
     *
     * @return InventoryUser
     */
    public function userVerif()
    {
        include_once INVENTAIRE_PATH_ROOT . '/class/InventoryUser.php';
        return $this->hasOne(InventoryUser::class, 'rowid', 'fk_user_verif');
    }
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

    /**
     * Nested lines
     *
     * @return ZoneLine[]
     */
    public function products()
    {
        include_once INVENTAIRE_PATH_ROOT . '/class/ZoneLine.php';
        return $this->HasMany(ZoneLine::class, 'fk_zone');
    }
    /**
     * Url
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        $rowid = $this->attributes['rowid'];
        $name  = $this->attributes['name'];
        return "<a href=\"" . INVENTAIRE_URL_ROOT . "/zone.php?id=$rowid\">$name</a>";
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
        return self::getStatutLabel($this->attributes['statut'], $short, $picto);
    }

    /**
     * Get statut label
     *
     * @param int $statut Zone statut
     * @param int $short  0 = long, 1=short
     * @param int $picto  show picto false = no picto 1 = label + picto 2 = only picto
     *
     * @return string
     */
    public static function getStatutLabel($statut, $short = 1, $picto = 1)
    {
        global $langs;
        $img = '';
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
