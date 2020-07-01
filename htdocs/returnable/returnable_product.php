<?php
/* Copyright (C) 2019  Jean-François FERRY	<hello+jf@librethic.io>
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
 *	\file		returnable_product.php
 *	\ingroup	returnable
 *	\brief		Manage option for product
 */

$res = 0;
if (! $res && file_exists("../main.inc.php")) {
    $res = @include("../main.inc.php");
}
if (! $res && file_exists("../../main.inc.php")) {
    $res = @include("../../main.inc.php");
}
if (! $res && file_exists("../../../main.inc.php")) {
    $res = @include("../../../main.inc.php");
}

if (! $res) {
    die("Main include failed");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

require_once "class/returnable.class.php";

// Load translation files required by the page
$langs->load("returnable@returnable");

$id=GETPOST('id','int');
$ref=GETPOST('ref','alpha');
$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$cancel=GETPOST('cancel','alpha');
$key=GETPOST('key');
$parent=GETPOST('parent');

// Security check
if (! empty($user->societe_id)) $socid=$user->societe_id;
$fieldvalue = (! empty($id) ? $id : (! empty($ref) ? $ref : ''));
$fieldtype = (! empty($ref) ? 'ref' : 'rowid');
$result=restrictedArea($user,'produit|service',$fieldvalue,'product&product','','',$fieldtype);

$mesg = '';

$product = new Returnable($db);
$productid=0;
if ($id || $ref)
{
    $result = $product->fetch($id,$ref);
    $productid=$product->id;
}

/*
 * ACTIONS
 *
 */
// Action association d'un produit en tant qu'option
if ($action == 'add_prod' &&
$cancel <> $langs->trans("Cancel") &&
($user->rights->produit->creer || $user->rights->service->creer))
{
    $error=0;
    for($i=0;$i<$_POST["max_prod"];$i++)
    {
        // print "<br> : ".$_POST["prod_id_chk".$i];
        if($_POST["prod_id_chk".$i] != "")
        {
            if($product->addProductReturnal($id, $_POST["prod_id_".$i],$_POST["prod_qty_".$i],$_POST["prod_price_increase_".$i]) > 0)
            {
                $action = 'edit';
                $nb_add++;
            }
            else
            {
                $error++;
                $action = 're-edit';
                if ($product->error == "isFatherOfThis") $mesg = $langs->trans("ErrorAssociationIsFatherOfThis");
                else $mesg=$product->error;

                setEventMessage($mesg,'error');
            }
        }
        else
        {
            if ($product->delProductReturnal($id, $_POST["prod_id_".$i]) > 0)
            {
                $action = 'edit';
            }
            else
            {
                $error++;
                $action = 're-edit';
                setEventMessage($product->error,'error');
            }
        }
    }
    if (! $error)
    {
        setEventMessage($langs->trans('ReturnableProductSuccessAdd',$nb_add));
        header("Location: ".$_SERVER["PHP_SELF"].'?id='.$product->id);
        exit;
    }
}


/*
 * VIEW
 *
 */

// action recherche des produits par mot-cle et/ou par categorie
if ($action == 'search')
{
    $current_lang = $langs->getDefaultLang();

    $sql = 'SELECT DISTINCT p.rowid, p.ref, p.label, p.price, p.fk_product_type as type';
    if (! empty($conf->global->MAIN_MULTILANGS)) $sql.= ', pl.label as labelm, pl.description as descriptionm';
    $sql.= ' FROM '.MAIN_DB_PREFIX.'product as p';
    $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'categorie_product as cp ON p.rowid = cp.fk_product';
    if (! empty($conf->global->MAIN_MULTILANGS)) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product_lang as pl ON pl.fk_product = p.rowid AND lang='".($current_lang)."'";
    $sql.= ' WHERE p.entity IN ('.getEntity("product", 1).')';
    if ($key != "")
    {
        if (! empty($conf->global->MAIN_MULTILANGS))
        {
            $sql.= " AND (p.ref LIKE '%".$key."%'";
            $sql.= " OR pl.label LIKE '%".$key."%')";
        }
        else
        {
            $sql.= " AND (p.ref LIKE '%".$key."%'";
            $sql.= " OR p.label LIKE '%".$key."%')";
        }
    }
    if (! empty($conf->categorie->enabled) && ! empty($parent) && $parent != -1)
    {
        $sql.= " AND cp.fk_categorie ='".$db->escape($parent)."'";
    }
    $sql.= " ORDER BY p.ref ASC";

    $resql = $db->query($sql);
}

$productstatic = new Returnable($db);
$form = new Form($db);

llxHeader("", $langs->trans('PageTitleReturnableProduct'));
$form = new Form($db);


$head=product_prepare_head($product, $user);
$titre=$langs->trans("CardProduct".$product->type);
$picto=($product->type==1?'service':'product');
dol_fiche_head($head, 'returnable', $titre, 0, $picto);


if ($id || $ref)
{
    $result = $product->fetch($productid);
    if ($result)
    {
        $prodsfather = $product->getReturnalFathers(); //Parent Products
        $product->getReturnableArbo();
        print '<table class="border" width="100%">';

        print "<tr>";

        $nblignes=6;
        if ($product->isproduct() && ! empty($conf->stock->enabled)) $nblignes++;
        if ($product->isservice()) $nblignes++;

        // Reference
        print '<td width="25%">'.$langs->trans("Ref").'</td><td>';
        print $form->showrefnav($product,'ref','',1,'ref');
        print '</td></tr>';

        // Libelle
        print '<tr><td>'.$langs->trans("Label").'</td><td>'.$product->label.'</td>';
        print '</tr>';

        // List of subproducts
        $prods_arbo = $product->getArboEachReturnable();
        print '<tr><td>'.$langs->trans("ReturnableProductNumber").'</td><td>'.count($prods_arbo).'</td>';

        // Number of parent products
        print '<tr><td>'.$langs->trans("ReturnableParentProductsNumber").'</td><td>'.count($prodsfather).'</td>';



        print "</table>\n";
        dol_fiche_end();

        if (count($prods_arbo) > 0)
        {
            print_titre($langs->trans("ReturnableProductListForThisReference"));

            print '<table class="noborder " width="100%">';
            print '<tr class="liste_titre">';
            print '<th class="liste_titre" width="20%">'.$langs->trans("Ref").'</th>';
            print '<th class="liste_titre" width="40%">'.$langs->trans("Label").'</th>';
            print '<th class="liste_titre" width="20%" align="right">'.$langs->trans("Qty").'</th>';
            if (! empty($conf->stock->enabled)) print '<th class="liste_titre" align="center">'.$langs->trans("Stock").'</th>';
            print '</tr>';

            $var='';
            foreach($prods_arbo as $value)
            {
                $productstatic->id=$value['id'];
                $productstatic->type=$value['type'];
                $productstatic->ref=$value['ref'];
                $productstatic->label=$value['fullpath'];
                if (! empty($conf->stock->enabled)) $productstatic->load_stock();

                $var=!$var;
                print "\n<tr ".$bc[$var].">";
                print '<td>'.$productstatic->getNomUrl(1,'returnable').'</td>';
                print '<td>'.$productstatic->label.'</td>';
                print '<td align="right">'.$value['nb'].'</td>';
                if (! empty($conf->stock->enabled)) print '<td align="center"><b>'.$productstatic->stock_reel.'</b></td>';
                print '</tr>';
            }
            print '</table><br />';

        }

        if ($action <> 'edit' && $action <> 'search' && $action <> 're-edit')
        {
            if (count($prodsfather) > 0)
            {
                print_titre($langs->trans("ProductReturnablesParentList"));

                print '<table class="noborder" width="100%">';
                print '<tr class="liste_titre">';
                print '<th class="liste_titre" width="20%">'.$langs->trans("Ref").'</th>';
                print '<th class="liste_titre" width="40%">'.$langs->trans("Label").'</th>';
                print '<th class="liste_titre" width="20%" align="right">'.$langs->trans("Qty").'</th>';
                if (! empty($conf->stock->enabled)) print '<th class="liste_titre" align="center">'.$langs->trans("Stock").'</th>';
                print '</tr>';

                $var='';
                foreach($prodsfather as $value)
                {
                    $idprod= $value["id"];
                    $productstatic->id=$idprod;// $value["id"];
                    $productstatic->type=$value["fk_product_type"];
                    $productstatic->ref=$value['ref'];
                    $productstatic->label=$value['label'];

                    if (! empty($conf->stock->enabled)) $productstatic->load_stock();
                    $var=!$var;
                    print "\n<tr ".$bc[$var].">";
                    print '<td>'.$productstatic->getNomUrl(1,'returnable').'</td>';
                    print '<td>'.$productstatic->label.'</td>';
                    print '<td align="right">'.$value['nb'].'</td>';
                    if (! empty($conf->stock->enabled)) print '<td align="center">'.$langs->trans("Stock").' : <b>'.$productstatic->stock_reel.'</b></td>';
                    print '</tr>';
                }
                print '</table>';

            }
        }

        /* ************************************************************************** */
        /*                                                                            */
        /* Barre d'action                                                             */
        /*                                                                            */
        /* ************************************************************************** */

        print "\n<div class=\"tabsAction\">\n";

        if ($action == '')
        {
            if ($user->rights->produit->creer || $user->rights->service->creer)
            {
                print '<a class="butAction" href="'.dol_buildpath('/returnable/returnable_product.php',1).'?action=edit&amp;id='.$productid.'">'.$langs->trans("EditProductReturnable").'</a>';
            }
        }

        print "\n</div>\n";


        /*
         * Fiche en mode edition
        */
        if (($action == 'edit' || $action == 'search' || $action == 're-edit') && ($user->rights->produit->creer || $user->rights->service->creer))
        {


            print_fiche_titre($langs->trans("ProductToAddSearch"),'','');
            print '<form action="'.dol_buildpath('/returnable/returnable_product.php',1).'?id='.$id.'" method="post">';
            print '<table class="border" width="100%"><tr><td>';
            print '<table class="nobordernopadding">';

            print '<tr><td>';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            print $langs->trans("KeywordFilter").' &nbsp; ';
            print '</td>';
            print '<td><input type="text" name="key" value="'.$key.'">';
            print '<input type="hidden" name="action" value="search">';
            print '<input type="hidden" name="id" value="'.$id.'">';
            print '</td>';
            print '<td rowspan="'.$rowspan.'" valign="middle">';
            print '<input type="submit" class="button" value="'.$langs->trans("Search").'">';
            print '</td></tr>';
            if (! empty($conf->categorie->enabled))
            {
                print '<tr><td>'.$langs->trans("CategoryFilter").' &nbsp; </td>';
                print '<td>'.$form->select_all_categories(0, $parent).'</td></tr>';
            }

            print '</table>';
            print '</td></td></table>';
            print '</form>';

            if ($action == 'search')
            {
                print '<br>';
                print '<form action="'.dol_buildpath('/returnable/returnable_product.php',1).'?id='.$id.'" method="post">';
                print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
                print '<input type="hidden" name="action" value="add_prod">';
                print '<input type="hidden" name="id" value="'.$id.'">';
                print '<table class="nobordernopadding" width="100%">';
                print '<tr class="liste_titre">';
                print '<th class="liste_titre">'.$langs->trans("Ref").'</td>';
                print '<th class="liste_titre">'.$langs->trans("Label").'</td>';
                print '<th class="liste_titre" align="center">'.$langs->trans("AddDel").'</td>';
                print '<th class="liste_titre" align="right">'.$langs->trans("Quantity").'</td>';
                //print '<th class="liste_titre" align="right">'.$langs->trans("PriceIncrease").'</td>';
                print '</tr>';
                if ($resql)
                {
                    $num = $db->num_rows($resql);
                    $i=0;
                    $var=true;

                    if($num == 0) print '<tr><td colspan="4">'.$langs->trans("NoMatchFound").'</td></tr>';

                    while ($i < $num)
                    {
                        $objp = $db->fetch_object($resql);
                        if($objp->rowid != $id)
                        {
                            // check if a product is not already a parent product of this one
                            $prod_arbo=new Returnable($db);
                            $prod_arbo->id=$objp->rowid;
                            if ($prod_arbo->type==2 || $prod_arbo->type==3)
                            {
                                $is_pere=0;
                                $prod_arbo->get_sousproduits_arbo();
                                // associations sousproduits
                                $prods_arbo = $prod_arbo->get_arbo_each_prod();
                                if (count($prods_arbo) > 0)
                                {
                                    foreach($prods_arbo as $key => $value)
                                    {
                                        if ($value[1]==$id)
                                        {
                                            $is_pere=1;
                                        }
                                    }
                                }
                                if ($is_pere==1)
                                {
                                    $i++;
                                    continue;
                                }
                            }
                            $var=!$var;
                            print "\n<tr ".$bc[$var].">";
                            $productstatic->id=$objp->rowid;
                            $productstatic->ref=$objp->ref;
                            $productstatic->libelle=$objp->label;
                            $productstatic->type=$objp->type;

                            print '<td>'.$productstatic->getNomUrl(1,'',24).'</td>';
                            $labeltoshow=$objp->label;
                            if ($conf->global->MAIN_MULTILANGS && $objp->labelm) $labeltoshow=$objp->labelm;

                            print '<td>'.$labeltoshow.'</td>';
                            if($product->isReturnable($id, $objp->rowid))
                            {
                                $addchecked = ' checked="checked"';
                                $qty=$product->is_returnable_qty;
                                $value_price = $product->is_returnable_price_increased;
                            }
                            else
                            {
                                $addchecked = '';
                                $qty="1";
                                $value_price = $objp->price;

                            }
                            print '<td align="center"><input type="hidden" name="prod_id_'.$i.'" value="'.$objp->rowid.'">';
                            print '<input type="checkbox" '.$addchecked.'name="prod_id_chk'.$i.'" value="'.$objp->rowid.'"></td>';
                            print '<td align="right"><input type="text" size="3" name="prod_qty_'.$i.'" value="'.$qty.'"></td>';
                            //print '<td align="right">+ <input type="text" size="8" name="prod_price_increase_'.$i.'" value="'.price($value_price).'">'.$conf->currency.'</td>';
                            print '</tr>';
                        }
                        $i++;
                    }

                }
                else
                {
                    dol_print_error($db);
                }
                print '</table>';
                print '<input type="hidden" name="max_prod" value="'.$i.'">';

                if($num > 0)
                {
                    print '<br><center><input type="submit" class="button" value="'.$langs->trans("Add").'/'.$langs->trans("Update").'">';
                    print ' &nbsp; &nbsp; <input type="submit" class="button" value="'.$langs->trans("Cancel").'">';
                    print '</center>';
                }

                print '</form>';
            }

        }
    }
}


// End of page
llxFooter();
$db->close();
