<?php
// Fonction helper pour enregistrer dans l'historique
include_once(__DIR__ . '/db_compat_helper.php');

function enregistrer_historique($conn, $id_utilisateur, $type_action, $table_concernée, $id_element, $description = "", $anciennes_valeurs = null, $nouvelles_valeurs = null) {
    // Vérifier si la table historique existe
    if (!table_exists($conn, 'historique')) {
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




