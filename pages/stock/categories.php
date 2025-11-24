<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

require('../../config/db_conn.php');   // Donne $pdo au lieu de $conn
require('../../includes/historique_helper.php');

$role = $_SESSION['role'];
$id_utilisateur = $_SESSION['id_utilisateur'];
$peut_modifier = in_array($role, ['admin', 'responsable_approvisionnement']);
$message = "";

/* ============================================================
   🔹 AJOUT CATÉGORIE
   ============================================================ */
if ($peut_modifier && isset($_POST['ajouter'])) {

    $nom = trim($_POST['nom']);

    if ($nom !== "") {

        $stmt = $pdo->prepare("INSERT INTO categories (nom) VALUES (:nom)");

        if ($stmt->execute(['nom' => $nom])) {
            $id_cat = db_last_id($pdo);

            enregistrer_historique($pdo, $id_utilisateur, 'ajout', 'categories',
                $id_cat, "Ajout de la catégorie : $nom");

            $message = "✅ Catégorie ajoutée avec succès.";
        } else {
            $message = "❌ Erreur : " . implode(" | ", $stmt->errorInfo());
        }

    } else {
        $message = "⚠️ Le nom de la catégorie est obligatoire.";
    }
}


/* ============================================================
   🔹 MODIFICATION CATÉGORIE
   ============================================================ */
if ($peut_modifier && isset($_POST['modifier'])) {

    $id = intval($_POST['id']);
    $nom = trim($_POST['nom']);

    // Ancien enregistrement
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $ancien = $stmt->fetch();

    // Mise à jour
    $stmt = $pdo->prepare("UPDATE categories SET nom = :nom WHERE id = :id");

    if ($stmt->execute(['nom' => $nom, 'id' => $id])) {

        enregistrer_historique($pdo, $id_utilisateur, 'modification', 'categories',
            $id, "Modification de la catégorie : $nom",
            $ancien, ['nom' => $nom]);

        $message = "✅ Catégorie modifiée avec succès.";
    } else {
        $message = "❌ Erreur : " . implode(" | ", $stmt->errorInfo());
    }
}


/* ============================================================
   🔹 SUPPRESSION CATÉGORIE
   ============================================================ */
if ($peut_modifier && isset($_GET['supprimer'])) {

    $id = intval($_GET['supprimer']);

    // Récupérer la catégorie
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $cat = $stmt->fetch();

    // Supprimer
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute(['id' => $id]);

    // Historique
    enregistrer_historique($pdo, $id_utilisateur, 'suppression', 'categories',
        $id, "Suppression de la catégorie : " . $cat['nom'], $cat, null);

    header("Location: categories.php");
    exit();
}


/* ============================================================
   🔹 RÉCUPÉRATION DES CATÉGORIES
   PostgreSQL nécessite que toutes les colonnes non-agrégées
   soient dans le GROUP BY → on adapte.
   ============================================================ */

$req = "
    SELECT c.id, c.nom, COUNT(p.id) AS nb_produits
    FROM categories c
    LEFT JOIN produits p ON p.id_categorie = c.id
    GROUP BY c.id, c.nom
    ORDER BY c.nom ASC
";

$result = $pdo->query($req);


/* ============================================================
   🔹 Catégorie en édition
   ============================================================ */
$cat_edit = null;

if (isset($_GET['edit']) && $peut_modifier) {
    $id_edit = intval($_GET['edit']);

    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->execute(['id' => $id_edit]);
    $cat_edit = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>📁 Gestion des catégories - Smart Stock</title>
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
            <a href="stock.php" class="nav-link">Stock</a>
            <a href="../ventes/ventes.php" class="nav-link">Ventes</a>
            <a href="../clients/clients.php" class="nav-link">Clients</a>
            <a href="../commandes/commandes.php" class="nav-link">Commandes</a>
            <a href="categories.php" class="nav-link">Catégories</a>
        </div>
        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="main-container">
    <div class="content-wrapper">

<h1>📁 Gestion des catégories</h1>

<?php if ($message): ?>
    <div class="message <?= strpos($message,'✅')!==false?'success':(strpos($message,'⚠️')!==false?'warning':'error') ?>">
        <?= $message ?>
    </div>
<?php endif; ?>

<?php if (!$peut_modifier): ?>
    <div class="message warning">
        ℹ️ <strong>Mode consultation</strong> : Vous ne pouvez pas ajouter ou modifier des catégories.
    </div>
<?php endif; ?>

<?php if ($peut_modifier): ?>
<h3><?= $cat_edit ? "✏️ Modifier une catégorie" : "➕ Ajouter une catégorie" ?></h3>

<form method="POST">

    <?php if ($cat_edit): ?>
        <input type="hidden" name="id" value="<?= $cat_edit['id'] ?>">
    <?php endif; ?>

    <div class="form-group">
        <label>Nom de la catégorie :</label>
        <input type="text" name="nom" required
               value="<?= $cat_edit ? htmlspecialchars($cat_edit['nom']) : '' ?>">
    </div>

    <button type="submit" class="btn" name="<?= $cat_edit ? "modifier" : "ajouter" ?>">
        <?= $cat_edit ? "✏️ Modifier" : "➕ Ajouter" ?>
    </button>

    <?php if ($cat_edit): ?>
        <a href="categories.php" class="btn btn-secondary">Annuler</a>
    <?php endif; ?>

</form>
<?php endif; ?>


<h3>📋 Liste des catégories</h3>

<table>
<thead>
<tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Nombre de produits</th>
    <?php if ($peut_modifier): ?><th>Actions</th><?php endif; ?>
</tr>
</thead>

<tbody>
<?php foreach ($result as $row): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['nom']) ?></td>
    <td><span class="badge badge-info"><?= $row['nb_produits'] ?></span></td>

    <?php if ($peut_modifier): ?>
    <td>
        <a href="?edit=<?= $row['id'] ?>" class="btn btn-info btn-sm">✏️ Modifier</a>
        <a href="?supprimer=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
           onclick="return confirm('Supprimer cette catégorie ?')">🗑️ Supprimer</a>
    </td>
    <?php endif; ?>

</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</div>

</body>
</html>
