<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

include('../../config/db_conn.php'); // $pdo = instance PDO
include('../../includes/historique_helper.php');

$role = $_SESSION['role'];
$id_utilisateur = $_SESSION['id_utilisateur'];
$peut_modifier = in_array($role, ['admin', 'responsable_approvisionnement']);
$message = "";

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Recherche
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : "";

// ========== AJOUT ==========
if ($peut_modifier && isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    if ($nom !== "") {
        $stmt = $pdo->prepare("INSERT INTO fournisseurs (nom, telephone, email, adresse) VALUES (?, ?, ?, ?)");
        $ok = $stmt->execute([$nom, $telephone, $email, $adresse]);
        if ($ok) {
            $id_fournisseur = db_last_id($pdo);
            enregistrer_historique($pdo, $id_utilisateur, 'ajout', 'fournisseurs', $id_fournisseur, "Ajout du fournisseur: $nom");
            $message = "✅ Fournisseur ajouté avec succès.";
        } else {
            $message = "❌ Erreur lors de l'ajout.";
        }
    } else {
        $message = "⚠️ Le nom du fournisseur est obligatoire.";
    }
}

// ========== MODIFICATION ==========
if ($peut_modifier && isset($_POST['modifier'])) {
    $id = intval($_POST['id']);
    $nom = trim($_POST['nom']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    $stmt = $pdo->prepare("SELECT * FROM fournisseurs WHERE id = ?");
    $stmt->execute([$id]);
    $ancien = $stmt->fetch();

    $stmt = $pdo->prepare("UPDATE fournisseurs SET nom=?, telephone=?, email=?, adresse=? WHERE id=?");
    $ok = $stmt->execute([$nom, $telephone, $email, $adresse, $id]);

    if ($ok) {
        enregistrer_historique($pdo, $id_utilisateur, 'modification', 'fournisseurs', $id, "Modification fournisseur: $nom", $ancien, [
            'nom'=>$nom,'telephone'=>$telephone,'email'=>$email
        ]);
        $message = "✅ Fournisseur modifié avec succès.";
    } else {
        $message = "❌ Erreur lors de la modification.";
    }
}

// ========== SUPPRESSION ==========
if ($peut_modifier && isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);

    $stmt = $pdo->prepare("SELECT * FROM fournisseurs WHERE id = ?");
    $stmt->execute([$id]);
    $fourn = $stmt->fetch();

    $pdo->prepare("DELETE FROM fournisseurs WHERE id = ?")->execute([$id]);

    enregistrer_historique($pdo, $id_utilisateur, 'suppression', 'fournisseurs', $id, "Suppression du fournisseur: ".$fourn['nom'], $fourn);

    header("Location: fournisseurs.php");
    exit();
}

// ========== EXPORT CSV ==========
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=fournisseurs_".date("Y-m-d").".csv");

    $out = fopen("php://output", "w");
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($out, ['ID','Nom','Téléphone','Email','Adresse'], ';');

    $rows = $pdo->query("SELECT * FROM fournisseurs ORDER BY nom ASC")->fetchAll();
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'],$r['nom'],$r['telephone'],$r['email'],$r['adresse']], ';');
    }
    exit();
}

// ========== LISTE AVEC RECHERCHE ==========

$where = "";
$params = [];

if ($recherche !== "") {
    $where = "WHERE (nom ILIKE ? OR telephone ILIKE ? OR email ILIKE ?)";
    $params = ["%$recherche%","%$recherche%","%$recherche%"];
}

// Compter total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM fournisseurs $where");
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $per_page);

// Requête principale
$sql = "
SELECT f.*,
       COUNT(a.id) AS nb_commandes,
       COALESCE(SUM(a.montant_total), 0) AS total_commandes
FROM fournisseurs f
LEFT JOIN achats a ON a.id_fournisseur = f.id
$where
GROUP BY f.id
ORDER BY f.nom ASC
LIMIT $per_page OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$fournisseurs = $stmt->fetchAll();

// Edition
$fournisseur_edit = null;
if (isset($_GET['edit']) && $peut_modifier) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM fournisseurs WHERE id = ?");
    $stmt->execute([$id]);
    $fournisseur_edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>🚚 Fournisseurs</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../../assets/css/styles_connected.css">
</head>
<body>

