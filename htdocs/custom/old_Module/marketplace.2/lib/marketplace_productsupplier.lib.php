<?php
/** 
 * Copyright (C) 2018      Jean-François Ferry  <hello+jf@librethic.io>
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
 * \file    lib/marketplace_productseller.lib.php
 * \ingroup marketplace
 * \brief   Library files with common functions for ProductSeller
 */

/**
 * Prepare array of tabs for ProductSeller
 *
 * @param ProductSeller $object ProductSeller
 * 
 * @return array Array of tabs
 */
function productsellerPrepareHead($object)
{
    global $db, $langs, $conf;

    $langs->load("marketplace@marketplace");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/marketplace/productseller_card.php", 1) . '?id=' . $object->id;
    $head[$h][1] = $langs->trans("Card");
    $head[$h][2] = 'card';
    $h++;

    if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
        $nbNote = 0;
        if (!empty($object->note_private)) $nbNote++;
        if (!empty($object->note_public)) $nbNote++;
        $head[$h][0] = dol_buildpath('/marketplace/productseller_note.php', 1) . '?id=' . $object->id;
        $head[$h][1] = $langs->trans('Notes');
        if ($nbNote > 0) $head[$h][1] .= ' <span class="badge">' . $nbNote . '</span>';
        $head[$h][2] = 'note';
        $h++;
    }

    complete_head_from_modules($conf, $langs, $object, $head, $h, 'productseller@marketplace');

    return $head;
}
