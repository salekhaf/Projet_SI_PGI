<?php
/**
 * Point d'entrée principal
 * Redirige vers la page d'accueil ou le dashboard selon l'état de connexion
 */
session_start();

if (isset($_SESSION['id_utilisateur'])) {
    // Utilisateur connecté -> Dashboard
    header("Location: pages/dashboard/index.php");
} else {
    // Utilisateur non connecté -> Page d'accueil
    header("Location: pages/public/accueil.php");
}
exit();
?>
