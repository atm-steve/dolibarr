<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
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
 */

/**
 * \file        class/suppliersales.class.php
 * \ingroup     marketplace
 * \brief       This file is a CRUD class file for Sales (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for Sales
 */
class Sales extends CommonObject
{
    /**
     * Sale must be to billed
     */
    const STATUS_TOBILL = 0;

    /**
     * Sale already billed but waiting payment
     */
    const STATUS_WAITING_PAYMENT = 1;

    /**
     * Sale billed and paid
     */
    const STATUS_PAID = 9;

    /**
     * Sale cancelled
     */
    const STATUS_CANCELLED = -1;

    /**
     * @var string ID to identify managed object
     */
    public $element = 'suppliersales';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'marketplace_sales';

    /**
     * @var int  Does suppliersales support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
     */
    public $ismultientitymanaged = 0;

    /**
     * @var int  Does suppliersales support extrafields ? 0=No, 1=Yes
     */
    public $isextrafieldmanaged = 1;

    /**
     * @var string String with name of icon for suppliersales. Must be the part after the 'object_' into object_suppliersales.png
     */
    public $picto = 'sales@marketplace';


    /**
     *  'type' if the field format.
     *  'label' the translation key.
     *  'enabled' is a condition when the field must be managed.
     *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only. Using a negative value means field is not shown by default on list but can be selected for viewing)
     *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
     *  'default' is a default value for creation (can still be replaced by the global setup of default values)
     *  'index' if we want an index in database.
     *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
     *  'position' is the sort order of field.
     *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
     *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
     *  'css' is the CSS style to use on field. For example: 'maxwidth200'
     *  'help' is a string visible as a tooltip on field
     *  'comment' is not used. You can store here any text of your choice. It is not used by application.
     *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
     *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
     */

