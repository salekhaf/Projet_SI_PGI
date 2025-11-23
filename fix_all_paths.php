<?php
/**
 * Script pour corriger automatiquement tous les chemins dans le projet
 * Après réorganisation des fichiers
 */

$base_dir = __DIR__;

echo "<h2>🔧 Correction automatique des chemins</h2>";
echo "<p>Mise à jour en cours...</p>";

// Fonction pour mettre à jour un fichier
function updateFile($file_path, $replacements) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $content = file_get_contents($file_path);
    $original = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $original) {
        file_put_contents($file_path, $content);
        return true;
    }
    return false;
}

// Patterns de remplacement par dossier
$patterns = [
    // Pages publiques
    'pages/public' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('../db_conn.php')" => "include('../../config/db_conn.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'src="fond-accueil.png"' => 'src="../../assets/images/fond-accueil.png"',
        'src="fond-auth.png"' => 'src="../../assets/images/fond-auth.png"',
        'url(\'fond-accueil.png\')' => 'url(\'../../assets/images/fond-accueil.png\')',
        'url("fond-accueil.png")' => 'url("../../assets/images/fond-accueil.png")',
        'href="styles.css"' => 'href="../../assets/css/styles.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
        'href="auth.php' => 'href="../auth/auth.php',
        'href="accueil.php"' => 'href="accueil.php"',
        'href="services.php"' => 'href="services.php"',
    ],
    
    // Pages d'authentification
    'pages/auth' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('../db_conn.php')" => "include('../../config/db_conn.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'src="fond-auth.png"' => 'src="../../assets/images/fond-auth.png"',
        'url(\'fond-auth.png\')' => 'url(\'../../assets/images/fond-auth.png\')',
        'url("fond-auth.png")' => 'url("../../assets/images/fond-auth.png")',
        'href="styles.css"' => 'href="../../assets/css/styles.css"',
        "Location: index.php" => "Location: ../dashboard/index.php",
        "Location: accueil.php" => "Location: ../public/accueil.php",
        "Location: register.php" => "Location: register.php",
        "Location: login.php" => "Location: login.php",
        "Location: auth.php" => "Location: auth.php",
        "Location: logout.php" => "Location: logout.php",
    ],
    
    // Dashboard
    'pages/dashboard' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('../db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('permissions_helper.php')" => "include('../../includes/permissions_helper.php')",
        "include('role_helper.php')" => "include('../../includes/role_helper.php')",
        "include('export_helper.php')" => "include('../../includes/export_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'src="fond-index.png"' => 'src="../../assets/images/fond-index.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: index.php",
        "Location: stock.php" => "Location: ../stock/stock.php",
        "Location: ventes.php" => "Location: ../ventes/ventes.php",
        "Location: clients.php" => "Location: ../clients/clients.php",
        "Location: commandes.php" => "Location: ../commandes/commandes.php",
        "Location: categories.php" => "Location: ../stock/categories.php",
        "Location: fournisseurs.php" => "Location: ../fournisseurs/fournisseurs.php",
        "Location: tresorerie.php" => "Location: ../tresorerie/tresorerie.php",
        "Location: utilisateurs.php" => "Location: ../admin/utilisateurs.php",
        "Location: demandes_acces.php" => "Location: ../admin/demandes_acces.php",
    ],
    
    // Stock
    'pages/stock' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('../db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('export_helper.php')" => "include('../../includes/export_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'src="fond-stock.png"' => 'src="../../assets/images/fond-stock.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
        "Location: stock.php" => "Location: stock.php",
        "Location: categories.php" => "Location: categories.php",
    ],
    
    // Ventes
    'pages/ventes' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('../db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
        "Location: ventes.php" => "Location: ventes.php",
        "Location: detailVente.php" => "Location: detailVente.php",
    ],
    
    // Commandes
    'pages/commandes' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('../db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
        "Location: commandes.php" => "Location: commandes.php",
        "Location: detailCommande.php" => "Location: detailCommande.php",
    ],
    
    // Clients
    'pages/clients' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('../db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('export_helper.php')" => "include('../../includes/export_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
    ],
    
    // Fournisseurs
    'pages/fournisseurs' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('../db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('export_helper.php')" => "include('../../includes/export_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
    ],
    
    // Trésorerie
    'pages/tresorerie' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('../db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('permissions_helper.php')" => "include('../../includes/permissions_helper.php')",
        "include('role_helper.php')" => "include('../../includes/role_helper.php')",
        "include('export_helper.php')" => "include('../../includes/export_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
    ],
    
    // Admin
    'pages/admin' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('../db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('permissions_helper.php')" => "include('../../includes/permissions_helper.php')",
        "include('role_helper.php')" => "include('../../includes/role_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
    ],
];

$updated = 0;
$total = 0;

foreach ($patterns as $dir => $replacements) {
    $dir_path = $base_dir . '/' . $dir;
    if (is_dir($dir_path)) {
        $files = glob($dir_path . '/*.php');
        foreach ($files as $file) {
            $total++;
            if (updateFile($file, $replacements)) {
                echo "✅ Mis à jour : " . str_replace($base_dir . '/', '', $file) . "<br>";
                $updated++;
            }
        }
    }
}

// Mettre à jour aussi services.php
if (file_exists($base_dir . '/pages/public/services.php')) {
    $total++;
    if (updateFile($base_dir . '/pages/public/services.php', $patterns['pages/public'])) {
        echo "✅ Mis à jour : pages/public/services.php<br>";
        $updated++;
    }
}

echo "<hr>";
echo "<h3>Résumé :</h3>";
echo "<p><strong>$updated</strong> fichiers mis à jour sur <strong>$total</strong> fichiers vérifiés</p>";
echo "<p><a href='index.php'>Tester l'application</a></p>";
?>

