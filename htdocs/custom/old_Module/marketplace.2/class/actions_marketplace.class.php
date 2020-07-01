<?php
/**
 * Copyright (C) 2018 Jean-François Ferry <hello+jf@librethic.io>
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
 * \file    marketplace/class/actions_marketplace.class.php
 * \ingroup marketplace
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsMarketplace
 */
class ActionsMarketplace
{
    /**
     * Database handler.
     *
     * @var DoliDB
     */
    public $db;

    /**
     * Error code (or message)
     *
     * @var string
     */
    public $error = '';

    /**
     * Errors
     *
     * @var array
     */
    public $errors = array();

    /**
     * Hook results. Propagated to $hookmanager->resArray for later reuse
     *
     * @var array
     */
    public $results = array();

    /**
     * String displayed by executeHook() immediately after return
     *
     * @var string
     */
    public $resprints;


    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }


    /**
     * Execute action
     *
     * @param array         $parameters		Array of parameters
     * @param CommonObject  $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param string        $action      	'add', 'update', 'view'
     *
     * @return int <0 if KO,
     *             =0 if OK but we want to process standard actions too,
     *             >0 if OK and we want to replace standard actions.
     */
    public function getNomUrl($parameters, &$object, &$action)
    {
        global $db, $langs, $conf, $user;
        $this->resprints = '';
        return 0;
    }


    /**
     * Overloading the doAction function
     *
     * @param array           $parameters     Hook metadatas (context, etc...)
     * @param CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param string          $action         Current action (if set). Generally create or edit or null
     * @param HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     *
     * @return int                             < 0 on error, 0 on success, 1 to replace standard code
     */
    public function doActions($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs, $form;

        $error = 0; // Error counter
        $out = '';
        
        /* print_r($parameters); print_r($object); echo "action: " . $action; */
        if (in_array($parameters['currentcontext'], array('productcard'))) {
            dol_include_once('/marketplace/class/productseller.class.php');
            require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';

            if ($action == 'add') {
                $object->marketplace_fk_seller = GETPOST('marketplace_fk_seller');
            } elseif ($action == 'update') {
                $object->marketplace_fk_seller = GETPOST('marketplace_fk_seller');
            }
        }

        if (!$error) {
            $this->results = array();
            $this->resprints = $out;
            return 0; // or return 1 to replace standard code
        } else {
            $this->errors[] = 'Error message';
            return -1;
        }
    }

    /**
     * Overloading the formObjectOptions function
     *
     * @param array           $parameters     Hook metadatas (context, etc...)
     * @param CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param string          $action         Current action (if set). Generally create or edit or null
     * @param HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     *
     * @return int                             < 0 on error, 0 on success, 1 to replace standard code
     */
    public function formObjectOptions($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs, $form;

        $error = 0; // Error counter
        
        /* print_r($parameters); print_r($object); echo "action: " . $action; */
        if (in_array($parameters['currentcontext'], array('productcard'))) {
            dol_include_once('/marketplace/class/productseller.class.php');
            require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';

            if ($action == 'create') {
                $out = '<tr><td>';
                $out .= $langs->trans("MarketPlaceSeller");
                $out .= '</td>';

                $out .= '<td>';
                $out .= $form->select_company(0, 'marketplace_fk_seller', '', 1);
                $out .= '</td>';
                $out .= '</tr>';
            } elseif ($action == 'view') {
                $out = '<tr><td>';
                $out .= $langs->trans("MarketPlaceSeller");
                $out .= '</td>';

                $retroSupplierProduct = new ProductSeller($object->db);
                $idSupplier = $retroSupplierProduct->getSellerIDForProduct($object->id);
                $out .= '<td>' . dolGetElementUrl($idSupplier, 'societe', 1, 'supplier') . '</td>';
                $out .= '</tr>';
            } elseif ($action == 'edit') {
                $out = '<tr><td>';
                $out .= $langs->trans("MarketPlaceSeller");
                $out .= '</td>';

                $out .= '<td>';
                $retroSupplierProduct = new ProductSeller($object->db);
                $idSupplier = $retroSupplierProduct->getSellerIDForProduct($object->id);
                $out .= $form->select_company($idSupplier, 'marketplace_fk_seller', '', 1);
                $out .= '</td>';
                $out .= '</tr>';
            }
        }

        if (!$error) {
            $this->results = array('myreturn' => 999);
            $this->resprints = $out;
            return 0; // or return 1 to replace standard code
        } else {
            $this->errors[] = 'Error message';
            return -1;
        }
    }
}
