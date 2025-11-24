<?php
/**
 * Fonction pour enregistrer une action dans l'historique
 * Version 100% PDO compatible MySQL + PostgreSQL
 */

function enregistrer_historique(PDO $pdo, $id_utilisateur, $type_action, $table_concernee, $id_element, $description = "", $anciennes_valeurs = null, $nouvelles_valeurs = null)
{
    // Vérifier si la table historique existe
    $check = $pdo->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_name = 'historique'
        )
    ");
    if (!$check->fetchColumn()) {
        return false;
    }

    $anciennes_json = $anciennes_valeurs ? json_encode($anciennes_valeurs, JSON_UNESCAPED_UNICODE) : null;
    $nouvelles_json = $nouvelles_valeurs ? json_encode($nouvelles_valeurs, JSON_UNESCAPED_UNICODE) : null;

    $stmt = $pdo->prepare("
        INSERT INTO historique 
        (id_utilisateur, type_action, table_concernée, id_element, description, anciennes_valeurs, nouvelles_valeurs)
        VALUES 
        (:id_utilisateur, :type_action, :table_concernee, :id_element, :description, :ancien, :nouveau)
    ");

    return $stmt->execute([
        ':id_utilisateur' => $id_utilisateur,
        ':type_action'    => $type_action,
        ':table_concernee'=> $table_concernee,
        ':id_element'     => $id_element,
        ':description'    => $description,
        ':ancien'         => $anciennes_json,
        ':nouveau'        => $nouvelles_json
    ]);
}
?>
