<?php
/**
 * Script pour corriger automatiquement TOUS les chemins de navigation dans toutes les pages
 */

function getCorrectPath($currentFile, $targetFile) {
    // Déterminer le chemin relatif correct depuis le fichier courant
    $currentFile = str_replace('\\', '/', $currentFile);
    
    // Extraire le répertoire du fichier courant (ex: pages/admin/)
    $currentDir = dirname($currentFile);
    
    // Mapping des fichiers vers leurs emplacements réels (relatifs à la racine pages/)
    $fileMap = [
        'index.php' => '../dashboard/index.php',
        'stock.php' => '../stock/stock.php',
        'ventes.php' => '../ventes/ventes.php',
        'clients.php' => '../clients/clients.php',
        'commandes.php' => '../commandes/commandes.php',
        'categories.php' => '../stock/categories.php',
        'fournisseurs.php' => '../fournisseurs/fournisseurs.php',
        'tresorerie.php' => '../tresorerie/tresorerie.php',
        'utilisateurs.php' => 'utilisateurs.php', // Même répertoire
        'demandes_acces.php' => 'demandes_acces.php', // Même répertoire
        'logout.php' => '../auth/logout.php',
    ];
    
    if (!isset($fileMap[$targetFile])) {
        return null;
    }
    
    $targetPath = $fileMap[$targetFile];
    
    // Si le chemin commence par ../, c'est déjà un chemin relatif depuis pages/
    // Sinon, c'est dans le même répertoire
    if (strpos($targetPath, '../') === 0) {
        // Compter le nombre de niveaux dans le répertoire courant
        // pages/admin/ = 1 niveau, pages/stock/ = 1 niveau, etc.
        $levels = substr_count($currentDir, '/') - substr_count(__DIR__ . '/pages', '/');
        $relativePath = str_repeat('../', $levels) . substr($targetPath, 3); // Enlever le premier ../
    } else {
        // Même répertoire
        return $targetPath;
    }
    
    return $relativePath;
}

$base_dir = __DIR__ . '/pages';
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base_dir),
    RecursiveIteratorIterator::SELF_FIRST
);

$processed = 0;
$errors = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filepath = $file->getRealPath();
        
        // Ignorer certains fichiers
        if (strpos($filepath, 'vendor') !== false || 
            strpos($filepath, 'node_modules') !== false ||
            strpos($filepath, '.backup') !== false ||
            strpos($filepath, 'fix_') !== false ||
            strpos($filepath, 'auth.php') !== false ||
            strpos($filepath, 'public/') !== false ||
            strpos($filepath, 'demandes_acces.php') !== false) { // Déjà corrigé manuellement
            continue;
        }
        
        $content = file_get_contents($filepath);
        $original = $content;
        
        // Remplacer tous les href avec des chemins relatifs incorrects
        $replacements = [
            '/href="index\.php"/' => 'index.php',
            '/href="stock\.php"/' => 'stock.php',
            '/href="ventes\.php"/' => 'ventes.php',
            '/href="clients\.php"/' => 'clients.php',
            '/href="commandes\.php"/' => 'commandes.php',
            '/href="categories\.php"/' => 'categories.php',
            '/href="fournisseurs\.php"/' => 'fournisseurs.php',
            '/href="tresorerie\.php"/' => 'tresorerie.php',
            '/href="utilisateurs\.php"/' => 'utilisateurs.php',
            '/href="demandes_acces\.php"/' => 'demandes_acces.php',
            '/href="logout\.php"/' => 'logout.php',
        ];
        
        foreach ($replacements as $pattern => $targetFile) {
            // Ne remplacer que si le href ne commence pas déjà par ../
            $content = preg_replace_callback($pattern, function($matches) use ($filepath, $targetFile) {
                $currentHref = $matches[0];
                // Si le href contient déjà ../ ou est un chemin absolu, ne pas le modifier
                if (strpos($currentHref, '../') !== false || strpos($currentHref, '/') === 5) {
                    return $currentHref;
                }
                $correctPath = getCorrectPath($filepath, $targetFile);
                if ($correctPath) {
                    return 'href="' . $correctPath . '"';
                }
                return $currentHref;
            }, $content);
        }
        
        // Si le contenu a changé, sauvegarder
        if ($content !== $original) {
            // Créer une sauvegarde
            $backup = $filepath . '.backup-navbar';
            if (!file_exists($backup)) {
                file_put_contents($backup, $original);
            }
            
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
?>

