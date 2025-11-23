<?php
/**
 * Proxy pour les fonctions mysqli_* compatible PostgreSQL
 * 
 * SOLUTION: Utiliser des fonctions avec des noms différents et créer
 * un script de remplacement automatique, OU utiliser un système de proxy
 * qui intercepte les appels via des fonctions globales.
 * 
 * La meilleure solution est de modifier le code pour utiliser les méthodes
 * de l'objet directement au lieu des fonctions mysqli_*
 */

// Cette approche ne fonctionne pas car on ne peut pas surcharger les fonctions natives
// Il faut donc modifier le code pour utiliser les méthodes de l'objet

// SOLUTION ALTERNATIVE: Créer un script qui remplace automatiquement
// $conn->prepare(...) par $conn->prepare(...) dans tous les fichiers

