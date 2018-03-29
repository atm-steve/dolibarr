<?php 

// Afficher les erreurs à l'écran
ini_set('display_errors', 1);
// Enregistrer les erreurs dans un fichier de log
ini_set('log_errors', 1);
// Nom du fichier qui enregistre les logs (attention aux droits à l'écriture)
//Afficher les erreurs et les avertissements
error_reporting(E_ALL);
echo "coucou";

$mysqli = new mysqli('localhost','root','iafscw45','dolibarr_new');
$fichier = 'CXProspectEudo.csv';
$csv = new SplFileObject($fichier); // On instancie l'objet SplFileObject
$csv->setFlags(SplFileObject::READ_CSV); // On indique que le fichier est de type CSV
$csv->setCsvControl(';'); // On indique le caractère délimiteur, ici c'est la virgule



foreach ($csv as $row) {
    list($idcx,$ideudo) = $row;
//Ici on recupere les composantes du texte 
  	
  	
		
//On recupere l'id du tiers en fonction du code Eudo
    	$sql = "select llx_societe_extrafields.fk_object from llx_societe_extrafields where llx_societe_extrafields.cegid='$ideudo'";
		$result = $mysqli->query($sql);
		$obj = $result->fetch_object();
		if ($obj ) {
		//echo "code".$ideudo.$obj->fk_object."<br>";
			
			$sqli= "INSERT INTO `llx_societe_commerciaux` (`fk_soc`, `fk_user`, `import_key`) VALUES ($obj->fk_object, $idcx, '66642')";
			
			//echo $sqli;
			//$resulti = $mysqli->query($sqli);
			if ($resulti)echo "ok<BR>";
			else echo "$sqli<BR>";
			//Insertion desactivée : sécurité
		}
	

	
}






?>