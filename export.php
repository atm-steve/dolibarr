<?php

require('config.php');

if (!$user->rights->exportcompta->generate) accessforbidden();

$langs->load('main');
$langs->load('exportcompta@exportcompta');
$langs->load('bills');

$error = '';

$logiciel_export = $conf->global->EXPORT_COMPTA_LOGICIEL_EXPORT;

dol_include_once('/exportcompta/class/export_'.$logiciel_export.'.class.php');
$className = 'TExportCompta'.ucfirst($logiciel_export);
$exp=new $className($db);

if(isset($_POST['submitBtn']) || isset($_POST['showMe'])) {
	$action = GETPOST('action');
	$type_export = GETPOST('type_export');
	$exp->set_date('dt_deb',$_REQUEST['dt_deb']);
	$exp->set_date('dt_fin',$_REQUEST['dt_fin']);
	$dt_deb = $exp->get_date('dt_deb','Y-m-d 00:00:00');
	$dt_fin = $exp->get_date('dt_fin','Y-m-d 23:59:59');
}

if(!empty($action) && $action == 'export') {	
	
	$fileContent = '';

	if(!empty($logiciel_export)) {
		
		try{
		    $addExportTimeToBill = isset($_REQUEST['showMe']) ? 0 : (int)GETPOST('addExportTimeToBill');
            
			$export=new $className($db, (int)GETPOST('exportAllreadyExported'), $addExportTimeToBill );
		}
		catch(Exception $e) {
			$error = $langs->trans('Error'). ' : ' . $langs->trans('UnknownExportLogiciel'). ' : ' . $logiciel_export;
		}
		
		if(!empty($export->filename)) {
			$fileName = $export->filename;
		}
		else{
			$fileName = $logiciel_export.$type_export.date('YmdHis').".".$conf->global->EXPORT_COMPTA_EXTENSION;
		}
		
		
		if(isset($export) && is_object($export)) {
			$formatvar = 'EXPORT_COMPTA_FORMAT_'.$type_export.'_'.$logiciel_export;
			$format = (!empty($conf->global->{$formatvar})) ? unserialize($conf->global->{$formatvar}) : '';

			switch ($type_export) {
				case 'ecritures_comptables_vente':
					$fileContent = $export->get_file_ecritures_comptables_ventes($format, $dt_deb, $dt_fin);
					break;
				case 'ecritures_comptables_achat':
					$fileContent = $export->get_file_ecritures_comptables_achats($format, $dt_deb, $dt_fin);
					break;
				case 'ecritures_comptables_ndf':
					$fileContent = $export->get_file_ecritures_comptables_ndf($format, $dt_deb, $dt_fin);
					break;
				case 'reglement_tiers':
					$fileContent = $export->get_file_reglement_tiers($format, $dt_deb, $dt_fin);
					break;
				case 'ecritures_comptables_banque':
					$fileContent = $export->get_file_ecritures_comptables_banque($format, $dt_deb, $dt_fin);
					break;
				case 'tiers':
					$fileContent = $export->get_file_tiers($format, $dt_deb, $dt_fin);
					break;
				case 'produits':
					$fileContent = $export->get_file_produits($format, $dt_deb, $dt_fin);
					break;
				default:
					$error = $langs->trans('Error'). ' : ' . $langs->trans('UnknownExportType'). ' : ' . $type_export;
					break;
			}
		}
		
	} else {
		$error = $langs->trans('Error'). ' : ' . $langs->trans('NoExportSelected');
	}
/*echo '<font style="font-family: Courier;">';
print nl2br($fileContent);
exit();*/
    if(isset($_REQUEST['showMe'])) {
        
        $Tab = explode("\n", $fileContent);
        
        print '<pre>';
        print $fileContent;
        print '</pre>';
        
        exit;
        
    }
    else if($fileContent != '') {
		$size = strlen($fileContent);
		
		header("Content-Type: application/force-download; name=\"$fileName\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: $size");
		header("Content-Disposition: attachment; filename=\"$fileName\"");
		header("Expires: 0");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		
		print $fileContent;
		
		exit();
	} else if(empty($error)) {
		$error = $langs->trans('Error'). ' : ' . $langs->trans('EmptyExport');
	}
}

/***************************************************
* PAGE
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans('AccountancyExports'),'');

$form = new TFormCore($_SERVER['PHP_SELF'], 'exportCompta');
print $form->hidden('action', 'export');

print_fiche_titre($langs->trans('AccountancyExportsInFormattedFile'));

?>
	<table width="100%" class="noborder">
		<tr class="liste_titre">
			<td colspan="2"><?php echo $langs->trans('FormatAndType') ?></td><br />
			<td colspan="3"><?php echo $langs->trans('Date') ?></td>
		</tr>
		<tr class="impair">
			<td><?php echo $langs->trans('ExportLogiciel') ?></td>
			<td>
				<?php echo $exp->TLogiciel[$conf->global->EXPORT_COMPTA_LOGICIEL_EXPORT] ?>
			</td>
			<td><?php echo $langs->trans('StartDate') ?></td>
			<td>
				<?php echo $form->calendrier('', 'dt_deb', $exp->get_date('dt_deb'), 12) ?>
			</td>
			<td>
                <input type="checkbox" name="exportAllreadyExported" value="1" /> <?php echo $langs->trans('exportAllreadyExported') ?>
                <br /> <input type="checkbox" name="addExportTimeToBill" value="1" checked="checked" /> <?php echo $langs->trans('addExportTimeToBill') ?>
				
			</td>
		</tr>	
		<tr class="impair">
			<td><?php echo $langs->trans('ExportType') ?></td>
			<td>
				<?php echo $form->combo('', 'type_export', $exp->TTypeExport, $type_export) ?>
			</td>
			<td><?php echo $langs->trans('EndDate') ?></td>
			<td>
				<?php echo $form->calendrier('', 'dt_fin', $exp->get_date('dt_fin'), 12) ?>
			</td>
			<td>
				<input type="submit" class="button" name="submitBtn" value="<?php echo $langs->trans('DoExport') ?>" />
				<input type="submit" class="button" name="showMe" value="<?php echo $langs->trans('Show') ?>" />
			</td>

		</tr>
	</table>
<?php

$form->end();

echo '<div style="background-color: #ffffff; text-align: center;"><font style="font-family: monospace; font-size: 8px;">';
print strtr($fileContent, array("\r\n" => '<br>', ' ' => '&nbsp;'));
echo '</font></div>';

dol_htmloutput_errors($error);

// End of page
$db->close();
llxFooter();
?>
