<?php
/**
 * Script final pour corriger TOUS les chemins de navigation
 */

$files_to_fix = [
    'pages/stock/categories.php',
    'pages/ventes/ventes.php',
    'pages/ventes/detailVente.php',
    'pages/clients/clients.php',
    'pages/commandes/commandes.php',
    'pages/commandes/detailCommande.php',
    'pages/fournisseurs/fournisseurs.php',
    'pages/tresorerie/tresorerie.php',
    'pages/dashboard/index.php',
];

$replacements = [
    // Patterns généraux à remplacer
    '/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/([^"]+)"/' => function($matches) {
        return 'href="../' . $matches[1] . '"';
    },
    '/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/([^"]+)"/' => function($matches) {
        return 'href="../' . $matches[1] . '"';
    },
];

$processed = 0;

foreach ($files_to_fix as $filepath) {
    $fullpath = __DIR__ . '/' . $filepath;
    
    if (!file_exists($fullpath)) {
        echo "⚠️  Fichier non trouvé: $fullpath\n";
        continue;
    }
    
    $content = file_get_contents($fullpath);
    $original = $content;
    
    // Déterminer le répertoire pour les remplacements spécifiques
    $dir = dirname($filepath);
    
    // Remplacements spécifiques selon le répertoire
    if ($dir === 'pages/stock') {
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/dashboard\/index\.php"/', 'href="../dashboard/index.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/ventes\/ventes\.php"/', 'href="../ventes/ventes.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/clients\/clients\.php"/', 'href="../clients/clients.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/commandes\/commandes\.php"/', 'href="../commandes/commandes.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/stock\/stock\.php"/', 'href="stock.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/stock\/categories\.php"/', 'href="categories.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/auth\/logout\.php"/', 'href="../auth/logout.php"', $content);
    } elseif ($dir === 'pages/ventes') {
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/dashboard\/index\.php"/', 'href="../dashboard/index.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/stock\/stock\.php"/', 'href="../stock/stock.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/ventes\/ventes\.php"/', 'href="ventes.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/clients\/clients\.php"/', 'href="../clients/clients.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/commandes\/commandes\.php"/', 'href="../commandes/commandes.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/stock\/categories\.php"/', 'href="../stock/categories.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/auth\/logout\.php"/', 'href="../auth/logout.php"', $content);
    } elseif ($dir === 'pages/clients' || $dir === 'pages/commandes' || $dir === 'pages/fournisseurs' || $dir === 'pages/tresorerie') {
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/dashboard\/index\.php"/', 'href="../dashboard/index.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/stock\/stock\.php"/', 'href="../stock/stock.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/ventes\/ventes\.php"/', 'href="../ventes/ventes.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/clients\/clients\.php"/', 'href="clients.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/commandes\/commandes\.php"/', 'href="commandes.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/stock\/categories\.php"/', 'href="../stock/categories.php"', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\/auth\/logout\.php"/', 'href="../auth/logout.php"', $content);
    }
    
    if ($content !== $original) {
        file_put_contents($fullpath, $content);
        $processed++;
        echo "✅ Corrigé: $filepath\n";
    }
}

echo "\n=== Résumé ===\n";
echo "Fichiers corrigés: $processed\n";
?>

