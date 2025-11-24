<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

require '../../config/db_conn.php';
require '../../includes/historique_helper.php';

$role = $_SESSION['role'];
$id_utilisateur = $_SESSION['id_utilisateur'];
$peut_modifier = in_array($role, ['admin', 'vendeur']);
$message = "";

/* ============================================================
   PAGINATION
============================================================ */
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

/* ============================================================
   RECHERCHE (compatible MySQL + PostgreSQL)
============================================================ */
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';

$where_clause = "";
$params = [];

if ($recherche !== "") {
    $where_clause = "WHERE LOWER(nom) LIKE LOWER(:search)
                     OR LOWER(telephone) LIKE LOWER(:search)
                     OR LOWER(email) LIKE LOWER(:search)";
    $params[':search'] = "%$recherche%";
}

/* ============================================================
   AJOUT CLIENT
============================================================ */
if (isset($_POST['ajouter'])) {

    $nom = trim($_POST['nom']);
    $tel = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    if ($nom === "") {
        $message = "⚠️ Le nom du client est obligatoire.";
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO clients (nom, telephone, email, adresse)
            VALUES (:nom, :tel, :email, :adresse)
        ");

        if ($stmt->execute([
            ':nom' => $nom,
            ':tel' => $tel,
            ':email' => $email,
            ':adresse' => $adresse
        ])) {
            $id_client = db_last_id($pdo);

            enregistrer_historique(
                $pdo, $id_utilisateur, 'ajout', 'clients', $id_client,
                "Ajout du client : $nom"
            );

            $message = "✅ Client ajouté avec succès.";
        } else {
            $message = "❌ Erreur lors de l'ajout.";
        }
    }
}

/* ============================================================
   MODIFICATION
============================================================ */
if ($peut_modifier && isset($_POST['modifier'])) {

    $id = intval($_POST['id']);
    $nom = trim($_POST['nom']);
    $tel = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    // Ancienne valeur
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $ancien = $stmt->fetch();

    $stmt = $pdo->prepare("
        UPDATE clients
        SET nom = :nom, telephone = :tel, email = :email, adresse = :adresse
        WHERE id = :id
    ");

    if ($stmt->execute([
        ':nom' => $nom,
        ':tel' => $tel,
        ':email' => $email,
        ':adresse' => $adresse,
        ':id' => $id
    ])) {

        enregistrer_historique(
            $pdo, $id_utilisateur, 'modification', 'clients', $id,
            "Modification du client : $nom",
            $ancien,
            ['nom' => $nom, 'telephone' => $tel, 'email' => $email]
        );

        $message = "✅ Client modifié.";
    } else {
        $message = "❌ Erreur lors de la modification.";
    }
}

/* ============================================================
   SUPPRESSION
============================================================ */
if ($peut_modifier && isset($_GET['supprimer'])) {

    $id = intval($_GET['supprimer']);

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $client = $stmt->fetch();

    $pdo->prepare("DELETE FROM clients WHERE id = :id")
        ->execute([':id' => $id]);

    enregistrer_historique(
        $pdo, $id_utilisateur, 'suppression', 'clients', $id,
        "Suppression du client : " . $client['nom'],
        $client, null
    );

    header("Location: clients.php");
    exit();
}

/* ============================================================
   EXPORT
============================================================ */
if (isset($_GET['export']) && $_GET['export'] === 'csv') {

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=clients_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

    fputcsv($output, ['ID', 'Nom', 'Téléphone', 'Email', 'Adresse'], ';');

    $stmt = $pdo->query("SELECT * FROM clients ORDER BY nom ASC");

    while ($row = $stmt->fetch()) {
        fputcsv($output, $row, ';');
    }

    exit();
}

/* ============================================================
   TOTAL LIGNES
============================================================ */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM clients $where_clause");
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $per_page);

/* ============================================================
   RÉCUPÉRATION DES CLIENTS
============================================================ */
$query = "
    SELECT * FROM clients
    $where_clause
    ORDER BY nom ASC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($query);

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}

$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$result = $stmt->fetchAll();

/* ============================================================
   CLIENT EN MODIFICATION
============================================================ */
$client_edit = null;

