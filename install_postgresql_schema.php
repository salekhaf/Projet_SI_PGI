<?php
/**
 * Script pour installer le schéma PostgreSQL sur Render
 * À exécuter une fois après le déploiement
 */

// Lire les variables d'environnement
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'epicerie_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$port = getenv('DB_PORT') ?: 5432;

echo "<h2>🗄️ Installation du schéma PostgreSQL</h2>";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "<p>✅ Connexion à PostgreSQL réussie</p>";
    
    // Lire le fichier SQL
    $sql_file = __DIR__ . '/database/db_postgresql.sql';
    if (!file_exists($sql_file)) {
        die("<p>❌ Fichier SQL non trouvé : $sql_file</p>");
    }
    
    $sql = file_get_contents($sql_file);
    
    // Exécuter les commandes SQL une par une
    $statements = explode(';', $sql);
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Ignorer les erreurs "already exists"
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'duplicate') === false) {
                echo "<p style='color: orange;'>⚠️ " . htmlspecialchars($e->getMessage()) . "</p>";
                $errors++;
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>Résumé :</h3>";
    echo "<p><strong>$executed</strong> commandes SQL exécutées</p>";
    if ($errors > 0) {
        echo "<p style='color: orange;'><strong>$errors</strong> avertissements</p>";
    }
    echo "<p style='color: green;'><strong>✅ Schéma installé avec succès !</strong></p>";
    echo "<p><a href='../index.php'>Retour à l'application</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur de connexion : " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Vérifiez vos variables d'environnement :</p>";
    echo "<ul>";
    echo "<li>DB_HOST: " . htmlspecialchars($host) . "</li>";
    echo "<li>DB_NAME: " . htmlspecialchars($dbname) . "</li>";
    echo "<li>DB_USER: " . htmlspecialchars($username) . "</li>";
    echo "<li>DB_PORT: " . htmlspecialchars($port) . "</li>";
    echo "</ul>";
}
?>

