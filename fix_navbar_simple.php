<?php
/**
 * Script simple pour corriger les chemins de navigation
 */

$corrections = [
    // Pages dans pages/admin/
    'pages/admin/utilisateurs.php' => [
        'href="index.php"' => 'href="../dashboard/index.php"',
        'href="stock.php"' => 'href="../stock/stock.php"',
        'href="ventes.php"' => 'href="../ventes/ventes.php"',
        'href="clients.php"' => 'href="../clients/clients.php"',
        'href="commandes.php"' => 'href="../commandes/commandes.php"',
        'href="categories.php"' => 'href="../stock/categories.php"',
        'href="logout.php"' => 'href="../auth/logout.php"',
        'href="demandes_acces.php"' => 'href="demandes_acces.php"', // Même répertoire
    ],
    'pages/admin/demandes_acces.php' => [
        // Déjà corrigé avec navbar.php
    ],
    
    // Pages dans pages/stock/
    'pages/stock/stock.php' => [
        'href="index.php"' => 'href="../dashboard/index.php"',
        'href="ventes.php"' => 'href="../ventes/ventes.php"',
        'href="clients.php"' => 'href="../clients/clients.php"',
        'href="fournisseurs.php"' => 'href="../fournisseurs/fournisseurs.php"',
        'href="commandes.php"' => 'href="../commandes/commandes.php"',
        'href="categories.php"' => 'href="categories.php"', // Même répertoire
        'href="logout.php"' => 'href="../auth/logout.php"',
    ],
    'pages/stock/categories.php' => [
        'href="index.php"' => 'href="../dashboard/index.php"',
        'href="stock.php"' => 'href="stock.php"', // Même répertoire
        'href="ventes.php"' => 'href="../ventes/ventes.php"',
        'href="clients.php"' => 'href="../clients/clients.php"',
        'href="commandes.php"' => 'href="../commandes/commandes.php"',
        'href="logout.php"' => 'href="../auth/logout.php"',
    ],
    
    // Pages dans pages/ventes/
    'pages/ventes/ventes.php' => [
        'href="index.php"' => 'href="../dashboard/index.php"',
        'href="stock.php"' => 'href="../stock/stock.php"',
        'href="clients.php"' => 'href="../clients/clients.php"',
        'href="commandes.php"' => 'href="../commandes/commandes.php"',
        'href="categories.php"' => 'href="../stock/categories.php"',
        'href="logout.php"' => 'href="../auth/logout.php"',
    ],
    'pages/ventes/detailVente.php' => [
        'href="index.php"' => 'href="../dashboard/index.php"',
        'href="stock.php"' => 'href="../stock/stock.php"',
        'href="ventes.php"' => 'href="ventes.php"', // Même répertoire
        'href="clients.php"' => 'href="../clients/clients.php"',
        'href="commandes.php"' => 'href="../commandes/commandes.php"',
        'href="categories.php"' => 'href="../stock/categories.php"',
        'href="logout.php"' => 'href="../auth/logout.php"',
    ],
    
    // Pages dans pages/clients/
    'pages/clients/clients.php' => [
        'href="index.php"' => 'href="../dashboard/index.php"',
        'href="stock.php"' => 'href="../stock/stock.php"',
        'href="ventes.php"' => 'href="../ventes/ventes.php"',
        'href="commandes.php"' => 'href="../commandes/commandes.php"',
        'href="categories.php"' => 'href="../stock/categories.php"',
        'href="logout.php"' => 'href="../auth/logout.php"',
    ],
    
    // Pages dans pages/commandes/
    'pages/commandes/commandes.php' => [
        'href="index.php"' => 'href="../dashboard/index.php"',
        'href="stock.php"' => 'href="../stock/stock.php"',
        'href="ventes.php"' => 'href="../ventes/ventes.php"',
        'href="clients.php"' => 'href="../clients/clients.php"',
        'href="categories.php"' => 'href="../stock/categories.php"',
        'href="logout.php"' => 'href="../auth/logout.php"',
    ],
    'pages/commandes/detailCommande.php' => [
        'href="index.php"' => 'href="../dashboard/index.php"',
        'href="stock.php"' => 'href="../stock/stock.php"',
        'href="ventes.php"' => 'href="../ventes/ventes.php"',
        'href="clients.php"' => 'href="../clients/clients.php"',
        'href="commandes.php"' => 'href="commandes.php"', // Même répertoire
        'href="categories.php"' => 'href="../stock/categories.php"',
        'href="logout.php"' => 'href="../auth/logout.php"',
    ],
    
    // Pages dans pages/fournisseurs/
    'pages/fournisseurs/fournisseurs.php' => [
        'href="index.php"' => 'href="../dashboard/index.php"',
        'href="stock.php"' => 'href="../stock/stock.php"',
        'href="ventes.php"' => 'href="../ventes/ventes.php"',
        'href="clients.php"' => 'href="../clients/clients.php"',
        'href="commandes.php"' => 'href="../commandes/commandes.php"',
        'href="categories.php"' => 'href="../stock/categories.php"',
        'href="logout.php"' => 'href="../auth/logout.php"',
    ],
    
    // Pages dans pages/tresorerie/
    'pages/tresorerie/tresorerie.php' => [
        'href="index.php"' => 'href="../dashboard/index.php"',
        'href="stock.php"' => 'href="../stock/stock.php"',
        'href="ventes.php"' => 'href="../ventes/ventes.php"',
        'href="clients.php"' => 'href="../clients/clients.php"',
        'href="commandes.php"' => 'href="../commandes/commandes.php"',
        'href="categories.php"' => 'href="../stock/categories.php"',
        'href="logout.php"' => 'href="../auth/logout.php"',
    ],
];

$processed = 0;
$errors = 0;

foreach ($corrections as $filepath => $replacements) {
    $fullpath = __DIR__ . '/' . $filepath;
    
    if (!file_exists($fullpath)) {
        echo "⚠️  Fichier non trouvé: $fullpath\n";
        continue;
    }
    
    $content = file_get_contents($fullpath);
    $original = $content;
    
    // Appliquer les remplacements
    foreach ($replacements as $old => $new) {
        // Ne remplacer que si le chemin n'est pas déjà correct
        if (strpos($content, $old) !== false && strpos($content, $new) === false) {
            $content = str_replace($old, $new, $content);
        }
    }
    
    // Nettoyer les chemins avec trop de ../
    $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\//', 'href="../', $content);
    
    if ($content !== $original) {
        // Sauvegarde
        $backup = $fullpath . '.backup-navbar2';
        if (!file_exists($backup)) {
            file_put_contents($backup, $original);
        }
        
        if (file_put_contents($fullpath, $content)) {
            $processed++;
            echo "✅ Modifié: $filepath\n";
        } else {
            $errors++;
            echo "❌ Erreur: $filepath\n";
        }
    }
}

echo "\n=== Résumé ===\n";
echo "Fichiers modifiés: $processed\n";
echo "Erreurs: $errors\n";
?>

