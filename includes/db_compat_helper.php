<?php
/**
 * Helper de compatibilité MySQL/PostgreSQL
 * Fonctions pour gérer les différences entre les deux SGBD
 */

/**
 * Vérifie si une table existe (compatible MySQL et PostgreSQL)
 */
function table_exists($conn, $table_name) {
    if (isset($GLOBALS['pdo_conn'])) {
        // PostgreSQL
        try {
            $check = $GLOBALS['pdo_conn']->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = '$table_name')");
            return $check->fetchColumn();
        } catch (Exception $e) {
            return false;
        }
    } else {
        // MySQL
        $check_table = mysqli_query($conn, "SHOW TABLES LIKE '$table_name'");
        return mysqli_num_rows($check_table) > 0;
    }
}

/**
 * Obtient le dernier ID inséré (compatible MySQL et PostgreSQL)
 */
function db_insert_id($conn) {
    if (isset($GLOBALS['pdo_conn'])) {
        // PostgreSQL
        try {
            $result = $GLOBALS['pdo_conn']->query("SELECT lastval()");
            return $result->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    } else {
        // MySQL
        return mysqli_insert_id($conn);
    }
}

/**
 * Convertit une requête MySQL en PostgreSQL
 */
function convert_mysql_to_postgresql($sql) {
    // Conversions MySQL -> PostgreSQL
    $sql = preg_replace('/\bCURDATE\(\)/i', 'CURRENT_DATE', $sql);
    
    // YEARWEEK() -> Approximation
    $sql = preg_replace('/YEARWEEK\(([^)]+)\)/i', "EXTRACT(YEAR FROM $1) * 100 + EXTRACT(WEEK FROM $1)", $sql);
    
    // DATE_SUB(CURDATE(), INTERVAL X DAY) -> CURRENT_DATE - INTERVAL 'X' DAY
    $sql = preg_replace("/DATE_SUB\(CURDATE\(\), INTERVAL\s+(\d+)\s+DAY\)/i", "CURRENT_DATE - INTERVAL '$1' DAY", $sql);
    $sql = preg_replace("/DATE_SUB\(CURRENT_DATE, INTERVAL\s+(\d+)\s+DAY\)/i", "CURRENT_DATE - INTERVAL '$1' DAY", $sql);
    
    // MONTH() -> EXTRACT(MONTH FROM ...)
    $sql = preg_replace('/\bMONTH\(([^)]+)\)/i', 'EXTRACT(MONTH FROM $1)', $sql);
    // YEAR() -> EXTRACT(YEAR FROM ...)
    $sql = preg_replace('/\bYEAR\(([^)]+)\)/i', 'EXTRACT(YEAR FROM $1)', $sql);
    
    return $sql;
}
?>

