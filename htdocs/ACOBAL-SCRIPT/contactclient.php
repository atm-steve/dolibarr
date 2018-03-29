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
$fichier = 'contactsprospects.csv';
$csv = new SplFileObject($fichier); // On instancie l'objet SplFileObject
$csv->setFlags(SplFileObject::READ_CSV); // On indique que le fichier est de type CSV
$csv->setCsvControl(';'); // On indique le caractère délimiteur, ici c'est la virgule
//Ligne;PmId;Metiers;Nom;Prenom;civilité;Email;Portable;Tel


foreach ($csv as $row) {
    list($ligne,$pmid,$metier,$nom,$prenom,$civ,$email,$port,$tel) = $row;
//Ici on recupere les composantes du texte 
  	
  
		
//On recupere l'id du tiers en fonction du code client Cegid
    	$sql = "select llx_societe_extrafields.fk_object from llx_societe_extrafields where llx_societe_extrafields.cegid='$pmid'";
		$result = $mysqli->query($sql);
		$obj = $result->fetch_object();
		if ($obj ) {
		//echo "code".$codeCegid.$obj->fk_object."<br>";
			
			$sqli= "INSERT INTO llx_socpeople 
			(datec, tms, fk_soc, entity, ref_ext, civility, lastname, firstname, address,zip, town, 
			fk_departement, fk_pays, birthday, poste, phone, phone_perso, phone_mobile, fax, email, jabberid,
			skype, photo, no_email, priv, fk_user_creat, fk_user_modif, note_private, note_public, default_lang, canvas, import_key, statut) 
			VALUES
			(CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, $obj->fk_object, '1', NULL, '$civ', '$nom', '$prenom', NULL, NULL, NULL, NULL, '0', NULL, '$metier', '$tel', 
			NULL, '$port', NULL, '$email', NULL, NULL, NULL, '0', '0', '1', '1', 'EudonetRecup', NULL, NULL, NULL, 66684, '1') ";
			
			//echo $sqli;
			//$resulti = $mysqli->query($sqli);
			if ($resulti) echo "OK<BR>";
			else echo "$sqli<br>";
			//Insertion desactivée : sécurité
		}
	
	
	
}






?>