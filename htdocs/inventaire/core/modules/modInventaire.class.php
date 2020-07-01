<?php
/* <one line to give the program's name and a brief idea of what it does.>
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

include_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

// The class name should start with a lower case mod for Dolibarr to pick it up
// so we ignore the Squiz.Classes.ValidClassName.NotCamelCaps rule.
// @codingStandardsIgnoreStart
/**
 *  Description and activation class for module MyModule
 */
class modInventaire extends DolibarrModules
{
    // @codingStandardsIgnoreEnd
    /**
     * Constructor. Define names, constants, directories, boxes, permissions
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db     = $db;
        $this->numero = 537010;
        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class    = 'inventaire';
        $this->family          = "products";
        $this->module_position = 500;
        // Gives the possibility to the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
        //$this->familyinfo = array('myownfamily' => array('position' => '001', 'label' => $langs->trans("MyOwnFamily")));

        $this->name = preg_replace('/^mod/i', '', get_class($this));
        // Module description, used if translation string 'ModuleMyModuleDesc' not found (MyModue is name of module).
        $this->description = "inventaireDescription";
        // Used only if file README.md and README-LL.md not found.
        $this->descriptionlong = "MyModuleDescription (Long)";

        $this->editor_name = 'Artis Auxilium';
        $this->editor_url  = 'https://www.artis-auxilium.fr';
        $this->version     = '1.1.2';
        $this->const_name  = 'MAIN_MODULE_' . strtoupper($this->name);
        // Name of image file used for this module.
        // If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
        // If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
        $this->picto = 'inventaire@inventaire';

        // Defined all module parts (triggers, login, substitutions, menus, css, etc...)
        // for default path (eg: /mymodule/core/xxxxx) (0=disable, 1=enable)
        // for specific path of parts (eg: /mymodule/core/modules/barcode)
        // for specific css file (eg: /mymodule/css/mymodule.css.php)
        $this->module_parts = array();

        // Data directories to create when module is enabled.
        // Example: this->dirs = array("/mymodule/temp","/mymodule/subdir");
        $this->dirs = array();

        // Config pages. Put here list of php page, stored into mymodule/admin directory, to use to setup module.
        $this->config_page_url = array("index.php@inventaire");

        // Dependencies
        $this->hidden                  = false;
        $this->depends                 = array();
        $this->requiredby              = array();
        $this->conflictwith            = array();
        $this->phpmin                  = array(5, 6);
        $this->need_dolibarr_version   = array(5, 0);
        $this->langfiles               = array("inventaire@inventaire");
        $this->warnings_activation     = array();
        $this->warnings_activation_ext = array();

        if (!isset($conf->inventaire) || !isset($conf->inventaire->enabled)) {
            $conf->inventaire          = new stdClass();
            $conf->inventaire->enabled = 0;
        }
        $this->tabs = array();

        // Dictionaries
        $this->dictionaries = array();
        // Permissions
        $this->rights = array();

        $r                   = 0;
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'Voir les inventaire';
        $this->rights[$r][3] = 1;
        $this->rights[$r][4] = 'read';
        $this->rights[$r][5] = '';

        $r++;
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'Creer un inventaire';
        $this->rights[$r][3] = 1;
        $this->rights[$r][4] = 'create';
        $this->rights[$r][5] = '';

        $r++;
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'Supprimer un inventaire';
        $this->rights[$r][3] = 1;
        $this->rights[$r][4] = 'delete';
        $this->rights[$r][5] = '';

        $r++;
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'gérer un inventaire';
        $this->rights[$r][3] = 1;
        $this->rights[$r][4] = 'manage';
        $this->rights[$r][5] = '';

        // Main menu entries
        $this->menu     = array();
        $r              = 0;
        $this->menu[$r] = array(
            'fk_menu'  => 'fk_mainmenu=products,fk_leftmenu=stock',
            'type'     => 'left',
            'titre'    => 'Liste des inventaires',
            'mainmenu' => 'products',
            'leftmenu' => 'inventaire',
            'url'      => '/inventaire/',
            'langs'    => 'inventaire@inventaire',
            'position' => 100,
            'enabled'  => '$conf->inventaire->enabled',
            'perms'    => '$user->rights->inventaire->read',
            'target'   => '',
            'user'     => 2,
        );
        $r++;

        $this->menu[$r] = array(
            'fk_menu'  => 'fk_mainmenu=products,fk_leftmenu=stock',
            'type'     => 'left',
            'titre'    => 'Nouvel inventaire',
            'mainmenu' => 'products',
            'leftmenu' => 'inventaire',
            'url'      => '/inventaire/index.php?action=new',
            'langs'    => 'inventaire@inventaire',
            'position' => 100,
            'enabled'  => '$conf->inventaire->enabled',
            'perms'    => '$user->rights->inventaire->create',
            'target'   => '',
            'user'     => 0,
        );
        $r++;
    }

    /**
     *        Function called when module is enabled.
     *        The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     *        It also creates data directories
     *
     *      @param      string    $options    Options when enabling module ('', 'noboxes')
     *      @return     int                 1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        global $db;
        $sql = array(
            "CREATE TABLE IF NOT EXISTS `" . MAIN_DB_PREFIX . "inventaire` (
            `rowid` INT(11) NOT NULL AUTO_INCREMENT,
          	`name` VARCHAR(255) NOT NULL,
          	`date` DATETIME NULL DEFAULT NULL,
          	`status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
          	`tms` DATETIME NULL DEFAULT NULL,
          	`date_created` DATETIME NULL DEFAULT NULL,
          	`entity` INT(10) UNSIGNED NULL DEFAULT '1',
						PRIMARY KEY (`rowid`)
					)	ENGINE=InnoDB;",
            "CREATE TABLE IF NOT EXISTS `" . MAIN_DB_PREFIX . "inventairedet` (
            `rowid` INT(11) NOT NULL AUTO_INCREMENT,
          	`fk_product` INT(11) NULL DEFAULT NULL,
          	`barcode` VARCHAR(255) NULL DEFAULT NULL,
          	`fk_barcode_type` INT(11) NULL DEFAULT NULL,
          	`fk_warehouse` INT(11) NOT NULL,
          	`fk_inventory` INT(11) NOT NULL,
          	`qty_view` INT(11) NULL DEFAULT NULL,
          	`qty_stock` INT(11) NULL DEFAULT NULL,
          	`qty_regulated` INT(11) NULL DEFAULT NULL,
          	`pmp` DOUBLE(24,8) NULL DEFAULT '0.00000000',
          	`new_pmp` DOUBLE(24,8) NULL DEFAULT '0.00000000',
          	`pa` DOUBLE(24,8) NULL DEFAULT '0.00000000',
          	`tms` DATETIME NULL DEFAULT NULL,
          	`date_created` DATETIME NULL DEFAULT NULL,
          	`statut` TINYINT(4) NULL DEFAULT '0',
						PRIMARY KEY (`rowid`),
						INDEX `FK_" . MAIN_DB_PREFIX . "inventairedet_" . MAIN_DB_PREFIX . "inventaire` (`fk_inventory`),
						CONSTRAINT `FK_" . MAIN_DB_PREFIX . "inventairedet_" . MAIN_DB_PREFIX . "inventaire` FOREIGN KEY (`fk_inventory`) REFERENCES `" . MAIN_DB_PREFIX . "inventaire` (`rowid`) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE=InnoDB;",
            "CREATE TABLE IF NOT EXISTS `" . MAIN_DB_PREFIX . "inventaire_user` (
            `rowid` INT(11) NOT NULL AUTO_INCREMENT,
          	`name` VARCHAR(255) NOT NULL,
          	`fk_inventory` INT(11) NOT NULL,
          	`fk_user` INT(11) NULL DEFAULT NULL,
          	`regid` BLOB NULL,
          	`uuid` VARCHAR(255) NULL DEFAULT NULL,
          	`tms` DATETIME NULL DEFAULT NULL,
          	`date_created` DATETIME NULL DEFAULT NULL,
						PRIMARY KEY (`rowid`),
						INDEX `FK_" . MAIN_DB_PREFIX . "inventaire_user_" . MAIN_DB_PREFIX . "inventaire` (`fk_inventory`),
						CONSTRAINT `FK_" . MAIN_DB_PREFIX . "inventaire_user_" . MAIN_DB_PREFIX . "inventaire` FOREIGN KEY (`fk_inventory`) REFERENCES `" . MAIN_DB_PREFIX . "inventaire` (`rowid`) ON UPDATE CASCADE ON DELETE CASCADE
					)	ENGINE=InnoDB;",
            "CREATE TABLE IF NOT EXISTS `" . MAIN_DB_PREFIX . "inventaire_zones` (
            `rowid` INT(11) NOT NULL AUTO_INCREMENT,
          	`fk_user` INT(11) NULL DEFAULT NULL,
          	`fk_user_verif` INT(11) NULL DEFAULT NULL,
          	`name` VARCHAR(255) NOT NULL,
            `comment` VARCHAR(255) NULL DEFAULT NULL,
          	`fk_warehouse` INT(11) NOT NULL,
          	`fk_inventory` INT(11) NOT NULL,
          	`statut` TINYINT(4) NULL DEFAULT '0',
            `tms` DATETIME NULL DEFAULT NULL,
            `date_created` DATETIME NULL DEFAULT NULL,
						PRIMARY KEY (`rowid`),
						INDEX `FK_" . MAIN_DB_PREFIX . "inventaire_zones_" . MAIN_DB_PREFIX . "inventaire` (`fk_inventory`),
						CONSTRAINT `FK_" . MAIN_DB_PREFIX . "inventaire_zones_" . MAIN_DB_PREFIX . "inventaire` FOREIGN KEY (`fk_inventory`) REFERENCES `" . MAIN_DB_PREFIX . "inventaire` (`rowid`) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE=InnoDB;",
            "CREATE TABLE IF NOT EXISTS `" . MAIN_DB_PREFIX . "inventaire_zonedet` (
            `rowid` INT(11) NOT NULL AUTO_INCREMENT,
          	`fk_product` INT(11) NULL DEFAULT NULL,
          	`barcode` VARCHAR(255) NULL DEFAULT NULL,
            `fk_barcode_type` INT(11) NULL DEFAULT NULL,
            `comment` TEXT NULL,
          	`qty_view` INT(11) NULL DEFAULT NULL,
          	`qty_verified` INT(11) NULL DEFAULT NULL,
          	`qty_stock` INT(11) NULL DEFAULT NULL,
          	`statut` TINYINT(4) NULL DEFAULT '0',
          	`fk_zone` INT(11) NOT NULL,
          	`fk_warehouse` INT(11) NOT NULL,
          	`fk_inventory` INT(11) NOT NULL,
          	`tms` DATETIME NULL DEFAULT NULL,
          	`date_created` DATETIME NULL DEFAULT NULL,
          	PRIMARY KEY (`rowid`),
          	INDEX `FK_" . MAIN_DB_PREFIX . "inventairedet_" . MAIN_DB_PREFIX . "inventaire` (`fk_inventory`),
          	CONSTRAINT `FK_" . MAIN_DB_PREFIX . "inventairezonedet_" . MAIN_DB_PREFIX . "inventaire` FOREIGN KEY (`fk_inventory`) REFERENCES `" . MAIN_DB_PREFIX . "inventaire` (`rowid`) ON UPDATE CASCADE ON DELETE CASCADE
          ) ENGINE=InnoDB;",
        );
        $sqlt = "select comment from `". MAIN_DB_PREFIX . "inventaire_zonedet`";
        $res  = $this->db->query($sqlt);
        if (!$res) {
            $sql[] = "ALTER TABLE `" . MAIN_DB_PREFIX . "inventaire_zonedet`
                    ADD COLUMN `comment` TEXT NULL AFTER `fk_barcode_type`;";
        }
        return $this->_init($sql, $options);
    }

    /**
     * Function called when module is disabled.
     * Remove from database constants, boxes and permissions from Dolibarr database.
     * Data directories are not deleted
     *
     * @param      string    $options    Options when enabling module ('', 'noboxes')
     * @return     int                 1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        $sql = array();

        return $this->_remove($sql, $options);
    }
}
