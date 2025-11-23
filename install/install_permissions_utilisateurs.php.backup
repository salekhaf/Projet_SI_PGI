<?php
// Script d'installation de la table permissions_utilisateurs
include('db_conn.php');

$sql = "CREATE TABLE IF NOT EXISTS permissions_utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    permission VARCHAR(100) NOT NULL,
    date_attribution TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_admin_attribueur INT NULL,
    id_demande_acces INT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_admin_attribueur) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (id_demande_acces) REFERENCES demandes_acces(id) ON DELETE SET NULL,
    UNIQUE KEY unique_permission_user (id_utilisateur, permission)
)";

if (mysqli_query($conn, $sql)) {
    echo "✅ Table 'permissions_utilisateurs' créée avec succès !\n";
    
    // Créer les index
    mysqli_query($conn, "CREATE INDEX IF NOT EXISTS idx_permissions_user ON permissions_utilisateurs(id_utilisateur)");
    mysqli_query($conn, "CREATE INDEX IF NOT EXISTS idx_permissions_permission ON permissions_utilisateurs(permission)");
    
    echo "✅ Index créés avec succès !\n";
} else {
    echo "❌ Erreur lors de la création : " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>

