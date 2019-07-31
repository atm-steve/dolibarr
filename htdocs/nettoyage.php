<?php 
$link = mysql_connect('vps93966.ovh.net', 'root', 'HqYJw78739mzsg4rGiZ5C6');
if (!$link) {
    die('Not connected : ' . mysql_error());
}



$query = ("PURGE BINARY LOGS BEFORE DATE_SUB( NOW(), INTERVAL 2 DAY )");

// Exécution de la requête
$result = mysql_query($query);

// Vérification du résultat
// Ceci montre la requête envoyée à MySQL ainsi que l'erreur. Utile pour déboguer.
if (!$result) {
    $message  = 'Requête invalide : ' . mysql_error() . "\n";
    $message .= 'Requête complète : ' . $query;
    die($message);
}


?>