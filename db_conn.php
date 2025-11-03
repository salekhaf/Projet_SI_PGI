<?php
$serveur = "localhost";
$utilisateur = "root";
$motdepasse = "";
$basededonnees = "epicerie_db";

$conn = mysqli_connect($serveur, $utilisateur, $motdepasse, $basededonnees);

if (!$conn) {
    die("Erreur de connexion à la base de données : " . mysqli_connect_error());
}
?>
