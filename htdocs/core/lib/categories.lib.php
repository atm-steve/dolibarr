<?php
/* Copyright (C) 2011 Regis Houssin  <regis.houssin@inodbox.com>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 * or see https://www.gnu.org/
 */

/**
 *	\file       htdocs/core/lib/categories.lib.php
 *	\brief      Ensemble de fonctions de base pour le module categorie
 *	\ingroup    categorie
 */

/**
 * Prepare array with list of tabs
 *
 * @param   Object	$object		Object related to tabs
 * @param	string	$type		Type of category
 * @return  array				Array of tabs to show
 */
function categories_prepare_head(Categorie $object, $type)
{
	global $langs, $conf, $user;

	// Load translation files required by the page
	$langs->loadLangs(array('categories', 'products'));

	$h = 0;
	$head = array();

	$head[$h][0] = DOL_URL_ROOT.'/categories/viewcat.php?id='.$object->id.'&amp;type='.$type;
	$head[$h][1] = $langs->trans("Category");
	$head[$h][2] = 'card';
	$h++;

	$head[$h][0] = DOL_URL_ROOT.'/categories/photos.php?id='.$object->id.'&amp;type='.$type;
	$head[$h][1] = $langs->trans("Photos");
	$head[$h][2] = 'photos';
	$h++;

	if (!empty($conf->global->MAIN_MULTILANGS))
	{
		$head[$h][0] = DOL_URL_ROOT.'/categories/traduction.php?id='.$object->id.'&amp;type='.$type;
		$head[$h][1] = $langs->trans("Translation");
		$head[$h][2] = 'translation';
		$h++;
	}

	$head[$h][0] = DOL_URL_ROOT.'/categories/info.php?id='.$object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname);   												to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'categories_'.$type);

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'categories_'.$type, 'remove');

	return $head;
}


/**
 * Prepare array with list of tabs
 *
 * @return  array				Array of tabs to show
 */
function categoriesadmin_prepare_head()
{
	global $langs, $conf, $user;

	$langs->load("categories");

	$h = 0;
	$head = array();

	$head[$h][0] = DOL_URL_ROOT.'/categories/admin/categorie.php';
	$head[$h][1] = $langs->trans("Setup");
	$head[$h][2] = 'setup';
	$h++;

	$head[$h][0] = DOL_URL_ROOT.'/categories/admin/categorie_extrafields.php';
	$head[$h][1] = $langs->trans("ExtraFieldsCategories");
	$head[$h][2] = 'attributes_categories';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname);   												to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'categoriesadmin');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'categoriesadmin', 'remove');

	return $head;
}

/* ************************* SPÉ VET COMPANY { *********************** */
function getCategoryAccountancyCode($id, $type)
{
    global $db;

    if (empty ($id) || empty ($type)) return array(-1, '');

    $motherof = array();
    // Load array[child]=parent
    $sql = "SELECT c.fk_parent as id_parent, c.rowid as id_son, cc.accountancy_code_sell as code_sell";
    $sql.= " FROM ".MAIN_DB_PREFIX."categorie as c";
    $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."categorie as cc ON c.fk_parent = cc.rowid";
    $sql.= " WHERE c.fk_parent != 0";
    $sql.= " AND c.entity IN (".getEntity('category').")";

    $resql = $db->query($sql);
    if ($resql)
    {
        while ($obj= $db->fetch_object($resql))
        {
            $motherof[$obj->id_son]['parent']=$obj->id_parent;
            $motherof[$obj->id_son]['parent_accountancy_code_sell'] = $obj->code_sell;
        }
    }

    require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
    $c = new Categorie($db);
    $prodcat = $c->containing($id, $type);

    if(empty($prodcat)) return array(-2, '');

    $cat_order = array();
    foreach ($prodcat as $cat)
    {
        $cat_order[$cat->id]['label'] = $cat->label;
        $cat_order[$cat->id]['profondeur'] = 0;
        $cat_order[$cat->id]['accountancy_code_sell'] = $cat->accountancy_code_sell;

        if (!empty($cat->fk_parent) && empty($cat->accountancy_code_sell))
        {
            $id_search = $cat->id;
            $accountancy_code_sell='';
            while (array_key_exists($id_search, $motherof))
            {
                // var_dump($motherof[$id_search], empty($cat_order[$cat->id]['accountancy_code_sell']), !empty($motherof[$id_search]['parent_accountancy_code_sell']));
                if (empty($cat_order[$cat->id]['accountancy_code_sell']) && !empty($motherof[$id_search]['parent_accountancy_code_sell']))
                {
                    $cat_order[$cat->id]['accountancy_code_sell'] = $motherof[$id_search]['parent_accountancy_code_sell'];
                }
                $cat_order[$cat->id]['profondeur']++;
                $id_search = $motherof[$id_search]['parent'];
            }
        }
    }

    $profondeur = 0;
    foreach ($cat_order as $cat)
    {
        if (!empty($cat['accountancy_code_sell']))
        {
            if ($cat['profondeur'] > $profondeur) $suggest = $cat;
            elseif ($cat['profondeur'] == $profondeur) $second_suggest = $cat;
        }
    }

    if (!empty($suggest)) return array($suggest['accountancy_code_sell'], $suggest['label']);
    elseif (!empty($second_suggest)) return array($second_suggest['accountancy_code_sell'], $second_suggest['label']);
    else return array('','');
}
/* ************************* SPÉ VET COMPANY } *********************** */
