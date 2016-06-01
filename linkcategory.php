<?php

	require 'config.php';
	
	
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
	
	
	echo '<table class="border" width="100%"><tr class="liste_titre"><td>categorie produit</td><td>code compta</tr>';
	$TCategory = TExportComptaLinkCat::getCategoryProduct($PDOdb, $object->id,$type);
	if(!empty($TCategory)) {
		
		foreach($TCategory as &$cat) {
			
				
			
			    print '<a href="?id='.$object->id.'&commid='.$u->id.'&action=delete">';
			    print img_delete();
			    print '</a>';
			
			
		}
	}
	
	echo '</table>';
	
	echo '<div class="tabsAction">
		<a href="?id='.$id.'&type='.$type.'&action=add" class="butAction">'.$langs->trans('Add').'</a>
	</div>';
	
	dol_fiche_end();
	
	
	llxFooter();
	
}