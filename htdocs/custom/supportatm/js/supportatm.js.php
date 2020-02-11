<?php

    require('../config.php');
/*
	if($user->rights->supportatm->read) {
	    $url = $_SERVER['HTTP_REFERER'];
	    $info = "URL : ".$url;
	    $info.="\nUtilisateur : ".$user->login;
	
	    $url_support ="http://support.atm-consulting.fr/bug_report_page.php?summary=".urlencode("Saisissez une résumé simple de votre problème")."&description=".urlencode("Donnez nous autant d'informations que possible.")
	        ."&additional_info=".urlencode($info)."&project_id=".$conf->global->SUPPORTATM_PROJECTID
			
		?>
		$(document).ready(function() {
		   
		   $('body').append('<div id="support_atm_popup"><div class="header">ATM Support</div></div>');
		    
		   $('#support_atm_popup').append('<div class="content"></div>');
		   $('#support_atm_popup div.content').append('Téléphone : <strong>09 77 19 50 69</strong> <br />'); 
		   $('#support_atm_popup div.content').append('Mail : <strong><a href="mailto:support@atm-consulting.fr?title=Demande de support&body=<?php echo urlencode($info) ?>">support@atm-consulting.fr</a></strong> <br />'); 
		   $('#support_atm_popup div.content').append('Accès web : <strong><a href="<?php echo $url_support; ?>" target="_blank">Support</a></strong> <br />'); 
		   $('#support_atm_popup div.content').append('<small>(du lundi au vendredi, hors jours fériés, de 9h 12h et de 14h à 17h)</small> <br />'); 
		  
		   $('#support_atm_popup').mouseover(function() {
		       $("#support_atm_popup").css("right","-5px" );
		   
		   });
		    
		   $('#support_atm_popup').mouseout(function() {
		       $("#support_atm_popup").css("right","" );
		   });
		    
		});
		
		<?php    		
	}

*/
