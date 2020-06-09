<?php 

// Afficher les erreurs à l'écran
ini_set('display_errors', 1);
// Enregistrer les erreurs dans un fichier de log
ini_set('log_errors', 1);
// Nom du fichier qui enregistre les logs (attention aux droits à l'écriture)
//Afficher les erreurs et les avertissements
error_reporting(E_ALL);


$mysqli = new mysqli('localhost','root','iafscw45','dolibarr_new');
$fichier = 'Histoprospect.csv';

$csv = new SplFileObject($fichier); // On instancie l'objet SplFileObject
$csv->setFlags(SplFileObject::READ_CSV); // On indique que le fichier est de type CSV
$csv->setCsvControl(';'); // On indique le caractère délimiteur, ici c'est la virgule



foreach ($csv as $row) {
    list($codeCegid, $historique) = $row;
//Ici on recupere les composantes du texte 
  	
  	if ($historique) {
		
		$historique=nl2br($historique);
		
		$histodecoupe = explode("<strong>", $historique);
		
		
		$nb=count($histodecoupe); 
		if ($nb<>0)
		for($i=0;$i<$nb;$i++) { 
		
			$histodecoupe[$i]=strip_tags($histodecoupe[$i]);
			//echo $histodecoupe[$i]."<br>";
			if (preg_match("#^[0-9][0-9]/[0-9][0-9]/[0-9][0-9]#",$histodecoupe[$i])){
				$date[$i]=substr($histodecoupe[$i],0,10);
				$note[$i]=substr($histodecoupe[$i],10,strlen($histodecoupe[$i]));
			}
			else {
				$date[$i]="01/01/1900";
				$note[$i]=$histodecoupe[$i];
			}
				
			//echo "histodecoupe: ".$histodecoupe[$i]."<br>---"; 
			//echo $codeCegid." dates: ".$date[$i]." note: ".$note[$i]."<br>";
			
			//On recupere l'id du tiers en fonction du code client Cegid
			$sql = "select llx_societe_extrafields.fk_object from llx_societe_extrafields where llx_societe_extrafields.cegid='$codeCegid'";
			$result = $mysqli->query($sql);
			$obj = $result->fetch_object();
			if ($obj ) {
					
				$sqli= "INSERT INTO `llx_actioncomm` (`ref_ext`, `entity`, `datep`, `datep2`, `fk_action`, `code`, `datec`, `tms`, 
				`fk_user_author`, `fk_user_mod`, `fk_project`, `fk_soc`, `fk_contact`, `fk_parent`, `fk_user_action`, `fk_user_done`, 
				`transparency`, `priority`, `fulldayevent`, `punctual`, `percent`, `location`, `durationp`, `label`, `note`, `email_subject`, 
				`email_msgid`, `email_from`, `email_sender`, `email_to`, `email_tocc`, `email_tobcc`, `errors_to`, `recurid`, `recurrule`, 
				`recurdateend`, `fk_element`, `elementtype`, `import_key`, `extraparams`) 
				VALUES (NULL, '1', '".date('Y-m-d H:i:s', strtotime($date[$i]))."', '".date('Y-m-d H:i:s', strtotime($date[$i]))."', 5, 'AC_RDV', $date[$i], CURRENT_TIMESTAMP, 1, 1, NULL, $obj->fk_object, NULL, '0', 
				1, NULL, 1, 0, '0', '1', '100', NULL, NULL, '".addslashes(substr($note[$i], 0, 100))."', '".addslashes($note[$i])."', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 66684, NULL)";
				
				echo "$sqli <br>";
				//$resulti = $mysqli->query($sqli);
				//if ($resulti) echo "ok<br>";
				//else echo $sqli;	
					
			}
		
		
	} 
		
		
	
	
}	



	
	
	
}





/*preg_match_all("#<strong>(.*)</strong>#Ui", $historique, $matchesdate,PREG_PATTERN_ORDER);
		$nb=count($matchesdate[0]); 
		if ($nb<>0)
		for($i=0;$i<$nb;$i++) { 
			echo "Date: ".$matchesdate[0][$i]."<br>"; 
		} 
	

	
	   preg_match_all("#</strong>(.*)<strong>#Ui", $historique, $matchesnote,PREG_PATTERN_ORDER);
   
	   $nb=count($matchesnote[0]); 
		for($i=0;$i<$nb;$i++) { 
			echo "note: ".$matchesnote[0][$i]."<BR>"; 
		} 
	
	  */

?>