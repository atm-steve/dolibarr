<?php

require('config.php');
require('./class/export.class.php');

if (!$user->rights->exportcompta->generate) accessforbidden();

$langs->load('main');
$langs->load('export-compta@export-compta');

$exp = new TExportCompta($db);

if(isset($_POST['submitBtn'])) {
	$action = GETPOST('action');
	$type_export = GETPOST('type_export');
	$logiciel_export = GETPOST('logiciel_export');
	$exp->set_date('dt_deb',$_REQUEST['dt_deb']);
	$exp->set_date('dt_fin',$_REQUEST['dt_fin']);
	$dt_deb = $exp->get_date('dt_deb','Y-m-d');
	$dt_fin = $exp->get_date('dt_fin','Y-m-d');
}

$langs->load('bills');
$error = '';
$logiciel_export = $conf->global->EXPORT_COMPTA_LOGICIEL_EXPORT;

if(!empty($action) && $action == 'export') {	
	$fileName = $logiciel_export.date('YmdHis').".txt";
	$fileContent = '';
	
	if(!empty($logiciel_export)) {
		dol_include_once('/export-compta/class/export_'.$logiciel_export.'.class.php');
		switch ($logiciel_export) {
			case 'quadratus':
				$export = new TExportComptaQuadratus();
				break;
			case 'sage':
				$export = new TExportComptaSage($db);
				break;
			default:
				$error = $langs->trans('Error'). ' : ' . $langs->trans('UnknownExportLogiciel'). ' : ' . $logiciel_export;
				break;
		}
		
		if(isset($export) && is_object($export)) {
			$formatvar = 'EXPORT_COMPTA_FORMAT_'.$type_export.'_'.$logiciel_export;
			$format = unserialize($conf->global->{$formatvar});
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
				default:
					$error = $langs->trans('Error'). ' : ' . $langs->trans('UnknownExportType'). ' : ' . $type_export;
					break;
			}
		}
		
	} else {
		$error = $langs->trans('Error'). ' : ' . $langs->trans('NoExportSelected');
	}

	if($fileContent != '') {
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
			<td><?= $langs->trans('ExportLogiciel') ?></td>
			<td>
				<?= $exp->TLogiciel[$conf->global->EXPORT_COMPTA_LOGICIEL_EXPORT] ?>
			</td>
			<td><?= $langs->trans('StartDate') ?></td>
			<td>
				<?= $form->calendrier('', 'dt_deb', $exp->get_date('dt_deb'), 12) ?>
			</td>
			<td rowspan="2">
				<input type="submit" class="button" name="submitBtn" value="<?php echo $langs->trans('DoExport') ?>" />
			</td>
		</tr>	
		<tr class="impair">
			<td><?php echo $langs->trans('ExportType') ?></td>
			<td>
				<?= $form->combo('', 'type_export', $exp->TTypeExport, $type_export) ?>
			</td>
			<td><?php echo $langs->trans('EndDate') ?></td>
			<td>
				<?= $form->calendrier('', 'dt_fin', $exp->get_date('dt_fin'), 12) ?>
			</td>
		</tr>
	</table>
<?

$form->end();

echo '<div style="background-color: #ffffff; text-align: center;"><font style="font-family: monospace; font-size: 8px;">';
print strtr($fileContent, array("\r\n" => '<br>', ' ' => '&nbsp;'));
echo '</font></div>';

dol_htmloutput_errors($error);

// End of page
$db->close();
llxFooter();
?>
