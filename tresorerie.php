<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: auth.php");
    exit();
}

include('db_conn.php');

$role = $_SESSION['role'];
if (!in_array($role, ['admin', 'tresorier'])) {
    header("Location: index.php");
    exit();
}

// Total recettes (ventes)
$sql_recettes = "SELECT SUM(total) AS recettes FROM ventes";
$res_recettes = mysqli_query($conn, $sql_recettes);
$recettes = mysqli_fetch_assoc($res_recettes)['recettes'] ?? 0;

// Total achats (dépenses)
$sql_achats = "SELECT SUM(montant_total) AS achats FROM achats";
$res_achats = mysqli_query($conn, $sql_achats);
$achats = mysqli_fetch_assoc($res_achats)['achats'] ?? 0;

// Chiffre d'affaires (recettes - achats)
$ca = $recettes - $achats;

// Liste des factures (ventes)
$factures = mysqli_query($conn, "
    SELECT id, date_vente AS date, total, 'Vente' AS type FROM ventes
    UNION
    SELECT id, date_achat AS date, montant_total AS total, 'Achat' AS type FROM achats
    ORDER BY date DESC
");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>💰 Trésorerie - PGI Épicerie</title>
<style>
body {font-family: Arial, sans-serif; background: #f5f6fa; padding: 20px;}
.container {max-width: 900px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);}
h1 {text-align: center; color: #007bff;}
.stats {display: flex; justify-content: space-around; margin-top: 20px;}
.card {background: #e9f7ef; padding: 15px; border-radius: 8px; text-align: center; width: 28%; box-shadow: 0 2px 6px rgba(0,0,0,0.1);}
.card h2 {color: #007bff;}
table {width: 100%; border-collapse: collapse; margin-top: 25px;}
th, td {border: 1px solid #ccc; padding: 8px; text-align: center;}
th {background: #007bff; color: white;}
</style>
</head>
<body>
<div class="container">
    <h1>💰 Tableau de bord Trésorerie</h1>
    <p><a href="index.php">⬅️ Retour</a></p>

    <div class="stats">
        <div class="card"><h3>Recettes</h3><h2><?= number_format($recettes, 2, ',', ' ') ?> €</h2></div>
        <div class="card"><h3>Dépenses</h3><h2><?= number_format($achats, 2, ',', ' ') ?> €</h2></div>
        <div class="card"><h3>Résultat (CA)</h3><h2><?= number_format($ca, 2, ',', ' ') ?> €</h2></div>
    </div>

    <h3>📄 Historique des opérations</h3>
    <table>
    <tr><th>Date</th><th>Type</th><th>Montant (€)</th></tr>
    <?php while ($f = mysqli_fetch_assoc($factures)): ?>
    <?php 
        $couleur = ($f['type'] === 'Vente') ? 'green' : 'red';
        $signe = ($f['type'] === 'Vente') ? '+' : '-';
    ?>
    <tr>
        <td><?= $f['date'] ?></td>
        <td><?= $f['type'] ?></td>
        <td style="color: <?= $couleur ?>; font-weight: bold;">
            <?= $signe . number_format($f['total'], 2, ',', ' ') ?> €
        </td>
    </tr>
    <?php endwhile; ?>
</table>
</div>
</body>
</html>
