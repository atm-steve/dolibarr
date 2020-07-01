<?php
/** 
 * Copyright (C) 2010-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018      Jean-François Ferry  <hello+jf@librethic.io>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 * \file  htdocs/osm/lib/osm.lib.php
 * \brief Library of admin functions for osm module
 */


/**
 *  \brief  Define head array for tabs of osm tools setup pages
 *  \return Array of head
 * 
 * @return array Array for tabs
 */
function marketplaceAdminPrepareHead()
{
    global $langs, $conf, $user;
    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/marketplace/admin/setup.php", 1);
    $head[$h][1] = $langs->trans("Setup");
    $head[$h][2] = 'settings';
    $h++;

    $head[$h][0] = dol_buildpath("/marketplace/admin/about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;
    return $head;
}
