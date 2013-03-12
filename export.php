<?php

require('config.php');

if (!$user->rights->exportcompta->generate) accessforbidden();

$langs->load('main');
$langs->load('export-compta@export-compta');

if(isset($_POST['submitBtn'])) {
	$action = GETPOST('action');
	$type_export = GETPOST('type_export');
	$logiciel_export = GETPOST('logiciel_export');
	$dt_deb_time=dol_mktime(12, 0, 0, $_POST['dt_debmonth'], $_POST['dt_debday'], $_POST['dt_debyear']);
	$dt_fin_time=dol_mktime(12, 0, 0, $_POST['dt_finmonth'], $_POST['dt_finday'], $_POST['dt_finyear']);
	$dt_deb = date('Y-m-d', $dt_deb_time);
	$dt_fin = date('Y-m-d', $dt_fin_time);
} else {
	$dt_deb_time=strtotime('first day of last month');
	$dt_fin_time=strtotime('last day of last month');
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
				$export = new ExportComptaQuadratus();
				break;
			case 'sage':
				$export = new ExportComptaSage($db);
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

$list_type_export = array();
if($conf->facture->enabled) $list_type_export[] = 'ecritures_comptables_vente';
if($conf->fournisseur->enabled || $conf->ndfp->enabled) $list_type_export[] = 'ecritures_comptables_achat';
if($conf->facture->enabled) $list_type_export[] = 'reglement_tiers';
if($conf->banque->enabled) $list_type_export[] = 'ecritures_bancaires';

llxHeader('',$langs->trans('AccountancyExports'),'');

$form=new Form($db);

print_fiche_titre($langs->trans('AccountancyExportsInFormattedFile'));

?>
<form name="exportCompta" action="<?=$_SERVER['PHP_SELF'] ?>" method="POST">
	<input type="hidden" name="action" value="export" />
	<table width="100%" class="noborder">
		<tr class="liste_titre">
			<td colspan="2"><?php echo $langs->trans('FormatAndType') ?></td><br />
			<td colspan="3"><?php echo $langs->trans('Date') ?></td>
		</tr>
		<tr class="impair">
			<td><?php echo $langs->trans('ExportLogiciel') ?></td>
			<td>
				<?php echo $langs->trans($conf->global->EXPORT_COMPTA_LOGICIEL_EXPORT) ?>
			</td>
			<td><?php echo $langs->trans('StartDate') ?></td>
			<td>
				<?php $form->select_date($dt_deb_time,'dt_deb','','','',"exportCompta"); ?>
			</td>
			<td rowspan="2">
				<input type="submit" class="button" name="submitBtn" value="<?php echo $langs->trans('DoExport') ?>" />
			</td>
		</tr>	
		<tr class="impair">
			<td><?php echo $langs->trans('ExportType') ?></td>
			<td>
				<select name="type_export" class="flat">
					<?php foreach ($list_type_export as $type) { ?>
					<option value="<?php echo $type ?>" <?php echo ($type_export == $type) ? 'selected' : ''; ?>><?php echo $langs->trans($type) ?></option>
					<?php } ?>
				</select>
			</td>
			<td><?php echo $langs->trans('EndDate') ?></td>
			<td>
				<?php $form->select_date($dt_fin_time,'dt_fin','','','',"exportCompta"); ?>
			</td>
		</tr>
	</table>
</form>
<?
echo '<div style="background-color: #ffffff; text-align: center;"><font style="font-family: monospace; font-size: 8px;">';
print strtr($fileContent, array("\r\n" => '<br>', ' ' => '&nbsp;'));
echo '</font></div>';

dol_htmloutput_errors($error);

// End of page
$db->close();
llxFooter('$Date: 2011/07/31 22:21:57 $ - $Revision: 1.19 $');
?>
