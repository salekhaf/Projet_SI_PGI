<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

require('../../config/db_conn.php'); // contient $pdo
require('../../includes/historique_helper.php');

$nom = $_SESSION['nom'];
$role = $_SESSION['role'];
$id_utilisateur = $_SESSION['id_utilisateur'];
$peut_ajouter = in_array($role, ['admin', 'responsable_approvisionnement']);
$peut_modifier = in_array($role, ['admin', 'responsable_approvisionnement']);
$message = "";

// --- PAGINATION ---
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// --- RECHERCHE ET FILTRES ---
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';
$filtre_categorie = isset($_GET['categorie']) ? intval($_GET['categorie']) : 0;
$filtre_stock = $_GET['stock'] ?? 'tous';

// --- AJOUT PRODUIT ---
if ($peut_ajouter && isset($_POST['ajouter_produit'])) {

    $nom_produit = trim($_POST['nom']);
    $prix_achat = floatval($_POST['prix_achat']);
    $prix_vente = floatval($_POST['prix_vente']);
    $quantite_stock = intval($_POST['quantite_stock']);
    $id_categorie = !empty($_POST['id_categorie']) ? intval($_POST['id_categorie']) : null;
    $fournisseur_id = !empty($_POST['fournisseur_id']) ? intval($_POST['fournisseur_id']) : null;

    if ($nom_produit !== "" && $prix_achat > 0 && $prix_vente > 0) {

        $stmt = $pdo->prepare("SELECT id FROM produits WHERE LOWER(nom)=LOWER(?)");
        $stmt->execute([$nom_produit]);

        if ($stmt->rowCount() > 0) {
            $message = "⚠️ Ce produit existe déjà.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO produits (nom, prix_achat, prix_vente, quantite_stock, id_categorie, fournisseur_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            if ($stmt->execute([$nom_produit, $prix_achat, $prix_vente, $quantite_stock, $id_categorie, $fournisseur_id])) {

                $id_produit = db_last_id($pdo, "produits");

                enregistrer_historique(
                    $pdo, $id_utilisateur, 'ajout', 'produits', $id_produit,
                    "Ajout du produit: $nom_produit",
                    null,
                    [
                        'nom' => $nom_produit,
                        'prix_achat' => $prix_achat,
                        'prix_vente' => $prix_vente
                    ]
                );

                $message = "✅ Nouveau produit ajouté avec succès.";
            } else {
                $message = "❌ Erreur SQL lors de l'ajout.";
            }
        }

    } else {
        $message = "⚠️ Veuillez remplir tous les champs correctement.";
    }
}

// --- MODIFICATION PRODUIT ---
if ($peut_modifier && isset($_POST['modifier_produit'])) {

    $id_produit = intval($_POST['id_produit']);

    // récupérer anciennes valeurs
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$id_produit]);
    $ancien = $stmt->fetch();

    $nom_produit = trim($_POST['nom']);
    $prix_achat = floatval($_POST['prix_achat']);
    $prix_vente = floatval($_POST['prix_vente']);
    $quantite_stock = intval($_POST['quantite_stock']);
    $id_categorie = !empty($_POST['id_categorie']) ? intval($_POST['id_categorie']) : null;
    $fournisseur_id = !empty($_POST['fournisseur_id']) ? intval($_POST['fournisseur_id']) : null;

    $stmt = $pdo->prepare("
        UPDATE produits
        SET nom=?, prix_achat=?, prix_vente=?, quantite_stock=?, id_categorie=?, fournisseur_id=?
        WHERE id=?
    ");

    if ($stmt->execute([
        $nom_produit, $prix_achat, $prix_vente,
        $quantite_stock, $id_categorie, $fournisseur_id, $id_produit
    ])) {

        enregistrer_historique(
            $pdo, $id_utilisateur, 'modification', 'produits', $id_produit,
            "Modification du produit: $nom_produit",
            $ancien,
            [
                "nom" => $nom_produit,
                "prix_achat" => $prix_achat,
                "prix_vente" => $prix_vente,
                "quantite_stock" => $quantite_stock
            ]
        );

        $message = "✅ Produit modifié avec succès.";

    } else {
        $message = "❌ Erreur SQL lors de la modification.";
    }
}

