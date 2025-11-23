<?php
/**
 * Script de test pour vérifier que tous les chemins sont corrects
 */

echo "<h2>🔍 Test des chemins</h2>";

$base_dir = __DIR__;

// Vérifier les fichiers essentiels
$files_to_check = [
    'index.php' => 'Point d\'entrée',
    'config/db_conn.php' => 'Configuration BDD',
    'includes/role_helper.php' => 'Helper rôles',
    'includes/historique_helper.php' => 'Helper historique',
    'includes/permissions_helper.php' => 'Helper permissions',
    'pages/public/accueil.php' => 'Page d\'accueil',
    'pages/auth/auth.php' => 'Authentification',
    'pages/dashboard/index.php' => 'Dashboard',
    'assets/css/styles_connected.css' => 'CSS connecté',
    'assets/images/logo_epicerie.png' => 'Logo',
];

echo "<h3>Vérification des fichiers :</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Fichier</th><th>Description</th><th>Statut</th></tr>";

$all_ok = true;
foreach ($files_to_check as $file => $desc) {
    $path = $base_dir . '/' . $file;
    $exists = file_exists($path);
    $status = $exists ? '✅ Existe' : '❌ Manquant';
    $color = $exists ? 'green' : 'red';
    if (!$exists) $all_ok = false;
    
    echo "<tr>";
    echo "<td><code>$file</code></td>";
    echo "<td>$desc</td>";
    echo "<td style='color: $color; font-weight: bold;'>$status</td>";
    echo "</tr>";
}

echo "</table>";

if ($all_ok) {
    echo "<h3 style='color: green;'>✅ Tous les fichiers essentiels sont présents</h3>";
} else {
    echo "<h3 style='color: red;'>❌ Certains fichiers sont manquants</h3>";
}

echo "<hr>";
echo "<h3>Test des URLs :</h3>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>Page d'accueil (index.php)</a></li>";
echo "<li><a href='pages/public/accueil.php' target='_blank'>Page d'accueil (directe)</a></li>";
echo "<li><a href='pages/auth/auth.php' target='_blank'>Page d'authentification</a></li>";
echo "<li><a href='pages/dashboard/index.php' target='_blank'>Dashboard</a></li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Structure des dossiers :</h3>";
echo "<pre>";
function listDir($dir, $prefix = '') {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.git') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            echo $prefix . "📁 $item/\n";
            if (strlen($prefix) < 40) { // Limiter la profondeur
                listDir($path, $prefix . '  ');
            }
        } else {
            $ext = pathinfo($item, PATHINFO_EXTENSION);
            $icon = in_array($ext, ['php', 'html']) ? '📄' : (in_array($ext, ['css']) ? '🎨' : (in_array($ext, ['png', 'jpg']) ? '🖼️' : '📄'));
            echo $prefix . "$icon $item\n";
        }
    }
}
listDir($base_dir);
echo "</pre>";
?>

