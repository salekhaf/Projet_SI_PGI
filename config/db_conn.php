<?php
// Configuration de la base de données
// Support MySQL (développement local) et PostgreSQL (production Render)
// Détection automatique du type de base de données

$db_type = getenv('DB_TYPE') ?: (getenv('DB_HOST') && strpos(getenv('DB_HOST'), 'postgres') !== false ? 'postgresql' : 'mysql');
$serveur = getenv('DB_HOST') ?: 'localhost';
$utilisateur = getenv('DB_USER') ?: 'root';
$motdepasse = getenv('DB_PASSWORD') ?: '';
$basededonnees = getenv('DB_NAME') ?: 'epicerie_db';
$port = getenv('DB_PORT') ?: null;

// Si c'est PostgreSQL (Render)
if ($db_type === 'postgresql' || strpos($serveur, 'postgres') !== false || strpos($serveur, 'dpg-') !== false) {
    // Utiliser PDO pour PostgreSQL
    try {
        if ($port) {
            $dsn = "pgsql:host=$serveur;port=$port;dbname=$basededonnees";
        } else {
            $dsn = "pgsql:host=$serveur;dbname=$basededonnees";
        }
        
        $pdo = new PDO($dsn, $utilisateur, $motdepasse, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // Créer une classe de compatibilité mysqli pour PostgreSQL
        class PostgreSQLConnection {
            private $pdo;
            private $last_result = null;
            
            public function __construct($pdo) {
                $this->pdo = $pdo;
            }
            
            public function query($sql) {
                // Convertir SHOW TABLES en requête PostgreSQL
                if (preg_match("/SHOW TABLES LIKE '([^']+)'/i", $sql, $matches)) {
                    $table_name = $matches[1];
                    $sql = "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = '$table_name')";
                    $stmt = $this->pdo->query($sql);
                    $exists = $stmt->fetchColumn();
                    // Retourner un résultat qui simule mysqli avec 0 ou 1 ligne
                    return new PostgreSQLShowTablesResult($exists);
                }
                
                $sql = $this->convertSQL($sql);
                try {
                    $stmt = $this->pdo->query($sql);
                    $this->last_result = new PostgreSQLResult($stmt);
                    return $this->last_result;
                } catch (PDOException $e) {
                    error_log("PostgreSQL Error: " . $e->getMessage() . " - SQL: " . $sql);
                    return false;
                }
            }
            
            public function prepare($sql) {
                $sql = $this->convertSQL($sql);
                return new PostgreSQLStatement($this->pdo->prepare($sql), $this->pdo);
            }
            
            public function insert_id() {
                // PostgreSQL utilise lastval() ou RETURNING
                $result = $this->pdo->query("SELECT lastval()");
                return $result->fetchColumn();
            }
            
            private function convertSQL($sql) {
                // Conversions MySQL -> PostgreSQL
                $sql = preg_replace('/\bCURDATE\(\)/i', 'CURRENT_DATE', $sql);
                $sql = preg_replace('/\bNOW\(\)/i', 'NOW()', $sql); // Identique
                
                // YEARWEEK() -> Approximation avec EXTRACT
                $sql = preg_replace('/YEARWEEK\(([^)]+)\)/i', "EXTRACT(YEAR FROM $1) * 100 + EXTRACT(WEEK FROM $1)", $sql);
                
                // DATE_SUB(CURDATE(), INTERVAL X DAY) -> CURRENT_DATE - INTERVAL 'X' DAY
                $sql = preg_replace("/DATE_SUB\(CURDATE\(\), INTERVAL\s+(\d+)\s+DAY\)/i", "CURRENT_DATE - INTERVAL '$1' DAY", $sql);
                $sql = preg_replace("/DATE_SUB\(CURRENT_DATE, INTERVAL\s+(\d+)\s+DAY\)/i", "CURRENT_DATE - INTERVAL '$1' DAY", $sql);
                
                // DATE() fonctionne aussi en PostgreSQL
                // MONTH() -> EXTRACT(MONTH FROM ...)
                $sql = preg_replace('/\bMONTH\(([^)]+)\)/i', 'EXTRACT(MONTH FROM $1)', $sql);
                // YEAR() -> EXTRACT(YEAR FROM ...)
                $sql = preg_replace('/\bYEAR\(([^)]+)\)/i', 'EXTRACT(YEAR FROM $1)', $sql);
                
                return $sql;
            }
            
            public function error() {
                $error = $this->pdo->errorInfo();
                return $error[2] ?? '';
            }
        }
        
        class PostgreSQLShowTablesResult {
            private $exists;
            
            public function __construct($exists) {
                $this->exists = $exists;
            }
            
            public function num_rows() {
                return $this->exists ? 1 : 0;
            }
        }
        
        class PostgreSQLResult {
            private $stmt;
            private $data = [];
            private $position = 0;
            
            public function __construct($stmt) {
                $this->stmt = $stmt;
                $this->data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            public function fetch_assoc() {
                if ($this->position < count($this->data)) {
                    return $this->data[$this->position++];
                }
                return false;
            }
            
            public function num_rows() {
                return count($this->data);
            }
            
            public function data_seek($offset) {
                $this->position = $offset;
            }
        }
        
        class PostgreSQLStatement {
            private $stmt;
            private $pdo;
            private $bound_params = [];
            
            public function __construct($stmt, $pdo) {
                $this->stmt = $stmt;
                $this->pdo = $pdo;
            }
            
            public function bind_param($types, ...$params) {
                // Stocker les paramètres pour bind_param qui est appelé avec des références
                $this->bound_params = $params;
                $i = 1;
                foreach ($params as $index => $param) {
                    $type = $types[$index] ?? 's';
                    $pdo_type = PDO::PARAM_STR;
                    if ($type === 'i') {
                        $pdo_type = PDO::PARAM_INT;
                    } elseif ($type === 'd') {
                        $pdo_type = PDO::PARAM_STR; // DECIMAL comme string
                    } elseif ($type === 'b') {
                        $pdo_type = PDO::PARAM_LOB;
                    }
                    $this->stmt->bindValue($i++, $param, $pdo_type);
                }
                return true;
            }
            
            public function execute() {
                return $this->stmt->execute();
            }
            
            public function get_result() {
                return new PostgreSQLResult($this->stmt);
            }
            
            public function close() {
                $this->stmt = null;
            }
        }
        
        // Créer l'objet de connexion compatible mysqli
        $conn = new PostgreSQLConnection($pdo);
        
        // Variable globale pour insert_id
        $GLOBALS['pdo_conn'] = $pdo;
        
    } catch (PDOException $e) {
        die("Erreur de connexion PostgreSQL : " . $e->getMessage());
    }
} else {
    // MySQL (développement local)
    if ($port) {
        $conn = mysqli_connect($serveur, $utilisateur, $motdepasse, $basededonnees, $port);
    } else {
        $conn = mysqli_connect($serveur, $utilisateur, $motdepasse, $basededonnees);
    }
    
    if (!$conn) {
        die("Erreur de connexion MySQL : " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, "utf8mb4");
}

// Fonction helper pour obtenir le dernier ID inséré (compatible MySQL et PostgreSQL)
// Utilisez cette fonction au lieu de mysqli_insert_id() directement
if (!function_exists('db_get_insert_id')) {
    function db_get_insert_id($connection) {
        if (isset($GLOBALS['pdo_conn'])) {
            // PostgreSQL
            try {
                $result = $GLOBALS['pdo_conn']->query("SELECT lastval()");
                return $result->fetchColumn();
            } catch (Exception $e) {
                return 0;
            }
        } else {
            // MySQL - utiliser la fonction native
            if (is_object($connection) && method_exists($connection, 'insert_id')) {
                return $connection->insert_id();
            }
            return mysqli_insert_id($connection);
        }
    }
}
?>