// --- EXPORT CSV ---
if (isset($_GET['export']) && $_GET['export'] == 'csv') {

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=stock_'.date('Y-m-d').'.csv');

    $output = fopen("php://output", "w");
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

    fputcsv($output, ['ID','Nom','Catégorie','Prix Achat','Prix Vente','Stock','Fournisseur'], ';');

    $query = "
        SELECT p.*, c.nom AS categorie_nom, f.nom AS fournisseur_nom
        FROM produits p
        LEFT JOIN categories c ON p.id_categorie = c.id
        LEFT JOIN fournisseurs f ON p.fournisseur_id = f.id
        ORDER BY p.nom ASC
    ";

    $rows = $pdo->query($query)->fetchAll();

    foreach ($rows as $row) {
        fputcsv($output, [
            $row['id'], $row['nom'],
            $row['categorie_nom'] ?? 'N/A',
            $row['prix_achat'], $row['prix_vente'],
            $row['quantite_stock'],
            $row['fournisseur_nom'] ?? 'N/A'
        ], ';');
    }
    exit;
}

// --- FILTRES SQL ---
$where = [];
$values = [];

if ($recherche !== "") {
    $where[] = "(p.nom ILIKE ? OR CAST(p.id AS TEXT) ILIKE ?)";
    $values[] = "%$recherche%";
    $values[] = "%$recherche%";
}

if ($filtre_categorie > 0) {
    $where[] = "p.id_categorie = ?";
    $values[] = $filtre_categorie;
}

if ($filtre_stock == "bas") {
    $where[] = "p.quantite_stock < 10 AND p.quantite_stock > 0";
} elseif ($filtre_stock == "critique") {
    $where[] = "p.quantite_stock <= 0";
}

$where_clause = count($where) ? "WHERE ".implode(" AND ", $where) : "";

// --- COUNT ---
$stmt = $pdo->prepare("SELECT COUNT(*) FROM produits p $where_clause");
$stmt->execute($values);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $per_page);

// --- RÉCUP LISTE PRODUITS ---
$query = "
    SELECT p.*, c.nom AS categorie_nom, f.nom AS fournisseur_nom
    FROM produits p
    LEFT JOIN categories c ON p.id_categorie = c.id
    LEFT JOIN fournisseurs f ON p.fournisseur_id = f.id
    $where_clause
    ORDER BY p.nom ASC
    LIMIT $per_page OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute($values);
$result = $stmt->fetchAll();

// --- LISTES CATÉGORIES & FOURNISSEURS ---
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom ASC")->fetchAll();
$fournisseurs = $pdo->query("SELECT * FROM fournisseurs ORDER BY nom ASC")->fetchAll();

