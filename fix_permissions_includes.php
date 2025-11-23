<?php
/**
 * Script pour remplacer tous les include/require de permissions_helper.php par include_once
 */

$base_dir = __DIR__ . '/pages';
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base_dir),
    RecursiveIteratorIterator::SELF_FIRST
);

$processed = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filepath = $file->getRealPath();
        
        // Ignorer certains fichiers
        if (strpos($filepath, 'vendor') !== false || 
            strpos($filepath, 'node_modules') !== false ||
            strpos($filepath, '.backup') !== false ||
            strpos($filepath, 'fix_') !== false ||
            strpos($filepath, 'auth.php') !== false ||
            strpos($filepath, 'public/') !== false) {
            continue;
        }
        
        $content = file_get_contents($filepath);
        $original = $content;
        
        // Remplacer include par include_once pour permissions_helper
        $content = preg_replace(
            "/(include|require)\s*\(\s*['\"]\.\.\/\.\.\/includes\/permissions_helper\.php['\"]\s*\)/",
            'include_once($1)',
            $content
        );
        
        // Correction : include_once('../../includes/permissions_helper.php')
        $content = str_replace(
            "include_once(include('../../includes/permissions_helper.php'))",
            "include_once('../../includes/permissions_helper.php')",
            $content
        );
        $content = str_replace(
            "include_once(require('../../includes/permissions_helper.php'))",
            "include_once('../../includes/permissions_helper.php')",
            $content
        );
        
        // Remplacer directement
        $content = str_replace(
            "include('../../includes/permissions_helper.php')",
            "include_once('../../includes/permissions_helper.php')",
            $content
        );
        $content = str_replace(
            "require('../../includes/permissions_helper.php')",
            "include_once('../../includes/permissions_helper.php')",
            $content
        );
        
        if ($content !== $original) {
            file_put_contents($filepath, $content);
            $processed++;
            echo "✅ Modifié: $filepath\n";
        }
    }
}

echo "\n=== Résumé ===\n";
echo "Fichiers modifiés: $processed\n";
?>

