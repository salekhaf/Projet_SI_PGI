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

// --- PAGINATION ---
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// --- RECHERCHE ---
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';

// --- AJOUT D'UN FOURNISSEUR ---
if ($peut_modifier && isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    if ($nom !== "") {
        $stmt = mysqli_prepare($conn, "INSERT INTO fournisseurs (nom, telephone, email, adresse) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $nom, $telephone, $email, $adresse);
        if (mysqli_stmt_execute($stmt)) {
            $id_fournisseur = mysqli_insert_id($conn);
            enregistrer_historique($conn, $id_utilisateur, 'ajout', 'fournisseurs', $id_fournisseur, "Ajout du fournisseur: $nom");
            $message = "✅ Fournisseur ajouté avec succès.";
        } else {
            $message = "❌ Erreur lors de l'ajout : " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "⚠️ Le nom du fournisseur est obligatoire.";
    }
}

// --- MODIFICATION D'UN FOURNISSEUR ---
if ($peut_modifier && isset($_POST['modifier'])) {
    $id = intval($_POST['id']);
    $nom = trim($_POST['nom']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM fournisseurs WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ancien = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    $stmt = mysqli_prepare($conn, "UPDATE fournisseurs SET nom = ?, telephone = ?, email = ?, adresse = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssi", $nom, $telephone, $email, $adresse, $id);
    if (mysqli_stmt_execute($stmt)) {
        enregistrer_historique($conn, $id_utilisateur, 'modification', 'fournisseurs', $id, "Modification du fournisseur: $nom", $ancien, ['nom' => $nom, 'telephone' => $telephone, 'email' => $email]);
        $message = "✅ Fournisseur modifié avec succès.";
    } else {
        $message = "❌ Erreur : " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// --- SUPPRESSION D'UN FOURNISSEUR ---
if ($peut_modifier && isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM fournisseurs WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $fournisseur = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM fournisseurs WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    enregistrer_historique($conn, $id_utilisateur, 'suppression', 'fournisseurs', $id, "Suppression du fournisseur: " . $fournisseur['nom'], $fournisseur, null);
    header("Location: fournisseurs.php");
    exit();
}

// --- EXPORT CSV ---
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=fournisseurs_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['ID', 'Nom', 'Téléphone', 'Email', 'Adresse'], ';');
    
    $query = "SELECT * FROM fournisseurs ORDER BY nom ASC";
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['id'],
            $row['nom'],
            $row['telephone'],
            $row['email'],
            $row['adresse']
        ], ';');
    }
    
    fclose($output);
    exit();
}

// --- CONSTRUCTION DE LA REQUÊTE ---
$where_clause = "";
$params = [];
$types = "";

if (!empty($recherche)) {
    $where_clause = "WHERE (nom LIKE ? OR telephone LIKE ? OR email LIKE ?)";
    $recherche_param = "%$recherche%";
    $params = [$recherche_param, $recherche_param, $recherche_param];
    $types = "sss";
}

// Compte total
$count_query = "SELECT COUNT(*) as total FROM fournisseurs $where_clause";
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $count_result = mysqli_stmt_get_result($stmt);
    $total_rows = mysqli_fetch_assoc($count_result)['total'];
    mysqli_stmt_close($stmt);
} else {
    $count_result = mysqli_query($conn, $count_query);
    $total_rows = mysqli_fetch_assoc($count_result)['total'];
}
$total_pages = ceil($total_rows / $per_page);

// Requête principale
$query = "SELECT f.*, COUNT(DISTINCT a.id) as nb_commandes, COALESCE(SUM(a.montant_total), 0) as total_commandes 
          FROM fournisseurs f 
          LEFT JOIN achats a ON f.id = a.id_fournisseur 
          $where_clause 
          GROUP BY f.id 
          ORDER BY f.nom ASC 
          LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fournisseur à modifier
