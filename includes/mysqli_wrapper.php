<?php
/**
 * Wrapper global pour les fonctions mysqli_* compatible PostgreSQL
 * Ce fichier doit être inclus APRÈS db_conn.php
 * 
 * IMPORTANT: Comme PHP ne permet pas de surcharger les fonctions natives,
 * nous devons utiliser runkit ou modifier le code pour utiliser ces wrappers.
 * 
 * Solution alternative: Utiliser des fonctions avec des noms différents
 * et créer un script de remplacement automatique.
 */

// Vérifier si on est en mode PostgreSQL
if (isset($GLOBALS['is_postgresql']) && $GLOBALS['is_postgresql']) {
    
    // Créer des alias pour les fonctions mysqli_* si runkit est disponible
    if (extension_loaded('runkit')) {
        runkit_function_rename('mysqli_prepare', 'mysqli_prepare_original');
        runkit_function_add('mysqli_prepare', '$conn, $sql', '
            if (isset($GLOBALS["is_postgresql"]) && is_object($conn) && get_class($conn) === "PostgreSQLConnection") {
                return $conn->prepare($sql);
            }
            return mysqli_prepare_original($conn, $sql);
        ');
        
        runkit_function_rename('mysqli_query', 'mysqli_query_original');
        runkit_function_add('mysqli_query', '$conn, $sql', '
            if (isset($GLOBALS["is_postgresql"]) && is_object($conn) && get_class($conn) === "PostgreSQLConnection") {
                return $conn->query($sql);
            }
            return mysqli_query_original($conn, $sql);
        ');
        
        runkit_function_rename('mysqli_fetch_assoc', 'mysqli_fetch_assoc_original');
        runkit_function_add('mysqli_fetch_assoc', '$result', '
            if (is_object($result) && method_exists($result, "fetch_assoc")) {
                return $result->fetch_assoc();
            }
            return mysqli_fetch_assoc_original($result);
        ');
        
        runkit_function_rename('mysqli_num_rows', 'mysqli_num_rows_original');
        runkit_function_add('mysqli_num_rows', '$result', '
            if (is_object($result) && method_exists($result, "num_rows")) {
                return $result->num_rows();
            }
            return mysqli_num_rows_original($result);
        ');
    } else {
        // Si runkit n'est pas disponible, on doit utiliser une autre approche
        // Créer un script de remplacement automatique des fonctions mysqli_*
        // Voir replace_mysqli_functions.php
    }
}
?>

