<?php
// Script d'installation de la table depenses_diverses
include('db_conn.php');

$sql = "CREATE TABLE IF NOT EXISTS depenses_diverses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_operation ENUM('depense', 'entree') NOT NULL,
    libelle VARCHAR(255) NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_operation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INT NOT NULL,
    notes TEXT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "✅ Table 'depenses_diverses' créée avec succès !\n";
    
    // Créer les index
    mysqli_query($conn, "CREATE INDEX IF NOT EXISTS idx_depenses_date ON depenses_diverses(date_operation)");
    mysqli_query($conn, "CREATE INDEX IF NOT EXISTS idx_depenses_type ON depenses_diverses(type_operation)");
    
    echo "✅ Index créés avec succès !\n";
} else {
    echo "❌ Erreur lors de la création : " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>

