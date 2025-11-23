<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

include('../../config/db_conn.php');
include('../../includes/historique_helper.php');

$role = $_SESSION['role'];
$id_utilisateur = $_SESSION['id_utilisateur'];
$peut_modifier = in_array($role, ['admin', 'responsable_approvisionnement']);
$message = "";

// --- AJOUT D'UNE CATÉGORIE ---
if ($peut_modifier && isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom']);
    if ($nom !== "") {
        $stmt = mysqli_prepare($conn, "INSERT INTO categories (nom) VALUES (?)");
        mysqli_stmt_bind_param($stmt, "s", $nom);
        if (mysqli_stmt_execute($stmt)) {
            $id_cat = mysqli_insert_id($conn);
            enregistrer_historique($conn, $id_utilisateur, 'ajout', 'categories', $id_cat, "Ajout de la catégorie: $nom");
            $message = "✅ Catégorie ajoutée avec succès.";
        } else {
            $message = "❌ Erreur : " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "⚠️ Le nom de la catégorie est obligatoire.";
    }
}

// --- MODIFICATION D'UNE CATÉGORIE ---
if ($peut_modifier && isset($_POST['modifier'])) {
    $id = intval($_POST['id']);
    $nom = trim($_POST['nom']);
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ancien = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    $stmt = mysqli_prepare($conn, "UPDATE categories SET nom = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $nom, $id);
    if (mysqli_stmt_execute($stmt)) {
        enregistrer_historique($conn, $id_utilisateur, 'modification', 'categories', $id, "Modification de la catégorie: $nom", $ancien, ['nom' => $nom]);
        $message = "✅ Catégorie modifiée avec succès.";
    } else {
        $message = "❌ Erreur : " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// --- SUPPRESSION D'UNE CATÉGORIE ---
if ($peut_modifier && isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $cat = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
    enregistrer_historique($conn, $id_utilisateur, 'suppression', 'categories', $id, "Suppression de la catégorie: " . $cat['nom'], $cat, null);
    header("Location: categories.php");
    exit();
}

// --- RÉCUPÉRATION DES CATÉGORIES ---
$result = mysqli_query($conn, "SELECT c.*, COUNT(p.id) as nb_produits FROM categories c LEFT JOIN produits p ON c.id = p.id_categorie GROUP BY c.id ORDER BY c.nom ASC");

// Catégorie à modifier
$cat_edit = null;
if (isset($_GET['edit']) && $peut_modifier) {
    $id_edit = intval($_GET['edit']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_edit);
    mysqli_stmt_execute($stmt);
    $result_edit = mysqli_stmt_get_result($stmt);
    $cat_edit = mysqli_fetch_assoc($result_edit);
    mysqli_stmt_close($stmt);
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
            <a href="index.php" class="logo-link">
                <img src="../../assets/images/logo_epicerie.png" alt="Logo" class="logo-navbar">
            </a>
            <a href="index.php" class="nav-link">Tableau de bord</a>
            <a href="stock.php" class="nav-link">Stock</a>
            <a href="ventes.php" class="nav-link">Ventes</a>
            <a href="clients.php" class="nav-link">Clients</a>
            <a href="commandes.php" class="nav-link">Commandes</a>
            <a href="categories.php" class="nav-link">Catégories</a>
        </div>
        <a href="logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="main-container">
    <div class="content-wrapper">
        <h1>📁 Gestion des catégories</h1>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') !== false ? 'success' : (strpos($message, '⚠️') !== false ? 'warning' : 'error') ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if (!$peut_modifier): ?>
            <div class="message warning">
                ℹ️ <strong>Mode consultation</strong> : Vous pouvez consulter les catégories mais vous n'avez pas les droits pour en créer ou modifier. 
                Seuls les <strong>admins</strong> et <strong>responsables approvisionnement</strong> peuvent gérer les catégories.
            </div>
        <?php endif; ?>

        <?php if ($peut_modifier): ?>
        <h3><?= $cat_edit ? '✏️ Modifier une catégorie' : '➕ Ajouter une catégorie' ?></h3>
        <form method="POST">
            <?php if ($cat_edit): ?>
                <input type="hidden" name="id" value="<?= $cat_edit['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Nom de la catégorie :</label>
                <input type="text" name="nom" placeholder="Nom de la catégorie" value="<?= $cat_edit ? htmlspecialchars($cat_edit['nom']) : '' ?>" required>
            </div>
            <button type="submit" name="<?= $cat_edit ? 'modifier' : 'ajouter' ?>" class="btn">
                <?= $cat_edit ? '✏️ Modifier' : '➕ Ajouter' ?>
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
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['nom']) ?></td>
                    <td><span class="badge badge-info"><?= $row['nb_produits'] ?></span></td>
                    <?php if ($peut_modifier): ?>
                        <td>
                            <a href="?edit=<?= $row['id'] ?>" class="btn btn-info btn-sm">✏️ Modifier</a>
                            <a href="?supprimer=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')">🗑️ Supprimer</a>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

