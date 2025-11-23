<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

include('../../config/db_conn.php');
include('../../includes/historique_helper.php');
include('../../includes/export_helper.php');
include('../../includes/role_helper.php');
include('../../includes/permissions_helper.php');

$role = $_SESSION['role'];
$id_utilisateur = $_SESSION['id_utilisateur'];
$message = "";

// Créer la table depenses_diverses si elle n'existe pas
$table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'depenses_diverses'");
if (mysqli_num_rows($table_exists) == 0) {
    $create_table = "CREATE TABLE IF NOT EXISTS depenses_diverses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type_operation ENUM('depense', 'entree') NOT NULL,
        libelle VARCHAR(255) NOT NULL,
        montant DECIMAL(10,2) NOT NULL,
        date_operation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        id_utilisateur INT NOT NULL,
        notes TEXT NULL,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $create_table);
}

// === GESTION DES ACCÈS ===
$est_admin = ($role === 'admin');
$est_vendeur = ($role === 'vendeur');

// Vérifier si l'utilisateur a la permission d'accès à la trésorerie
$acces_autorise = false;
if ($est_vendeur || !$est_admin) {
    $acces_autorise = aPermission($conn, $id_utilisateur, 'acces_tresorerie');
}

// Si utilisateur sans accès, vérifier s'il a déjà une demande en attente
$demande_en_attente = false;
if (!$est_admin && !$acces_autorise) {
    $check_attente = mysqli_prepare($conn, "
        SELECT id FROM demandes_acces 
        WHERE id_utilisateur = ? 
        AND (permission_demande = 'acces_tresorerie' OR permission_demande = 'acces_general')
        AND statut = 'en_attente'
        LIMIT 1
    ");
    mysqli_stmt_bind_param($check_attente, "i", $id_utilisateur);
    mysqli_stmt_execute($check_attente);
    $result_attente = mysqli_stmt_get_result($check_attente);
    $demande_en_attente = (mysqli_num_rows($result_attente) > 0);
    mysqli_stmt_close($check_attente);
}

// Créer une demande d'accès
if (!$est_admin && !$acces_autorise && !$demande_en_attente && isset($_POST['demander_acces'])) {
    $raison = trim($_POST['raison'] ?? '');
    if (empty($raison)) {
        $message = "⚠️ Veuillez indiquer une raison pour votre demande.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO demandes_acces (id_utilisateur, type_demande, permission_demande, raison) VALUES (?, 'permission_specifique', 'acces_tresorerie', ?)");
        mysqli_stmt_bind_param($stmt, "is", $id_utilisateur, $raison);
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ Votre demande d'accès a été envoyée. Un administrateur va l'examiner.";
            $demande_en_attente = true;
        } else {
            $message = "❌ Erreur lors de l'envoi de la demande.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Si vendeur sans accès et sans demande, afficher le formulaire de demande
if (!$est_admin && !$acces_autorise && !$demande_en_attente) {
    // Afficher la page de demande d'accès
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
    <meta charset="UTF-8">
    <title>💰 Trésorerie - Accès limité - Smart Stock</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../../assets/css/styles_connected.css">
    </head>
    <body>
    <header>
        <nav class="navbar">
            <div class="nav-left">
                <a href="index.php" class="logo-link">
                    <img src="../../assets/images/logo_epicerie.png" alt="Logo" class="logo-navbar">
                </a>
                <a href="index.php" class="nav-link">Tableau de bord</a>
            </div>
            <a href="logout.php" class="logout">🚪 Déconnexion</a>
        </nav>
    </header>
    <div class="main-container">
        <div class="content-wrapper">
            <h1>💰 Trésorerie</h1>
            <div class="alert alert-warning">
                <h3>🔐 Accès limité</h3>
                <p>Vous n'avez pas accès à cette page. Veuillez demander l'autorisation à un administrateur.</p>
            </div>
            <?php if ($message): ?>
                <div class="alert <?= strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <form method="POST" class="form-container">
                <h3>Demander un accès à la trésorerie</h3>
                <div class="form-group">
                    <label>Raison de la demande *</label>
                    <textarea name="raison" class="form-control" rows="4" required placeholder="Ex: Besoin de consulter le chiffre d'affaires du mois pour préparer un rapport..."></textarea>
                </div>
                <button type="submit" name="demander_acces" class="btn btn-primary">📤 Envoyer la demande</button>
                <a href="index.php" class="btn btn-secondary">⬅️ Retour au tableau de bord</a>
            </form>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit();
}

// Si utilisateur avec demande en attente (mais pas encore approuvée)
if (!$est_admin && !$acces_autorise && $demande_en_attente) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
    <meta charset="UTF-8">
    <title>💰 Trésorerie - Accès limité - Smart Stock</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../../assets/css/styles_connected.css">
    </head>
    <body>
    <header>
        <nav class="navbar">
            <div class="nav-left">
                <a href="index.php" class="logo-link">
                    <img src="../../assets/images/logo_epicerie.png" alt="Logo" class="logo-navbar">
                </a>
                <a href="index.php" class="nav-link">Tableau de bord</a>
            </div>
            <a href="logout.php" class="logout">🚪 Déconnexion</a>
        </nav>
    </header>
    <div class="main-container">
        <div class="content-wrapper">
            <h1>💰 Trésorerie</h1>
            <div class="alert alert-info">
                <h3>⏳ Demande en attente</h3>
                <p>Votre demande d'accès a été envoyée et est en cours d'examen par un administrateur.</p>
                <p>Vous pouvez consulter l'historique de vos demandes dans la page <a href="demandes_acces.php">Demandes d'accès</a>.</p>
            </div>
            <a href="index.php" class="btn btn-secondary">⬅️ Retour au tableau de bord</a>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit();
}

// === FONCTIONNALITÉS ADMIN ===

// Filtre de période
$periode = $_GET['periode'] ?? 'mois';
$date_debut = '';
$date_fin = '';

switch ($periode) {
    case 'jour':
        $date_debut = date('Y-m-d');
        $date_fin = date('Y-m-d');
        break;
    case 'semaine':
        $date_debut = date('Y-m-d', strtotime('monday this week'));
        $date_fin = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'mois':
        $date_debut = date('Y-m-01');
        $date_fin = date('Y-m-t');
        break;
    case 'annee':
        $date_debut = date('Y-01-01');
        $date_fin = date('Y-12-31');
        break;
    case 'personnalise':
        $date_debut = $_GET['date_debut'] ?? date('Y-m-01');
        $date_fin = $_GET['date_fin'] ?? date('Y-m-t');
        break;
}

// Export CSV
if ($est_admin && isset($_GET['export']) && $_GET['export'] === 'csv') {
    $query = "
        SELECT 
            DATE(date_vente) as date,
            'Vente' as type,
            total as montant,
            CONCAT('Vente #', id) as libelle
        FROM ventes
        WHERE DATE(date_vente) BETWEEN '$date_debut' AND '$date_fin'
        UNION ALL
        SELECT 
            DATE(date_achat) as date,
            'Achat' as type,
            montant_total as montant,
            CONCAT('Achat #', id) as libelle
        FROM achats
        WHERE DATE(date_achat) BETWEEN '$date_debut' AND '$date_fin'
        UNION ALL
        SELECT 
            DATE(date_operation) as date,
            IF(type_operation = 'depense', 'Dépense', 'Entrée') as type,
            IF(type_operation = 'depense', -montant, montant) as montant,
            libelle
        FROM depenses_diverses
        WHERE DATE(date_operation) BETWEEN '$date_debut' AND '$date_fin'
        ORDER BY date DESC
    ";
    $headers = ['Date', 'Type', 'Montant (€)', 'Libellé'];
    export_excel($conn, $query, 'tresorerie_' . $periode . '_' . date('Y-m-d'), $headers);
}

// Ajout d'une dépense/entrée diverse
if ($est_admin && isset($_POST['ajouter_operation'])) {
    $type_op = $_POST['type_operation'];
    $libelle = trim($_POST['libelle']);
    $montant = floatval($_POST['montant']);
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($libelle) || $montant <= 0) {
        $message = "⚠️ Veuillez remplir tous les champs correctement.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO depenses_diverses (type_operation, libelle, montant, id_utilisateur, notes) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssdis", $type_op, $libelle, $montant, $id_utilisateur, $notes);
        if (mysqli_stmt_execute($stmt)) {
            $id_operation = mysqli_insert_id($conn);
            enregistrer_historique($conn, $id_utilisateur, 'INSERT', 'depenses_diverses', $id_operation, "Ajout d'une opération de trésorerie : $libelle");
            $message = "✅ Opération enregistrée avec succès.";
        } else {
            $message = "❌ Erreur lors de l'enregistrement.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Modification d'une dépense/entrée diverse
if ($est_admin && isset($_POST['modifier_operation'])) {
    $id_op = intval($_POST['id_operation']);
    $type_op = $_POST['type_operation'];
    $libelle = trim($_POST['libelle']);
    $montant = floatval($_POST['montant']);
    $notes = trim($_POST['notes'] ?? '');
    
    // Récupérer anciennes valeurs
    $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM depenses_diverses WHERE id = $id_op"));
    
    $stmt = mysqli_prepare($conn, "UPDATE depenses_diverses SET type_operation = ?, libelle = ?, montant = ?, notes = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssdsi", $type_op, $libelle, $montant, $notes, $id_op);
    if (mysqli_stmt_execute($stmt)) {
        enregistrer_historique($conn, $id_utilisateur, 'UPDATE', 'depenses_diverses', $id_op, "Modification d'une opération de trésorerie", $old, ['type_operation' => $type_op, 'libelle' => $libelle, 'montant' => $montant, 'notes' => $notes]);
        $message = "✅ Opération modifiée avec succès.";
    } else {
        $message = "❌ Erreur lors de la modification.";
    }
    mysqli_stmt_close($stmt);
}

// Suppression d'une dépense/entrée diverse
if ($est_admin && isset($_GET['supprimer_operation'])) {
    $id_op = intval($_GET['supprimer_operation']);
    $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM depenses_diverses WHERE id = $id_op"));
    mysqli_query($conn, "DELETE FROM depenses_diverses WHERE id = $id_op");
    enregistrer_historique($conn, $id_utilisateur, 'DELETE', 'depenses_diverses', $id_op, "Suppression d'une opération de trésorerie", $old);
    $message = "✅ Opération supprimée avec succès.";
    header("Location: tresorerie.php?periode=$periode");
    exit();
}

// === CALCUL DES STATISTIQUES ===

// CA du jour
$ca_jour_query = "SELECT SUM(total) as total FROM ventes WHERE DATE(date_vente) = CURDATE()";
$ca_jour_result = mysqli_query($conn, $ca_jour_query);
$ca_jour = floatval(mysqli_fetch_assoc($ca_jour_result)['total'] ?? 0);

// CA du mois
$ca_mois_query = "SELECT SUM(total) as total FROM ventes WHERE MONTH(date_vente) = MONTH(CURDATE()) AND YEAR(date_vente) = YEAR(CURDATE())";
$ca_mois_result = mysqli_query($conn, $ca_mois_query);
$ca_mois = floatval(mysqli_fetch_assoc($ca_mois_result)['total'] ?? 0);

// CA total
$ca_total_query = "SELECT SUM(total) as total FROM ventes";
$ca_total_result = mysqli_query($conn, $ca_total_query);
$ca_total = floatval(mysqli_fetch_assoc($ca_total_result)['total'] ?? 0);

// Total achats fournisseurs
$achats_total_query = "SELECT SUM(montant_total) as total FROM achats";
$achats_total_result = mysqli_query($conn, $achats_total_query);
$achats_total = floatval(mysqli_fetch_assoc($achats_total_result)['total'] ?? 0);

// Achats de la période
$achats_periode_query = "SELECT SUM(montant_total) as total FROM achats WHERE DATE(date_achat) BETWEEN '$date_debut' AND '$date_fin'";
$achats_periode_result = mysqli_query($conn, $achats_periode_query);
$achats_periode = floatval(mysqli_fetch_assoc($achats_periode_result)['total'] ?? 0);

// CA de la période
$ca_periode_query = "SELECT SUM(total) as total FROM ventes WHERE DATE(date_vente) BETWEEN '$date_debut' AND '$date_fin'";
$ca_periode_result = mysqli_query($conn, $ca_periode_query);
$ca_periode = floatval(mysqli_fetch_assoc($ca_periode_result)['total'] ?? 0);

// Dépenses diverses (total)
$depenses_diverses_query = "SELECT 
    SUM(CASE WHEN type_operation = 'depense' THEN montant ELSE 0 END) as total_depenses,
    SUM(CASE WHEN type_operation = 'entree' THEN montant ELSE 0 END) as total_entrees
    FROM depenses_diverses";
$depenses_diverses_result = mysqli_query($conn, $depenses_diverses_query);
$depenses_diverses = mysqli_fetch_assoc($depenses_diverses_result);
$total_depenses_diverses = floatval($depenses_diverses['total_depenses'] ?? 0);
$total_entrees_diverses = floatval($depenses_diverses['total_entrees'] ?? 0);

// Dépenses diverses de la période
$depenses_periode_query = "SELECT 
    SUM(CASE WHEN type_operation = 'depense' THEN montant ELSE 0 END) as total_depenses,
    SUM(CASE WHEN type_operation = 'entree' THEN montant ELSE 0 END) as total_entrees
    FROM depenses_diverses
    WHERE DATE(date_operation) BETWEEN '$date_debut' AND '$date_fin'";
$depenses_periode_result = mysqli_query($conn, $depenses_periode_query);
$depenses_periode = mysqli_fetch_assoc($depenses_periode_result);
$total_depenses_periode = floatval($depenses_periode['total_depenses'] ?? 0);
$total_entrees_periode = floatval($depenses_periode['total_entrees'] ?? 0);

// Marge brute (ventes - achats)
$marge_brute = $ca_total - $achats_total;
$marge_brute_periode = $ca_periode - $achats_periode;

// Bénéfice estimé (marge brute - dépenses diverses + entrées diverses)
$benefice_estime = $marge_brute - $total_depenses_diverses + $total_entrees_diverses;
$benefice_estime_periode = $marge_brute_periode - $total_depenses_periode + $total_entrees_periode;

// === DONNÉES POUR GRAPHIQUES ===

// Évolution des ventes (7 derniers jours)
$ventes_7jours = mysqli_query($conn, "
    SELECT DATE(date_vente) as date, SUM(total) as total
    FROM ventes
    WHERE date_vente >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(date_vente)
    ORDER BY date ASC
");

$labels_ventes = [];
$data_ventes = [];
while ($row = mysqli_fetch_assoc($ventes_7jours)) {
    $labels_ventes[] = date('d/m', strtotime($row['date']));
    $data_ventes[] = floatval($row['total']);
}

// Évolution des achats (7 derniers jours)
$achats_7jours = mysqli_query($conn, "
    SELECT DATE(date_achat) as date, SUM(montant_total) as total
    FROM achats
    WHERE date_achat >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(date_achat)
    ORDER BY date ASC
");

$labels_achats = [];
$data_achats = [];
$dates_achats = [];
while ($row = mysqli_fetch_assoc($achats_7jours)) {
    $dates_achats[] = date('d/m', strtotime($row['date']));
    $data_achats[] = floatval($row['total']);
}

// Fusionner les dates pour le graphique comparatif
$all_dates = array_unique(array_merge($labels_ventes, $dates_achats));
sort($all_dates);

// Remplir les données manquantes avec 0
$data_ventes_complete = [];
$data_achats_complete = [];
foreach ($all_dates as $date) {
    $idx_vente = array_search($date, $labels_ventes);
    $data_ventes_complete[] = $idx_vente !== false ? $data_ventes[$idx_vente] : 0;
    
    $idx_achat = array_search($date, $dates_achats);
    $data_achats_complete[] = $idx_achat !== false ? $data_achats[$idx_achat] : 0;
}

// Bénéfice net par période (30 derniers jours)
$benefices_30jours = mysqli_query($conn, "
    SELECT 
        DATE(v.date_vente) as date,
        COALESCE(SUM(v.total), 0) as ventes,
        COALESCE(SUM(a.montant_total), 0) as achats,
        COALESCE(SUM(CASE WHEN d.type_operation = 'depense' THEN d.montant ELSE 0 END), 0) as depenses,
        COALESCE(SUM(CASE WHEN d.type_operation = 'entree' THEN d.montant ELSE 0 END), 0) as entrees
    FROM (
        SELECT DISTINCT DATE(date_vente) as date FROM ventes WHERE date_vente >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        UNION
        SELECT DISTINCT DATE(date_achat) as date FROM achats WHERE date_achat >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        UNION
        SELECT DISTINCT DATE(date_operation) as date FROM depenses_diverses WHERE date_operation >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ) dates
    LEFT JOIN ventes v ON DATE(v.date_vente) = dates.date
    LEFT JOIN achats a ON DATE(a.date_achat) = dates.date
    LEFT JOIN depenses_diverses d ON DATE(d.date_operation) = dates.date
    GROUP BY dates.date
    ORDER BY dates.date ASC
");

$labels_benefices = [];
$data_benefices = [];
while ($row = mysqli_fetch_assoc($benefices_30jours)) {
    $labels_benefices[] = date('d/m', strtotime($row['date']));
    $benefice_jour = floatval($row['ventes']) - floatval($row['achats']) - floatval($row['depenses']) + floatval($row['entrees']);
    $data_benefices[] = $benefice_jour;
}

// Liste des opérations (pour l'historique)
$operations_query = "
    SELECT 
        DATE(date_vente) as date,
        'Vente' as type,
        total as montant,
        CONCAT('Vente #', id) as libelle,
        NULL as notes,
        id as id_vente,
        NULL as id_operation
    FROM ventes
    WHERE DATE(date_vente) BETWEEN '$date_debut' AND '$date_fin'
    UNION ALL
    SELECT 
        DATE(date_achat) as date,
        'Achat' as type,
        montant_total as montant,
        CONCAT('Achat #', id) as libelle,
        NULL as notes,
        NULL as id_vente,
        NULL as id_operation
    FROM achats
    WHERE DATE(date_achat) BETWEEN '$date_debut' AND '$date_fin'
    UNION ALL
    SELECT 
        DATE(date_operation) as date,
        IF(type_operation = 'depense', 'Dépense', 'Entrée') as type,
        montant,
        libelle,
        notes,
        NULL as id_vente,
        id as id_operation
    FROM depenses_diverses
    WHERE DATE(date_operation) BETWEEN '$date_debut' AND '$date_fin'
    ORDER BY date DESC, montant DESC
    LIMIT 100
";

$operations = mysqli_query($conn, $operations_query);

// Récupérer une opération pour modification (admin)
$operation_edit = null;
if ($est_admin && isset($_GET['editer_operation'])) {
    $id_edit = intval($_GET['editer_operation']);
    $operation_edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM depenses_diverses WHERE id = $id_edit"));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>💰 Trésorerie - Smart Stock</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../assets/css/styles_connected.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.chart-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.chart-title {
    font-size: 1.1em;
    font-weight: bold;
    margin-bottom: 15px;
    color: #333;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}
.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid var(--primary-color);
}
.stat-card.success {
    border-left-color: var(--success-color);
}
.stat-card.danger {
    border-left-color: var(--danger-color);
}
.stat-card.warning {
    border-left-color: #ffc107;
}
.stat-card.info {
    border-left-color: #17a2b8;
}
.stat-card h3 {
    font-size: 0.85em;
    color: #666;
    margin: 0 0 10px 0;
    text-transform: uppercase;
    font-weight: 600;
}
.stat-card .value {
    font-size: 1.8em;
    font-weight: bold;
    color: #333;
}
.filter-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.filter-group {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}
.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 30px;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
}
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
.close:hover {
    color: #000;
}
</style>
</head>
<body>

<header>
    <nav class="navbar">
        <div class="nav-left">
            <a href="index.php" class="logo-link">
                <img src="../../assets/images/logo_epicerie.png" alt="Logo" class="logo-navbar">
            </a>
            <a href="index.php" class="nav-link">Tableau de bord</a>
            <a href="tresorerie.php" class="nav-link active">Trésorerie</a>
            <?php if ($est_admin): ?>
                <a href="utilisateurs.php" class="nav-link">Utilisateurs</a>
                <a href="demandes_acces.php" class="nav-link">Demandes</a>
            <?php endif; ?>
        </div>
        <a href="logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="main-container">
    <div class="content-wrapper">
        <h1>💰 Trésorerie</h1>
        
        <?php if ($message): ?>
            <div class="alert <?= strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($est_admin): ?>
            <div class="filter-section">
                <h3 style="margin-top: 0;">📅 Filtres de période</h3>
                <form method="GET" class="filter-group">
                    <select name="periode" class="form-control" style="width: auto;" onchange="this.form.submit()">
                        <option value="jour" <?= $periode === 'jour' ? 'selected' : '' ?>>Aujourd'hui</option>
                        <option value="semaine" <?= $periode === 'semaine' ? 'selected' : '' ?>>Cette semaine</option>
                        <option value="mois" <?= $periode === 'mois' ? 'selected' : '' ?>>Ce mois</option>
                        <option value="annee" <?= $periode === 'annee' ? 'selected' : '' ?>>Cette année</option>
                        <option value="personnalise" <?= $periode === 'personnalise' ? 'selected' : '' ?>>Personnalisé</option>
                    </select>
                    <?php if ($periode === 'personnalise'): ?>
                        <input type="date" name="date_debut" value="<?= htmlspecialchars($date_debut) ?>" class="form-control" style="width: auto;">
                        <span>au</span>
                        <input type="date" name="date_fin" value="<?= htmlspecialchars($date_fin) ?>" class="form-control" style="width: auto;">
                        <button type="submit" class="btn btn-primary">Appliquer</button>
                    <?php endif; ?>
                    <a href="?export=csv&periode=<?= urlencode($periode) ?>&date_debut=<?= urlencode($date_debut) ?>&date_fin=<?= urlencode($date_fin) ?>" class="btn btn-secondary">📥 Exporter CSV</a>
                </form>
            </div>

            <div style="margin-bottom: 20px;">
                <button onclick="document.getElementById('modalOperation').style.display='block'" class="btn btn-primary">➕ Ajouter une opération</button>
            </div>
        <?php endif; ?>

        <!-- Résumé financier -->
        <h2>📊 Résumé financier</h2>
        <div class="stats-grid">
            <div class="stat-card success">
                <h3>CA du jour</h3>
                <div class="value"><?= number_format($ca_jour, 2, ',', ' ') ?> €</div>
            </div>
            <div class="stat-card success">
                <h3>CA du mois</h3>
                <div class="value"><?= number_format($ca_mois, 2, ',', ' ') ?> €</div>
            </div>
            <div class="stat-card success">
                <h3>CA total</h3>
                <div class="value"><?= number_format($ca_total, 2, ',', ' ') ?> €</div>
            </div>
            <div class="stat-card danger">
                <h3>Total achats fournisseurs</h3>
                <div class="value"><?= number_format($achats_total, 2, ',', ' ') ?> €</div>
            </div>
            <div class="stat-card info">
                <h3>Marge brute</h3>
                <div class="value" style="color: <?= $marge_brute >= 0 ? 'var(--success-color)' : 'var(--danger-color)' ?>;">
                    <?= number_format($marge_brute, 2, ',', ' ') ?> €
                </div>
            </div>
            <div class="stat-card <?= $benefice_estime >= 0 ? 'success' : 'danger' ?>">
                <h3>Bénéfice estimé</h3>
                <div class="value"><?= number_format($benefice_estime, 2, ',', ' ') ?> €</div>
            </div>
            <div class="stat-card danger">
                <h3>Dépenses diverses</h3>
                <div class="value"><?= number_format($total_depenses_diverses, 2, ',', ' ') ?> €</div>
            </div>
            <?php if ($periode !== 'mois'): ?>
            <div class="stat-card info">
                <h3>Période sélectionnée</h3>
                <div class="value" style="font-size: 1.2em;">
                    CA: <?= number_format($ca_periode, 2, ',', ' ') ?> €<br>
                    <small style="font-size: 0.6em;">Achats: <?= number_format($achats_periode, 2, ',', ' ') ?> €</small><br>
                    <small style="font-size: 0.6em;">Bénéfice: <?= number_format($benefice_estime_periode, 2, ',', ' ') ?> €</small>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Graphiques -->
        <h2>📈 Graphiques financiers</h2>
        
        <div class="chart-container">
            <div class="chart-title">📈 Évolution des ventes (7 derniers jours)</div>
            <canvas id="chartVentes"></canvas>
        </div>

        <div class="chart-container">
            <div class="chart-title">📉 Évolution des achats fournisseurs (7 derniers jours)</div>
            <canvas id="chartAchats"></canvas>
        </div>

        <div class="chart-container">
            <div class="chart-title">💵 Bénéfice net par période (30 derniers jours)</div>
            <canvas id="chartBenefices"></canvas>
        </div>

        <div class="chart-container">
            <div class="chart-title">📊 Comparaison Ventes vs Achats (7 derniers jours)</div>
            <canvas id="chartComparatif"></canvas>
        </div>

        <!-- Historique des opérations -->
        <h2>📄 Historique des opérations</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Libellé</th>
                    <th>Montant (€)</th>
                    <?php if ($est_admin): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $has_data = false;
                while ($op = mysqli_fetch_assoc($operations)): 
                    $has_data = true;
                    $couleur = in_array($op['type'], ['Vente', 'Entrée']) ? 'var(--success-color)' : 'var(--danger-color)';
                    $signe = in_array($op['type'], ['Vente', 'Entrée']) ? '+' : '-';
                ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($op['date'])) ?></td>
                    <td>
                        <span class="badge <?= in_array($op['type'], ['Vente', 'Entrée']) ? 'badge-success' : 'badge-danger' ?>">
                            <?= htmlspecialchars($op['type']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($op['libelle']) ?><?= $op['notes'] ? ' <small style="color: #666;">(' . htmlspecialchars($op['notes']) . ')</small>' : '' ?></td>
                    <td style="color: <?= $couleur ?>; font-weight: bold;">
                        <?= $signe . number_format($op['montant'], 2, ',', ' ') ?> €
                    </td>
                    <?php if ($est_admin && $op['id_operation']): ?>
                        <td>
                            <a href="?editer_operation=<?= $op['id_operation'] ?>&periode=<?= urlencode($periode) ?>" class="btn btn-sm btn-primary">✏️ Modifier</a>
                            <a href="?supprimer_operation=<?= $op['id_operation'] ?>&periode=<?= urlencode($periode) ?>" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette opération ?')" 
                               class="btn btn-sm btn-danger">🗑️ Supprimer</a>
                        </td>
                    <?php elseif ($est_admin): ?>
                        <td>-</td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
                <?php if (!$has_data): ?>
                <tr>
                    <td colspan="<?= $est_admin ? '5' : '4' ?>" style="text-align: center; padding: 20px; color: #666;">
                        Aucune opération pour cette période.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal pour ajouter/modifier une opération (Admin uniquement) -->
<?php if ($est_admin): ?>
<div id="modalOperation" class="modal" style="<?= $operation_edit ? 'display:block;' : '' ?>">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('modalOperation').style.display='none'">&times;</span>
        <h2><?= $operation_edit ? '✏️ Modifier une opération' : '➕ Ajouter une opération' ?></h2>
        <form method="POST">
            <?php if ($operation_edit): ?>
                <input type="hidden" name="id_operation" value="<?= $operation_edit['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Type d'opération *</label>
                <select name="type_operation" class="form-control" required>
                    <option value="depense" <?= $operation_edit && $operation_edit['type_operation'] === 'depense' ? 'selected' : '' ?>>💸 Dépense</option>
                    <option value="entree" <?= $operation_edit && $operation_edit['type_operation'] === 'entree' ? 'selected' : '' ?>>💰 Entrée</option>
                </select>
            </div>
            <div class="form-group">
                <label>Libellé *</label>
                <input type="text" name="libelle" class="form-control" value="<?= $operation_edit ? htmlspecialchars($operation_edit['libelle']) : '' ?>" required placeholder="Ex: Loyer, Électricité, Vente occasionnelle...">
            </div>
            <div class="form-group">
                <label>Montant (€) *</label>
                <input type="number" name="montant" class="form-control" step="0.01" min="0.01" value="<?= $operation_edit ? $operation_edit['montant'] : '' ?>" required>
            </div>
            <div class="form-group">
                <label>Notes (optionnel)</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Informations complémentaires..."><?= $operation_edit ? htmlspecialchars($operation_edit['notes'] ?? '') : '' ?></textarea>
            </div>
            <div class="form-group">
                <button type="submit" name="<?= $operation_edit ? 'modifier_operation' : 'ajouter_operation' ?>" class="btn btn-primary">
                    <?= $operation_edit ? '💾 Enregistrer les modifications' : '✅ Ajouter l\'opération' ?>
                </button>
                <a href="tresorerie.php?periode=<?= urlencode($periode) ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
// Graphique évolution des ventes
const ctxVentes = document.getElementById('chartVentes').getContext('2d');
new Chart(ctxVentes, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels_ventes) ?>,
        datasets: [{
            label: 'Ventes (€)',
            data: <?= json_encode($data_ventes) ?>,
            borderColor: 'rgb(40, 167, 69)',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Graphique évolution des achats
const ctxAchats = document.getElementById('chartAchats').getContext('2d');
new Chart(ctxAchats, {
    type: 'bar',
    data: {
        labels: <?= json_encode($dates_achats) ?>,
        datasets: [{
            label: 'Achats (€)',
            data: <?= json_encode($data_achats) ?>,
            backgroundColor: 'rgba(220, 53, 69, 0.7)',
            borderColor: 'rgb(220, 53, 69)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Graphique bénéfices
const ctxBenefices = document.getElementById('chartBenefices').getContext('2d');
new Chart(ctxBenefices, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels_benefices) ?>,
        datasets: [{
            label: 'Bénéfice net (€)',
            data: <?= json_encode($data_benefices) ?>,
            borderColor: 'rgb(23, 162, 184)',
            backgroundColor: 'rgba(23, 162, 184, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: false
            }
        }
    }
});

// Graphique comparatif
const ctxComparatif = document.getElementById('chartComparatif').getContext('2d');
new Chart(ctxComparatif, {
    type: 'bar',
    data: {
        labels: <?= json_encode($all_dates) ?>,
        datasets: [{
            label: 'Ventes (€)',
            data: <?= json_encode($data_ventes_complete) ?>,
            backgroundColor: 'rgba(40, 167, 69, 0.7)',
            borderColor: 'rgb(40, 167, 69)',
            borderWidth: 1
        }, {
            label: 'Achats (€)',
            data: <?= json_encode($data_achats_complete) ?>,
            backgroundColor: 'rgba(220, 53, 69, 0.7)',
            borderColor: 'rgb(220, 53, 69)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Fermer la modal en cliquant en dehors
window.onclick = function(event) {
    const modal = document.getElementById('modalOperation');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

</body>
</html>
