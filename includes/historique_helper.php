<?php
// Fonction helper pour enregistrer dans l'historique
function enregistrer_historique($conn, $id_utilisateur, $type_action, $table_concernée, $id_element, $description = "", $anciennes_valeurs = null, $nouvelles_valeurs = null) {
    // Vérifier si la table historique existe
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'historique'");
    if (mysqli_num_rows($check_table) == 0) {
        // Table n'existe pas, on ne fait rien (pour éviter les erreurs)
        return false;
    }
    
    $anciennes_json = $anciennes_valeurs ? json_encode($anciennes_valeurs) : null;
    $nouvelles_json = $nouvelles_valeurs ? json_encode($nouvelles_valeurs) : null;
    
    $stmt = mysqli_prepare($conn, "INSERT INTO historique (id_utilisateur, type_action, table_concernée, id_element, description, anciennes_valeurs, nouvelles_valeurs) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "issssss", $id_utilisateur, $type_action, $table_concernée, $id_element, $description, $anciennes_json, $nouvelles_json);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }
    return false;
}
?>




