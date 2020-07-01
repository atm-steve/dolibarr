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

include './config.php';
include DOL_DOCUMENT_ROOT . '/product/stock/class/entrepot.class.php';
include './class/Inventaire.php';

$action = GETPOST('action', 'alpha');
$name   = GETPOST('name', 'alpha');
$date   = dateFromSelect('date', true);

if ($action == 'create') {
    $validate = $validator->make(compact('name', 'date'), array(
        'name' => 'required|unique:inventaire',
        'date' => 'required',
    ), array('after' => 'la date ne peut être dans le passé'), array('name' => $langs->trans('name')));

    if ($validate->passes()) {
        $date       = dateFromSelect('date');
        $inventaire = (new Inventaire)->create(compact('name', 'date'));
        header('location:' . INVENTAIRE_URL_ROOT);
    }
    $errors = $validate->errors()->toArray();
    $action = 'new';
}
if ($action == 'update') {
    $id       = GETPOST('id');
    $validate = $validator->make(compact('name', 'date'), array(
        'name' => 'required',
        'date' => 'required',
    ), array('after' => 'la date ne peut etre dans le passé'), array('name' => $langs->trans('name')));
    if ($validate->passes()) {
        $inventaire       = (new Inventaire)->find($id);
        $inventaire->date = dateFromSelect('date');
        $inventaire->name = $name;
        $inventaire->save();
        header('location:' . INVENTAIRE_URL_ROOT . '/inventaire.php?id=' . $id);
    }
    $errors = $validate->errors()->toArray();
    $action = 'modify';
}
llxheader();
$url = $_SERVER["PHP_SELF"];
switch ($action) {
    case 'modify':
        $id         = GETPOST('id');
        $inventaire = (new Inventaire)->find($id);
        $date       = $inventaire->date;
        $name       = $inventaire->name;
        $url .= '?id=' . $id;
        $view->display('inventaires/create.html.twig', compact('errors', 'name', 'date', 'action', 'url'));
        break;
    case 'new':
        $view->display('inventaires/create.html.twig', compact('errors', 'name', 'date', 'action', 'url'));
        break;

    default:
        $inventaires = (new Inventaire)->withCount('users')->get();
        print_titre('liste des inventaire');
        $view->display('inventaires/index.html.twig', compact('inventaires'));
        break;
}
llxfooter();
