<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: auth.php");
    exit();
}
include('db_conn.php');

if (!isset($_GET['id'])) {
    header("Location: ventes.php");
    exit();
}

$id_vente = intval($_GET['id']);

// --- Récupérer la vente principale ---
$sql_vente = "SELECT v.id, v.date_vente, v.total, 
                     c.nom AS client, 
                     u.nom AS vendeur
              FROM ventes v
              LEFT JOIN clients c ON v.id_client = c.id
              LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id
              WHERE v.id = $id_vente";
$vente = mysqli_query($conn, $sql_vente);
if (mysqli_num_rows($vente) == 0) {
    die("Vente introuvable !");
}
$vente = mysqli_fetch_assoc($vente);

// --- Récupérer les détails ---
$sql_details = "SELECT d.quantite, d.prix_unitaire, p.nom 
                FROM details_vente d
                JOIN produits p ON d.id_produit = p.id
                WHERE d.id_vente = $id_vente";
$details = mysqli_query($conn, $sql_details);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Détails de la vente #<?= $vente['id'] ?> - PGI Épicerie</title>
<style>
body {font-family: Arial, sans-serif; background: #f5f6fa; padding: 20px;}
.container {max-width: 800px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);}
h1 {text-align: center; color: #007bff;}
table {width: 100%; border-collapse: collapse; margin-top: 15px;}
th, td {border: 1px solid #ccc; padding: 8px; text-align: center;}
th {background: #007bff; color: white;}
.total {font-weight: bold; text-align: right; margin-top: 15px;}
a {color: #007bff; text-decoration: none;}
a:hover {text-decoration: underline;}
.back-btn {display: inline-block; background: #007bff; color: white; padding: 8px 14px; border-radius: 6px; text-decoration: none;}
.back-btn:hover {background: #0056b3;}
</style>
</head>
<body>
<div class="container">
    <h1>🧾 Détails de la vente n°<?= $vente['id'] ?></h1>
    <p><a class="back-btn" href="ventes.php">⬅️ Retour aux ventes</a></p>

    <h3>Informations générales</h3>
    <table>
        <tr><th>Client</th><td><?= htmlspecialchars($vente['client'] ?? "Non spécifié") ?></td></tr>
        <tr><th>Vendeur</th><td><?= htmlspecialchars($vente['vendeur']) ?></td></tr>
        <tr><th>Date</th><td><?= $vente['date_vente'] ?></td></tr>
        <tr><th>Total</th><td><strong><?= number_format($vente['total'], 2, ',', ' ') ?> €</strong></td></tr>
    </table>

    <h3 style="margin-top:25px;">Produits vendus</h3>
    <table>
        <tr><th>Produit</th><th>Quantité</th><th>Prix unitaire (€)</th><th>Sous-total (€)</th></tr>
        <?php $total_calcule = 0; ?>
        <?php while ($d = mysqli_fetch_assoc($details)): 
            $sous_total = $d['quantite'] * $d['prix_unitaire'];
            $total_calcule += $sous_total;
        ?>
        <tr>
            <td><?= htmlspecialchars($d['nom']) ?></td>
            <td><?= $d['quantite'] ?></td>
            <td><?= number_format($d['prix_unitaire'], 2, ',', ' ') ?></td>
            <td><?= number_format($sous_total, 2, ',', ' ') ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <p class="total">Total calculé : <strong><?= number_format($total_calcule, 2, ',', ' ') ?> €</strong></p>
</div>
</body>
</html>
