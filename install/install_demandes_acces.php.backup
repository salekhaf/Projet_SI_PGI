<?php
/**
 * Script d'installation pour créer la table demandes_acces
 * Exécuter ce fichier une seule fois pour créer la table
 */

include('db_conn.php');

echo "<h2>Installation de la table demandes_acces</h2>";

// Vérifier si la table existe déjà
$check = mysqli_query($conn, "SHOW TABLES LIKE 'demandes_acces'");

if (mysqli_num_rows($check) > 0) {
    echo "<p style='color: orange;'>⚠️ La table 'demandes_acces' existe déjà.</p>";
} else {
    // Créer la table
    $sql = "CREATE TABLE IF NOT EXISTS demandes_acces (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        type_demande ENUM('role', 'permission_specifique') NOT NULL,
        role_demande VARCHAR(50) NULL,
        permission_demande VARCHAR(100) NULL,
        raison TEXT,
        statut ENUM('en_attente', 'approuvee', 'refusee') DEFAULT 'en_attente',
        id_admin_approbateur INT NULL,
        date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_traitement TIMESTAMP NULL,
        commentaire_admin TEXT NULL,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (id_admin_approbateur) REFERENCES utilisateurs(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (mysqli_query($conn, $sql)) {
        // Créer les index
        mysqli_query($conn, "CREATE INDEX idx_statut ON demandes_acces(statut)");
        mysqli_query($conn, "CREATE INDEX idx_utilisateur ON demandes_acces(id_utilisateur)");
        
        echo "<p style='color: green;'>✅ Table 'demandes_acces' créée avec succès !</p>";
        echo "<p>Vous pouvez maintenant utiliser le système de demandes d'accès.</p>";
        echo "<p><a href='index.php'>Retour au tableau de bord</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Erreur lors de la création : " . mysqli_error($conn) . "</p>";
    }
}

mysqli_close($conn);
?>



