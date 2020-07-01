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

class WareHouse extends \Illuminate\Database\Eloquent\Model
{
    protected $table      = 'entrepot';
    protected $primaryKey = 'rowid';

    public function getLabelAttribute()
    {
        if (version_compare(DOL_VERSION, '7.0.0', '>=')) {
            return $this->attributes['ref'];
        }
        return $this->attributes['label'];
    }
}
