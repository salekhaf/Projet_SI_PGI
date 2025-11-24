<?php
/**
 * Compatibilité PDO MySQL / PostgreSQL
 * Version 100% propre, sans mysqli
 */

/**
 * Vérifie si une table existe (MySQL et PostgreSQL)
 */
function table_exists(PDO $pdo, string $table_name): bool
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'pgsql') {
        $stmt = $pdo->prepare("
            SELECT EXISTS (
                SELECT 1 
                FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = :t
            )
        ");
        $stmt->execute([':t' => $table_name]);
        return (bool)$stmt->fetchColumn();
    } else {
        // MySQL
        $stmt = $pdo->prepare("SHOW TABLES LIKE :t");
        $stmt->execute([':t' => $table_name]);
        return $stmt->rowCount() > 0;
    }
}


/**
 * Alias sécurisé pointant toujours vers db_last_id() (voir db_conn.php)
 */
function db_insert_id(PDO $pdo, string $table = null, string $col = 'id')
{
    return db_last_id($pdo, $table, $col);
}


/**
 * Convertit certaines fonctions MySQL vers PostgreSQL
 */
function convert_mysql_to_postgresql(string $sql): string
{
    // CURDATE() -> CURRENT_DATE
    $sql = preg_replace('/\bCURDATE\(\)/i', 'CURRENT_DATE', $sql);

    // YEARWEEK(date)
    $sql = preg_replace(
        '/YEARWEEK\(([^)]+)\)/i',
        "EXTRACT(YEAR FROM $1) * 100 + EXTRACT(WEEK FROM $1)",
        $sql
    );

    // DATE_SUB(...)
    $sql = preg_replace(
        "/DATE_SUB\(CURDATE\(\), INTERVAL\s+(\d+)\s+DAY\)/i",
        "CURRENT_DATE - INTERVAL '$1 DAY'",
        $sql
    );

    // MONTH(date)
    $sql = preg_replace('/\bMONTH\(([^)]+)\)/i', 'EXTRACT(MONTH FROM $1)', $sql);

    // YEAR(date)
    $sql = preg_replace('/\bYEAR\(([^)]+)\)/i', 'EXTRACT(YEAR FROM $1)', $sql);

    return $sql;
}
?>
