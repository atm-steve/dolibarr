<?php
// Afficher les erreurs à lécran
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__file__) . '/log_error_EDI.txt');
error_reporting(E_ALL);
$link=mysqli_connect("localhost", "root", "iafscw45", "dolibarr_test");
$EDI_IN_DIR="/home/acobal/EDI/IN/";
$DOC_DIR="/var/www/dolibarrtest/documents/commande/";
$EDI_INA_DIR="/home/acobal/EDI/IN/Archive/";

if ($handle = opendir($EDI_IN_DIR)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            echo "fichier $entry";
 			//Ouvre le fichier et retourne un tableau contenant une ligne par élément
			$filename=$entry;
			$lines = file("$EDI_IN_DIR$filename");
			//On parcourt le tableau $lines et on affiche le contenu de chaque ligne précédée de son numéro
			// au debut pas de produits
			$produit=0;
			foreach ($lines as $lineNumber => $lineContent){
				//echo $lineNumber,' ',$lineContent,'<BR>';
				preg_match("#T1000(.*)#",$lineContent,$message);
				preg_match("#C0500(.*)#",$lineContent,$destinataire);
				//preg_match("#C0100(.*)#",$lineContent,$acheteur);
				//preg_match("#C0500(.*)#",$lineContent,$destinataire);
				preg_match("#C0300(.*)#",$lineContent,$transporteur);
				preg_match("#A0900(.*)#",$lineContent,$produit_refcmd);
				preg_match("#G0601(.*)#",$lineContent,$produit_qte_delai_carte);
				preg_match("#H0200(.*)#",$lineContent,$porte);
	
				if ($message) {
					//echo $message[1];
					$date_cmd=substr($message[1],85,8);
					//echo "Date message: ".$date_cmd;echo "<BR>";	
				}
				if ($destinataire) {
					//echo $destinataire[1];
					$siret_dest=substr($destinataire[1],5,14);
					//echo "Clef EDI destinataire: ".$siret_dest;echo "<BR>";
					$sql= "SELECT * FROM `llx_societe_extrafields` WHERE `clefedi` LIKE ".$siret_dest;
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Pb de requete Clef EDI définie".$sql);
					If (!mysqli_num_rows($result))  { 
						die("Pas de clef EDI définie $siret_dest");
					}
					$obj_dest = mysqli_fetch_object($result);
					
					//echo $obj_dest->fk_object;echo "<BR>";
				}
				if ($transporteur) {
					//echo $transporteur[1];echo "<BR>";
				}
				//A partir d'ici on peut créer l'entete de commande 	
				if ($produit_refcmd) {
					//La référence produit et la reférence commande du client 
					$produit=0;
					//ADV en charge du client ?
					//$sql= "SELECT fk_user FROM `llx_societe_commerciaux` WHERE ";
					//echo "<BR>";
					//$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Q=".$sql);
					//$obj_user= mysqli_fetch_object($result);
					
					//Insertion commande
					$sql="SHOW TABLE STATUS LIKE 'llx_commande'";
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Q=".$sql);
					$row = mysqli_fetch_array($result); 
					$nextId = $row['Auto_increment']; 
					$ref="(PROV".$nextId.")";
					$refcmd=substr($produit_refcmd[1],36,10);
					$sql= "INSERT INTO llx_commande (ref,date_creation,date_commande,fk_soc,fk_user_author,ref_client)
							VALUES ('".$ref."',".$date_cmd.",".$date_cmd.",".$obj_dest->fk_object.",2,$refcmd)";	
			   		//echo $sql;echo "<BR>";	
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." PB insertion entete commande".$sql);
					$rowidcmd=mysqli_insert_id($link);
					//Fin insertion commande
		
					//On connait la ref produit
					//echo $produit_refcmd[1];echo "<BR>";
					$refproduit=substr($produit_refcmd[1],1,10);		
					$sql= "SELECT fk_product FROM `llx_custom_product_ref` WHERE `ref` LIKE '".$refproduit."'";
					//echo $sql;echo "<BR>";
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Pb de requete custom ref produit".$sql);
					If (!mysqli_num_rows($result))  { 
						die("Pas de custom ref définie $refproduit");
					}
					$obj_custom_produit= mysqli_fetch_object($result);
				
					//Information complémentaire de l'article : Ajouter le poids ??
					$sql= "SELECT rowid,pmp FROM `llx_product` WHERE rowid=$obj_custom_produit->fk_product";
					//echo $sql;echo "<BR>";
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Pb info requete complémentaire article".$sql);
					$obj_produit= mysqli_fetch_object($result);
					//echo $obj_produit->rowid;echo " IDPRODUIT<BR>";
		
					//On asocié le fichier EDI à lka commande crée
					mkdir("$DOC_DIR$ref", 0777);
					if (!copy("$EDI_IN_DIR$filename","$DOC_DIR$ref/$filename")) {
						echo "La copie du fichier $filename dans la GED a échoué...\n";
						}
				}
				if ($produit_qte_delai_carte) {
					//echo $produit_qte_delai_carte[1];echo "<BR>";
					$produit++;
					$delai=substr($produit_qte_delai_carte[1],137,8);
					//echo $delai;echo "<BR>";
					$qte=substr($produit_qte_delai_carte[1],186,12);
					//echo $qte;echo "<BR>";
					$carte=substr($produit_qte_delai_carte[1],281,10);
					//echo $carte;echo "<BR>";
		
					//Prix du produit pour le client 
					$sql= "SELECT prix FROM `llx_tarif_conditionnement` WHERE `fk_product` =".$obj_produit->rowid." AND fk_soc=".$obj_dest->fk_object;
					//echo $sql;echo "<BR>";
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Pb requete PX CLIENT $obj_dest->fk_object pour cet article $obj_produit->rowid".$sql);
					If (!mysqli_num_rows($result))  { 
						die("Pas de prix client $obj_produit->rowid");
					}
					$obj_prixproduit= mysqli_fetch_object($result);
					//echo $obj_prixproduit->prix;echo " prixPRODUIT<BR>";
		
		
					//Insertion de chaque ligne de produit
					$sql= "INSERT INTO llx_commandedet (fk_commande,fk_product,tva_tx,qty,rang,price,subprice,buy_price_ht)
							VALUES (".$rowidcmd.",".$obj_produit->rowid.",'20.000',".$qte.",".$produit.",".$obj_prixproduit->prix.",".$obj_prixproduit->prix.",".$obj_produit->pmp.")";	
					//echo $sql;echo "<BR>";	
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Pb insertion ligne commande $rowidcmd".$sql);
					$rowidcmdln=mysqli_insert_id($link);
		
					// date livraison et carte dans des extrafields
					$sql= "INSERT INTO llx_commandedet_extrafields (fk_object,date_de_livraison,num_carte)
							VALUES (".$rowidcmdln.",".$delai.",$carte)";	
					//echo $sql;echo "<BR>";	
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Pb insertion extrafields ligne commande".$sql);
				}
				if ($porte) {
					$magasin=substr($porte[1],4,3);
					//echo $porte[1];
					//echo "Magasin : ".$magasin;echo "<BR>";
					$sql= "SELECT * FROM `llx_socpeople` WHERE `lastname`='$magasin' and fk_soc=$obj_dest->fk_object";
					//echo $sql;echo "<BR>";
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Q=".$sql);
					If (!mysqli_num_rows($result))  { 
						die("Pas de porte $obj_dest->fk_object $magasin");
					}
					$obj_socpeople= mysqli_fetch_object($result);
		
					$sql= "INSERT INTO `llx_element_contact` (statut,element_id,fk_c_type_contact,fk_socpeople)
						   VALUES (4,$rowidcmd,102,$obj_socpeople->rowid)";		       
					//echo $sql;echo "<BR>";	
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Q=".$sql);
				}
	
				//else echo "NOPe<BR>";
	
			} //Fin ligne de fichier
	    echo " OK<BR>";
	    //Ici on doit archiver le fichier
	    // copier le fichier dans le rep archive 
	    if (!copy("$EDI_IN_DIR$filename","$EDI_INA_DIR$filename")) {
			echo "La copie du fichier $filename dans l'archive a échoué...\n";
		}
	    //et l'effacer
	    unlink("$EDI_IN_DIR$filename");
	    
	    } //Fin du if (type de fichier)
    }//fin du While
    closedir($handle);
    
}//fin du if ok ouverture répertoire
	
?>