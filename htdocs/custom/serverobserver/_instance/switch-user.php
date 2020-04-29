<?php

	if(is_file('../main.inc.php'))$dir = '../';
	else  if(is_file('../../../main.inc.php'))$dir = '../../../';
	else  if(is_file('../../../../main.inc.php'))$dir = '../../../../';
	else  if(is_file('../../../../../main.inc.php'))$dir = '../../../../../';
	else $dir = '../../';

	require $dir.'main.inc.php';
	
	if(empty($user->admin))exit('NotAdmin');
	
	$action=GETPOST('action');
	if($action==='switch') {
		
		 $u=new User($db);
		 $u->fetch(GETPOST('userid'));
		
		 $_SESSION["dol_login"] = $u->login;
		
		 header('location:'.dol_buildpath('/',1));
		
	}
	
	llxHeader();
	
	?>
	<form action="?" name="f1" >
		<input type="hidden" name="action" value="switch" />
	<?php
	$form=new Form($db);
	$form->select_users();
	
	?>
		<input type="submit" name="switch" value="Switch" />
	</form>
	<?php
	
	llxFooter();
