<?php
/**
 * Helper pour gérer les permissions des utilisateurs
 * Protection contre les inclusions multiples
 */
if (!function_exists('getPermissionsDisponibles')) {
    include_once(__DIR__ . '/db_compat_helper.php');

/**
 * Liste des permissions disponibles dans l'application
 */
function getPermissionsDisponibles() {
    return [
        'acces_tresorerie' => [
            'nom' => '💰 Accès à la Trésorerie',
            'description' => 'Permet de consulter les données financières (CA, bénéfices, graphiques)',
            'page' => 'tresorerie.php'
        ],
        'modifier_stock' => [
            'nom' => '📦 Modifier le Stock',
            'description' => 'Permet d\'ajouter, modifier et supprimer des produits',
            'page' => 'stock.php'
        ],
        'modifier_fournisseurs' => [
            'nom' => '🚚 Modifier les Fournisseurs',
            'description' => 'Permet d\'ajouter, modifier et supprimer des fournisseurs',
            'page' => 'fournisseurs.php'
        ],
        'creer_commandes' => [
            'nom' => '📋 Créer des Commandes',
            'description' => 'Permet de créer de nouvelles commandes auprès des fournisseurs',
            'page' => 'commandes.php'
        ],
        'modifier_categories' => [
            'nom' => '🏷️ Modifier les Catégories',
            'description' => 'Permet d\'ajouter, modifier et supprimer des catégories de produits',
            'page' => 'categories.php'
        ],
        'modifier_clients' => [
            'nom' => '👥 Modifier les Clients',
            'description' => 'Permet d\'ajouter, modifier et supprimer des clients',
            'page' => 'clients.php'
        ],
        'voir_utilisateurs' => [
            'nom' => '👤 Voir les Utilisateurs',
            'description' => 'Permet de consulter la liste des utilisateurs (lecture seule)',
            'page' => 'utilisateurs.php'
        ]
    ];
}

/**
 * Vérifie si un utilisateur a une permission spécifique
 */
function aPermission($conn, $id_utilisateur, $permission) {
    // Vérifier si la table existe
    if (!table_exists($conn, 'permissions_utilisateurs')) {
        return false;
    }
    
    $stmt = $conn->prepare("SELECT id FROM permissions_utilisateurs WHERE id_utilisateur = ? AND permission = ?");
    $stmt->bind_param("is", $id_utilisateur, $permission);
    $stmt->execute();
    $result = $stmt->get_result();
    $has_permission = ((is_object($result) && method_exists($result, 'num_rows') ? $result->num_rows() : mysqli_num_rows($result)) > 0);
    $stmt->close();
    
    return $has_permission;
}

/**
 * Vérifie si un utilisateur a au moins une des permissions données
 */
function aAuMoinsUnePermission($conn, $id_utilisateur, $permissions) {
    if (empty($permissions)) {
        return false;
    }
    
    // Vérifier si la table existe
    if (!table_exists($conn, 'permissions_utilisateurs')) {
        return false;
    }
    
    $placeholders = implode(',', array_fill(0, count($permissions), '?'));
    $stmt = $conn->prepare("SELECT id FROM permissions_utilisateurs WHERE id_utilisateur = ? AND permission IN ($placeholders) LIMIT 1");
    
    $types = 'i' . str_repeat('s', count($permissions));
    $params = array_merge([$id_utilisateur], $permissions);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $has_permission = ((is_object($result) && method_exists($result, 'num_rows') ? $result->num_rows() : mysqli_num_rows($result)) > 0);
    $stmt->close();
    
    return $has_permission;
}

/**
 * Récupère toutes les permissions d'un utilisateur
 */
function getPermissionsUtilisateur($conn, $id_utilisateur) {
    // Vérifier si la table existe
    if (!table_exists($conn, 'permissions_utilisateurs')) {
        return [];
    }
    
    $stmt = $conn->prepare("SELECT permission FROM permissions_utilisateurs WHERE id_utilisateur = ?");
    $stmt->bind_param("i", $id_utilisateur);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $permissions = [];
    while ($row = (is_object($result) && method_exists($result, 'fetch_assoc') ? $result->fetch_assoc() : mysqli_fetch_assoc($result))) {
        $permissions[] = $row['permission'];
    }
    $stmt->close();
    
    return $permissions;
}

/**
 * Ajoute une permission à un utilisateur
 */
function ajouterPermission($conn, $id_utilisateur, $permission, $id_admin_attribueur = null, $id_demande_acces = null) {
    // Vérifier si la table existe, sinon la créer
    if (!table_exists($conn, 'permissions_utilisateurs')) {
        $create_table = "CREATE TABLE IF NOT EXISTS permissions_utilisateurs (
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
        $conn->query($create_table);
    }
    
    // Vérifier si la permission existe déjà
    if (aPermission($conn, $id_utilisateur, $permission)) {
        return false; // Permission déjà accordée
    }
    
    $stmt = $conn->prepare("INSERT INTO permissions_utilisateurs (id_utilisateur, permission, id_admin_attribueur, id_demande_acces) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isii", $id_utilisateur, $permission, $id_admin_attribueur, $id_demande_acces);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Supprime une permission d'un utilisateur
 */
function supprimerPermission($conn, $id_utilisateur, $permission) {
    $stmt = $conn->prepare("DELETE FROM permissions_utilisateurs WHERE id_utilisateur = ? AND permission = ?");
    $stmt->bind_param("is", $id_utilisateur, $permission);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Supprime toutes les permissions d'un utilisateur
 */
function supprimerToutesPermissions($conn, $id_utilisateur) {
    $stmt = $conn->prepare("DELETE FROM permissions_utilisateurs WHERE id_utilisateur = ?");
    $stmt->bind_param("i", $id_utilisateur);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

} // Fin de la protection contre les inclusions multiples
?>

