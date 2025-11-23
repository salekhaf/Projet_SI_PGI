<?php
/**
 * Script de réorganisation du projet
 * Déplace les fichiers dans leur dossier approprié
 */

$base_dir = __DIR__;

// Mapping des fichiers vers leurs nouveaux emplacements
$moves = [
    // Configuration
    'db_conn.php' => 'config/db_conn.php',
    
    // Helpers
    'historique_helper.php' => 'includes/historique_helper.php',
    'permissions_helper.php' => 'includes/permissions_helper.php',
    'role_helper.php' => 'includes/role_helper.php',
    'export_helper.php' => 'includes/export_helper.php',
    
    // Pages d'authentification
    'auth.php' => 'pages/auth/auth.php',
    'register.php' => 'pages/auth/register.php',
    'login.php' => 'pages/auth/login.php',
    'logout.php' => 'pages/auth/logout.php',
    
    // Pages publiques
    'accueil.php' => 'pages/public/accueil.php',
    'services.php' => 'pages/public/services.php',
    
    // Dashboard
    'index.php' => 'pages/dashboard/index.php',
    
    // Stock
    'stock.php' => 'pages/stock/stock.php',
    'categories.php' => 'pages/stock/categories.php',
    
    // Ventes
    'ventes.php' => 'pages/ventes/ventes.php',
    'detailVente.php' => 'pages/ventes/detailVente.php',
    
    // Commandes
    'commandes.php' => 'pages/commandes/commandes.php',
    'detailCommande.php' => 'pages/commandes/detailCommande.php',
    'bonCommande.php' => 'pages/commandes/bonCommande.php',
    
    // Clients
    'clients.php' => 'pages/clients/clients.php',
    
    // Fournisseurs
    'fournisseurs.php' => 'pages/fournisseurs/fournisseurs.php',
    
    // Trésorerie
    'tresorerie.php' => 'pages/tresorerie/tresorerie.php',
    
    // Administration
    'utilisateurs.php' => 'pages/admin/utilisateurs.php',
    'demandes_acces.php' => 'pages/admin/demandes_acces.php',
    
    // CSS
    'styles.css' => 'assets/css/styles.css',
    'styles_connected.css' => 'assets/css/styles_connected.css',
    
    // Images
    'logo_epicerie.png' => 'assets/images/logo_epicerie.png',
    'fond-accueil.png' => 'assets/images/fond-accueil.png',
    'fond-auth.png' => 'assets/images/fond-auth.png',
    'fond-index.png' => 'assets/images/fond-index.png',
    'fond-stock.png' => 'assets/images/fond-stock.png',
    
    // SQL
    'db.sql' => 'database/db.sql',
    'db_historique.sql' => 'database/db_historique.sql',
    'db_demandes_acces.sql' => 'database/db_demandes_acces.sql',
    'db_depenses_diverses.sql' => 'database/db_depenses_diverses.sql',
    'db_permissions_utilisateurs.sql' => 'database/db_permissions_utilisateurs.sql',
    'db_donnees_demo.sql' => 'database/db_donnees_demo.sql',
    
    // Scripts d'installation
    'create_admin.php' => 'install/create_admin.php',
    'fix_admin_password.php' => 'install/fix_admin_password.php',
    'install_demandes_acces.php' => 'install/install_demandes_acces.php',
    'install_depenses_diverses.php' => 'install/install_depenses_diverses.php',
    'install_permissions_utilisateurs.php' => 'install/install_permissions_utilisateurs.php',
    'install_donnees_demo.php' => 'install/install_donnees_demo.php',
    
    // Documentation
    'ARCHITECTURE_SCHEMA.md' => 'docs/ARCHITECTURE_SCHEMA.md',
    'PRESENTATION_PROJET.md' => 'docs/PRESENTATION_PROJET.md',
    'PLAN_PRESENTATION_ORALE.md' => 'docs/PLAN_PRESENTATION_ORALE.md',
    'GUIDE_CONNEXION_ADMIN.md' => 'docs/GUIDE_CONNEXION_ADMIN.md',
    'GUIDE_DEMANDES_ACCES.md' => 'docs/GUIDE_DEMANDES_ACCES.md',
    'GUIDE_PERMISSIONS_GRANULAIRES.md' => 'docs/GUIDE_PERMISSIONS_GRANULAIRES.md',
    'GUIDE_INTEGRATION_CSS.md' => 'docs/GUIDE_INTEGRATION_CSS.md',
    'README_DONNEES_DEMO.md' => 'docs/README_DONNEES_DEMO.md',
    'NOUVELLES_FONCTIONNALITES.md' => 'docs/NOUVELLES_FONCTIONNALITES.md',
    'AMELIORATIONS_CSS.md' => 'docs/AMELIORATIONS_CSS.md',
    'AMELIORATIONS_PROPOSEES.md' => 'docs/AMELIORATIONS_PROPOSEES.md',
    'PROPOSITIONS_GESTION_ADMINS.md' => 'docs/PROPOSITIONS_GESTION_ADMINS.md',
    'GUIDE_SIMPLE_DEMANDES.md' => 'docs/GUIDE_SIMPLE_DEMANDES.md',
    'STRUCTURE_PROJET.md' => 'docs/STRUCTURE_PROJET.md',
    
    // Vendor
    'fpdf' => 'vendor/fpdf',
];

echo "<h2>🔄 Réorganisation du projet</h2>";
echo "<p>Déplacement des fichiers en cours...</p>";

$moved = 0;
$errors = 0;

foreach ($moves as $source => $destination) {
    $source_path = $base_dir . '/' . $source;
    $dest_path = $base_dir . '/' . $destination;
    
    if (file_exists($source_path)) {
        // Créer le dossier de destination si nécessaire
        $dest_dir = dirname($dest_path);
        if (!is_dir($dest_dir)) {
            mkdir($dest_dir, 0755, true);
        }
        
        // Déplacer le fichier
        if (rename($source_path, $dest_path)) {
            echo "✅ Déplacé : $source → $destination<br>";
            $moved++;
        } else {
            echo "❌ Erreur lors du déplacement : $source<br>";
            $errors++;
        }
    } else {
        echo "⚠️ Fichier non trouvé : $source<br>";
    }
}

echo "<hr>";
echo "<h3>Résumé :</h3>";
echo "<p><strong>$moved</strong> fichiers déplacés avec succès</p>";
if ($errors > 0) {
    echo "<p><strong style='color: red;'>$errors</strong> erreurs</p>";
}
echo "<p><a href='index.php'>Retour au projet</a></p>";
?>

