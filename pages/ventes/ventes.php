<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

require_once('../../config/db_conn.php'); // fournit $pdo
$message = "";

// ======================================
// 🔵 AJOUT D’UNE VENTE
// ======================================
if (isset($_POST['ajouter'])) {

    $id_client = $_POST['id_client'] !== "" ? intval($_POST['id_client']) : null;
    $id_utilisateur = $_SESSION['id_utilisateur'];

    $produits = $_POST['produit_id'];
    $quantites = $_POST['quantite'];
    $totaux = $_POST['prix_total'];

    $total_general = 0;

    // ----- Vérification du stock -----
    $stock_ok = true;
    $erreurs_stock = [];

    for ($i = 0; $i < count($produits); $i++) {

        $id_produit = intval($produits[$i]);
        $qte = intval($quantites[$i]);

        if ($id_produit > 0 && $qte > 0) {
            $stmt = $pdo->prepare("SELECT nom, quantite_stock FROM produits WHERE id = :id");
            $stmt->execute([':id' => $id_produit]);
            $p = $stmt->fetch();

            if ($qte > $p['quantite_stock']) {
                $stock_ok = false;
                $erreurs_stock[] = "❌ Le produit <strong>" . htmlspecialchars($p['nom']) . "</strong> n’a que <strong>" . $p['quantite_stock'] . "</strong> en stock.";
            }
        }
    }

    if (!$stock_ok) {
        $message = implode("<br>", $erreurs_stock);

    } else {

        // Calcul du total général
        foreach ($totaux as $t) {
            $total_general += floatval($t);
        }

        // ----- Création de la vente -----
        $stmt = $pdo->prepare("
            INSERT INTO ventes (id_client, id_utilisateur, total)
            VALUES (:client, :user, :total)
        ");

        $stmt->execute([
            ':client' => $id_client,
            ':user'   => $id_utilisateur,
            ':total'  => $total_general
        ]);

        $id_vente = db_last_id($pdo, 'ventes');

        // ----- Ajouter les détails + mettre à jour le stock -----
        for ($i = 0; $i < count($produits); $i++) {

            $id_produit = intval($produits[$i]);
            $qte = intval($quantites[$i]);

            if ($id_produit > 0 && $qte > 0) {

                // Récup prix + stock
                $stmt = $pdo->prepare("SELECT prix_vente, quantite_stock FROM produits WHERE id = :id");
                $stmt->execute([':id' => $id_produit]);
                $p = $stmt->fetch();

                $prix_unitaire = $p['prix_vente'];
                $nouveau_stock = $p['quantite_stock'] - $qte;

                // Insert détail
                $stmt = $pdo->prepare("
                    INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire)
                    VALUES (:v, :p, :q, :prix)
                ");
                $stmt->execute([
                    ':v' => $id_vente,
                    ':p' => $id_produit,
                    ':q' => $qte,
                    ':prix' => $prix_unitaire
                ]);

                // Mise à jour du stock
                $stmt = $pdo->prepare("
                    UPDATE produits SET quantite_stock = :s WHERE id = :id
                ");
                $stmt->execute([
                    ':s' => $nouveau_stock,
                    ':id' => $id_produit
                ]);
            }
        }

        $message = "✅ Vente enregistrée avec succès.";
    }
}

// ======================================
// 🔴 SUPPRESSION
// ======================================
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    $pdo->prepare("DELETE FROM ventes WHERE id = :id")->execute([':id' => $id]);
    header("Location: ventes.php");
    exit();
}

// ======================================
// 📥 RÉCUPÉRER LES DONNÉES
// ======================================
$ventes = $pdo->query("
    SELECT v.id, v.total, v.date_vente,
           c.nom AS client,
           u.nom AS vendeur
    FROM ventes v
    LEFT JOIN clients c ON v.id_client = c.id
    LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id
    ORDER BY v.id DESC
")->fetchAll();

$clients = $pdo->query("SELECT id, nom FROM clients ORDER BY nom ASC")->fetchAll();

$produits = $pdo->query("
    SELECT id, nom, prix_vente, quantite_stock
    FROM produits
    ORDER BY nom ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>💰 Gestion des ventes - Smart Stock</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../assets/css/styles_connected.css">

<script>
function updateTotal(row) {
    let prix = parseFloat(row.querySelector('.prix').value);
    let qte = parseInt(row.querySelector('.qte').value);
    let stock = parseInt(row.querySelector('.qte').max);

    if (qte > stock) {
        alert("❌ Quantité demandée supérieure au stock disponible (" + stock + ").");
        qte = stock;
        row.querySelector('.qte').value = stock;
    }

    if (!isNaN(prix) && !isNaN(qte)) {
        row.querySelector('.total').value = (prix * qte).toFixed(2);
        updateGrandTotal();
    }
}

function updateGrandTotal() {
    let totaux = document.querySelectorAll('.total');
    let somme = 0;
    totaux.forEach(t => somme += parseFloat(t.value || 0));
    document.getElementById('grand_total').innerText = somme.toFixed(2) + " €";
}

function ajouterLigne() {
    const table = document.getElementById('table_produits');
    const clone = table.rows[1].cloneNode(true);

    clone.querySelectorAll('input').forEach(i => i.value = '');
    clone.querySelector('select').selectedIndex = 0;

    table.appendChild(clone);
}

function setPrixStock(select) {
    const option = select.selectedOptions[0];
    const prix = option.dataset.prix;
    const stock = option.dataset.stock;

    const row = select.closest('tr');
    row.querySelector('.prix').value = prix;

    const qteInput = row.querySelector('.qte');
    qteInput.max = stock;
    qteInput.placeholder = "max " + stock;

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
            <a href="ventes.php" class="nav-link active">Ventes</a>
            <a href="../clients/clients.php" class="nav-link">Clients</a>
            <a href="../commandes/commandes.php" class="nav-link">Commandes</a>
            <a href="../stock/categories.php" class="nav-link">Catégories</a>
        </div>

        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="main-container">
<div class="content-wrapper">

<h1>💰 Gestion des ventes</h1>

<?php if ($message): ?>
<div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
    <?= $message ?>
</div>
<?php endif; ?>

<h3>🧾 Nouvelle vente</h3>

<form method="POST">

    <div class="form-group">
        <label>Client :</label>
        <select name="id_client">
            <option value="">-- Aucun client --</option>
            <?php foreach ($clients as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <table id="table_produits">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Prix (€)</th>
                <th>Quantité</th>
                <th>Total (€)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <select name="produit_id[]" onchange="setPrixStock(this)">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($produits as $p): ?>
                        <option value="<?= $p['id'] ?>"
                                data-prix="<?= $p['prix_vente'] ?>"
                                data-stock="<?= $p['quantite_stock'] ?>">
                            <?= htmlspecialchars($p['nom']) ?> (<?= $p['quantite_stock'] ?> en stock)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>

                <td><input type="number" class="prix" name="prix_unitaire[]" readonly></td>

                <td><input type="number" class="qte" name="quantite[]" min="1"
                           oninput="updateTotal(this.closest('tr'))"></td>

                <td><input type="number" class="total" name="prix_total[]" readonly></td>
            </tr>
        </tbody>
    </table>

    <p>
        <button type="button" onclick="ajouterLigne()" class="btn btn-info">➕ Ajouter un produit</button>
    </p>

    <div style="text-align: right; font-weight: bold; font-size: 1.4em;">
        Total général : <span id="grand_total">0.00 €</span>
    </div>

    <button type="submit" name="ajouter" class="btn">💾 Enregistrer la vente</button>
</form>

<h3>📋 Liste des ventes</h3>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Client</th>
            <th>Vendeur</th>
            <th>Date</th>
            <th>Total (€)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

        <?php foreach ($ventes as $v): ?>
        <tr>
            <td><?= $v['id'] ?></td>
            <td><?= htmlspecialchars($v['client'] ?? "N/A") ?></td>
            <td><?= htmlspecialchars($v['vendeur'] ?? "N/A") ?></td>
            <td><?= date('d/m/Y H:i', strtotime($v['date_vente'])) ?></td>
            <td style="font-weight:bold;color:var(--success-color);">
                <?= number_format($v['total'], 2, ',', ' ') ?> €
            </td>
            <td>
                <a href="detailVente.php?id=<?= $v['id'] ?>" class="btn btn-info btn-sm">🔍 Détails</a>
                <a href="?supprimer=<?= $v['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Supprimer cette vente ?');">🗑️ Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>

    </tbody>
</table>

</div>
</div>

</body>
</html>
