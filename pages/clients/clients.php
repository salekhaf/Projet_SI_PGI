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
$peut_modifier = in_array($role, ['admin', 'vendeur']);
$message = "";

// --- PAGINATION ---
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// --- RECHERCHE ---
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';

// --- AJOUT D'UN CLIENT ---
if (isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom']);
    $tel = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);
    
    if ($nom !== "") {
        $stmt = mysqli_prepare($conn, "INSERT INTO clients (nom, telephone, email, adresse) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $nom, $tel, $email, $adresse);
        if (mysqli_stmt_execute($stmt)) {
            $id_client = mysqli_insert_id($conn);
            enregistrer_historique($conn, $id_utilisateur, 'ajout', 'clients', $id_client, "Ajout du client: $nom");
            $message = "✅ Client ajouté avec succès.";
        } else {
            $message = "❌ Erreur lors de l'ajout du client : " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "⚠️ Le nom du client est obligatoire.";
    }
}

// --- MODIFICATION D'UN CLIENT ---
if ($peut_modifier && isset($_POST['modifier'])) {
    $id = intval($_POST['id']);
    $nom = trim($_POST['nom']);
    $tel = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM clients WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ancien = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    $stmt = mysqli_prepare($conn, "UPDATE clients SET nom = ?, telephone = ?, email = ?, adresse = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssi", $nom, $tel, $email, $adresse, $id);
    if (mysqli_stmt_execute($stmt)) {
        enregistrer_historique($conn, $id_utilisateur, 'modification', 'clients', $id, "Modification du client: $nom", $ancien, ['nom' => $nom, 'telephone' => $tel, 'email' => $email]);
        $message = "✅ Client modifié avec succès.";
    } else {
        $message = "❌ Erreur : " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// --- SUPPRESSION D'UN CLIENT ---
if ($peut_modifier && isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM clients WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $client = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM clients WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    enregistrer_historique($conn, $id_utilisateur, 'suppression', 'clients', $id, "Suppression du client: " . $client['nom'], $client, null);
    header("Location: clients.php");
    exit();
}

// --- EXPORT CSV ---
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=clients_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['ID', 'Nom', 'Téléphone', 'Email', 'Adresse'], ';');
    
    $query = "SELECT * FROM clients ORDER BY nom ASC";
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
$count_query = "SELECT COUNT(*) as total FROM clients $where_clause";
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
$query = "SELECT * FROM clients $where_clause ORDER BY nom ASC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Client à modifier
$client_edit = null;
if (isset($_GET['edit']) && $peut_modifier) {
    $id_edit = intval($_GET['edit']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM clients WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_edit);
    mysqli_stmt_execute($stmt);
    $result_edit = mysqli_stmt_get_result($stmt);
    $client_edit = mysqli_fetch_assoc($result_edit);
    mysqli_stmt_close($stmt);
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
    <h1>👥 Gestion des clients</h1>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') !== false ? 'success' : (strpos($message, '⚠️') !== false ? 'warning' : 'error') ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

    <!-- Recherche -->
    <div class="filters">
        <form method="GET" style="display: flex; gap: 10px; width: 100%;">
            <input type="text" name="recherche" placeholder="🔍 Rechercher un client..." value="<?= htmlspecialchars($recherche) ?>" style="flex: 1;">
            <button type="submit" class="btn">Filtrer</button>
            <a href="clients.php" class="btn btn-secondary">Réinitialiser</a>
        </form>
    </div>

    <!-- Export -->
    <div class="export-buttons">
        <a href="?export=csv">📥 Exporter en CSV</a>
    </div>

        <h3><?= $client_edit ? '✏️ Modifier un client' : '➕ Ajouter un client' ?></h3>
    <form method="POST">
            <?php if ($client_edit): ?>
                <input type="hidden" name="id" value="<?= $client_edit['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Nom :</label>
                <input type="text" name="nom" placeholder="Nom" value="<?= $client_edit ? htmlspecialchars($client_edit['nom']) : '' ?>" required>
            </div>
            <div class="form-group">
                <label>Téléphone :</label>
                <input type="text" name="telephone" placeholder="Téléphone" value="<?= $client_edit ? htmlspecialchars($client_edit['telephone']) : '' ?>">
            </div>
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" placeholder="Email" value="<?= $client_edit ? htmlspecialchars($client_edit['email']) : '' ?>">
            </div>
            <div class="form-group">
                <label>Adresse :</label>
                <textarea name="adresse" placeholder="Adresse"><?= $client_edit ? htmlspecialchars($client_edit['adresse']) : '' ?></textarea>
            </div>
            <button name="<?= $client_edit ? 'modifier' : 'ajouter' ?>" class="btn">
                <?= $client_edit ? '✏️ Modifier' : '➕ Ajouter' ?>
            </button>
            <?php if ($client_edit): ?>
                <a href="clients.php" class="btn btn-secondary">Annuler</a>
            <?php endif; ?>
    </form>

        <h3>📋 Liste des clients (<?= $total_rows ?> client<?= $total_rows > 1 ? 's' : '' ?>)</h3>
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
                    <td colspan="<?= $peut_modifier ? '6' : '5' ?>" style="text-align: center; padding: 20px;">
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
