<html>
<head>
	<meta name="robots" content="noindex,nofollow" />
</head>
	<body>
	<?php 

	//ini_set('display_errors','on');
	//error_reporting(E_ALL);

	if($_GET['keyexport'] == 'b7d05c10a5e85a004632e0635439bb83'){

		$a = mysql_connect("localhost","netreferencement","2i3h4P2uhtN3TANj2tk86A4LPpEb89wY") or die("erreur de connexion au serveur");
		$b = mysql_select_db("dolibarr", $a) or die("erreur de connexion a la base de donnees");


		$sql = "SELECT s.nom, s.rowid as socid, s.email, f.rowid as facid, f.facnumber, f.ref_client, f.increment, f.total as total_ht, f.tva as total_tva, f.total_ttc, f.localtax1, f.localtax2, f.revenuestamp, f.datef as df, f.date_lim_reglement as datelimite,SUBSTRING(f.facnumber,-4) as numero, f.paye as paye, f.fk_statut, f.type, dic_p.libelle as mode_rglt, sum(pf.amount) as am FROM comptallx_societe as s,comptallx_facture as f LEFT JOIN comptallx_paiement_facture as pf ON f.rowid=pf.fk_facture LEFT JOIN comptallx_c_paiement as dic_p ON f.fk_mode_reglement=dic_p.id WHERE f.fk_soc = s.rowid AND f.entity = 1 AND f.type IN (0,1,3) AND f.fk_statut = 1 AND f.paye = 0 GROUP BY s.nom, s.rowid, s.email, f.rowid, f.facnumber, f.increment, f.total, f.tva, f.total_ttc, f.localtax1, f.localtax2, f.revenuestamp, f.datef, f.date_lim_reglement, f.paye, f.fk_statut, f.type ORDER BY f.date_lim_reglement DESC, f.facnumber DESC";

		// on envoie la requête
		$req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error()); 

		// On récupère tout le contenu de la table 


		// On affiche chaque entrée une à une
		echo "<table>";
			echo "<tr>";
			echo "<td>FacId</td><td>Ref</td><td>Numero</td><td>Societe</td><td>Mode de reglement</td><td>Date</td><td>Montant HT</td><td>Taxes</td><td>Montant TTC</td><td>Re&ccedil;u</td>";
		while ($donnees = mysql_fetch_assoc($req))
		{
			echo "<tr>";
				echo '<td>'.$donnees['facid'].'</td>';	
				echo '<td>'.$donnees['facnumber'].'</td>';	
				echo '<td>'.$donnees['numero'].'</td>';	
				echo '<td>'.$donnees['nom'].'</td>';	
				echo '<td>'.$donnees['mode_rglt'].'</td>';	
				echo '<td>'.$donnees['df'].'</td>';	
				echo '<td>'.round ($donnees['total_ht']).'</td>';	
				echo '<td>'.round ($donnees['total_tva']).'</td>';	
				echo '<td>'.round ($donnees['total_ttc']).'</td>';	
				$donnees['am'] = round ($donnees['am'] ,2);
				if($donnees['am'] == 'NULL')
					$donnees['am'] = 0;
				echo '<td>'.str_replace('.',',',$donnees['am']).'</td>';	

			echo "</tr>";	
		}

		echo "</table>";


		$reponse->closeCursor(); // Termine le traitement de la requête
	}
	else{
		header('Location: /index.php');
	}
	?>
</body>
</html>