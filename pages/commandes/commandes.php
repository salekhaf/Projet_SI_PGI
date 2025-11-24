<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

include('../../config/db_conn.php'); // Fournit $pdo au lieu de mysqli

$role = $_SESSION['role'];
$id_user = $_SESSION['id_utilisateur'];
$message = "";

// Vérifier si l'utilisateur peut gérer les commandes
$peut_commander = in_array($role, ['admin', 'responsable_approvisionnement']);

/*************************************************
 * 1 — AJOUT D’UNE COMMANDE
 *************************************************/
if ($peut_commander && isset($_POST['ajouter'])) {
    try {
        $pdo->beginTransaction();

        $id_fournisseur = intval($_POST['id_fournisseur']);
        $produits = $_POST['produit_id'];
        $quantites = $_POST['quantite'];
        $prix_achats = $_POST['prix_achat'];
        $total_general = 0;

        foreach ($produits as $i => $p) {
            $quantite = intval($quantites[$i]);
            $prix = floatval($prix_achats[$i]);

            if ($quantite > 0 && $prix > 0) {
                $total_general += $quantite * $prix;
            }
        }

        // Insert achat
        $stmt = $pdo->prepare("INSERT INTO achats (id_fournisseur, date_achat, montant_total) 
                               VALUES (:f, NOW(), :montant) RETURNING id");
        $stmt->execute([
            ':f' => $id_fournisseur,
            ':montant' => $total_general
        ]);
        $id_achat = $stmt->fetchColumn();

        // Insert détails + mise à jour stock
        $stmt_detail = $pdo->prepare("
            INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat)
            VALUES (:id_achat, :id_produit, :qty, :prix)
        ");

        $stmt_stock = $pdo->prepare("
            UPDATE produits
            SET quantite_stock = quantite_stock + :qty,
                prix_achat = :prix
            WHERE id = :id_prod
        ");

        foreach ($produits as $i => $id_produit) {
            $id_produit = intval($id_produit);
            $quantite = intval($quantites[$i]);
            $prix = floatval($prix_achats[$i]);

            if ($id_produit > 0 && $quantite > 0) {
                $stmt_detail->execute([
                    ':id_achat' => $id_achat,
                    ':id_produit' => $id_produit,
                    ':qty' => $quantite,
                    ':prix' => $prix
                ]);

                $stmt_stock->execute([
                    ':qty' => $quantite,
                    ':prix' => $prix,
                    ':id_prod' => $id_produit
                ]);
            }
        }

        $pdo->commit();
        $message = "✅ Commande enregistrée avec succès.";

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "❌ Erreur : " . $e->getMessage();
    }
}

/*************************************************
 * 2 — SUPPRESSION D’UNE COMMANDE
 *************************************************/
if ($peut_commander && isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);

    $stmt = $pdo->prepare("DELETE FROM achats WHERE id=:id");
    $stmt->execute([':id' => $id]);

    header("Location: commandes.php");
    exit();
}

/*************************************************
 * 3 — LISTE DES FOURNISSEURS, PRODUITS, COMMANDES
 *************************************************/

// Fournisseurs
$fournisseurs = $pdo->query("SELECT id, nom FROM fournisseurs ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

// Produits
$produits = $pdo->query("SELECT id, nom, prix_achat FROM produits ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

// Commandes
$achats = $pdo->query("
    SELECT a.id, a.date_achat, a.montant_total, f.nom AS fournisseur
    FROM achats a
    JOIN fournisseurs f ON a.id_fournisseur = f.id
    ORDER BY a.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>📦 Commandes fournisseurs - Smart Stock</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../assets/css/styles_connected.css">
<script>
function updateTotal(row) {
    const prix = parseFloat(row.querySelector('.prix').value) || 0;
    const qte = parseInt(row.querySelector('.qte').value) || 0;
    row.querySelector('.total-cell').textContent = (prix * qte).toFixed(2) + " €";
    updateGrandTotal();
}

function updateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.total-cell').forEach(cell => {
        total += parseFloat(cell.textContent) || 0;
    });
    document.getElementById('grand_total').textContent = total.toFixed(2) + " €";
}

function ajouterLigne() {
    const table = document.getElementById('table_produits');
    const clone = table.rows[1].cloneNode(true);
    clone.querySelectorAll('input').forEach(i => i.value = '');
    clone.querySelector('.total-cell').textContent = "0.00 €";
    table.appendChild(clone);
}

function setPrix(select) {
    const prix = select.selectedOptions[0].dataset.prix || 0;
    const row = select.closest('tr');
    row.querySelector('.prix').value = prix;
    updateTotal(row);
}
</script>
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
            <a href="commandes.php" class="nav-link">Commandes</a>
            <a href="../stock/categories.php" class="nav-link">Catégories</a>
        </div>
        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="main-container">
    <div class="content-wrapper">

<h1>📦 Commandes fournisseurs</h1>

<?php if ($message): ?>
    <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
        <?= $message ?>
    </div>
<?php endif; ?>

<?php if (!$peut_commander): ?>
<div class="message warning">
    ℹ️ Mode consultation uniquement.  
</div>
<?php endif; ?>

<?php if ($peut_commander): ?>
<h3>➕ Nouvelle commande</h3>

<form method="POST">
    <div class="form-group">
        <label>Fournisseur :</label>
        <select name="id_fournisseur" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach($fournisseurs as $f): ?>
            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <table id="table_produits">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Prix achat (€)</th>
                <th>Quantité</th>
                <th>Total (€)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <select name="produit_id[]" onchange="setPrix(this)">
                        <option value="">-- Choisir --</option>
                        <?php foreach($produits as $p): ?>
                        <option value="<?= $p['id'] ?>" data-prix="<?= $p['prix_achat'] ?>">
                            <?= htmlspecialchars($p['nom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="number" class="prix" name="prix_achat[]" step="0.01" oninput="updateTotal(this.closest('tr'))"></td>
                <td><input type="number" class="qte" name="quantite[]" min="1" value="1" oninput="updateTotal(this.closest('tr'))"></td>
                <td class="total-cell">0.00 €</td>
            </tr>
        </tbody>
    </table>

    <button type="button" onclick="ajouterLigne()" class="btn btn-info">➕ Ajouter un produit</button>

    <div style="margin-top:20px;font-weight:bold;">
        Total général : <span id="grand_total">0.00 €</span>
    </div>

    <button type="submit" name="ajouter" class="btn">Enregistrer</button>
</form>
<?php endif; ?>

<h3>📋 Liste des commandes</h3>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Fournisseur</th>
            <th>Date</th>
            <th>Total</th>
            <th>Détails</th>
            <?php if ($peut_commander): ?><th>Action</th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($achats as $a): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['fournisseur']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($a['date_achat'])) ?></td>
            <td><?= number_format($a['montant_total'], 2, ',', ' ') ?> €</td>
            <td>
                <a href="detailCommande.php?id=<?= $a['id'] ?>" class="btn btn-info btn-sm">🔍 Voir</a>
                <a href="bonCommande.php?id=<?= $a['id'] ?>" class="btn btn-success btn-sm">📄 PDF</a>
            </td>
            <?php if ($peut_commander): ?>
            <td><a href="?supprimer=<?= $a['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">🗑️</a></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div>
</div>

</body>
</html>
