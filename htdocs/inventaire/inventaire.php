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

require './config.php';
require DOL_DOCUMENT_ROOT . '/product/stock/class/entrepot.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';
require './class/Inventaire.php';
require './class/InventoryUser.php';
require './class/Zone.php';

$action        = GETPOST('action');
$zoneStart     = GETPOST('zoneStart');
$zoneEnd       = GETPOST('zoneEnd');
$comment       = GETPOST('comment');
$warehouse     = GETPOST('warehouse');
$warehouseZone = GETPOST('warehouseZone');
$inventory     = GETPOST('id', 'int');
$name          = GETPOST('name');

$formproduct = new FormProduct($db);

if ($action == 'add_zone') {
    $validate = $validator->make(
        compact('name', 'warehouseZone'),
        [
            'name'          => 'required',
            'warehouseZone' => 'required|integer|min:1',
        ],
        [
            'warehouseZone.min' => $langs->trans('validation.required'),
        ],
        [
            'name'          => $langs->trans('name'),
            'warehouseZone' => $langs->trans('warehouse'),
        ]
    );
    if ($validate->passes()) {
        (new Zone)->create(
            [
                'name'         => $name,
                'fk_warehouse' => $warehouseZone,
                'fk_inventory' => $inventory,
                'comment'      => $comment,
            ]
        );
        header('location:' . INVENTAIRE_URL_ROOT . '/inventaire.php?id=' . $inventory);
    }
    $errors = $validate->errors()->toArray();
}

if ($action == 'valide') {
    $inventaire = (new Inventaire)->find($inventory);
    $inventaire->status = Inventaire::STATUT_VALIDE;
    $inventaire->save();
    header('location:' . INVENTAIRE_URL_ROOT . '/inventaire.php?id=' . $inventory);
}

if ($action == 'add_number_zones') {
    $validate = $validator->make(
        compact('zoneStart', 'zoneEnd', 'warehouse'),
        [
            'zoneStart' => 'required|integer|min:1',
            'warehouse' => 'required|integer|min:1',
            'zoneEnd'   => 'required|integer|min:' . $zoneStart,
        ],
        [
            'warehouseZone.min' => $langs->trans('validation.required'),
        ],
        [
            'zoneStart' => $langs->trans('zoneStart'),
            'zoneEnd'   => $langs->trans('zoneEnd'),
            'warehouse' => $langs->trans('warehouse'),
        ]
    );

    if ($validate->passes()) {
        print 'pass' . $zoneStart . $zoneEnd;
        $zone = new Zone;
        $data = [
            'fk_warehouse' => $warehouse,
            'comment'      => $comment,
        ];
        for ($i = $zoneStart; $i <= $zoneEnd; $i++) {
            $newZone = $zone->firstOrCreate(
                ['name' => $i, 'fk_inventory' => $inventory],
                $data
            );
            if (!$newZone->wasRecentlyCreated) {
                $newZone->update($data);
            }
        }
        header('location:' . INVENTAIRE_URL_ROOT . '/inventaire.php?id=' . $inventory);
    }
    $errors = $validate->errors()->toArray();
}
$css = [
    'inventaire/includes/datatables/media/css/jquery.dataTables.css',
    'inventaire/css/datatable.css',
];
$js = [
    'inventaire/includes/datatables/media/js/jquery.dataTables.js',
    'inventaire/js/datatable.editable.js',
];
llxHeader('', 'inventaire', '', '', 0, 0, $js, $css);

$inventaire = (new Inventaire)->with('zones.user')->find($inventory);
$users      = (new InventoryUser)->where('fk_inventory', $inventory)
    ->get(['rowid', 'name'])
    ->map(
        function ($user) {
            return [
                'value'   => $user->rowid,
                'display' => $user->name,
            ];
        }
    )->prepend(['value' => 0, 'display' => ''])->toJson();

$head = inventairePrepareHead($inventaire);
dol_fiche_head(
    $head,
    'inventaire',
    $langs->trans("Inventaire"),
    -1,
    "inventaire@inventaire"
);
$view->display(
    'inventaires/inventaire.html.twig',
    compact(
        'inventaire',
        'zoneStart',
        'zoneEnd',
        'comment',
        'errors',
        'warehouse',
        'warehouseZone',
        'formproduct',
        'name',
        'users'
    )
);
