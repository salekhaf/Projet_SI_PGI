<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

require_once('../../config/db_conn.php'); // fournit $pdo

if (!isset($_GET['id'])) {
    header("Location: ventes.php");
    exit();
}

$id_vente = intval($_GET['id']);

// ====== RÉCUPÉRER LA VENTE PRINCIPALE ======
$sql_vente = "
    SELECT v.id, v.date_vente, v.total,
           c.nom AS client,
           u.nom AS vendeur
    FROM ventes v
    LEFT JOIN clients c ON v.id_client = c.id
    LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id
    WHERE v.id = :id
";

$stmt = $pdo->prepare($sql_vente);
$stmt->execute([':id' => $id_vente]);
$vente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vente) {
    die("Vente introuvable !");
}

// ====== RÉCUPÉRER LES DÉTAILS ======
$sql_details = "
    SELECT d.quantite, d.prix_unitaire, p.nom
    FROM details_vente d
    JOIN produits p ON d.id_produit = p.id
    WHERE d.id_vente = :id
";

$stmt = $pdo->prepare($sql_details);
$stmt->execute([':id' => $id_vente]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>🧾 Détails de la vente #<?= htmlspecialchars($vente['id']) ?> - Smart Stock</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../assets/css/styles_connected.css">
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
            <a href="ventes.php" class="nav-link">Ventes</a>
            <a href="../clients/clients.php" class="nav-link">Clients</a>
            <a href="../commandes/commandes.php" class="nav-link">Commandes</a>
            <a href="../stock/categories.php" class="nav-link">Catégories</a>
        </div>

        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="main-container">
<div class="content-wrapper">

<h1>🧾 Détails de la vente n°<?= htmlspecialchars($vente['id']) ?></h1>

<p style="margin-bottom: 25px;">
    <a href="ventes.php" class="btn btn-secondary">⬅️ Retour aux ventes</a>
</p>

<h3>Informations générales</h3>

<table>
    <thead>
        <tr>
            <th>Client</th>
            <th>Vendeur</th>
            <th>Date</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?= htmlspecialchars($vente['client'] ?? "Non spécifié") ?></td>
            <td><?= htmlspecialchars($vente['vendeur']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($vente['date_vente'])) ?></td>
            <td style="font-weight: bold; color: var(--success-color); font-size: 1.1em;">
                <?= number_format($vente['total'], 2, ',', ' ') ?> €
            </td>
        </tr>
    </tbody>
</table>

<h3 style="margin-top: 30px;">Produits vendus</h3>

<table>
    <thead>
        <tr>
            <th>Produit</th>
            <th>Quantité</th>
            <th>Prix unitaire (€)</th>
            <th>Sous-total (€)</th>
        </tr>
    </thead>
    <tbody>

<?php 
$total_calcule = 0;

foreach ($details as $d):
    $sous_total = $d['quantite'] * $d['prix_unitaire'];
    $total_calcule += $sous_total;
?>
<tr>
    <td><?= htmlspecialchars($d['nom']) ?></td>
    <td><?= $d['quantite'] ?></td>
    <td><?= number_format($d['prix_unitaire'], 2, ',', ' ') ?></td>
    <td style="font-weight: bold; color: var(--primary-color);">
        <?= number_format($sous_total, 2, ',', ' ') ?> €
    </td>
</tr>

<?php endforeach; ?>

    </tbody>
</table>

<div style="text-align: right; font-weight: bold; font-size: 1.3em; margin-top: 20px; color: var(--success-color);">
    Total calculé : <strong><?= number_format($total_calcule, 2, ',', ' ') ?> €</strong>
</div>

</div>
</div>
</body>
</html>
