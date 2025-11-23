<?php
/**
 * Script pour nettoyer les chemins avec trop de ../
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
        
        // Remplacer les chemins avec trop de ../
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\//', 'href="../', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/\.\.\/pages\//', 'href="../', $content);
        $content = preg_replace('/href="\.\.\/\.\.\/\.\.\/\.\.\/pages\//', 'href="../', $content);
        
        if ($content !== $original) {
            file_put_contents($filepath, $content);
            $processed++;
            echo "✅ Nettoyé: $filepath\n";
        }
    }
}

echo "\n=== Résumé ===\n";
echo "Fichiers nettoyés: $processed\n";
?>