// --- PRODUIT À MODIFIER ---
$produit_edit = null;
if (isset($_GET['edit']) && $peut_modifier) {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id=?");
    $stmt->execute([intval($_GET['edit'])]);
    $produit_edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>📦 Gestion du stock - Smart Stock</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="stylesheet" href="../../assets/css/styles_connected.css">
</head>
<body>

<header>
    <nav class="navbar">
        <div class="nav-left">
            <a href="../dashboard/index.php" class="logo-link">
                <img src="../../assets/images/logo_epicerie.png" class="logo-navbar" alt="Logo">
            </a>

            <a href="../dashboard/index.php" class="nav-link">Tableau de bord</a>
            <a href="stock.php" class="nav-link">Stock</a>
            <a href="../ventes/ventes.php" class="nav-link">Ventes</a>
            <a href="../clients/clients.php" class="nav-link">Clients</a>
            <a href="../fournisseurs/fournisseurs.php" class="nav-link">Fournisseurs</a>
            <a href="../commandes/commandes.php" class="nav-link">Commandes</a>
            <a href="categories.php" class="nav-link">Catégories</a>
        </div>

        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="main-container">
    <div class="content-wrapper">

    <h1>📦 Gestion du stock</h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, '✅') !== false ? 'success' : (strpos($message, '⚠️') !== false ? 'warning' : 'error') ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="filters">
        <form method="GET" style="display: flex; flex-wrap: wrap; gap: 10px; width: 100%;">

            <input type="text" name="recherche" placeholder="🔍 Rechercher un produit..."
                   value="<?= htmlspecialchars($recherche) ?>" style="flex: 1 1 200px;">

            <select name="categorie" style="flex: 0 1 150px;">
                <option value="0">Toutes les catégories</option>

                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= $filtre_categorie == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="stock" style="flex: 0 1 150px;">
                <option value="tous"     <?= $filtre_stock == 'tous' ? 'selected' : '' ?>>Tous les stocks</option>
                <option value="bas"      <?= $filtre_stock == 'bas' ? 'selected' : '' ?>>Stock bas (&lt; 10)</option>
                <option value="critique" <?= $filtre_stock == 'critique' ? 'selected' : '' ?>>Stock critique (0)</option>
            </select>

            <button type="submit" class="btn">Filtrer</button>
            <a href="stock.php" class="btn btn-secondary">Réinitialiser</a>
        </form>
    </div>

    <div class="export-buttons">
        <a href="?export=csv&<?= http_build_query($_GET) ?>">📥 Exporter en CSV</a>
    </div>
    <?php if ($peut_ajouter): ?>
    <h3><?= $produit_edit ? '✏️ Modifier un produit' : '➕ Ajouter un produit' ?></h3>

    <form method="POST">
        <?php if ($produit_edit): ?>
            <input type="hidden" name="id_produit" value="<?= $produit_edit['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Nom du produit :</label>
            <input type="text" name="nom" placeholder="Nom du produit"
                   value="<?= $produit_edit ? htmlspecialchars($produit_edit['nom']) : '' ?>" required>
        </div>

        <div class="form-group">
            <label>Prix d'achat (€) :</label>
            <input type="number" name="prix_achat" step="0.01" placeholder="Prix d'achat"
                   value="<?= $produit_edit ? $produit_edit['prix_achat'] : '' ?>" required>
        </div>

        <div class="form-group">
            <label>Prix de vente (€) :</label>
            <input type="number" name="prix_vente" step="0.01" placeholder="Prix de vente"
                   value="<?= $produit_edit ? $produit_edit['prix_vente'] : '' ?>" required>
        </div>

        <div class="form-group">
            <label>Quantité en stock :</label>
            <input type="number" name="quantite_stock" min="0" placeholder="Quantité"
                   value="<?= $produit_edit ? $produit_edit['quantite_stock'] : '0' ?>">
        </div>

        <div class="form-group">
            <label>Catégorie :</label>
            <select name="id_categorie">
                <option value="">-- Catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= ($produit_edit && $produit_edit['id_categorie'] == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Fournisseur :</label>
            <select name="fournisseur_id">
                <option value="">-- Fournisseur --</option>
                <?php foreach ($fournisseurs as $four): ?>
                    <option value="<?= $four['id'] ?>"
                        <?= ($produit_edit && $produit_edit['fournisseur_id'] == $four['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($four['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" name="<?= $produit_edit ? 'modifier_produit' : 'ajouter_produit' ?>" class="btn">
            <?= $produit_edit ? '✏️ Modifier' : '➕ Ajouter' ?>
        </button>

        <?php if ($produit_edit): ?>
            <a href="stock.php" class="btn btn-secondary">Annuler</a>
        <?php endif; ?>
    </form>
    <?php endif; ?>

    <h3>📋 Liste des produits (<?= $total_rows ?> produit<?= $total_rows > 1 ? 's' : '' ?>)</h3>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Produit</th>
            <th>Catégorie</th>
            <th>Prix d'achat (€)</th>
            <th>Prix de vente (€)</th>
            <th>Quantité</th>
            <th>Fournisseur</th>
            <?php if ($peut_modifier): ?><th>Actions</th><?php endif; ?>
        </tr>
        </thead>

        <tbody>
        <?php if ($result && count($result) > 0): ?>
            <?php foreach ($result as $p): ?>
                <?php
                $class = "";
                if ($p['quantite_stock'] <= 0) $class = "critical-stock";
                elseif ($p['quantite_stock'] < 10) $class = "low-stock";
                ?>
                <tr class="<?= $class ?>">
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nom']) ?></td>
                    <td><?= htmlspecialchars($p['categorie_nom'] ?? 'N/A') ?></td>
                    <td><?= number_format($p['prix_achat'], 2, ',', ' ') ?></td>
                    <td><?= number_format($p['prix_vente'], 2, ',', ' ') ?></td>
                    <td><?= $p['quantite_stock'] ?></td>
                    <td><?= htmlspecialchars($p['fournisseur_nom'] ?? 'N/A') ?></td>

                    <?php if ($peut_modifier): ?>
                        <td>
                            <a href="?edit=<?= $p['id'] ?>&<?= http_build_query($_GET) ?>"
                               class="btn btn-info btn-sm">✏️ Modifier</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?= $peut_modifier ? '8' : '7' ?>" style="text-align:center; padding:20px;">
                    Aucun produit trouvé.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&<?= http_build_query($_GET) ?>">« Précédent</a>
        <?php endif; ?>

        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>&<?= http_build_query($_GET) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>&<?= http_build_query($_GET) ?>">Suivant »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    </div>
</div>

</body>
</html>