    // BEGIN MODULEBUILDER PROPERTIES
    /**
     * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
     */
    public $fields=array(
        'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-1, 'position'=>1, 'notnull'=>1, 'index'=>1, 'comment'=>"Id",),
        'fk_seller' => array('type'=>'integer:Societe:societe/class/societe.class.php', 'label'=>'MarketPlaceSeller', 'enabled'=>1, 'visible'=>1, 'position'=>10, 'notnull'=>-1, 'checked' => true, 'index'=>1, 'help'=>"LinkToSupplier",),
        'fk_product' => array('type'=>'integer:Product:product/class/product.class.php', 'label'=>'Product', 'enabled'=>1, 'visible'=>1, 'position'=>11, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToProduct",),
        'price' => array('type'=>'price', 'label'=>'MarketPlaceSalePriceNet', 'enabled'=>1, 'visible'=>1, 'position'=>15, 'notnull'=>-1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Sale price", 'css' => 'right'),
        'care_rate' => array('type' => 'double(24,2)', 'label' => 'MarketplaceCareRate', 'enabled' => 1, 'visible' => -1, 'position' => 21, 'notnull' => -1, 'default' => 'null', 'css' => 'center'),
        'care_amount' => array('type' => 'price', 'label' => 'MarketplaceCareAmount', 'enabled' => 1, 'visible' => -1, 'position' => 22, 'notnull' => -1, 'default' => 'null', 'isameasure' => '1', 'css' => 'right'),
        'discount_rate' => array('type' => 'double(24,2)', 'label' => 'MarketplaceDiscountRate', 'enabled' => 1, 'visible' => -1, 'position' => 32, 'notnull' => -1, 'default' => 'null', 'css' => 'right'),
        'discount_amount' => array('type' => 'real', 'label' => 'MarketplaceDiscountAmount', 'enabled' => 1, 'visible' => -1, 'position' => 32, 'notnull' => -1, 'default' => 'null', 'css' => 'right'),
        'collection_rate' => array('type'=>'double(24,2)', 'label'=>'MarketplaceCollectionRate', 'enabled'=>1, 'visible'=>1, 'position'=>41, 'notnull'=>-1, 'default'=>'null', 'css' => 'center'),
        'collection_amount' => array('type'=>'price', 'label'=>'MarketplaceCollectionAmount', 'enabled'=>1, 'visible'=>1, 'position'=>42, 'notnull'=>-1, 'default'=>'null', 'isameasure'=>'1', 'css' => 'right'),
        'tax_rate' => array('type' => 'double(24,2)', 'label' => 'MarketplaceSaleTaxRate', 'enabled' => 1, 'visible' => 1, 'position' => 17, 'notnull' => -1, 'default' => 'null', 'css' => 'right'),
        'tax_total' => array('type' => 'price', 'label' => 'MarketplaceSaleTaxTotal', 'enabled' => 1, 'visible' => 1, 'position' => 16, 'notnull' => -1, 'default' => 'null', 'isameasure' => '1', 'css' => 'right'),
        'retrocession_amount' => array('type' => 'price', 'label' => 'MarketplaceSaleRetrocessionAmount', 'enabled' => 1, 'visible' => 1, 'position' => 62, 'notnull' => -1, 'default' => 'null', 'isameasure' => '1', 'css' => 'right'),
        'fk_customer_invoice' => array('type'=>'integer:Facture:compta/facture/class/facture.class.php', 'label'=>'CustomerInvoice', 'enabled'=>1, 'visible'=>2, 'position'=>70, 'notnull'=>-1, 'index'=>1, 'foreignkey'=>'llx_facture.rowid', 'help'=>"Id of customer invoice",),
        'fk_customer_invoice_line' => array('type'=>'integer', 'label'=>'CustomerInvoiceLine', 'enabled'=>1, 'visible'=>0, 'position'=>71, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToCustomerInvoiceLine",),
        'fk_seller_invoice' => array('type'=>'integer:FactureFournisseur:fourn/class/fournisseur.facture.class.php', 'label'=>'MarketplaceSellerInvoice', 'enabled'=>1, 'visible'=>2, 'position'=>72, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToSupplier",),
        'fk_seller_invoice_line' => array('type'=>'integer', 'label'=>'SupplierInvoiceLine', 'enabled'=>1, 'visible'=>0, 'position'=>73, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToSupplier",),
        'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'position'=>500, 'notnull'=>1,),
        'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'position'=>501, 'notnull'=>1,),
        'fk_user_creat' => array('type'=>'integer', 'label'=>'UserAuthor', 'enabled'=>1, 'visible'=>-2, 'position'=>510, 'notnull'=>1, 'foreignkey'=>'llx_user.rowid',),
        'fk_user_modif' => array('type'=>'integer', 'label'=>'UserModif', 'enabled'=>1, 'visible'=>-2, 'position'=>511, 'notnull'=>-1,),
        'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>1, 'visible'=>-2, 'position'=>1000, 'notnull'=>-1,),
        'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>1, 'visible'=>1, 'position'=>1000, 'notnull'=>1, 'index'=>1, 'arrayofkeyval'=>array('0'=>'ToBill', '1'=>'WaitingPayment', '9'=>'Paid', '-1'=>'Cancelled')),
    );
    public $rowid;
    public $fk_seller;
    public $fk_product;
    public $price;
    public $discount_rate;
    public $discount_amount;
    public $care_rate;
    public $care_amount;
    public $collection_rate;
    public $collection_amount;
    public $tax_rate;
    public $tax_total;
    public $retrocession_amount;
    public $fk_customer_invoice;
    public $fk_customer_invoice_line;
    public $fk_seller_invoice;
    public $fk_seller_invoice_line;
    public $date_creation;
    public $tms;
    public $fk_user_creat;
    public $fk_user_modif;
    public $import_key;
    public $status;
    // END MODULEBUILDER PROPERTIES



    // If this object has a subtable with lines

    /**
     * @var int    Name of subtable line
     */
    //public $table_element_line = 'suppliersalesdet';

    /**
     * @var int    Field with ID of parent key if this field has a parent
     */
    //public $fk_element = 'fk_sellersales';

    /**
     * @var int    Name of subtable class that manage subtable lines
     */
    //public $class_element_line = 'Salesline';

    /**
     * @var array  Array of child tables (child tables to delete before deleting a record)
     */
    //protected $childtables=array('suppliersalesdet');

    /**
     * @var SalesLine[]     Array of subtable lines
     */
    //public $lines = array();



    /**
     * Constructor
     *
     * @param DoliDb $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        global $conf, $langs, $user;

        $this->db = $db;

        if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
            $this->fields['rowid']['visible']=0;
        }
        if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
            $this->fields['entity']['enabled']=0;
        }

        // Unset fields that are disabled
        foreach ($this->fields as $key => $val) {
            if (isset($val['enabled']) && empty($val['enabled'])) {
                unset($this->fields[$key]);
            }
        }

        // Translate some data of arrayofkeyval
        foreach ($this->fields as $key => $val) {
            if (is_array($this->fields['status']['arrayofkeyval'])) {
                foreach ($this->fields['status']['arrayofkeyval'] as $key2 => $val2) {
                    $this->fields['status']['arrayofkeyval'][$key2]=$langs->trans($val2);
                }
            }
        }
    }

    /**
     * Create object into database
     *
     * @param  User $user      User that creates
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, Id of created object if OK
     */
    public function create(User $user, $notrigger = false)
    {
        global $conf, $langs;

        $error=0;
        $now=dol_now();

        if (empty($this->fk_product) && empty($this->fk_seller)) {
            return 0;
        }

        $this->db->begin();

        // Clean parameters

        $this->entity = ((isset($this->entity) && is_numeric($this->entity))?$this->entity:$conf->entity);

        $sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element." (";
        //$sql.= ", entity";
        $sql.= " fk_seller";
        $sql.= ", fk_product";
        $sql.= ", price";
        $sql .= ", discount_rate";
        $sql.= ", discount_amount";
        $sql.= ", care_rate";
        $sql.= ", care_amount";
        $sql.= ", collection_rate";
        $sql.= ", collection_amount ";
        $sql.= ", tax_rate ";
        $sql.= ", tax_total ";
        $sql.= ", retrocession_amount ";
        $sql.= ", fk_customer_invoice";
        $sql.= ", fk_customer_invoice_line";
        $sql.= ", fk_seller_invoice";
        $sql.= ", fk_seller_invoice_line";
        $sql.= ", date_creation";
        $sql.= ", fk_user_creat";
        //$sql.= ", fk_user_modif ";
        $sql.= ", import_key";
        $sql.= ", status";
        $sql.= ") VALUES (";
        $sql .= " " . (!empty($this->fk_seller) ?  $this->db->escape($this->fk_seller) : "null") . ",";
        $sql .= " " . (!empty($this->fk_product) ? $this->db->escape($this->fk_product) : "null") . ",";
        $sql .= " " . (!empty($this->price) ? $this->db->escape($this->price) : "null") . ",";
        $sql .= " " . (!empty($this->discount_rate) ? $this->db->escape($this->discount_rate) : "null") . ",";
        $sql .= " " . (!empty($this->discount_amount) ? $this->db->escape($this->discount_amount) : "null") . ",";
        $sql .= " " . (!empty($this->care_rate) ? $this->db->escape($this->care_rate) : "null") . ",";
        $sql .= " " . (!empty($this->care_amount) ? $this->db->escape($this->care_amount) : "null") . ",";
        $sql .= " " . (!empty($this->collection_rate) ? $this->db->escape($this->collection_rate) : "null") . ",";
        $sql .= " " . (!empty($this->collection_amount) ? $this->db->escape($this->collection_amount) : "null") . ",";
        $sql .= " " . (!empty($this->tax_rate) ? $this->db->escape($this->tax_rate) : "null") . ",";
        $sql .= " " . (!empty($this->tax_total) ? $this->db->escape($this->tax_total) : "null") . ",";
        $sql .= " " . (!empty($this->retrocession_amount) ? $this->db->escape($this->retrocession_amount) : "null") . ",";
        $sql .= " " . (!empty($this->fk_customer_invoice) ? $this->db->escape($this->fk_customer_invoice) : "null") . ",";
        $sql .= " " . (!empty($this->fk_customer_invoice_line) ? $this->db->escape($this->fk_customer_invoice_line) : "null") . ",";
        $sql .= " " . (!empty($this->fk_seller_invoice) ? $this->db->escape($this->fk_seller_invoice) : "null") . ",";
        $sql .= " " . (!empty($this->fk_seller_invoice_line) ? $this->db->escape($this->price) : "null") . ",";
        $sql .= "'" . (!empty($this->date_creation) ? $this->db->idate($this->date_creation) : $this->db->idate($now)) ."',";
        $sql.= " ".($user->id > 0 ? "'".$this->db->escape($user->id)."'":"null").",";
        $sql.= " ".(! empty($this->import_key)?"'".$this->db->escape($this->import_key)."'":"null").",";
        $sql .= " " . (!empty($this->status) ? $this->db->escape($this->status) : 0);
        $sql.= ")";

        dol_syslog(get_class($this)."::create", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);

            if (! $error && ! $notrigger) {
                // Call trigger
                $result=$this->call_trigger('MARKETPLACE_SALE_CREATE', $user);
                if ($result < 0) {
                    $error++;
                }
                // End call triggers
            }

            if (! $error) {
                $this->db->commit();
                return $this->id;
            } else {
                $this->db->rollback();
                dol_syslog(get_class($this)."::create ".$this->error, LOG_ERR);
                return -2;
            }
        } else {
            $this->error=$this->db->lasterror();

            $this->db->rollback();
            dol_syslog(get_class($this)."::create ".$this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     * Clone and object into another one
     *
     * @param  	User 	$user      	User that creates
     * @param  	int 	$fromid     Id of object to clone
     * @return 	mixed 				New object created, <0 if KO
     */
    public function createFromClone(User $user, $fromid)
    {
        global $langs, $hookmanager, $extrafields;
        $error = 0;

        dol_syslog(__METHOD__, LOG_DEBUG);

        $object = new self($this->db);

        $this->db->begin();

        // Load source object
        $object->fetchCommon($fromid);
        // Reset some properties
        unset($object->id);
        unset($object->fk_user_creat);
        unset($object->import_key);

        // Clear fields
        $object->ref = "copy_of_".$object->ref;
        $object->title = $langs->trans("CopyOf")." ".$object->title;
        // ...
        // Clear extrafields that are unique
        if (is_array($object->array_options) && count($object->array_options) > 0) {
            $extrafields->fetch_name_optionals_label($this->element);
            foreach ($object->array_options as $key => $option) {
                $shortkey = preg_replace('/options_/', '', $key);
                if (! empty($extrafields->attributes[$this->element]['unique'][$shortkey])) {
                    //var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
                    unset($object->array_options[$key]);
                }
            }
        }

        // Create clone
        $object->context['createfromclone'] = 'createfromclone';
        $result = $object->createCommon($user);
        if ($result < 0) {
            $error++;
            $this->error = $object->error;
            $this->errors = $object->errors;
        }

        // End
        if (!$error) {
            $this->db->commit();
            return $object;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Load object in memory from the database
     *
     * @param int    $id   Id object
     * @param string $ref  Ref
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetch($id, $ref = null)
    {
        $result = $this->fetchCommon($id, $ref);
        if ($result > 0 && ! empty($this->table_element_line)) {
            $this->fetchLines();
        }
        return $result;
    }

    /**
     * Load object lines in memory from the database
     *
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    /*public function fetchLines()
    {
        $this->lines=array();

        // Load lines with object SalesLine

        return count($this->lines)?1:0;
    }*/

    /**
     * Load list of objects in memory from the database.
     *
     * @param  string      $sortorder    Sort Order
     * @param  string      $sortfield    Sort field
     * @param  int         $limit        limit
     * @param  int         $offset       Offset
     * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
     * @param  string      $filtermode   Filter mode (AND or OR)
     * @return array|int                 int <0 if KO, array of pages if OK
     */
    public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
    {
        global $conf;

        dol_syslog(__METHOD__, LOG_DEBUG);

        $records=array();

        $sql = 'SELECT';
        $sql .= ' t.rowid';
        // TODO Get all fields
        $sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element. ' as t';
        $sql .= ' WHERE t.entity = '.$conf->entity;
        // Manage filter
        $sqlwhere = array();
        if (count($filter) > 0) {
            foreach ($filter as $key => $value) {
                if ($key=='t.rowid') {
                    $sqlwhere[] = $key . '='. $value;
                } elseif (strpos($key, 'date') !== false) {
                    $sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
                } elseif ($key=='customsql') {
                    $sqlwhere[] = $value;
                } else {
                    $sqlwhere[] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
                }
            }
        }
        if (count($sqlwhere) > 0) {
            $sql .= ' AND (' . implode(' '.$filtermode.' ', $sqlwhere).')';
        }

        if (!empty($sortfield)) {
            $sql .= $this->db->order($sortfield, $sortorder);
        }
        if (!empty($limit)) {
            $sql .=  ' ' . $this->db->plimit($limit, $offset);
        }

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);

            while ($obj = $this->db->fetch_object($resql)) {
                $record = new self($this->db);

                $record->id = $obj->rowid;
                // TODO Get other fields

                //var_dump($record->id);
                $records[$record->id] = $record;
            }
            $this->db->free($resql);

            return $records;
        } else {
            $this->errors[] = 'Error ' . $this->db->lasterror();
            dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

            return -1;
        }
    }

    /**
     * Update object into database
     *
     * @param  User $user      User that modifies
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, >0 if OK
     */
    public function update(User $user, $notrigger = false)
    {
        return $this->updateCommon($user, $notrigger);
    }

    /**
     * Delete object in database
     *
     * @param User $user       User that deletes
     * @param bool $notrigger  false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, >0 if OK
     */
    public function delete(User $user, $notrigger = false)
    {
        return $this->deleteCommon($user, $notrigger);
        //return $this->deleteCommon($user, $notrigger, 1);
    }

    /**
     *  Return a link to the object card (with optionaly the picto)
     *
     *	@param	int		$withpicto					Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
     *	@param	string	$option						On what the link point to ('nolink', ...)
     *  @param	int  	$notooltip					1=Disable tooltip
     *  @param  string  $morecss            		Add more css on link
     *  @param  int     $save_lastsearch_value    	-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
     *	@return	string								String with URL
     */
    public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
    {
        global $db, $conf, $langs, $hookmanager;
        global $dolibarr_main_authentication, $dolibarr_main_demo;
        global $menumanager;

        if (! empty($conf->dol_no_mouse_hover)) {
            $notooltip=1;   // Force disable tooltips
        }

        $result = '';

        $label = '<u>' . $langs->trans("Sales") . '</u>';
        $label.= '<br>';
        $label.= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;

        $url = dol_buildpath('/marketplace/suppliersales_card.php', 1).'?id='.$this->id;

        if ($option != 'nolink') {
            // Add param to save lastsearch_values or not
            $add_save_lastsearch_values=($save_lastsearch_value == 1 ? 1 : 0);
            if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
                $add_save_lastsearch_values=1;
            }
            if ($add_save_lastsearch_values) {
                $url.='&save_lastsearch_values=1';
            }
        }

        $linkclose='';
        if (empty($notooltip)) {
            if (! empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
                $label=$langs->trans("ShowSales");
                $linkclose.=' alt="'.dol_escape_htmltag($label, 1).'"';
            }
            $linkclose.=' title="'.dol_escape_htmltag($label, 1).'"';
            $linkclose.=' class="classfortooltip'.($morecss?' '.$morecss:'').'"';

            /*
             $hookmanager->initHooks(array('suppliersalesdao'));
             $parameters=array('id'=>$this->id);
             $reshook=$hookmanager->executeHooks('getnomurltooltip',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
             if ($reshook > 0) $linkclose = $hookmanager->resPrint;
             */
        } else {
            $linkclose = ($morecss?' class="'.$morecss.'"':'');
        }

        $linkstart = '<a href="'.$url.'"';
        $linkstart.=$linkclose.'>';
        $linkend='</a>';

        $result .= $linkstart;
        if ($withpicto) {
            $result.=img_object(($notooltip?'':$label), ($this->picto?$this->picto:'generic'), ($notooltip?(($withpicto != 2) ? 'class="paddingright"' : ''):'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip?0:1);
        }
        if ($withpicto != 2) {
            $result.= $this->ref;
        }
        $result .= $linkend;
        //if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

        global $action,$hookmanager;
        $hookmanager->initHooks(array('suppliersalesdao'));
        $parameters=array('id'=>$this->id, 'getnomurl'=>$result);
        $reshook=$hookmanager->executeHooks('getNomUrl', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks
        if ($reshook > 0) {
            $result = $hookmanager->resPrint;
        } else {
            $result .= $hookmanager->resPrint;
        }

        return $result;
    }

    /**
     *  Return label of the status
     *
     *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
     *  @return	string 			       Label of status
     */
    public function getLibStatut($mode = 0)
    {
        return $this->LibStatut($this->status, $mode);
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
    /**
     *  Return the status
     *
     *  @param	int		$status        Id status
     *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
     *  @return string 			       Label of status
     */
    public function LibStatut($status, $mode = 0)
    {
        // phpcs:enable
        if (empty($this->labelstatus)) {
            global $langs;
            //$langs->load("marketplace");
            $this->labelstatus[Sales::STATUS_TOBILL] = $langs->trans('ToBill');
            $this->labelstatus[Sales::STATUS_WAITING_PAYMENT] = $langs->trans('WaitingPayment');
            $this->labelstatus[Sales::STATUS_PAID] = $langs->trans('Paid');
            $this->labelstatus[Sales::STATUS_CANCELLED] = $langs->trans('Cancelled');
        }

        if ($mode == 0) {
            return $this->labelstatus[$status];
        } elseif ($mode == 1) {
            return $this->labelstatus[$status];
        } elseif ($mode == 2) {
            if ($status == Sales::STATUS_TOBILL) {
                return img_picto($this->labelstatus[$status], 'statut0', '', false, 0, 0, '', 'valignmiddle') . ' ' . $this->labelstatus[$status];
            } elseif ($status == Sales::STATUS_WAITING_PAYMENT) {
                return img_picto($this->labelstatus[$status], 'statut3', '', false, 0, 0, '', 'valignmiddle') . ' ' . $this->labelstatus[$status];
            } elseif ($status == Sales::STATUS_PAID) {
                return img_picto($this->labelstatus[$status], 'statut4', '', false, 0, 0, '', 'valignmiddle') . ' ' . $this->labelstatus[$status];
            } elseif ($status == Sales::STATUS_CANCELLED) {
                return img_picto($this->labelstatus[$status], 'statut5', '', false, 0, 0, '', 'valignmiddle') . ' ' . $this->labelstatus[$status];
            }
        } elseif ($mode == 3) {
            if ($status == Sales::STATUS_TOBILL) {
                return img_picto($this->labelstatus[$status], 'statut0', '', false, 0, 0, '', 'valignmiddle');
            } elseif ($status == Sales::STATUS_WAITING_PAYMENT) {
                return img_picto($this->labelstatus[$status], 'statut3', '', false, 0, 0, '', 'valignmiddle');
            } elseif ($status == Sales::STATUS_PAID) {
                return img_picto($this->labelstatus[$status], 'statut4', '', false, 0, 0, '', 'valignmiddle');
            } elseif ($status == Sales::STATUS_CANCELLED) {
                return img_picto($this->labelstatus[$status], 'statut5', '', false, 0, 0, '', 'valignmiddle');
            }
        } elseif ($mode == 4) {
            if ($status == Sales::STATUS_TOBILL) {
                return img_picto($this->labelstatus[$status], 'statut0', '', false, 0, 0, '', 'valignmiddle') . ' ' . $this->labelstatus[$status];
            } elseif ($status == Sales::STATUS_WAITING_PAYMENT) {
                return img_picto($this->labelstatus[$status], 'statut3', '', false, 0, 0, '', 'valignmiddle') . ' ' . $this->labelstatus[$status];
            } elseif ($status == Sales::STATUS_PAID) {
                return img_picto($this->labelstatus[$status], 'statut4', '', false, 0, 0, '', 'valignmiddle') . ' ' . $this->labelstatus[$status];
            } elseif ($status == Sales::STATUS_CANCELLED) {
                return img_picto($this->labelstatus[$status], 'statut5', '', false, 0, 0, '', 'valignmiddle') . ' ' . $this->labelstatus[$status];
            }
        } elseif ($mode == 5) {
            if ($status == Sales::STATUS_TOBILL) {
                return $this->labelstatus[$status] . ' ' . img_picto($this->labelstatus[$status], 'statut0', '', false, 0, 0, '', 'valignmiddle');
            } elseif ($status == Sales::STATUS_WAITING_PAYMENT) {
                return $this->labelstatus[$status] . ' ' . img_picto($this->labelstatus[$status], 'statut3', '', false, 0, 0, '', 'valignmiddle');
            } elseif ($status == Sales::STATUS_PAID) {
                return $this->labelstatus[$status] . ' ' . img_picto($this->labelstatus[$status], 'statut4', '', false, 0, 0, '', 'valignmiddle');
            } elseif ($status == Sales::STATUS_CANCELLED) {
                return $this->labelstatus[$status] . ' ' . img_picto($this->labelstatus[$status], 'statut5', '', false, 0, 0, '', 'valignmiddle');
            }
        } elseif ($mode == 6) {
            if ($status == Sales::STATUS_TOBILL) {
                return $this->labelstatus[$status] . ' ' . img_picto($this->labelstatus[$status], 'statut0', '', false, 0, 0, '', 'valignmiddle');
            } elseif ($status == Sales::STATUS_WAITING_PAYMENT) {
                return $this->labelstatus[$status] . ' ' . img_picto($this->labelstatus[$status], 'statut3', '', false, 0, 0, '', 'valignmiddle');
            } elseif ($status == Sales::STATUS_PAID) {
                return $this->labelstatus[$status] . ' ' . img_picto($this->labelstatus[$status], 'statut4', '', false, 0, 0, '', 'valignmiddle');
            } elseif ($status == Sales::STATUS_CANCELLED) {
                return $this->labelstatus[$status] . ' ' . img_picto($this->labelstatus[$status], 'statut5', '', false, 0, 0, '', 'valignmiddle');
            }
        }
    }

    /**
     *	Charge les informations d'ordre info dans l'objet commande
     *
     *	@param  int		$id       Id of order
     *	@return	void
     */
    public function info($id)
    {
        $sql = 'SELECT rowid, date_creation as datec, tms as datem,';
        $sql.= ' fk_user_creat, fk_user_modif';
        $sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
        $sql.= ' WHERE t.rowid = '.$id;
        $result=$this->db->query($sql);
        if ($result) {
            if ($this->db->num_rows($result)) {
                $obj = $this->db->fetch_object($result);
                $this->id = $obj->rowid;
                if ($obj->fk_user_author) {
                    $cuser = new User($this->db);
                    $cuser->fetch($obj->fk_user_author);
                    $this->user_creation   = $cuser;
                }

                if ($obj->fk_user_valid) {
                    $vuser = new User($this->db);
                    $vuser->fetch($obj->fk_user_valid);
                    $this->user_validation = $vuser;
                }

                if ($obj->fk_user_cloture) {
                    $cluser = new User($this->db);
                    $cluser->fetch($obj->fk_user_cloture);
                    $this->user_cloture   = $cluser;
                }

                $this->date_creation     = $this->db->jdate($obj->datec);
                $this->date_modification = $this->db->jdate($obj->datem);
                $this->date_validation   = $this->db->jdate($obj->datev);
            }

            $this->db->free($result);
        } else {
            dol_print_error($this->db);
        }
    }

    /**
     * Mark sale as billed
     *
     * @param User $user          User for action
     * @param int  $invoiceId     ID of supplier invoice
     * @param int  $invoiceLineId ID of supplier invoice line
     *
     * @return int 1 if ok, -1 otherwise
     */
    public function setBilled($user, $invoiceId, $invoiceLineId)
    {
        // phpcs:enable
        global $conf,$langs;
        $error=0;

        $this->db->begin();

        dol_syslog(__METHOD__." Sales ID ".$this->id." / Invoice ID : ".$invoiceId." / invoice line ip : ".$invoiceLineId, LOG_DEBUG);

        $sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element;
        $sql.= ' SET fk_seller_invoice ='.$invoiceId;
        $sql.= ', fk_seller_invoice_line='.$invoiceLineId;
        $sql.= ', status=' . Sales::STATUS_WAITING_PAYMENT;
        $sql.= ', fk_user_modif='. intval($user->id);
        $sql.= ' WHERE rowid = '.$this->id;

        $resql = $this->db->query($sql);
        if ($resql) {
            // Call trigger
            //$result=$this->call_trigger('BILL_SUPPLIER_PAYED',$user);
            //if ($result < 0) $error++;
            // End call triggers
        } else {
            $error++;
            $this->error=$this->db->error();
            dol_print_error($this->db);
        }

        if (! $error) {
            $this->db->commit();
            return 1;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Mark sale as paid
     *
     * @param User $user          User for action
     * @param int  $invoiceId     ID of supplier invoice
     * @param int  $invoiceLineId ID of supplier invoice line
     *
     * @return int 1 if ok, -1 otherwise
     */
    public function setBillPaid($user, $invoiceId, $invoiceLineId)
    {
        global $conf, $langs;
        $error = 0;

        $this->db->begin();

        $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element;
        $sql .= ' SET status='. Sales::STATUS_PAID . ', fk_user_modif='.$user->id;
        $sql .= ' WHERE fk_seller_invoice =' . $invoiceId . ' AND fk_seller_invoice_line=' . $invoiceLineId;

        $resql = $this->db->query($sql);
        if ($resql) {
            // Call trigger
            //$result=$this->call_trigger('BILL_SUPPLIER_PAYED',$user);
            //if ($result < 0) $error++;
            // End call triggers
        } else {
            $error++;
            $this->error = $this->db->error();
            dol_print_error($this->db);
        }

        if (!$error) {
            $this->db->commit();
            return 1;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Mark sale as to bill (after invoice deletion)
     *
     * @param User $user          User for action
     * @param int  $invoiceId     ID of supplier invoice
     * @param int  $invoiceLineId ID of supplier invoice line
     *
     * @return int 1 if ok, -1 otherwise
     */
    public function setToBill($user, $invoiceId, $invoiceLineId)
    {
        // phpcs:enable
        global $conf,$langs;
        $error=0;

        $this->db->begin();

        dol_syslog(__METHOD__." Sales ID ".$this->id." / Invoice ID : ".$invoiceId." / invoice line ip : ".$invoiceLineId, LOG_DEBUG);

        $sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element;
        $sql.= ' SET fk_seller_invoice =\'\'';
        $sql.= ', fk_seller_invoice_line=\'\'';
        $sql.= ', status=' . Sales::STATUS_TOBILL;
        $sql.= ', fk_user_modif='. intval($user->id);
        $sql.= ' WHERE fk_seller_invoice ='.$invoiceId;
        $sql.= ' AND fk_seller_invoice_line='.$invoiceLineId;

        $resql = $this->db->query($sql);
        if ($resql) {
            // Call trigger
            //$result=$this->call_trigger('BILL_SUPPLIER_PAYED',$user);
            //if ($result < 0) $error++;
            // End call triggers
        } else {
            $error++;
            $this->error=$this->db->error();
            dol_print_error($this->db);
        }

        if (! $error) {
            $this->db->commit();
            return 1;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

      /**
     * Mark sale as canceled
     *
     * @param User $user          User for action
     * @param int  $id     ID of marketplace sale
     *
     * @return int 1 if ok, -1 otherwise
     */
    public function setCancelled($user, $id)
    {
        // phpcs:enable
        global $conf,$langs;
        $error=0;

        $this->db->begin();

        dol_syslog(__METHOD__." Sales ID ".$this->id." / Invoice ID : ".$invoiceId, LOG_DEBUG);

        $sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element;
        $sql.= ' SET  status=' . Sales::STATUS_CANCELLED;
        $sql.= ', fk_user_modif='. intval($user->id);
        $sql.= ' WHERE rowid = '.$id;

        $resql = $this->db->query($sql);
        if ($resql) {
            // Call trigger
            //$result=$this->call_trigger('BILL_SUPPLIER_PAYED',$user);
            //if ($result < 0) $error++;
            // End call triggers
        } else {
            $error++;
            $this->error=$this->db->error();
            dol_print_error($this->db);
        }

        if (! $error) {
            $this->db->commit();
            return 1;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Calculate marketplace retrocession amount for supplier invoice line
     *
     * @param FactureLigne   $line           Customer invoice line object
     * @param sellerCareRate $sellerCareRate Marketplace rate
     *
     * @return float Calculated HT amount
     */
    public function calculateRetrocessionAmountForLine($line, $sellerCareRate = 0)
    {
        $amount = 0;

        if (! $this->collection_rate > 0) {
            return 0;
        }
        if (isset($line->total_ht)) {
            //$totalHtBrut = $line->subprice * $line->qty;


            $this->discount_rate = $line->remise_percent;
            $this->care_rate = $sellerCareRate;
            $this->tax_rate = $line->tva_tx;
            $amountRetrocession = $this->calculateRetrocessionAmount($line->total_ht);
        }
        return $amountRetrocession;
    }

    /**
     * Calculate retrocession amount from a price, a collection rate, a discount rate and care rate
     *
     * @param float   $priceNet      Price of sale (included discount, without tax)
     *
     * @return float
     */
    public function calculateRetrocessionAmount($priceNet)
    {
        if (!$priceNet || ! $this->collection_rate) {
            return 0;
        }

        if (empty($this->collection_amount)) {
            $this->collection_amount = $this->calculateCollectionAmount($priceNet);
        }


        // For marketplace rate (company will pay xx%)
        // $amountRetrocession = $priceNet *  ( $CollectionRate / 100);
        
        // for drawdown rate (company will keep xx%)
        $amountRetrocession = $priceNet - $this->collection_amount;
        
        return $amountRetrocession;
    }

    /**
     * Calculate marketplace collection amount for supplier invoice line
     *
     * @param FactureLigne   $line           Customer invoice line object
     * @param sellerCareRate $sellerCareRate Marketplace rate
     *
     * @return float Calculated HT amount
     */
    public function calculateCollectionAmountForLine($line, $sellerCareRate = 0)
    {
        $amount = 0;

        if (!$this->collection_rate > 0) {
            return 0;
        }
        if (isset($line->total_ht)) {
            $this->collection_rate;
            $this->discount_rate = $line->remise_percent;
            $this->care_rate = $sellerCareRate;
            $this->tax_rate = $line->tva_tx;
            $amount = $this->calculateCollectionAmount($line->total_ht);
        }
        return $amount;
    }

    /**
     * Calculate collection amount from price and retrocession amount, discount and seller care rate
     *
     * @param float   $priceNet      Price net (included discount, without tax)
     * @param integer $sellerCareRate Seller care rate, in percent
     *
     * @return float Collection amount
     */
    public function calculateCollectionAmount($priceNet, $sellerCareRate = 0)
    {
        if (!$priceNet || !$this->collection_rate) {
            return 0;
        }

        $collectionAmount = $priceNet * ($this->collection_rate / 100);

        if ($this->discount_rate > 0) {
            $collectionAmount = $priceNet * ( ($this->collection_rate - $this->discount_rate ) / 100);
        }
        if ($this->care_rate > 0) {
            $collectionAmount = $priceNet * (  $this->collection_rate / 100);
        }

        return $collectionAmount;
    }

    /**
     * Calculate discount amount from price (with tax)
     * @param float $priceBrut
     *
     * @return float
     */
    public function calculateDiscountAmount($priceBrut)
    {
        $discountAmount = 0;

        $priceBrutTTC = $priceBrut;
        //if ($this->tax_rate > 0) {
        //    $priceBrutTTC = $priceBrut + ($priceBrut * ($this->tax_rate / 100));
        //}
        if ($this->discount_rate > 0) {
            $discountAmount = $priceBrutTTC * ($this->discount_rate / 100);
        }
        return price2num($discountAmount, 2);
    }

    /**
     * Calculate seller care amount from price
     *
     * @param float $priceBrut
     *
     * @return float
     */
    public function calculateCareAmount($priceBrut)
    {

        $careAmount = 0;
        $priceBrutTTC = $priceBrut;
        //if ($this->tax_rate > 0) {
        //    $priceBrutTTC = $priceBrut * (1 + ($this->tax_rate / 100));
        // }
        if (!empty($this->care_rate)) {
            $careAmount = $priceBrutTTC * ($this->care_rate / 100);
        }
        return price2num($careAmount, 2);
    }

    /**
     * Calculate price brut from price net
     *
     * @param float $priceNet
     *
     * @return float
     */
    public function calculatePriceBrut($priceNet)
    {

        if ($this->discount_rate === 0 && $this->care_rate === 0) {
            return $priceNet;
        }
        if ($this->care_rate > 0) {
            $coef = (1 - $this->care_rate / 100);
            return $priceNet / $coef;
        }
        return $priceNet / (1 - ($this->discount_rate / 100));
    }

    /**
     * Check if a supplier sale exists for supplier bill line
     *
     * @param SupplierInvoiceLine $line Line object of supplier bill
     *
     * @return int <0 if KO, 0 if OK but not found, >0 if OK and exists
     */
    public static function isExistingSale($line)
    {
        global $db, $conf;

        if (!$line->id) {
            return -1;
        }

        $sql = "SELECT rowid";
        $sql .= " FROM " . MAIN_DB_PREFIX . $element;
        //$sql .= " WHERE entity IN (" . getEntity($element) . ")";
        $sql .= " WHERE fk_seller_invoice_line = " . $db->escape($line->id);
        
        dol_syslog(get_class() . "::isExistingSale", LOG_DEBUG);
        $resql = $db->query($sql);
        if ($resql) {
            $num = $db->num_rows($resql);
            if ($num > 0) {
                return 1;
            } else {
                return 0;
            }
        }
        return -1;
    }

    /**
     * Check if there is sales with no collection amount
     *
     * @return void
     */
    public function checkSalesWithNoCollection()
    {
        global $db, $conf;

        $sql = "SELECT rowid";
        $sql .= " FROM " . MAIN_DB_PREFIX . $this->table_element;
        //$sql .= " WHERE entity IN (" . getEntity($element) . ")";
        $sql .= " WHERE collection_rate IS NULL;";

        dol_syslog(get_class() . "::checkSalesWithNoCollection", LOG_DEBUG);
        $resql = $db->query($sql);
        if ($resql) {
            $num = $db->num_rows($resql);
            if ($num > 0) {
                return 1;
            } else {
                return 0;
            }
        } else {
            dol_print_error($db);
        }
        return -1;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function fixNullCollections()
    {
        global $db, $conf, $user;

        if (!class_exists('FactureLigne')) {
            require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
        }
        if (!class_exists('Categorie')) {
            require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
        }
        if (! class_exists('CollectionRate')) {
            dol_include_once('/marketplace/class/collectionrate.class.php');
        }
        $cat = new Categorie($db);
        $nbUpdated = 0;

        $this->db->begin();

        $sql = "SELECT t.rowid, t.fk_product, t.price, t.discount_rate, t.care_rate, t.fk_seller";
        $sql .= ", l.qty, l.subprice, l.remise_percent";
        $sql .= " FROM " . MAIN_DB_PREFIX . $this->table_element ." as t";
        $sql .= ", ". MAIN_DB_PREFIX . "facturedet as l";
        //$sql .= " WHERE entity IN (" . getEntity($element) . ")";
        $sql .= " WHERE t.collection_rate IS NULL";
        $sql .= " AND l.rowid = t.fk_customer_invoice_line";

        dol_syslog(get_class() . "::fixCollections", LOG_DEBUG);
        $result = $db->query($sql);
        if ($result) {
            $num = $db->num_rows($result);
            $i=0;
            while ($i < $num) {
                $obj = $db->fetch_object($result);
                $line = new FactureLigne($db);
                $line->total_ht = $obj->price;
                $line->qty = $obj->qty;
                $line->subprice = $obj->subprice;

                // Find collection rate
                $retroTx = new CollectionRate($db);
                $resCats = $cat->getListForItem($obj->fk_seller, 'supplier');
                // Search if existing amount for category
                if (is_array($resCats)) {
                    foreach ($resCats as $catarray) {
                        $retroTx->getCollectionRateForCategory($catarray['id']);
                        if ($retroTx->amount > 0) {
                            break;
                        }
                    }
                }

                if ($retroTx->amount > 0) {
                    $this->collection_rate = $retroTx->amount;
                }
                $this->discount_rate = $obj->discount_rate;
                $this->care_rate = $obj->care_rate;

                if (!empty($conf->global->MARKETPLACE_USE_DISCOUNT_AS_CARE_RATE)) {
                    $this->care_rate = $this->discount_rate;
                    $this->discount_rate = 0;
                }

                // Calcul retrocession amount
                $retroamount = $this->calculateRetrocessionAmountForLine($line, $this->care_rate);

                // And collection amount
                $collectionAmount = $this->calculateCollectionAmountForLine($line, $this->care_rate);

                $priceBrut = $this->calculatePriceBrut($line->total_ht);
                $careAmount = $this->calculateCareAmount($priceBrut);

                // Update line
                $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element;
                $sql .= ' SET collection_rate='. $retroTx->amount . ', fk_user_modif='.$user->id;
                $sql .= ", retrocession_amount='" . $retroamount ."'";
                $sql .= ", collection_amount='" . $collectionAmount ."'";
                $sql .= ", discount_rate='" . $this->discount_rate . "'";
                $sql .= ", care_rate='" . $this->care_rate . "'";
                $sql .= ", care_amount='" . $careAmount . "'";
                $sql .= ' WHERE rowid =' . $obj->rowid;
                
                $resql = $this->db->query($sql);
                if ($resql) {
                    // Call trigger
                    //$result=$this->call_trigger('BILL_SUPPLIER_PAYED',$user);
                    //if ($result < 0) $error++;
                    // End call triggers

                    $nbUpdated++;
                } else {
                    $error++;
                    $this->error = $this->db->error();
                }

                $i++;
            }
        }
        if (!$error) {
            $this->db->commit();
            return $nbUpdated;
        } else {
            $this->db->rollback();
            return -1;
        }
    }
    /**
     * Initialise object with example values
     * Id must be 0 if object instance is a specimen
     *
     * @return void
     */
    public function initAsSpecimen()
    {
        $this->initAsSpecimenCommon();
    }


    /**
     * Action executed by scheduler
     * CAN BE A CRON TASK. In such a case, paramerts come from the schedule job setup field 'Parameters'
     *
     * @return int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
     */
    //public function doScheduledJob($param1, $param2, ...)
    public function doScheduledJob()
    {
        global $conf, $langs;

        //$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

        $error = 0;
        $this->output = '';
        $this->error='';

        dol_syslog(__METHOD__, LOG_DEBUG);

        $now = dol_now();

        $this->db->begin();

        // ...

        $this->db->commit();

        return $error;
    }

    /**
     * Process customer invoice to save marketplace sales.
     * For each lines of object, save a new sales into marketplace module with collection and retrocession amounts.
     *
     * @param User $user    User for action
     * @param Facture $invoice Invoice object
     * @param bool $forceInvoiceDate    True : use invoice date for sale creation date
     *
     * @return int < 0 if error, 0 if no sales created, > 0 : number of created entries for marketplace sales
     */
    public function processInvoice($user, $invoice, $forceInvoiceDate = false)
    {
        global $conf;

        if (!$invoice->id) {
            return 0;
        }

        $salesCreated = 0;

        dol_syslog(__METHOD__.': process invoice '.$invoice->id);

        if (!class_exists('ProductSeller')) {
            dol_include_once('/marketplace/class/productseller.class.php');
        }
        if (!class_exists('CollectionRate')) {
            dol_include_once('/marketplace/class/collectionrate.class.php');
        }
        if (!class_exists('Categorie')) {
            require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
        }

        $sellerProduct = new ProductSeller($this->db);
        $retroTx = new CollectionRate($this->db);
        $cat = new Categorie($this->db);

        foreach ($invoice->lines as $line) {
            //var_dump($line); exit;

            if (!$line->fk_product) {
                continue;
            }
            
            if ($line->special_code === "0") {
                $idSeller = $sellerProduct->getSellerIDForProduct($line->fk_product);
                if ($idSeller > 0) {
                    $supplierSale = new Sales($this->db);
                    $supplierSale->fk_seller = $idSeller;
                    $supplierSale->fk_product = $line->fk_product;

                    // Price HT with discount
//                    $supplierSale->price = $line->total_ht;
                    $supplierSale->price = $line->subprice * $line->qty; // [PH] Reduce rounding diff

                    $resCats = $cat->getListForItem($idSeller, 'supplier');
                    // Search if existing amount for category
                    if (is_array($resCats)) {
                        foreach ($resCats as $catarray) {
                            $retroTx->getCollectionRateForCategory($catarray['id']);
                            if ($retroTx->amount > 0) {
                                break;
                            }
                        }
                    }

                    // Seller take over
                    $supplierSale->care_rate = 0;
                    if (!empty($conf->global->MARKETPLACE_USE_DISCOUNT_AS_CARE_RATE)) {
                        $supplierSale->care_rate = $line->remise_percent;
                        $line->remise_percent = 0;
                    }

                    $supplierSale->care_amount = $supplierSale->calculateCareAmount($line->total_ht);
                    
                    // Discount amount
                    $supplierSale->discount_rate = $line->remise_percent;

                    $supplierSale->collection_rate = $retroTx->amount;

                    $supplierSale->collection_amount = $supplierSale->calculateCollectionAmountForLine($line, $supplierSale->care_rate);
                    //$supplierSale->retrocession_amount = $line->total_ht - $supplierSale->collection_amount;

                    // Collection / retrocession
                    $retroamount = $supplierSale->calculateRetrocessionAmountForLine($line, $supplierSale->care_rate);
                    $supplierSale->retrocession_amount = $retroamount;


                    // Taxes
                    $supplierSale->tax_rate = $line->tva_tx;
//                    $supplierSale->tax_total = $line->total_tva;
                    $supplierSale->tax_total = $line->subprice * $line->qty * ($line->tva_tx / 100); // [PH] Reduce rounding diff

                    // Invoices
                    $supplierSale->fk_customer_invoice = $line->fk_facture;
                    $supplierSale->fk_customer_invoice_line = $line->id;
                    //$supplierSale->fk_seller_invoice = '';
                    //$supplierSale->fk_seller_invoice_line = '';
                    if ($forceInvoiceDate) {
                        $supplierSale->date_creation = $invoice->date;
                    }

                    $supplierSale->status = 0;
                    $res = $supplierSale->create($user);
                    if ($res < 0) {
                        $error++;
                    } else {
                        $salesCreated++;
                    }
                } else {
                    $msgError = __METHOD__.': seller not found for product '.$line->fk_product;
                    $this->errors[] = $msgError;
                    dol_syslog($msgError, LOG_WARNING);
                }
            }
        }

        if ($error) {
            return -1 * $errors;
        } else {
            return $salesCreated;
        }
    }


    public function getSalesListForCustomerInvoice($fk_invoice)
    {
        $sql = "SELECT ";
        $sql.= " ts.rowid, ts.fk_seller, ts.fk_product, ts.price";
        $sql.= ", ts.discount_rate, ts.discount_amount";
        $sql. ", ts.care_amount, ts.care_rate";
        $sql.= ", ts.collection_rate, ts.collection_amount";
        $sql.= ", ts.tax_rate, ts.tax_total";
        $sql.= ", ts.retrocession_amount";
        $sql.= ", ts.fk_customer_invoice, ts.fk_customer_invoice_line";
        $sql.= ", ts.fk_seller_invoice, ts.fk_seller_invoice_line";
        $sql.= ", ts.date_creation";
        $sql.= ", i.qty";
        $sql .= ", f.facnumber";
        $sql.= " FROM ".MAIN_DB_PREFIX."marketplace_sales as ts";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX. "facturedet as i ON i.rowid=ts.fk_customer_invoice_line";
        $sql.= " INNER JOIN " . MAIN_DB_PREFIX . "facture as f ON f.rowid=ts.fk_customer_invoice";
        $sql.= " WHERE  fk_customer_invoice=". $fk_invoice . " AND status IN(0)";
        $sql.= " ORDER BY ts.date_creation ASC";

        $resql=$this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
           
            $arraySales = array();

            // Build an array to group sales by seller
            while ($i < $num) {
                $obj = $this->db->fetch_object($resql);
                
                $retroSale = array(
                    'id' =>$obj->rowid,
                    'fk_product' => $obj->fk_product,
                    'fk_seller' => $obj->fk_seller,
                    'price' => $obj->price,
                    'qty' => $obj->qty,
                    'discount_rate' => $obj->discount_rate,
                    'discount_amount' => $obj->discount_amount,
                    'care_rate' => $obj->care_rate,
                    'care_amount' => $obj->care_amount,

                    'collection_amount' => $obj->collection_amount,
                    'collection_rate' => $obj->collection_rate,
                    'retrocession_amount' => $obj->retrocession_amount,

                    'tax_total' => $obj->tax_total,
                    'tax_rate' => $obj->tax_rate,

                    'fk_customer_invoice' => $obj->fk_customer_invoice,
                    'fk_customer_invoice_line' => $obj->fk_customer_invoice_line,

                    'date_creation' => $obj->date_creation,

                    'facnumber' => $obj->facnumber,
                );
                $arraySales[] = $retroSale;
                
                $i++;
            }
            return $arraySales;
        } else {
            $this->error=$this->db->lasterror();
            return -1;
        }
    }
}

/**
 * Class SalesLine. You can also remove this and generate a CRUD class for lines objects.
 */
/*
class SalesLine
{
    // @var int ID
    public $id;
    // @var mixed Sample line property 1
    public $prop1;
    // @var mixed Sample line property 2
    public $prop2;
}
*/
