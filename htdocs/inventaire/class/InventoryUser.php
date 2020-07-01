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

class InventoryUser extends \Illuminate\Database\Eloquent\Model
{
    protected $table      = 'inventaire_user';
    protected $primaryKey = 'rowid';
    const CREATED_AT      = 'date_created';
    const UPDATED_AT      = 'tms';
    protected $hidden     = [
        'fk_user',
        'tms',
        'regid',
        'date_created',
        'rowid',
    ];

    protected $appends = [
        'id',
        'hasRegid'
    ];

    protected $fillable = array('name', 'fk_inventory', 'uuid', 'fk_user', 'regid');

    public function getIdAttribute()
    {
        return $this->attributes['rowid'];

    }

    public function getHasRegidAttribute()
    {
        return isset($this->attributes['regid']);
    }
}
