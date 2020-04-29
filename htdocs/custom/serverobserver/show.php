<?php

	require 'config.php';
	
	llxHeader();
	dol_fiche_head();
	
	$societe = new Societe($db);
	
	if($societe->fetch(GETPOST('id'))>0) {
		
		if(function_exists('dol_banner_tab')) {
			dol_banner_tab($societe, 'socid', '', ($user->societe_id?0:1), 'rowid', 'nom');
		}
		else{
			echo $societe->getNomUrl(1).'<br />';
		}

		$TData = json_decode(file_get_contents($societe->array_options['options_serverobserverchecker']));
		
		echo '<a href = "'.$TData->dolibarr->path->http.'" target="_blank">'.$TData->dolibarr->path->http.'</a>';
		
		foreach($TData as $title=>&$subdata) {
			
			echo '<table class="border" width="100%">';
			echo '<tr class="titre"><th colspan="2">'.$title.'</th></tr>';
			
			foreach($subdata as $key=>$data) {
				
				echo '<tr><td width="20%">'.$key.'<td>';
				
				if(is_object($data)) {
					foreach($data as $k=>$v) {
						
						echo '<strong>'. $k.' : </strong> '.$v.'<br />';
						
					}
					
				}
				else{
					echo $data;
				}
				
				echo '</tr>';
				
			}
			
			echo '</table><br /><br />';
			
		}
		
	}

	dol_fiche_end();	
	llxFooter();
