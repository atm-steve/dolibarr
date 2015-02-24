<?php


function exportcompta_admin_prepare_head()
{
	global $langs, $conf, $user;
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/exportcompta/admin/admin.php', 1);
	$head[$h][1] = $langs->trans("Admin");
	$head[$h][2] = 'config';
	$h++;
	
	$head[$h][0] = dol_buildpath('/exportcompta/admin/format.php', 1);
	$head[$h][1] = $langs->trans("Formats");
	$head[$h][2] = 'format';
	$h++;

	return $head;
}

?>