<?php

	require 'config.php';
	
//ini_set('display_errors',1);	
	require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/categories.lib.php';
	
	dol_include_once('/exportcompta/class/link.class.php');
	
	$langs->load("categories");
	
	$action = GETPOST('action');
	$id = (int)GETPOST('id');
	$type = GETPOST('type');
	$linkid = (int)GETPOST('linkid');
		
	$PDOdb = new TPDOdb;
	
	switch ($action) {
		case 'add':
			
			$l=new TExportComptaLinkCat;
			
			$l->fk_category = $id;
			$l->type_category = $type;
			
			$l->save($PDOdb);
			
			_fiche($PDOdb,$id,$type);
			
			break;
		case 'delete':
			
			$l=new TExportComptaLinkCat;
			if($l->load($PDOdb, $linkid)) {
				$l->delete($PDOdb);	
			}
			
			_fiche($PDOdb,$id,$type);
			
			break;
			
		case 'save':
			
			if(!empty($_POST['TExportComptaLinkCat'])) {
				
				foreach($_POST['TExportComptaLinkCat'] as $linkid=>$data) {
					$l=new TExportComptaLinkCat;
					
					if($l->load($PDOdb, $linkid)) {
						$l->set_values($data);
						$l->save($PDOdb);	
					}
				}
				
			}
			
			_fiche($PDOdb,$id,$type);
			
			
			break;
			
		default:
		
			_fiche($PDOdb,$id,$type);
			
			break;
	}
	
	
function _fiche(&$PDOdb,$id,$type) {
	global $conf,$db,$langs,$user,$form;
		
	$object = new Categorie($db);
	$result=$object->fetch($id);
	$object->fetch_optionals($id,$extralabels);
	if ($result <= 0)
	{
		dol_print_error($db,$object->error);
		exit;
	}
			
	
	llxHeader("","",$langs->trans("Categories"));
	
	$title=$langs->trans("CustomersCategoryShort");
	$head = categories_prepare_head($object,Categorie::TYPE_CUSTOMER);
	
	dol_fiche_head($head, 'exportcompta', $title, 0, 'category');
	
	print '<table class="border" width="100%">';
	print '<tr><td width="20%" class="notopnoleft">';
	
	$ways = $object->print_all_ways(' &gt;&gt; ','',1 );
	print $langs->trans("Ref").'</td><td>';
	print '<a href="'.DOL_URL_ROOT.'/categories/index.php?leftmenu=cat&type='.$type.'">'.$langs->trans("Root").'</a> >> ';
	foreach ($ways as $way)
	{
		print $way."<br>\n";
	}
	
	
	print '</td></tr>';
	// Description
	
	print '</td></tr>';
	print '</table>';
	
	$formCore=new TFormCore('auto','formLink','post');
	echo $formCore->hidden('id', $id);
	echo $formCore->hidden('action', 'save');
	echo $formCore->hidden('type', $type);
	
	echo '<table class="border" width="100%"><tr class="liste_titre"><td>categorie produit</td><td>code compta</td><td></td></tr>';
	$TCategory = TExportComptaLinkCat::getCategoryProduct($PDOdb, $object->id,$type);
	if(!empty($TCategory)) {
		$form=new Form($db);
		foreach($TCategory as &$cat) {
				echo '<tr>
					<td>'.$form->select_all_categories(0,$cat->fk_category_product,'TExportComptaLinkCat['.$cat->getId().'][fk_category_product]').'</td>
					<td>'.$formCore->texte('', 'TExportComptaLinkCat['.$cat->getId().'][code_compta]', $cat->code_compta, 20,255).'</td>
					<td><a href="?id='.$object->id.'&type='.$type.'&linkid='.$cat->getId().'&action=delete">';
				    print img_delete();
				    print '</a></td>
				</tr>';
			
			    
			
		}
	}
	
	echo '</table>';
	
	echo '<div class="tabsAction">
		<a href="?id='.$id.'&type='.$type.'&action=add" class="butAction">'.$langs->trans('Add').'</a>
		<input class="butAction" type="submit" value="'.$langs->trans('Save').'" />
	</div>';
	
	$formCore->end();
	
	dol_fiche_end();
	
	
	llxFooter();
	
}