if ($peut_modifier && isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
    $stmt->execute([':id' => intval($_GET['edit'])]);
    $client_edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>👥 Gestion des clients - Smart Stock</title>
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
            <a href="clients.php" class="nav-link active">Clients</a>
            <a href="../commandes/commandes.php" class="nav-link">Commandes</a>
            <a href="../stock/categories.php" class="nav-link">Catégories</a>
        </div>
        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="main-container">
<div class="content-wrapper">

<h1>👥 Gestion des clients</h1>

<?php if ($message): ?>
    <div class="message <?= strpos($message,'✅')!==false?'success':(strpos($message,'⚠️')!==false?'warning':'error') ?>">
        <?= $message ?>
    </div>
<?php endif; ?>

<!-- Recherche -->
<div class="filters">
    <form method="GET" style="display:flex; gap:10px;">
        <input type="text" name="recherche" placeholder="🔍 Rechercher..."
               value="<?= htmlspecialchars($recherche) ?>" style="flex:1;">
        <button class="btn">Filtrer</button>
        <a href="clients.php" class="btn btn-secondary">Réinitialiser</a>
    </form>
</div>

<!-- Export -->
<div class="export-buttons">
    <a href="?export=csv">📥 Exporter CSV</a>
</div>

<h3><?= $client_edit ? "✏️ Modifier un client" : "➕ Ajouter un client" ?></h3>

<form method="POST">

<?php if ($client_edit): ?>
    <input type="hidden" name="id" value="<?= $client_edit['id'] ?>">
<?php endif; ?>

<div class="form-group">
    <label>Nom :</label>
    <input type="text" name="nom" required
           value="<?= $client_edit ? htmlspecialchars($client_edit['nom']) : '' ?>">
</div>

<div class="form-group">
    <label>Téléphone :</label>
    <input type="text" name="telephone"
           value="<?= $client_edit ? htmlspecialchars($client_edit['telephone']) : '' ?>">
</div>

<div class="form-group">
    <label>Email :</label>
    <input type="email" name="email"
           value="<?= $client_edit ? htmlspecialchars($client_edit['email']) : '' ?>">
</div>

<div class="form-group">
    <label>Adresse :</label>
    <textarea name="adresse"><?= $client_edit ? htmlspecialchars($client_edit['adresse']) : '' ?></textarea>
</div>

<button class="btn" name="<?= $client_edit ? 'modifier':'ajouter' ?>">
    <?= $client_edit ? '✏️ Modifier' : '➕ Ajouter' ?>
</button>

<?php if ($client_edit): ?>
    <a href="clients.php" class="btn btn-secondary">Annuler</a>
<?php endif; ?>

</form>

<h3>📋 Liste des clients (<?= $total_rows ?>)</h3>

<table>
<thead>
<tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Téléphone</th>
    <th>Email</th>
    <th>Adresse</th>
    <?php if ($peut_modifier): ?><th>Actions</th><?php endif; ?>
</tr>
</thead>

<tbody>
<?php if (count($result) > 0): ?>

<?php foreach ($result as $row): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['nom']) ?></td>
    <td><?= htmlspecialchars($row['telephone']) ?></td>
    <td><?= htmlspecialchars($row['email']) ?></td>
    <td><?= htmlspecialchars($row['adresse']) ?></td>

    <?php if ($peut_modifier): ?>
    <td>
        <a href="?edit=<?= $row['id'] ?>" class="btn btn-info btn-sm">✏️ Modifier</a>
        <a href="?supprimer=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
           onclick="return confirm('Supprimer ce client ?')">🗑️</a>
    </td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>

<?php else: ?>

<tr>
    <td colspan="<?= $peut_modifier ? '6' : '5' ?>" style="text-align:center; padding:20px;">
        Aucun client trouvé.
    </td>
</tr>

<?php endif; ?>
</tbody>
</table>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="pagination">

    <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>">« Précédent</a>
    <?php endif; ?>

    <?php for ($i=max(1,$page-2); $i<=min($total_pages,$page+2); $i++): ?>
        <?php if ($i == $page): ?>
            <span class="active"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page+1 ?>">Suivant »</a>
    <?php endif; ?>

</div>
<?php endif; ?>

</div>
</div>

</body>
</html>
