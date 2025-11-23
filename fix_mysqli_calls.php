<?php
/**
 * Script pour remplacer automatiquement les appels mysqli_* par des versions compatibles PostgreSQL
 * 
 * Remplace dans tous les fichiers PHP :
 * - mysqli_prepare($conn, ...) -> $conn->prepare(...)
 * - mysqli_query($conn, ...) -> $conn->query(...)
 * - mysqli_fetch_assoc($result) -> (is_object($result) && method_exists($result, 'fetch_assoc') ? $result->fetch_assoc() : mysqli_fetch_assoc($result))
 * - mysqli_num_rows($result) -> (is_object($result) && method_exists($result, 'num_rows') ? $result->num_rows() : mysqli_num_rows($result))
 * - mysqli_stmt_bind_param($stmt, ...) -> $stmt->bind_param(...)
 * - mysqli_stmt_execute($stmt) -> $stmt->execute()
 * - mysqli_stmt_get_result($stmt) -> $stmt->get_result()
 * - mysqli_stmt_close($stmt) -> $stmt->close()
 * - mysqli_error($conn) -> (isset($GLOBALS['is_postgresql']) && is_object($conn) && get_class($conn) === 'PostgreSQLConnection' ? $conn->error() : mysqli_error($conn))
 * - mysqli_insert_id($conn) -> db_get_insert_id($conn)
 */

$base_dir = __DIR__;
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base_dir),
    RecursiveIteratorIterator::SELF_FIRST
);

$replacements = [
    // mysqli_prepare($conn, $sql) -> $conn->prepare($sql)
    '/mysqli_prepare\s*\(\s*\$conn\s*,\s*([^)]+)\s*\)/i' => '$conn->prepare($1)',
    
    // mysqli_query($conn, $sql) -> $conn->query($sql)
    '/mysqli_query\s*\(\s*\$conn\s*,\s*([^)]+)\s*\)/i' => '$conn->query($1)',
    
    // mysqli_fetch_assoc($result) -> (is_object($result) && method_exists($result, 'fetch_assoc') ? $result->fetch_assoc() : mysqli_fetch_assoc($result))
    '/mysqli_fetch_assoc\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)/i' => '(is_object($$1) && method_exists($$1, \'fetch_assoc\') ? $$1->fetch_assoc() : mysqli_fetch_assoc($$1))',
    
    // mysqli_num_rows($result) -> (is_object($result) && method_exists($result, 'num_rows') ? $result->num_rows() : mysqli_num_rows($result))
    '/mysqli_num_rows\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)/i' => '(is_object($$1) && method_exists($$1, \'num_rows\') ? $$1->num_rows() : mysqli_num_rows($$1))',
    
    // mysqli_stmt_bind_param($stmt, $types, ...) -> $stmt->bind_param($types, ...)
    '/mysqli_stmt_bind_param\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*,\s*([^,]+)\s*,\s*(.+?)\s*\)/i' => '$$1->bind_param($2, $3)',
    
    // mysqli_stmt_execute($stmt) -> $stmt->execute()
    '/mysqli_stmt_execute\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)/i' => '$$1->execute()',
    
    // mysqli_stmt_get_result($stmt) -> $stmt->get_result()
    '/mysqli_stmt_get_result\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)/i' => '$$1->get_result()',
    
    // mysqli_stmt_close($stmt) -> $stmt->close()
    '/mysqli_stmt_close\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)/i' => '$$1->close()',
    
    // mysqli_error($conn) -> (isset($GLOBALS['is_postgresql']) && is_object($conn) && get_class($conn) === 'PostgreSQLConnection' ? $conn->error() : mysqli_error($conn))
    '/mysqli_error\s*\(\s*\$conn\s*\)/i' => '(isset($GLOBALS[\'is_postgresql\']) && is_object($conn) && get_class($conn) === \'PostgreSQLConnection\' ? $conn->error() : mysqli_error($conn))',
    
    // mysqli_insert_id($conn) -> db_get_insert_id($conn)
    '/mysqli_insert_id\s*\(\s*\$conn\s*\)/i' => 'db_get_insert_id($conn)',
];

$processed = 0;
$errors = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filepath = $file->getRealPath();
        
        // Ignorer certains fichiers
        if (strpos($filepath, 'vendor') !== false || 
            strpos($filepath, 'node_modules') !== false ||
            strpos($filepath, 'fix_mysqli_calls.php') !== false ||
            strpos($filepath, 'replace_mysqli_functions.php') !== false) {
            continue;
        }
        
        $content = file_get_contents($filepath);
        $original = $content;
        
        // Appliquer les remplacements
        foreach ($replacements as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        // Si le contenu a changé, sauvegarder
        if ($content !== $original) {
            // Créer une sauvegarde
            $backup = $filepath . '.backup';
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
echo "\n⚠️  Des fichiers de sauvegarde (.backup) ont été créés.\n";
echo "Vérifiez les modifications avant de supprimer les sauvegardes.\n";
?>

