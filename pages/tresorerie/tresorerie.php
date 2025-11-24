<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

require_once('../../config/db_conn.php'); // fourni : $pdo
require_once('../../includes/historique_helper.php');
require_once('../../includes/export_helper.php');
require_once('../../includes/role_helper.php');
require_once('../../includes/permissions_helper.php');

$role = $_SESSION['role'];
$id_utilisateur = $_SESSION['id_utilisateur'];
$message = "";

// === CRÉATION TABLE POSTGRESQL ===
// remplace SHOW TABLES par information_schema
$check = $pdo->prepare("
    SELECT table_name 
    FROM information_schema.tables 
    WHERE table_schema='public' AND table_name='depenses_diverses'
");
$check->execute();
$table_exists = $check->fetchColumn();

if (!$table_exists) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS depenses_diverses (
            id SERIAL PRIMARY KEY,
            type_operation VARCHAR(20) NOT NULL CHECK (type_operation IN ('depense','entree')),
            libelle VARCHAR(255) NOT NULL,
            montant NUMERIC(10,2) NOT NULL,
            date_operation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            id_utilisateur INT NOT NULL,
            notes TEXT NULL,
            CONSTRAINT fk_utilisateur FOREIGN KEY(id_utilisateur)
                REFERENCES utilisateurs(id) ON DELETE CASCADE
        )
    ");
}

// === GESTION DES ACCÈS ===
$est_admin = ($role === 'admin');
$est_vendeur = ($role === 'vendeur');

$acces_autorise = false;
if ($est_vendeur || !$est_admin) {
    $acces_autorise = aPermission($pdo, $id_utilisateur, 'acces_tresorerie');
}

$demande_en_attente = false;

