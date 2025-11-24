<?php
/**
 * Connexion universelle MySQL / PostgreSQL via PDO
 * Compatible Render (PostgreSQL) et XAMPP local (MySQL)
 */

$db_type = getenv('DB_TYPE') ?: 'mysql';
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'epicerie_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$port = getenv('DB_PORT') ?: null;

// Construction du DSN
if ($db_type === 'postgresql') {

    $port = $port ?: 5432;
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

} else {

    $port = $port ?: 3306;
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
}

try {
    // Connexion universelle via PDO
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

} catch (PDOException $e) {
    die("Erreur de connexion à la base : " . $e->getMessage());
}

/**
 * Fonction générique pour récupérer le dernier ID
 * compatible MySQL et PostgreSQL
 */
function db_last_id(PDO $pdo, string $table = null, string $column = 'id') {

    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {

        // PostgreSQL : lastval() seulement si une séquence a été utilisée dans cette session
        return $pdo->query("SELECT lastval()")->fetchColumn();

    } else {
        // MySQL
        return $pdo->lastInsertId();
    }
}
?>
