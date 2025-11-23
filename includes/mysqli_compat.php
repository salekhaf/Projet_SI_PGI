<?php
/**
 * Wrapper de compatibilité mysqli pour PostgreSQL
 * Permet d'utiliser les fonctions mysqli_* avec PostgreSQL
 */

// Vérifier si on est en mode PostgreSQL
function is_postgresql_connection($conn) {
    return isset($GLOBALS['pdo_conn']) && is_object($conn) && get_class($conn) === 'PostgreSQLConnection';
}

/**
 * Wrapper pour mysqli_prepare
 */
if (!function_exists('mysqli_prepare_compat')) {
    function mysqli_prepare_compat($conn, $sql) {
        if (is_postgresql_connection($conn)) {
            return $conn->prepare($sql);
        }
        return $conn->prepare($sql);
    }
}

/**
 * Wrapper pour mysqli_query
 */
if (!function_exists('mysqli_query_compat')) {
    function mysqli_query_compat($conn, $sql) {
        if (is_postgresql_connection($conn)) {
            return $conn->query($sql);
        }
        return $conn->query($sql);
    }
}

/**
 * Wrapper pour mysqli_fetch_assoc
 */
if (!function_exists('mysqli_fetch_assoc_compat')) {
    function mysqli_fetch_assoc_compat($result) {
        if (is_object($result) && method_exists($result, 'fetch_assoc')) {
            return $result->fetch_assoc();
        }
        return (is_object($result) && method_exists($result, 'fetch_assoc') ? $result->fetch_assoc() : mysqli_fetch_assoc($result));
    }
}

/**
 * Wrapper pour mysqli_num_rows
 */
if (!function_exists('mysqli_num_rows_compat')) {
    function mysqli_num_rows_compat($result) {
        if (is_object($result) && method_exists($result, 'num_rows')) {
            return $result->num_rows();
        }
        return (is_object($result) && method_exists($result, 'num_rows') ? $result->num_rows() : mysqli_num_rows($result));
    }
}

/**
 * Wrapper pour mysqli_error
 */
if (!function_exists('mysqli_error_compat')) {
    function mysqli_error_compat($conn) {
        if (is_postgresql_connection($conn)) {
            return $conn->error();
        }
        return (isset($GLOBALS['is_postgresql']) && is_object($conn) && get_class($conn) === 'PostgreSQLConnection' ? $conn->error() : mysqli_error($conn));
    }
}

/**
 * Surcharge des fonctions mysqli si on est en mode PostgreSQL
 * Note: On ne peut pas vraiment surcharger les fonctions natives, donc on va
 * modifier db_conn.php pour créer des alias
 */
?>