if (!$est_admin && !$acces_autorise) {

    $stmt = $pdo->prepare("
        SELECT id
        FROM demandes_acces
        WHERE id_utilisateur = ?
        AND (permission_demande='acces_tresorerie' OR permission_demande='acces_general')
        AND statut='en_attente'
        LIMIT 1
    ");
    $stmt->execute([$id_utilisateur]);
    $demande_en_attente = (bool)$stmt->fetch();
}

// CREER DEMANDE D'ACCÈS
if (!$est_admin && !$acces_autorise && !$demande_en_attente && isset($_POST['demander_acces'])) {
    $raison = trim($_POST['raison'] ?? '');
    if ($raison === '') {
        $message = "⚠️ Veuillez indiquer une raison.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO demandes_acces (id_utilisateur, type_demande, permission_demande, raison)
            VALUES (?, 'permission_specifique', 'acces_tresorerie', ?)
        ");
        if ($stmt->execute([$id_utilisateur, $raison])) {
            $message = "✅ Demande envoyée.";
            $demande_en_attente = true;
        } else {
            $message = "❌ Erreur lors de l'envoi.";
        }
    }
}

// PAGE DEMANDE — PAS D’ACCÈS
if (!$est_admin && !$acces_autorise && !$demande_en_attente) {
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>💰 Trésorerie - Accès limité</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../assets/css/styles_connected.css">
</head>
<body>
<header>
    <nav class="navbar">
        <div class="nav-left">
            <a href="../dashboard/index.php" class="logo-link">
                <img src="../../assets/images/logo_epicerie.png" class="logo-navbar">
            </a>
            <a href="../dashboard/index.php" class="nav-link">Tableau de bord</a>
            <a href="../stock/stock.php" class="nav-link">Stock</a>
            <a href="../ventes/ventes.php" class="nav-link">Ventes</a>
            <a href="../clients/clients.php" class="nav-link">Clients</a>
            <a href="../commandes/commandes.php" class="nav-link">Commandes</a>
            <a href="../stock/categories.php" class="nav-link">Catégories</a>
        </div>
        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="main-container">
<div class="content-wrapper">

<h1>💰 Trésorerie</h1>

<div class="alert alert-warning">
    <h3>🔐 Accès limité</h3>
    <p>Vous n'avez pas accès à cette page.</p>
</div>

<?php if ($message): ?>
<div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST">
    <h3>Demander un accès</h3>
    <textarea name="raison" required></textarea>
    <button name="demander_acces" class="btn btn-primary">Envoyer</button>
    <a class="btn btn-secondary" href="../dashboard/index.php">Retour</a>
</form>

</div></div></body></html>
<?php exit; } ?>
<?php
// SI DEMANDE EN ATTENTE
if (!$est_admin && !$acces_autorise && $demande_en_attente) {
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>💰 Trésorerie - Accès en attente</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../assets/css/styles_connected.css">
</head>
<body>
<header>
<nav class="navbar">
    <div class="nav-left">
        <a href="../dashboard/index.php" class="logo-link">
            <img src="../../assets/images/logo_epicerie.png" class="logo-navbar">
        </a>
        <a href="../dashboard/index.php" class="nav-link">Tableau de bord</a>
        <a href="../stock/stock.php" class="nav-link">Stock</a>
        <a href="../ventes/ventes.php" class="nav-link">Ventes</a>
        <a href="../clients/clients.php" class="nav-link">Clients</a>
        <a href="../commandes/commandes.php" class="nav-link">Commandes</a>
        <a href="../stock/categories.php" class="nav-link">Catégories</a>
    </div>
    <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
</nav>
</header>

<div class="main-container">
<div class="content-wrapper">

<h1>💰 Trésorerie</h1>

<div class="alert alert-info">
    <h3>⏳ Demande en attente</h3>
    <p>Votre demande a été transmise et attend validation.</p>
</div>

<a href="../dashboard/index.php" class="btn btn-secondary">Retour</a>

</div></div>
</body>
</html>
<?php
exit;
}

// === FILTRE DE PÉRIODE ===
$periode = $_GET['periode'] ?? 'mois';

switch ($periode) {
    case 'jour':
        $date_debut = date('Y-m-d');
        $date_fin   = date('Y-m-d');
        break;

    case 'semaine':
        $date_debut = date('Y-m-d', strtotime('monday this week'));
        $date_fin   = date('Y-m-d', strtotime('sunday this week'));
        break;

    case 'annee':
        $date_debut = date('Y-01-01');
        $date_fin   = date('Y-12-31');
        break;

    case 'personnalise':
        $date_debut = $_GET['date_debut'] ?? date('Y-m-01');
        $date_fin   = $_GET['date_fin'] ?? date('Y-m-t');
        break;

    default:
        $date_debut = date('Y-m-01');
        $date_fin   = date('Y-m-t');
}

// === EXPORT CSV ===
if ($est_admin && isset($_GET['export']) && $_GET['export'] === 'csv') {

    $query = "
        SELECT date_vente AS date, 'Vente' AS type, total AS montant,
               CONCAT('Vente #', id) AS libelle
        FROM ventes
        WHERE DATE(date_vente) BETWEEN :d1 AND :d2

        UNION ALL

        SELECT date_achat AS date, 'Achat' AS type, montant_total AS montant,
               CONCAT('Achat #', id) AS libelle
        FROM achats
        WHERE DATE(date_achat) BETWEEN :d1 AND :d2

        UNION ALL

        SELECT date_operation AS date,
               CASE WHEN type_operation='depense' THEN 'Dépense' ELSE 'Entrée' END AS type,
               CASE WHEN type_operation='depense' THEN -montant ELSE montant END AS montant,
               libelle
        FROM depenses_diverses
        WHERE DATE(date_operation) BETWEEN :d1 AND :d2

        ORDER BY date DESC
    ";

    export_excel_pdo(
        $pdo,
        $query,
        ['d1' => $date_debut, 'd2' => $date_fin],
        "tresorerie_{$periode}_" . date('Y-m-d'),
        ['Date','Type','Montant (€)','Libellé']
    );

    exit;
}

// === AJOUT D'UNE OPERATION ===
if ($est_admin && isset($_POST['ajouter_operation'])) {

    $type = $_POST['type_operation'];
    $lib  = trim($_POST['libelle']);
    $mont = floatval($_POST['montant']);
    $notes = trim($_POST['notes'] ?? '');

    if ($lib === '' || $mont <= 0) {
        $message = "⚠️ Champs invalides.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO depenses_diverses (type_operation, libelle, montant, id_utilisateur, notes)
            VALUES (:t, :l, :m, :u, :n)
        ");
        $stmt->execute([
            ':t' => $type,
            ':l' => $lib,
            ':m' => $mont,
            ':u' => $id_utilisateur,
            ':n' => $notes
        ]);

        $id_op = db_last_id($pdo, 'depenses_diverses');

        enregistrer_historique(
            $pdo,
            $id_utilisateur,
            'INSERT',
            'depenses_diverses',
            $id_op,
            "Ajout trésorerie : $lib"
        );

        $message = "✅ Opération enregistrée.";
    }
}

// === MODIFICATION ===
if ($est_admin && isset($_POST['modifier_operation'])) {

    $id_op = intval($_POST['id_operation']);

    $r = $pdo->prepare("SELECT * FROM depenses_diverses WHERE id = ?");
    $r->execute([$id_op]);
    $old = $r->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        UPDATE depenses_diverses
        SET type_operation=:t, libelle=:l, montant=:m, notes=:n
        WHERE id=:id
    ");

    $stmt->execute([
        ':t' => $_POST['type_operation'],
        ':l' => trim($_POST['libelle']),
        ':m' => floatval($_POST['montant']),
        ':n' => trim($_POST['notes'] ?? ''),
        ':id' => $id_op
    ]);

    enregistrer_historique($pdo, $id_utilisateur, 'UPDATE', 'depenses_diverses', $id_op, "Modification trésorerie", $old);

    $message = "✅ Opération modifiée.";
}

// === SUPPRESSION ===
if ($est_admin && isset($_GET['supprimer_operation'])) {

    $id_op = intval($_GET['supprimer_operation']);

    $r = $pdo->prepare("SELECT * FROM depenses_diverses WHERE id = ?");
    $r->execute([$id_op]);
    $old = $r->fetch(PDO::FETCH_ASSOC);

    $pdo->prepare("DELETE FROM depenses_diverses WHERE id = ?")->execute([$id_op]);

    enregistrer_historique($pdo, $id_utilisateur, 'DELETE', 'depenses_diverses', $id_op, "Suppression trésorerie", $old);

    header("Location: tresorerie.php?periode=$periode");
    exit;
}
// === CALCUL DES STATISTIQUES ===

// CA du jour
$stmt = $pdo->prepare("SELECT SUM(total) as total FROM ventes WHERE DATE(date_vente) = CURRENT_DATE");
$stmt->execute();
$ca_jour = floatval($stmt->fetchColumn() ?: 0);

// CA du mois
$stmt = $pdo->prepare("
    SELECT SUM(total) 
    FROM ventes 
    WHERE EXTRACT(MONTH FROM date_vente) = EXTRACT(MONTH FROM CURRENT_DATE)
    AND EXTRACT(YEAR FROM date_vente) = EXTRACT(YEAR FROM CURRENT_DATE)
");
$stmt->execute();
$ca_mois = floatval($stmt->fetchColumn() ?: 0);

// CA total
$stmt = $pdo->query("SELECT SUM(total) FROM ventes");
$ca_total = floatval($stmt->fetchColumn() ?: 0);

// Total achats fournisseurs
$stmt = $pdo->query("SELECT SUM(montant_total) FROM achats");
$achats_total = floatval($stmt->fetchColumn() ?: 0);

// Achats période
$stmt = $pdo->prepare("
    SELECT SUM(montant_total)
    FROM achats
    WHERE DATE(date_achat) BETWEEN :d1 AND :d2
");
$stmt->execute([':d1'=>$date_debut, ':d2'=>$date_fin]);
$achats_periode = floatval($stmt->fetchColumn() ?: 0);

// CA période
$stmt = $pdo->prepare("
    SELECT SUM(total)
    FROM ventes
    WHERE DATE(date_vente) BETWEEN :d1 AND :d2
");
$stmt->execute([':d1'=>$date_debut, ':d2'=>$date_fin]);
$ca_periode = floatval($stmt->fetchColumn() ?: 0);

// Dépenses diverses globales
$stmt = $pdo->query("
    SELECT 
        SUM(CASE WHEN type_operation='depense' THEN montant ELSE 0 END) AS total_depenses,
        SUM(CASE WHEN type_operation='entree' THEN montant ELSE 0 END) AS total_entrees
    FROM depenses_diverses
");
$row = $stmt->fetch();
$total_depenses_diverses = floatval($row['total_depenses'] ?? 0);
$total_entrees_diverses  = floatval($row['total_entrees'] ?? 0);

// Dépenses diverses période
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN type_operation='depense' THEN montant ELSE 0 END) AS total_depenses,
        SUM(CASE WHEN type_operation='entree' THEN montant ELSE 0 END) AS total_entrees
    FROM depenses_diverses
    WHERE DATE(date_operation) BETWEEN :d1 AND :d2
");
$stmt->execute([':d1'=>$date_debut, ':d2'=>$date_fin]);
$row = $stmt->fetch();
$total_depenses_periode = floatval($row['total_depenses'] ?? 0);
$total_entrees_periode  = floatval($row['total_entrees'] ?? 0);

// Marge brute
$marge_brute = $ca_total - $achats_total;
$marge_brute_periode = $ca_periode - $achats_periode;

// Bénéfice estimé
$benefice_estime =
    $marge_brute - $total_depenses_diverses + $total_entrees_diverses;

$benefice_estime_periode =
    $marge_brute_periode - $total_depenses_periode + $total_entrees_periode;

// =========================
// 📈 Évolution des ventes 7 jours
// =========================
$stmt = $pdo->query("
    SELECT DATE(date_vente) AS d, SUM(total) AS total
    FROM ventes
    WHERE date_vente >= CURRENT_DATE - INTERVAL '7 days'
    GROUP BY DATE(date_vente)
    ORDER BY d ASC
");

$labels_ventes = [];
$data_ventes = [];

while ($row = $stmt->fetch()) {
    $labels_ventes[] = date('d/m', strtotime($row['d']));
    $data_ventes[] = floatval($row['total']);
}

// =========================
// 📉 Évolution achats 7 jours
// =========================
$stmt = $pdo->query("
    SELECT DATE(date_achat) AS d, SUM(montant_total) AS total
    FROM achats
    WHERE date_achat >= CURRENT_DATE - INTERVAL '7 days'
    GROUP BY DATE(date_achat)
    ORDER BY d ASC
");

$dates_achats = [];
$data_achats = [];

while ($row = $stmt->fetch()) {
    $dates_achats[] = date('d/m', strtotime($row['d']));
    $data_achats[] = floatval($row['total']);
}

// Fusion des dates
$all_dates = array_unique(array_merge($labels_ventes, $dates_achats));
sort($all_dates);

// Remplissage automatique des trous
$data_ventes_complete = [];
$data_achats_complete = [];

foreach ($all_dates as $d) {
    $i_v = array_search($d, $labels_ventes);
    $i_a = array_search($d, $dates_achats);

    $data_ventes_complete[] = $i_v !== false ? $data_ventes[$i_v] : 0;
    $data_achats_complete[] = $i_a !== false ? $data_achats[$i_a] : 0;
}
// =========================
// 💵 Bénéfice net 30 jours
// =========================
$stmt = $pdo->query("
    WITH dates AS (
        SELECT DISTINCT DATE(date_vente) AS d
        FROM ventes
        WHERE date_vente >= CURRENT_DATE - INTERVAL '30 days'
        UNION
        SELECT DISTINCT DATE(date_achat)
        FROM achats
        WHERE date_achat >= CURRENT_DATE - INTERVAL '30 days'
        UNION
        SELECT DISTINCT DATE(date_operation)
        FROM depenses_diverses
        WHERE date_operation >= CURRENT_DATE - INTERVAL '30 days'
    )
    SELECT 
        d AS date,
        COALESCE((SELECT SUM(total) FROM ventes v WHERE DATE(v.date_vente)=d), 0) AS ventes,
        COALESCE((SELECT SUM(montant_total) FROM achats a WHERE DATE(a.date_achat)=d), 0) AS achats,
        COALESCE((SELECT SUM(montant) FROM depenses_diverses dd WHERE DATE(dd.date_operation)=d AND dd.type_operation='depense'), 0) AS depenses,
        COALESCE((SELECT SUM(montant) FROM depenses_diverses dd WHERE DATE(dd.date_operation)=d AND dd.type_operation='entree'), 0) AS entrees
    FROM dates
    ORDER BY d ASC
");

$labels_benefices = [];
$data_benefices = [];

while ($row = $stmt->fetch()) {
    $labels_benefices[] = date('d/m', strtotime($row['date']));

    $benefice = floatval($row['ventes'])
                - floatval($row['achats'])
                - floatval($row['depenses'])
                + floatval($row['entrees']);

    $data_benefices[] = $benefice;
}


// =========================
// 📄 HISTORIQUE DES OPÉRATIONS
// =========================
$stmt = $pdo->prepare("
    SELECT * FROM (
        SELECT 
            DATE(date_vente) AS date,
            'Vente' AS type,
            total AS montant,
            CONCAT('Vente #', id) AS libelle,
            NULL AS notes,
            id AS id_vente,
            NULL AS id_operation
        FROM ventes
        WHERE DATE(date_vente) BETWEEN :d1 AND :d2

        UNION ALL

        SELECT 
            DATE(date_achat),
            'Achat',
            montant_total,
            CONCAT('Achat #', id),
            NULL,
            NULL,
            NULL
        FROM achats
        WHERE DATE(date_achat) BETWEEN :d1 AND :d2

        UNION ALL

        SELECT 
            DATE(date_operation),
            CASE WHEN type_operation='depense' THEN 'Dépense' ELSE 'Entrée' END,
            montant,
            libelle,
            notes,
            NULL,
            id
        FROM depenses_diverses
        WHERE DATE(date_operation) BETWEEN :d1 AND :d2
    ) AS t
    ORDER BY date DESC, montant DESC
    LIMIT 100
");

$stmt->execute([
    ':d1' => $date_debut,
    ':d2' => $date_fin
]);

$operations = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =========================
// 📄 Récupération d’une opération à éditer
// =========================
$operation_edit = null;

if ($est_admin && isset($_GET['editer_operation'])) {
    $id_edit = intval($_GET['editer_operation']);

    $stmt = $pdo->prepare("SELECT * FROM depenses_diverses WHERE id = :id");
    $stmt->execute([':id' => $id_edit]);

    $operation_edit = $stmt->fetch(PDO::FETCH_ASSOC);
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
.stat-card.success { border-left-color: var(--success-color); }
.stat-card.danger { border-left-color: var(--danger-color); }
.stat-card.warning { border-left-color: #ffc107; }
.stat-card.info { border-left-color: #17a2b8; }
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
.close:hover { color: #000; }
</style>
</head>

<body>

<header>
    <nav class="navbar">
        <div class="nav-left">
            <a href="../dashboard/index.php" class="logo-link">
                <img src="../../assets/images/logo_epicerie.png" alt="Logo" class="logo-navbar">
            </a>

            <a href="../dashboard/index.php" class="nav-link">Tableau de bord</a>
            <a href="../stock/stock.php" class="nav-link">Stock</a>
            <a href="../ventes/ventes.php" class="nav-link">Ventes</a>
            <a href="../clients/clients.php" class="nav-link">Clients</a>
            <a href="../commandes/commandes.php" class="nav-link">Commandes</a>
            <a href="../stock/categories.php" class="nav-link">Catégories</a>

            <?php if ($est_admin || $role === 'responsable_approvisionnement'): ?>
                <a href="../fournisseurs/fournisseurs.php" class="nav-link">Fournisseurs</a>
            <?php endif; ?>

            <?php if ($est_admin): ?>
                <a href="../admin/utilisateurs.php" class="nav-link">Utilisateurs</a>
                <a href="../admin/demandes_acces.php" class="nav-link">Demandes</a>
            <?php endif; ?>
        </div>

        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>


<div class="main-container">
    <div class="content-wrapper">

        <h1>💰 Trésorerie</h1>

        <?php if (!empty($message)): ?>
            <div class="alert <?= str_contains($message, '✅') ? 'alert-success' : 'alert-danger' ?>">
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
                        <input type="date" name="date_debut"
                               value="<?= htmlspecialchars($date_debut) ?>"
                               class="form-control" style="width: auto;">
                        <span>au</span>
                        <input type="date" name="date_fin"
                               value="<?= htmlspecialchars($date_fin) ?>"
                               class="form-control" style="width: auto;">
                        <button type="submit" class="btn btn-primary">Appliquer</button>
                    <?php endif; ?>

                    <a href="?export=csv&periode=<?= urlencode($periode) ?>&date_debut=<?= urlencode($date_debut) ?>&date_fin=<?= urlencode($date_fin) ?>"
                       class="btn btn-secondary">📥 Exporter CSV</a>
                </form>
            </div>

            <div style="margin-bottom: 20px;">
                <button onclick="document.getElementById('modalOperation').style.display='block'"
                        class="btn btn-primary">➕ Ajouter une opération</button>
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
            <div class="chart-title">💵 Bénéfice net (30 derniers jours)</div>
            <canvas id="chartBenefices"></canvas>
        </div>

        <div class="chart-container">
            <div class="chart-title">📊 Comparaison Ventes / Achats (7 derniers jours)</div>
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

                while ($op = $operations->fetch(PDO::FETCH_ASSOC)): 
                    $has_data = true;

                    $couleur = in_array($op['type'], ['Vente', 'Entrée']) 
                        ? 'var(--success-color)' 
                        : 'var(--danger-color)';

                    $signe = in_array($op['type'], ['Vente', 'Entrée']) ? '+' : '-';
                ?>

                <tr>
                    <td><?= date('d/m/Y', strtotime($op['date'])) ?></td>

                    <td>
                        <span class="badge <?= in_array($op['type'], ['Vente', 'Entrée']) ? 'badge-success' : 'badge-danger' ?>">
                            <?= htmlspecialchars($op['type']) ?>
                        </span>
                    </td>

                    <td>
                        <?= htmlspecialchars($op['libelle']) ?>
                        <?= $op['notes'] ? ' <small style="color:#666;">(' . htmlspecialchars($op['notes']) . ')</small>' : '' ?>
                    </td>

                    <td style="color: <?= $couleur ?>; font-weight: bold;">
                        <?= $signe . number_format($op['montant'], 2, ',', ' ') ?> €
                    </td>

                    <?php if ($est_admin): ?>
                        <?php if (!empty($op['id_operation'])): ?>
                            <td>
                                <a href="?editer_operation=<?= $op['id_operation'] ?>&periode=<?= urlencode($periode) ?>" 
                                   class="btn btn-sm btn-primary">✏️ Modifier</a>

                                <a href="?supprimer_operation=<?= $op['id_operation'] ?>&periode=<?= urlencode($periode) ?>"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette opération ?')"
                                   class="btn btn-sm btn-danger">🗑️ Supprimer</a>
                            </td>
                        <?php else: ?>
                            <td>-</td>
                        <?php endif; ?>
                    <?php endif; ?>

                </tr>

                <?php endwhile; ?>

                <?php if (!$has_data): ?>
                <tr>
                    <td colspan="<?= $est_admin ? '5' : '4' ?>" style="text-align:center; padding:20px; color:#666;">
                        Aucune opération pour cette période.
                    </td>
                </tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

<!-- Modal ajouter/modifier une opération -->
<?php if ($est_admin): ?>

<div id="modalOperation" class="modal" style="<?= $operation_edit ? 'display:block;' : '' ?>">
    <div class="modal-content">

        <span class="close"
              onclick="document.getElementById('modalOperation').style.display='none'">&times;</span>

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
                <input type="text" name="libelle" class="form-control"
                       value="<?= $operation_edit ? htmlspecialchars($operation_edit['libelle']) : '' ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Montant (€) *</label>
                <input type="number" name="montant" class="form-control" step="0.01" min="0.01"
                       value="<?= $operation_edit ? $operation_edit['montant'] : '' ?>" required>
            </div>

            <div class="form-group">
                <label>Notes (optionnel)</label>
                <textarea name="notes" class="form-control" rows="3"><?= $operation_edit ? htmlspecialchars($operation_edit['notes']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit"
                        name="<?= $operation_edit ? 'modifier_operation' : 'ajouter_operation' ?>"
                        class="btn btn-primary">
                    <?= $operation_edit ? '💾 Enregistrer' : '✅ Ajouter' ?>
                </button>

                <a href="tresorerie.php?periode=<?= urlencode($periode) ?>" class="btn btn-secondary">Annuler</a>
            </div>

        </form>
    </div>
</div>

<?php endif; ?>
<script>
// Graphique ventes
new Chart(document.getElementById('chartVentes'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels_ventes) ?>,
        datasets: [{
            label: "Ventes (€)",
            data: <?= json_encode($data_ventes) ?>,
            borderColor: "rgb(40, 167, 69)",
            backgroundColor: "rgba(40,167,69,0.15)",
            tension: 0.3,
            fill: true
        }]
    }
});

// Graphique achats
new Chart(document.getElementById('chartAchats'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($dates_achats) ?>,
        datasets: [{
            label: "Achats (€)",
            data: <?= json_encode($data_achats) ?>,
            backgroundColor: "rgba(220,53,69,0.6)",
            borderColor: "rgb(220,53,69)",
            borderWidth: 1
        }]
    }
});

// Graphique bénéfices
new Chart(document.getElementById('chartBenefices'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels_benefices) ?>,
        datasets: [{
            label: "Bénéfice Net (€)",
            data: <?= json_encode($data_benefices) ?>,
            borderColor: "rgb(23,162,184)",
            backgroundColor: "rgba(23,162,184,0.15)",
            tension: 0.3,
            fill: true
        }]
    }
});

// Comparatif ventes vs achats
new Chart(document.getElementById('chartComparatif'), {
    type: "bar",
    data: {
        labels: <?= json_encode($all_dates) ?>,
        datasets: [
            {
                label: "Ventes (€)",
                data: <?= json_encode($data_ventes_complete) ?>,
                backgroundColor: "rgba(40,167,69,0.6)",
                borderColor: "rgb(40,167,69)",
                borderWidth: 1
            },
            {
                label: "Achats (€)",
                data: <?= json_encode($data_achats_complete) ?>,
                backgroundColor: "rgba(220,53,69,0.6)",
                borderColor: "rgb(220,53,69)",
                borderWidth: 1
            }
        ]
    }
});

// Fermeture modale
window.onclick = function(e) {
    const modal = document.getElementById("modalOperation");
    if (e.target === modal) modal.style.display = "none";
};
</script>

</body>
</html>
