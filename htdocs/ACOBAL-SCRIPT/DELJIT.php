<?php
// Afficher les erreurs à lécran
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__file__) . '/log_error_EDI.txt');
error_reporting(E_ALL);
$link=mysqli_connect("localhost", "root", "iafscw45", "dolibarr_new");
$EDI_IN_DIR="/home/acobal/EDI/IN/";
$DOC_DIR="/var/www/dolibarr/documents/commande/";
$EDI_INA_DIR="/home/acobal/EDI/IN/Archive/";

if ($handle = opendir($EDI_IN_DIR)) {
	while (false !== ($entry = readdir($handle))) {

		if ($entry != "." && $entry != ".." && $entry != "Archive" && stripos($entry,"CALDEL")=='TRUE') {
			echo "fichier selectionné $entry<BR>";
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
					$siret_dest=substr($destinataire[1],1,20);
					//echo "Clef EDI destinataire: ".$siret_dest;echo "<BR>";
					$siret_dest=str_replace(' ','',$siret_dest);
					$sql= "SELECT * FROM `llx_societe_extrafields` WHERE `clefedi` LIKE '".$siret_dest."'";

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

					//Info sur le client
					$sql="SELECT fk_incoterms, mode_reglement, cond_reglement, tva_assuj from llx_societe where rowid=$obj_dest->fk_object";
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." PB select info de société".$sql);
					$obj_tiers = mysqli_fetch_object($result);

					$sql="SELECT fk_socpeople from llx_societe_contact where element_id=$obj_dest->fk_object and fk_c_type_contact=100";
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." PB select info de société adr fact".$sql);
					$obj_adr_fact = mysqli_fetch_object($result);


					$sql= "INSERT INTO llx_commande (ref,date_creation,date_commande,fk_soc,fk_user_author,ref_client,fk_incoterms,fk_mode_reglement,fk_cond_reglement,fk_multicurrency,multicurrency_code)
							VALUES ('".$ref."','".date('Y-m-d H:i:s')."',".$date_cmd.",".$obj_dest->fk_object.",2,$refcmd,$obj_tiers->fk_incoterms,$obj_tiers->mode_reglement,$obj_tiers->cond_reglement,1,'EUR')";
					//echo $sql;echo "<BR>";
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." PB insertion entete commande".$sql);
					$rowidcmd=mysqli_insert_id($link);

					$sql="INSERT INTO `llx_element_contact`(`datecreate`, `statut`, `element_id`, `fk_c_type_contact`, `fk_socpeople`) VALUES ('".date('Y-m-d H:i:s')."',4,$rowidcmd,100,$obj_adr_fact->fk_socpeople)";
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." PB insertion adr fact commande".$sql);
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
						$prix_produit=0;
						$pmp_produit=0;
						//Pas de prix client
					}
					else {

						$obj_prixproduit= mysqli_fetch_object($result);
						$prix_produit=$obj_prixproduit->prix;
						$pmp_produit=$obj_produit->pmp;
					}


					//Insertion de chaque ligne de produit

					// Attention tva pour Vigo, if dans tiers.

					if ($obj_tiers->tva_assuj==0) $tva=0.000; else $tva=20.000;
					$sql= "INSERT INTO llx_commandedet (fk_commande,fk_product,tva_tx,qty,rang,price,subprice,buy_price_ht,fk_multicurrency,multicurrency_code,multicurrency_subprice,multicurrency_total_ht,multicurrency_total_tva,multicurrency_total_ttc)
							VALUES (".$rowidcmd.",".$obj_produit->rowid.",".$tva.",".$qte.",".$produit.",".$prix_produit.",".$prix_produit.",".$pmp_produit.",1,'EUR',0.00,0.00,0.00,0.00)";
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Pb insertion ligne commande $rowidcmd".$sql);
					$rowidcmdln=mysqli_insert_id($link);

					// date livraison et carte dans des extrafields
					$sql= "INSERT INTO llx_commandedet_extrafields (fk_object,date_de_livraison,num_carte)
							VALUES (".$rowidcmdln.",".$delai.",'$carte')";
					//echo $sql;echo "<BR>";
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Pb insertion extrafields ligne commande".$sql);
				}
				if ($porte) {
					$magasin=substr($porte[1],1,6);

					//echo $porte[1];
					//echo "Magasin : ".$magasin;echo "<BR>";
					$sql= "SELECT * FROM `llx_socpeople` WHERE `firstname`='$magasin' and fk_soc=$obj_dest->fk_object";
					//echo $sql;echo "<BR>";
					$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Q=".$sql);
					If (mysqli_num_rows($result))  {
						$obj_socpeople= mysqli_fetch_object($result);
						$sql= "INSERT INTO `llx_element_contact` (statut,element_id,fk_c_type_contact,fk_socpeople)
						   		VALUES (4,$rowidcmd,102,$obj_socpeople->rowid)";
						$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Q=".$sql);

					}

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
