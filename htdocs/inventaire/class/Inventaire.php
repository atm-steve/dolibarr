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
 * Class Inventaire
 */
class Inventaire extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'inventaire';

    protected $fillable = array('name', 'date');

    protected $primaryKey = 'rowid';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'tms';

    const STATUT_DRAFT     = 0;
    const STATUT_VALIDE    = 1;
    const STATUT_ON_HOLD   = 2;
    const STATUT_COUNTED   = 3;
    const STATUT_TERMINATE = 4;

    public static $statutLabel = [
        // long label
        0 => [
            self::STATUT_DRAFT     => 'statutInventaireDaft',
            self::STATUT_VALIDE    => 'statutInventaireValide',
            self::STATUT_ON_HOLD   => 'statutInventaireOnHoldLong',
            self::STATUT_COUNTED   => 'statutInventaireCountedLong',
            self::STATUT_TERMINATE => 'statutInventaireTerminateLong',
        ],
        // short label
        1 => [
            self::STATUT_DRAFT     => 'statutInventaireDaftShort',
            self::STATUT_VALIDE    => 'statutInventaireValide',
            self::STATUT_ON_HOLD   => 'statutInventaireOnHold',
            self::STATUT_COUNTED   => 'statutInventaireCounted',
            self::STATUT_TERMINATE => 'statutInventaireTerminate',
        ],

    ];

    public static $statutPicto = [
        self::STATUT_DRAFT     => 'statut9',
        self::STATUT_VALIDE    => 'statut1',
        self::STATUT_ON_HOLD   => 'statut3',
        self::STATUT_COUNTED   => 'statut4',
        self::STATUT_TERMINATE => 'tick',
    ];

    /**
     * Nested zones
     *
     * @return Zone[]
     */
    public function zones()
    {
        include_once INVENTAIRE_PATH_ROOT . '/class/Zone.php';
        return $this->hasMany(Zone::class, 'fk_inventory');
    }

    /**
     * Nested lines
     *
     * @return InventoryLine[]
     */
    public function lines()
    {
        include_once INVENTAIRE_PATH_ROOT . '/class/InventoryLine.php';
        return $this->HasMany(InventoryLine::class, 'fk_inventory');
    }
    /**
     * Nested user
     *
     * @return InventoryUser[]
     */
    public function users()
    {
        include_once INVENTAIRE_PATH_ROOT . '/class/InventoryUser.php';
        return $this->HasMany(InventoryUser::class, 'fk_inventory');
    }

    /**
     * Url attribute
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        $idInventaire = $this->attributes['rowid'];
        $name         = $this->attributes['name'];
        return "<a href=\"" . INVENTAIRE_URL_ROOT . "/inventaire.php?id=$idInventaire\">$name</a>";
    }

    public function getIsValideAttribute()
    {
        return $this->attributes['status'] > self::STATUT_DRAFT;
    }

    /**
     * Status icon
     *
     * @return string
     */
    public function getStatusIconAttribute()
    {
        return $this->statutLabel();
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
        return self::getStatutLabel($this->attributes['status'], $short, $picto);
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