<header>
    <nav class="navbar">
        <div class="nav-left">
            <a href="../dashboard/index.php"><img src="../../assets/images/logo_epicerie.png" class="logo-navbar"></a>
            <a href="../dashboard/index.php" class="nav-link">Dashboard</a>
            <a href="../stock/stock.php" class="nav-link">Stock</a>
            <a href="../ventes/ventes.php" class="nav-link">Ventes</a>
            <a href="../clients/clients.php" class="nav-link">Clients</a>
            <a href="fournisseurs.php" class="nav-link">Fournisseurs</a>
            <a href="../commandes/commandes.php" class="nav-link">Commandes</a>
        </div>
        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="main-container"><div class="content-wrapper">

<h1>🚚 Gestion des fournisseurs</h1>

<?php if ($message): ?>
<div class="message"><?= $message ?></div>
<?php endif; ?>

<!-- Recherche -->
<form method="GET" style="display:flex; gap:10px;">
    <input type="text" name="recherche" placeholder="Rechercher..." value="<?= htmlspecialchars($recherche) ?>" style="flex:1;">
    <button class="btn">Filtrer</button>
    <a href="fournisseurs.php" class="btn btn-secondary">Réinitialiser</a>
</form>

<a href="?export=csv" class="btn">📥 Export CSV</a>

<?php if ($peut_modifier): ?>
<h3><?= $fournisseur_edit ? "✏️ Modifier un fournisseur" : "➕ Ajouter un fournisseur" ?></h3>

<form method="POST">
    <?php if ($fournisseur_edit): ?>
        <input type="hidden" name="id" value="<?= $fournisseur_edit['id'] ?>">
    <?php endif; ?>

    <div class="form-group">
        <label>Nom :</label>
        <input type="text" name="nom" required value="<?= $fournisseur_edit ? htmlspecialchars($fournisseur_edit['nom']) : '' ?>">
    </div>

    <div class="form-group">
        <label>Téléphone :</label>
        <input type="text" name="telephone" value="<?= $fournisseur_edit ? htmlspecialchars($fournisseur_edit['telephone']) : '' ?>">
    </div>

    <div class="form-group">
        <label>Email :</label>
        <input type="email" name="email" value="<?= $fournisseur_edit ? htmlspecialchars($fournisseur_edit['email']) : '' ?>">
    </div>

    <div class="form-group">
        <label>Adresse :</label>
        <textarea name="adresse"><?= $fournisseur_edit ? htmlspecialchars($fournisseur_edit['adresse']) : '' ?></textarea>
    </div>

    <button name="<?= $fournisseur_edit ? 'modifier' : 'ajouter' ?>" class="btn">
        <?= $fournisseur_edit ? "✔ Modifier" : "➕ Ajouter" ?>
    </button>

    <?php if ($fournisseur_edit): ?>
    <a href="fournisseurs.php" class="btn btn-secondary">Annuler</a>
    <?php endif; ?>
</form>
<?php endif; ?>

<h3>📋 Liste des fournisseurs (<?= $total_rows ?>)</h3>

<table>
<thead>
<tr>
    <th>ID</th><th>Nom</th><th>Téléphone</th><th>Email</th><th>Adresse</th>
    <th>Commandes</th><th>Total (€)</th>
    <?php if ($peut_modifier): ?><th>Actions</th><?php endif; ?>
</tr>
</thead>
<tbody>

<?php foreach ($fournisseurs as $f): ?>
<tr>
    <td><?= $f['id'] ?></td>
    <td><?= htmlspecialchars($f['nom']) ?></td>
    <td><?= htmlspecialchars($f['telephone']) ?></td>
    <td><?= htmlspecialchars($f['email']) ?></td>
    <td><?= htmlspecialchars($f['adresse']) ?></td>
    <td><?= $f['nb_commandes'] ?></td>
    <td><?= number_format($f['total_commandes'],2,',',' ') ?> €</td>

    <?php if ($peut_modifier): ?>
    <td>
        <a href="?edit=<?= $f['id'] ?>" class="btn btn-info btn-sm">✏️</a>
        <a href="?supprimer=<?= $f['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">🗑️</a>
    </td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<!-- PAGINATION -->
<?php if ($total_pages > 1): ?>
<div class="pagination">
<?php for ($i=1;$i<=$total_pages;$i++): ?>
    <a class="<?= $i==$page?'active':'' ?>" href="?page=<?= $i ?>&<?= http_build_query($_GET) ?>"><?= $i ?></a>
<?php endfor; ?>
</div>
<?php endif; ?>

</div></div>

</body>
</html>
