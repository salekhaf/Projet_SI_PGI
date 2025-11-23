<?php
/**
 * Script pour corriger automatiquement tous les chemins de navigation dans toutes les pages
 */

$base_dir = __DIR__ . '/pages';
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base_dir),
    RecursiveIteratorIterator::SELF_FIRST
);

$replacements = [
    // Pages dans pages/admin/
    [
        'pattern' => '/href="index\.php"/',
        'replacement' => 'href="../dashboard/index.php"',
        'path_contains' => 'pages/admin/'
    ],
    [
        'pattern' => '/href="stock\.php"/',
        'replacement' => 'href="../stock/stock.php"',
        'path_contains' => 'pages/admin/'
    ],
    [
        'pattern' => '/href="ventes\.php"/',
        'replacement' => 'href="../ventes/ventes.php"',
        'path_contains' => 'pages/admin/'
    ],
    [
        'pattern' => '/href="clients\.php"/',
        'replacement' => 'href="../clients/clients.php"',
        'path_contains' => 'pages/admin/'
    ],
    [
        'pattern' => '/href="commandes\.php"/',
        'replacement' => 'href="../commandes/commandes.php"',
        'path_contains' => 'pages/admin/'
    ],
    [
        'pattern' => '/href="categories\.php"/',
        'replacement' => 'href="../stock/categories.php"',
        'path_contains' => 'pages/admin/'
    ],
    [
        'pattern' => '/href="utilisateurs\.php"/',
        'replacement' => 'href="utilisateurs.php"',
        'path_contains' => 'pages/admin/'
    ],
    [
        'pattern' => '/href="logout\.php"/',
        'replacement' => 'href="../auth/logout.php"',
        'path_contains' => 'pages/admin/'
    ],
    
    // Pages dans pages/stock/
    [
        'pattern' => '/href="index\.php"/',
        'replacement' => 'href="../dashboard/index.php"',
        'path_contains' => 'pages/stock/'
    ],
    [
        'pattern' => '/href="stock\.php"/',
        'replacement' => 'href="stock.php"',
        'path_contains' => 'pages/stock/'
    ],
    [
        'pattern' => '/href="categories\.php"/',
        'replacement' => 'href="categories.php"',
        'path_contains' => 'pages/stock/'
    ],
    
    // Pages dans pages/ventes/
    [
        'pattern' => '/href="index\.php"/',
        'replacement' => 'href="../dashboard/index.php"',
        'path_contains' => 'pages/ventes/'
    ],
    [
        'pattern' => '/href="ventes\.php"/',
        'replacement' => 'href="ventes.php"',
        'path_contains' => 'pages/ventes/'
    ],
    
    // Pages dans pages/clients/
    [
        'pattern' => '/href="index\.php"/',
        'replacement' => 'href="../dashboard/index.php"',
        'path_contains' => 'pages/clients/'
    ],
    [
        'pattern' => '/href="clients\.php"/',
        'replacement' => 'href="clients.php"',
        'path_contains' => 'pages/clients/'
    ],
    
    // Pages dans pages/commandes/
    [
        'pattern' => '/href="index\.php"/',
        'replacement' => 'href="../dashboard/index.php"',
        'path_contains' => 'pages/commandes/'
    ],
    [
        'pattern' => '/href="commandes\.php"/',
        'replacement' => 'href="commandes.php"',
        'path_contains' => 'pages/commandes/'
    ],
    
    // Pages dans pages/fournisseurs/
    [
        'pattern' => '/href="index\.php"/',
        'replacement' => 'href="../dashboard/index.php"',
        'path_contains' => 'pages/fournisseurs/'
    ],
    [
        'pattern' => '/href="fournisseurs\.php"/',
        'replacement' => 'href="fournisseurs.php"',
        'path_contains' => 'pages/fournisseurs/'
    ],
    
    // Pages dans pages/tresorerie/
    [
        'pattern' => '/href="index\.php"/',
        'replacement' => 'href="../dashboard/index.php"',
        'path_contains' => 'pages/tresorerie/'
    ],
    [
        'pattern' => '/href="tresorerie\.php"/',
        'replacement' => 'href="tresorerie.php"',
        'path_contains' => 'pages/tresorerie/'
    ],
];

$processed = 0;
$errors = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filepath = $file->getRealPath();
        
        // Ignorer certains fichiers
        if (strpos($filepath, 'vendor') !== false || 
            strpos($filepath, 'node_modules') !== false ||
            strpos($filepath, '.backup') !== false ||
            strpos($filepath, 'fix_navbar_paths.php') !== false ||
            strpos($filepath, 'auth.php') !== false) {
            continue;
        }
        
        $content = file_get_contents($filepath);
        $original = $content;
        
        // Appliquer les remplacements selon le chemin
        foreach ($replacements as $replacement) {
            if (strpos($filepath, $replacement['path_contains']) !== false) {
                $content = preg_replace($replacement['pattern'], $replacement['replacement'], $content);
            }
        }
        
        // Si le contenu a changé, sauvegarder
        if ($content !== $original) {
            // Créer une sauvegarde
            $backup = $filepath . '.backup-navbar';
            file_put_contents($backup, $original);
            
            // Écrire le nouveau contenu
            if (file_put_contents($filepath, $content)) {
                $processed++;
                echo "✅ Modifié: $filepath\n";
            } else {
                $errors++;
                echo "❌ Erreur: $filepath\n";
            }
        }
    }
}

echo "\n=== Résumé ===\n";
echo "Fichiers modifiés: $processed\n";
echo "Erreurs: $errors\n";
echo "\n⚠️  Des fichiers de sauvegarde (.backup-navbar) ont été créés.\n";
echo "Vérifiez les modifications avant de supprimer les sauvegardes.\n";
?>

