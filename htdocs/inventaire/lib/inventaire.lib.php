<?php
/* Copyright (C) 2017-2018  <dev2a> contact@dev2a.pro
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
 * \file    inventaire/lib/inventaire.lib.php
 * \ingroup inventaire
 * \brief   Library files with common functions for inventaire
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function inventaireAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("inventaire@inventaire");

    $head = array();

    $head[] = [
        INVENTAIRE_URL_ROOT . "/admin/index.php",
        $langs->trans("Settings"),
        'settings',
    ];
    // $head[$h][0] = dol_buildpath("/inventaire/admin/about.php", 1);
    // $head[$h][1] = $langs->trans("About");
    // $head[$h][2] = 'about';
    // $h++;

    complete_head_from_modules($conf, $langs, $object, $head, count($head), 'inventaire');
    return $head;
}

function inventairePrepareHead($object)
{
    global $langs, $conf;

    $langs->load("inventaire@inventaire");
    $head = array();

    $head[] = [
        INVENTAIRE_URL_ROOT . '/inventaire.php?id=' . $object->rowid,
        $langs->trans("card"),
        'inventaire',
    ];
    if ($object->isValide) {
        $head[] = [
            INVENTAIRE_URL_ROOT . '/products.php?id=' . $object->rowid,
            $langs->trans("Products"),
            'products',
        ];
        $head[] = [
            INVENTAIRE_URL_ROOT . '/users.php?inventaire=' . $object->rowid,
            $langs->trans("users"),
            'user',
        ];
    }

    complete_head_from_modules($conf, $langs, $object, $head, count($head), 'inventaire');
    return $head;
}
function inventaireZonePrepareHead($object)
{
    global $langs, $conf;

    $langs->load("inventaire@inventaire");
    $head = array();

    $head[] = [
        INVENTAIRE_URL_ROOT . '/zone.php?id=' . $object->rowid,
        $langs->trans("card"),
        'inventaire',
    ];

    complete_head_from_modules($conf, $langs, $object, $head, count($head), 'inventaireZone');
    return $head;
}

function twigConstant($name)
{
    return constant($name);
}

function twigDisplayErrors($errors, $key)
{
    if (!array_key_exists($key, $errors)) {
        return;
    }
    $html = '';
    foreach ($errors[$key] as $error) {
        $html .= '<br><span class="error" > ' . $error . ' </span>';
    }
    return $html;
}

function dateFromSelect($name, $timestamp = null)
{
    if ($timestamp) {
        return dol_mktime(0, 0, 0, GETPOST("{$name}month"), GETPOST("{$name}day"), GETPOST("{$name}year"));
    }
    return GETPOST("{$name}year") . '-' . GETPOST("{$name}month") . '-' . GETPOST("{$name}day");
}

function trans($key)
{
    global $langs;
    return $langs->trans($key);
}
