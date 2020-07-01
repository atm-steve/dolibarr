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

if (!defined('NOREQUIREMENU')) {
    define('NOREQUIREMENU', '1');
}

if (!defined('NOREQUIRESOC')) {
    define('NOREQUIRESOC', '1');
}

if (!defined('NOCSRFCHECK')) {
    define('NOCSRFCHECK', '1');
}

require '../config.php';

$action = GETPOST('action');

switch ($action) {
    case 'setVerif':
        include_once INVENTAIRE_PATH_ROOT . '/class/Zone.php';
        include_once INVENTAIRE_PATH_ROOT . '/class/ZoneLine.php';
        $line         = (new ZoneLine)->find(GETPOST('id'));
        $line->statut = ZoneLine::STATUT_TO_VERIFIE;
        $line->save();
        $zone         = $line->zone;
        $zone->statut = Zone::STATUT_TO_VERIF;
        $zone->save();

        sendOutput([
            'statut' => Zone::getStatutLabel($zone->statut),
        ]);
        break;
    case 'addToInventaire':
        include_once INVENTAIRE_PATH_ROOT . '/class/ZoneLine.php';
        include_once INVENTAIRE_PATH_ROOT . '/class/Zone.php';
        include_once INVENTAIRE_PATH_ROOT . '/class/InventoryLine.php';
        $line = (new ZoneLine)->select([
            'rowid',
            'fk_product',
            'barcode',
            'fk_barcode_type',
            'qty_view',
            'qty_verified',
            'statut',
            'fk_warehouse',
            'fk_zone',
            'fk_inventory',
        ])->find(GETPOST('id'));
        if (isset($line->barcode) || isset($line->fk_product)) {
            $inventoryline = (new InventoryLine)
                ->where('fk_inventory', $line->fk_inventory)
                ->where('fk_warehouse', $line->fk_warehouse)
                ->where(function ($query) use ($line) {
                    if (isset($line->barcode)) {
                        $query->orWhere('barcode', $line->barcode);
                    }
                    if (isset($line->fk_product)) {
                        $query->orWhere('fk_product', $line->fk_product);
                    }
                })->first();
        }
        $qty = $line->qty_verified > 0 ? $line->qty_verified : $line->qty_view;
        if ($inventoryline) {
            $inventoryline->qty_view += $qty;
            $inventoryline->save();
        } else {

            $data = $line->toArray();
            $res  = $db->query("SELECT reel from " . MAIN_DB_PREFIX . "product_stock where fk_entrepot={$line->fk_warehouse} and fk_product={$line->fk_product}");
            if ($res) {
                $stock = $db->fetch_object($res);
            }
            $data['qty_stock'] = empty($stock) ? 0 : $stock->reel;
            $res               = $db->query("SELECT pmp from " . MAIN_DB_PREFIX . "product where rowid={$line->fk_product}");
            if ($res) {
                $product = $db->fetch_object($res);
            }
            $data['pmp'] = empty($product) ? 0 : $product->pmp;
            dol_include_once('/fourn/class/fournisseur.product.class.php');
            $p = new ProductFournisseur($db);
            $p->find_min_price_product_fournisseur($line->fk_product);
            if ($p->fourn_qty > 0) {
                $data['pa'] = $p->fourn_price / $p->fourn_qty;
            }

            unset($data['qty_verified']);
            unset($data['rowid']);
            unset($data['statut']);
            unset($data['fk_zone']);
            $data['qty_view'] = $qty;
            (new InventoryLine)->create($data);
        }
        $line->statut = ZoneLine::STATUT_MERGED;
        $line->save();
        $line->load('zone.products');
        $products  = $line->zone->products;
        $notMerged = $products->filter(function ($prod) {
            return $prod->statut != ZoneLine::STATUT_MERGED;
        })->count();
        if ($notMerged == 0) {
            $line->zone->statut = Zone::STATUT_MERGED;
            $line->zone->save();
        }
        sendOutput(compact('line', 'stock', 'data'));
        break;
    case 'valideProduct':
    case 'validateProducts':
        $id  = GETPOST('id');
        $ids = explode(',', $id);
        include_once INVENTAIRE_PATH_ROOT . '/class/InventoryLine.php';
        (new InventoryLine)->whereIn('rowid', $ids)->update([
            'statut' => InventoryLine::STATUT_VALIDE,
        ]);
        break;
    case 'addProduct';
        require INVENTAIRE_PATH_ROOT . '/class/Inventaire.php';
        include_once INVENTAIRE_PATH_ROOT . '/class/InventoryLine.php';
        $langs->load('inventaire@inventaire');
        $langs->load('products');
        $idprod    = GETPOST('product');
        $warehouse = GETPOST('warehouse');
        $qty       = GETPOST('qty');
        $id        = GETPOST('id');
        $validate  = $validator->make(
            compact('idprod', 'qty', 'warehouse'),
            [
                'idprod'    => 'required|integer|min:1',
                'warehouse' => 'required|integer|min:1',
                'qty'       => 'required|integer',
            ],
            [
                'warehouse.min' => $langs->trans('validation.required'),
                'idprod.min'    => $langs->trans('validation.required'),
            ],
            [
                'qty'       => $langs->trans('Qty'),
                'idprod'    => $langs->trans('Product'),
                'warehouse' => $langs->trans('warehouse'),
            ]
        );
        if ($validate->passes()) {
            $data = array(
                'qty_view'     => $qty,
                'fk_product'   => $idprod,
                'fk_warehouse' => $warehouse,
            );
            // check line exist
            $line = (new InventoryLine)->where([
                'fk_product'   => $idprod,
                'fk_warehouse' => $warehouse,
                'fk_inventory' => $id,
            ])->first();
            if ($line) {
                $line->qty_view += $qty;
                $line->save();
            } else {
                $res = $db->query("SELECT reel from " . MAIN_DB_PREFIX . "product_stock where fk_entrepot=$warehouse and fk_product=$idprod");
                if ($res) {
                    $stock = $db->fetch_object($res);
                }
                $data['qty_stock'] = !isset($stock) ? 0 : $stock->reel;
                $res               = $db->query("SELECT pmp from " . MAIN_DB_PREFIX . "product where rowid=$idprod");
                if ($res) {
                    $product = $db->fetch_object($res);
                }
                $data['pmp'] = isset($product) ? $product->pmp : 0;
                dol_include_once('/fourn/class/fournisseur.product.class.php');
                $p = new ProductFournisseur($db);
                $p->find_min_price_product_fournisseur($idprod);
                if ($p->fourn_qty > 0) {
                    $data['pa'] = $p->fourn_price / $p->fourn_qty;
                }

                $inventaire = (new inventaire)->find($id);
                if ($inventaire) {
                    $inventaire->lines()->create($data);
                }
            }
        } else {
            sendOutput(['errors' => $validate->errors()->toArray()]);
        }
        break;
    case 'assignProduct':
        include_once INVENTAIRE_PATH_ROOT . '/class/ZoneLine.php';
        include_once INVENTAIRE_PATH_ROOT . '/class/Product.class.php';
        $idprod   = GETPOST('idAssignProduct');
        $barcode  = GETPOST('barcode');
        $products = (new ProductEloquent)->where('barcode', $barcode)->count();
        $line     = (new ZoneLine)->find(GETPOST('id'));
        if ($products > 0) {
            sendOutput(['errors' => $langs->trans('BarcodeAlreadyExist')]);
            $product          = (new ProductEloquent)->where('barcode', $barcode)->first();
            $line->fk_product = $product->rowid;
            $product->loadStock($line->fk_warehouse);
            $line->qty_stock = $product->stock;
            $line->save();
            return;
        }
        $product = (new ProductEloquent)->find($idprod);
        if (isset($product->barecode)) {
            sendOutput(['errors' => $langs->trans('BarcodeAlreadySet')]);
            return;
        }
        $product->updateBarcode($barcode, $capsule->getDatabaseManager());
        $line->fk_product = $idprod;
        $product->loadStock($line->fk_warehouse);
        $line->qty_stock = $product->stock;
        $line->save();
        break;
    case 'appliquePmp':
        include_once INVENTAIRE_PATH_ROOT . '/class/InventoryLine.php';
        $id    = GETPOST('id');
        $ids   = explode(',', $id);
        $lines = (new InventoryLine)->with('product')->whereIn('rowid', $ids)->get();
        $lines->each(function (InventoryLine $line) {
            if ($line->new_pmp > 0) {
                $line->product->pmp = $line->new_pmp;
                $line->pmp          = $line->new_pmp;
                $line->new_pmp      = 0;
                $line->product->save();
                $line->save();
            }
        });
        break;
    case 'mergeInStock':
        include_once INVENTAIRE_PATH_ROOT . '/class/InventoryLine.php';
        require_once DOL_DOCUMENT_ROOT . '/product/stock/class/mouvementstock.class.php';
        $langs->load('inventaire@inventaire');
        $id  = GETPOST('id');
        $ids = explode(',', $id);

        $lines     = (new InventoryLine)->with('product')->whereIn('rowid', $ids)->get();
        $firstLine = $lines->first();
        $url       = INVENTAIRE_URL_ROOT . '/inventaire.php?id=' . $firstLine->inventaire->rowid;
        $label     = $langs->trans('reguleStockInventaire', $url, $firstLine->inventaire->name);
        $lines->each(function (InventoryLine $line) use ($db, $user, $label) {
            $line->qty_regulated = $line->qty_view - $line->qty_stock;
            if ($line->qty_view != $line->qty_stock) {
                $movement       = (int) ($line->qty_view < $line->qty_stock);
                $mouvementstock = new MouvementStock($db);
                $mouvementstock->_create(
                    $user,
                    $line->fk_product,
                    $line->fk_warehouse,
                    $line->qty_regulated,
                    $movement,
                    0,
                    $label,
                    '',
                    $line->inventaire->date
                );
                if ($line->new_pmp > 0) {
                    $line->product->pmp = $line->new_pmp;
                    $line->product->save();
                }
            }
            $line->statut = InventoryLine::STATUT_MERGED;
            $line->save();
            $merged = (new InventoryLine)
                ->where('fk_inventory', $line->fk_inventory)
                ->where('statut', InventoryLine::STATUT_MERGED)->count();
            $all = (new InventoryLine)
                ->where('fk_inventory', $line->fk_inventory)
                ->count();
            var_dump($all, $merged);
            if ($all == $merged) {
                $inventaire         = $line->inventaire;
                $inventaire->status = Inventaire::STATUT_TERMINATE;
                $inventaire->save();
            }
        });
        break;
    default:
        break;
}

function sendOutput($res)
{
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    header('Content-Type: application/json;charset=UTF-8');
    print json_encode($res);
}
