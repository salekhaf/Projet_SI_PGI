<?php
/**
 * Script pour mettre à jour automatiquement les chemins après réorganisation
 * ⚠️ À utiliser avec précaution - Faites une sauvegarde avant !
 */

$base_dir = __DIR__;

// Patterns de remplacement selon le dossier
$replacements = [
    'pages/auth' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('../db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('permissions_helper.php')" => "include('../../includes/permissions_helper.php')",
        "include('role_helper.php')" => "include('../../includes/role_helper.php')",
        "include('export_helper.php')" => "include('../../includes/export_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        'href="styles.css"' => 'href="../../assets/css/styles.css"',
        "Location: auth.php" => "Location: auth.php",
        "Location: register.php" => "Location: register.php",
        "Location: login.php" => "Location: login.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
        "Location: logout.php" => "Location: logout.php",
    ],
    'pages/public' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'src="fond-accueil.png"' => 'src="../../assets/images/fond-accueil.png"',
        'src="fond-auth.png"' => 'src="../../assets/images/fond-auth.png"',
        'href="styles.css"' => 'href="../../assets/css/styles.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
    ],
    'pages/dashboard' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('permissions_helper.php')" => "include('../../includes/permissions_helper.php')",
        "include('role_helper.php')" => "include('../../includes/role_helper.php')",
        "include('export_helper.php')" => "include('../../includes/export_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: index.php",
    ],
    'pages/stock' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('export_helper.php')" => "include('../../includes/export_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
    ],
    'pages/ventes' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
    ],
    'pages/commandes' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
    ],
    'pages/clients' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('export_helper.php')" => "include('../../includes/export_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
    ],
    'pages/fournisseurs' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('export_helper.php')" => "include('../../includes/export_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
    ],
    'pages/tresorerie' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('export_helper.php')" => "include('../../includes/export_helper.php')",
        "include('permissions_helper.php')" => "include('../../includes/permissions_helper.php')",
        "include('role_helper.php')" => "include('../../includes/role_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
    ],
    'pages/admin' => [
        "include('db_conn.php')" => "include('../../config/db_conn.php')",
        "include('historique_helper.php')" => "include('../../includes/historique_helper.php')",
        "include('permissions_helper.php')" => "include('../../includes/permissions_helper.php')",
        "include('role_helper.php')" => "include('../../includes/role_helper.php')",
        'src="logo_epicerie.png"' => 'src="../../assets/images/logo_epicerie.png"',
        'href="styles_connected.css"' => 'href="../../assets/css/styles_connected.css"',
        "Location: auth.php" => "Location: ../auth/auth.php",
        "Location: index.php" => "Location: ../dashboard/index.php",
    ],
];

echo "<h2>🔄 Mise à jour des chemins</h2>";
echo "<p><strong>⚠️ Attention :</strong> Faites une sauvegarde avant de continuer !</p>";
echo "<p>Ce script va modifier tous les fichiers PHP pour mettre à jour les chemins.</p>";

if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    $updated = 0;
    $errors = 0;
    
    foreach ($replacements as $dir => $patterns) {
        $dir_path = $base_dir . '/' . $dir;
        if (is_dir($dir_path)) {
            $files = glob($dir_path . '/*.php');
            foreach ($files as $file) {
                $content = file_get_contents($file);
                $original = $content;
                
                foreach ($patterns as $search => $replace) {
                    $content = str_replace($search, $replace, $content);
                }
                
                if ($content !== $original) {
                    if (file_put_contents($file, $content)) {
                        echo "✅ Mis à jour : " . basename($file) . " dans $dir<br>";
                        $updated++;
                    } else {
                        echo "❌ Erreur : " . basename($file) . "<br>";
                        $errors++;
                    }
                }
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>Résumé :</h3>";
    echo "<p><strong>$updated</strong> fichiers mis à jour</p>";
    if ($errors > 0) {
        echo "<p><strong style='color: red;'>$errors</strong> erreurs</p>";
    }
} else {
    echo "<form method='POST'>";
    echo "<input type='hidden' name='confirm' value='yes'>";
    echo "<button type='submit' style='padding: 10px 20px; background: #dc3545; color: white; border: none; cursor: pointer;'>Confirmer la mise à jour</button>";
    echo "</form>";
}
?>

