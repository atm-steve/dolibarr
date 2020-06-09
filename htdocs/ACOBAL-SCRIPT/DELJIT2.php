<?php

/ Afficher les erreurs à lécran
ini_set('display_errors', 1);

// Afficher les erreurs et les avertissements

error_reporting(e_all);


$con=mysqli_connect("localhost", "root", "iafscw45", "dolibarr_test");
/*Ouvre le fichier et retourne un tableau contenant une ligne par élément*/
$lines = file('CALDEL0019766425.TXT');
/*On parcourt le tableau $lines et on affiche le contenu de chaque ligne précédée de son numéro*/
foreach ($lines as $lineNumber => $lineContent)
{
	//echo $lineNumber,' ',$lineContent,'<BR>';
	preg_match("#C0500(.*)#",$lineContent,$destinataire);
	preg_match("#C0100(.*)#",$lineContent,$acheteur);
	//preg_match("#C0500(.*)#",$lineContent,$destinataire);
	preg_match("#C0300(.*)#",$lineContent,$transporteur);
	preg_match("#A0900(.*)#",$lineContent,$produit);
	preg_match("#G0601(.*)#",$lineContent,$produit_qte_delai_carte);
	preg_match("#H0200(.*)#",$lineContent,$porte);
	
	if ($destinataire) {
		//echo $destinataire[1];
		$siret_dest=substr($destinataire[1],5,14);
		echo "SIRET destinataire: ".$siret_dest;
		echo "<BR>";
	}
	if ($acheteur) {
		$siret_achat=substr($acheteur[1],5,9);
		//echo $acheteur[1];
		echo "SIRET Acheteur: ".$siret_achat;
		echo "<BR>";
	}
	if ($porte) {
		$magasin=substr($porte[1],4,3);
		//echo $porte[1];
		echo "Magasin : ".$magasin;
		echo "<BR>";
	}
	if ($transporteur) {
		echo $transporteur[1];
		echo "<BR>";
	}
	if ($produit) {
		echo $produit[1];
		echo "<BR>";
	}
	if ($produit_qte_delai_carte) {
		echo $produit_qte_delai_carte[1];
		echo "<BR>";
	}
	
	else echo "NOP<BR>";
	
}
?>