$fournisseur_edit = null;
if (isset($_GET['edit']) && $peut_modifier) {
    $id_edit = intval($_GET['edit']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM fournisseurs WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_edit);
    mysqli_stmt_execute($stmt);
    $result_edit = mysqli_stmt_get_result($stmt);
    $fournisseur_edit = mysqli_fetch_assoc($result_edit);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>🚚 Gestion des fournisseurs - Smart Stock</title>
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
            <a href="fournisseurs.php" class="nav-link">Fournisseurs</a>
            <a href="commandes.php" class="nav-link">Commandes</a>
            <a href="categories.php" class="nav-link">Catégories</a>
        </div>
        <a href="logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="main-container">
    <div class="content-wrapper">
    <h1>🚚 Gestion des fournisseurs</h1>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') !== false ? 'success' : (strpos($message, '⚠️') !== false ? 'warning' : 'error') ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

    <!-- Recherche -->
    <div class="filters">
        <form method="GET" style="display: flex; gap: 10px; width: 100%;">
            <input type="text" name="recherche" placeholder="🔍 Rechercher un fournisseur..." value="<?= htmlspecialchars($recherche) ?>" style="flex: 1;">
            <button type="submit" class="btn">Filtrer</button>
            <a href="fournisseurs.php" class="btn btn-secondary">Réinitialiser</a>
        </form>
    </div>

    <!-- Export -->
    <div class="export-buttons">
        <a href="?export=csv">📥 Exporter en CSV</a>
    </div>

    <?php if ($peut_modifier): ?>
        <h3><?= $fournisseur_edit ? '✏️ Modifier un fournisseur' : '➕ Ajouter un fournisseur' ?></h3>
        <form method="POST">
            <?php if ($fournisseur_edit): ?>
                <input type="hidden" name="id" value="<?= $fournisseur_edit['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Nom du fournisseur :</label>
                <input type="text" name="nom" placeholder="Nom du fournisseur" value="<?= $fournisseur_edit ? htmlspecialchars($fournisseur_edit['nom']) : '' ?>" required>
            </div>
            <div class="form-group">
                <label>Téléphone :</label>
                <input type="text" name="telephone" placeholder="Téléphone" value="<?= $fournisseur_edit ? htmlspecialchars($fournisseur_edit['telephone']) : '' ?>">
            </div>
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" placeholder="Email" value="<?= $fournisseur_edit ? htmlspecialchars($fournisseur_edit['email']) : '' ?>">
            </div>
            <div class="form-group">
                <label>Adresse :</label>
                <textarea name="adresse" placeholder="Adresse"><?= $fournisseur_edit ? htmlspecialchars($fournisseur_edit['adresse']) : '' ?></textarea>
            </div>
            <button type="submit" name="<?= $fournisseur_edit ? 'modifier' : 'ajouter' ?>" class="btn">
                <?= $fournisseur_edit ? '✏️ Modifier' : '➕ Ajouter' ?>
            </button>
            <?php if ($fournisseur_edit): ?>
                <a href="fournisseurs.php" class="btn btn-secondary">Annuler</a>
            <?php endif; ?>
        </form>
    <?php else: ?>
            <div class="message warning">
                ℹ️ Vous n'avez pas les droits pour ajouter ou supprimer des fournisseurs.<br>
                Seuls les <strong>admins</strong> et <strong>responsables approvisionnement</strong> peuvent le faire.
            </div>
    <?php endif; ?>

        <h3>📋 Liste des fournisseurs (<?= $total_rows ?> fournisseur<?= $total_rows > 1 ? 's' : '' ?>)</h3>
    <table>
            <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Téléphone</th>
            <th>Email</th>
            <th>Adresse</th>
                    <th>Commandes</th>
                    <th>Total (€)</th>
                    <?php if ($peut_modifier): ?><th>Actions</th><?php endif; ?>
        </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($result) > 0):
                    while ($row = mysqli_fetch_assoc($result)): 
                ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['nom']) ?></td>
            <td><?= htmlspecialchars($row['telephone']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['adresse']) ?></td>
                    <td><?= $row['nb_commandes'] ?></td>
                    <td style="font-weight: bold; color: var(--primary-color);"><?= number_format($row['total_commandes'], 2, ',', ' ') ?> €</td>
            <?php if ($peut_modifier): ?>
                        <td>
                            <a href="?edit=<?= $row['id'] ?>&<?= http_build_query(array_merge($_GET, ['edit' => $row['id']])) ?>" class="btn btn-info btn-sm">✏️ Modifier</a>
                            <a href="?supprimer=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr ?')">🗑️ Supprimer</a>
                        </td>
            <?php endif; ?>
        </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="<?= $peut_modifier ? '8' : '7' ?>" style="text-align: center; padding: 20px;">
                        Aucun fournisseur trouvé.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">« Précédent</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>&<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Suivant »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    </div>
</div>

</body>
</html>
