<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: auth.php");
    exit();
}

include('db_conn.php');

if (!isset($_GET['id'])) {
    header("Location: commandes.php");
    exit();
}

$id_achat = intval($_GET['id']);

// Récupération des infos principales
$sql = "SELECT a.id, a.date_achat, a.montant_total, 
               f.nom AS fournisseur, f.email, f.telephone
        FROM achats a
        JOIN fournisseurs f ON a.id_fournisseur = f.id
        WHERE a.id = $id_achat";
$res = mysqli_query($conn, $sql);
$achat = mysqli_fetch_assoc($res);

if (!$achat) {
    die("❌ Commande introuvable.");
}

// Récupération des produits achetés
$details = mysqli_query($conn, "
    SELECT p.nom AS produit, d.quantite, d.prix_achat
    FROM details_achat d
    JOIN produits p ON d.id_produit = p.id
    WHERE d.id_achat = $id_achat
");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Détails commande #<?= $id_achat ?> - PGI Épicerie</title>
<style>
body {font-family: Arial, sans-serif; background: #f5f6fa; padding: 20px;}
.container {max-width: 800px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);}
h1, h2 {text-align: center; color: #007bff;}
table {width: 100%; border-collapse: collapse; margin-top: 20px;}
th, td {border: 1px solid #ccc; padding: 8px; text-align: center;}
th {background: #007bff; color: white;}
.total-box {text-align: right; margin-top: 10px; font-weight: bold;}
a {text-decoration: none; color: #007bff;}
a:hover {text-decoration: underline;}
</style>
</head>
<body>
<div class="container">
    <h1>📦 Détails de la commande #<?= $achat['id'] ?></h1>
    <p><a href="commandes.php">⬅️ Retour aux commandes</a></p>

    <h3>Fournisseur</h3>
    <p><strong>Nom :</strong> <?= htmlspecialchars($achat['fournisseur']) ?><br>
       <strong>Email :</strong> <?= htmlspecialchars($achat['email'] ?? '—') ?><br>
       <strong>Téléphone :</strong> <?= htmlspecialchars($achat['telephone'] ?? '—') ?></p>

    <h3>Informations commande</h3>
    <p><strong>Date :</strong> <?= $achat['date_achat'] ?><br>
       <strong>Total :</strong> <?= number_format($achat['montant_total'], 2, ',', ' ') ?> €</p>

    <h3>Produits achetés</h3>
    <table>
        <tr>
            <th>Produit</th>
            <th>Prix unitaire (€)</th>
            <th>Quantité</th>
            <th>Sous-total (€)</th>
        </tr>
        <?php $total = 0; while ($d = mysqli_fetch_assoc($details)): 
            $sous_total = $d['prix_achat'] * $d['quantite'];
            $total += $sous_total;
        ?>
        <tr>
            <td><?= htmlspecialchars($d['produit']) ?></td>
            <td><?= number_format($d['prix_achat'], 2, ',', ' ') ?></td>
            <td><?= $d['quantite'] ?></td>
            <td><?= number_format($sous_total, 2, ',', ' ') ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div class="total-box">
        Total commande : <?= number_format($total, 2, ',', ' ') ?> €
    </div>
</div>
</body>
</html>
