<?php
require_once(__DIR__ . '/db_compat_helper.php');

/**
 * Enregistre une action dans l’historique (version PDO)
 */
function enregistrer_historique(PDO $pdo, $id_utilisateur, $type_action, $table_concernee, $id_element, $description = "", $anciennes_valeurs = null, $nouvelles_valeurs = null)
{
    // Vérifier si la table existe
    if (!table_exists($pdo, 'historique')) {
        return false;
    }

    $anciennes_json = $anciennes_valeurs ? json_encode($anciennes_valeurs) : null;
    $nouvelles_json = $nouvelles_valeurs ? json_encode($nouvelles_valeurs) : null;

    $stmt = $pdo->prepare("
        INSERT INTO historique 
        (id_utilisateur, type_action, table_concernee, id_element, description, anciennes_valeurs, nouvelles_valeurs)
        VALUES (:id_utilisateur, :type_action, :table_concernee, :id_element, :description, :anciennes, :nouvelles)
    ");

    return $stmt->execute([
        ':id_utilisateur' => $id_utilisateur,
        ':type_action'    => $type_action,
        ':table_concernee'=> $table_concernee,
        ':id_element'     => $id_element,
        ':description'    => $description,
        ':anciennes'      => $anciennes_json,
        ':nouvelles'      => $nouvelles_json
    ]);
}
?>
