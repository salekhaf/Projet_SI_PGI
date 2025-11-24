<?php
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

include('../../config/db_conn.php'); // $pdo
include('../../includes/role_helper.php');

$nom = $_SESSION['nom'];
$role = $_SESSION['role'];
$id_utilisateur = $_SESSION['id_utilisateur'];

/*-----------------------------------------------
    STATISTIQUES (COMPATIBLES POSTGRESQL)
------------------------------------------------*/

// ventes du jour
$sql = "SELECT SUM(total) AS total 
        FROM ventes 
        WHERE DATE(date_vente) = CURRENT_DATE";
$total_jour = ($pdo->query($sql)->fetch()['total']) ?? 0;

// ventes semaine (PostgreSQL)
$sql = "SELECT SUM(total) AS total
        FROM ventes
        WHERE date_vente >= date_trunc('week', CURRENT_DATE)";
$total_semaine = ($pdo->query($sql)->fetch()['total']) ?? 0;

// ventes mois
$sql = "SELECT SUM(total) AS total
        FROM ventes
        WHERE EXTRACT(MONTH FROM date_vente) = EXTRACT(MONTH FROM CURRENT_DATE)
        AND EXTRACT(YEAR FROM date_vente) = EXTRACT(YEAR FROM CURRENT_DATE)";
$total_mois = ($pdo->query($sql)->fetch()['total']) ?? 0;

// total ventes
$sql = "SELECT SUM(total) AS total FROM ventes";
$total_ventes = ($pdo->query($sql)->fetch()['total']) ?? 0;

// nombre de produits
$sql = "SELECT COUNT(*) AS total FROM produits";
$total_produits = ($pdo->query($sql)->fetch()['total']) ?? 0;

// valeur totale stock
$sql = "SELECT SUM(quantite_stock * prix_achat) AS total FROM produits";
$valeur_stock = ($pdo->query($sql)->fetch()['total']) ?? 0;

// stock bas
$sql = "SELECT COUNT(*) AS total FROM produits WHERE quantite_stock < 10 AND quantite_stock > 0";
$nb_stock_bas = ($pdo->query($sql)->fetch()['total']) ?? 0;

// stock critique
$sql = "SELECT COUNT(*) AS total FROM produits WHERE quantite_stock <= 0";
$nb_stock_critique = ($pdo->query($sql)->fetch()['total']) ?? 0;

// liste des alertes
$sql = "SELECT id, nom, quantite_stock 
        FROM produits 
        WHERE quantite_stock < 10 
        ORDER BY quantite_stock ASC 
        LIMIT 10";
$produits_alertes = $pdo->query($sql)->fetchAll();

// top ventes
$sql = "SELECT p.nom, SUM(dv.quantite) AS total_vendu
        FROM details_vente dv
        JOIN produits p ON dv.id_produit = p.id
        GROUP BY p.nom
        ORDER BY total_vendu DESC
        LIMIT 5";
$top_produits = $pdo->query($sql)->fetchAll();

// nombre de clients
$sql = "SELECT COUNT(*) AS total FROM clients";
$total_clients = ($pdo->query($sql)->fetch()['total']) ?? 0;

// ventes récentes
$sql = "SELECT v.id, v.date_vente, v.total, c.nom AS client_nom
        FROM ventes v
        LEFT JOIN clients c ON v.id_client = c.id
        ORDER BY date_vente DESC
        LIMIT 5";
$ventes_recentes = $pdo->query($sql)->fetchAll();

// données 7 jours
$sql = "SELECT DATE(date_vente) AS date, SUM(total) AS total
        FROM ventes
        WHERE date_vente >= CURRENT_DATE - INTERVAL '7 days'
        GROUP BY DATE(date_vente)
        ORDER BY date ASC";
$data = $pdo->query($sql)->fetchAll();

$labels_graph = [];
$data_graph = [];

foreach ($data as $row) {
    $labels_graph[] = date("d/m", strtotime($row['date']));
    $data_graph[] = floatval($row['total']);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Tableau de bord - Smart Stock</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../../assets/css/styles_connected.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<header>
    <nav class="navbar">
        <div class="nav-left">
            <a href="index.php" class="logo-link"><img src="../../assets/images/logo_epicerie.png" class="logo-navbar"></a>
            <a href="index.php" class="nav-link">Tableau de bord</a>
            <a href="../stock/stock.php" class="nav-link">Stock</a>
            <a href="../ventes/ventes.php" class="nav-link">Ventes</a>
            <a href="../clients/clients.php" class="nav-link">Clients</a>
            <a href="../commandes/commandes.php" class="nav-link">Commandes</a>
            <a href="../stock/categories.php" class="nav-link">Catégories</a>
        </div>
        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="main-container">
    <div class="content-wrapper">
        <h2>Bienvenue, <?= htmlspecialchars($nom) ?> 👋</h2>

        <!-- STATISTIQUES -->
        <div class="stats-grid">
            <div class="stat-card"><h3>💰 Ventes du jour</h3><div class="value"><?= number_format($total_jour,2,',',' ') ?> €</div></div>
            <div class="stat-card"><h3>📅 Ventes semaine</h3><div class="value"><?= number_format($total_semaine,2,',',' ') ?> €</div></div>
            <div class="stat-card"><h3>📊 Ventes du mois</h3><div class="value"><?= number_format($total_mois,2,',',' ') ?> €</div></div>
            <div class="stat-card"><h3>💵 Total ventes</h3><div class="value"><?= number_format($total_ventes,2,',',' ') ?> €</div></div>
            <div class="stat-card"><h3>📦 Produits</h3><div class="value"><?= $total_produits ?></div></div>
            <div class="stat-card"><h3>💎 Valeur du stock</h3><div class="value"><?= number_format($valeur_stock,2,',',' ') ?> €</div></div>
            <div class="stat-card alertes"><h3>⚠️ Stock bas</h3><div class="value"><?= $nb_stock_bas ?></div></div>
            <div class="stat-card alertes"><h3>🚨 Stock critique</h3><div class="value"><?= $nb_stock_critique ?></div></div>
            <div class="stat-card"><h3>👥 Clients</h3><div class="value"><?= $total_clients ?></div></div>
        </div>

        <!-- ALERTES -->
        <?php if (!empty($produits_alertes)): ?>
        <div class="alertes-box">
            <h3>⚠️ Alertes de stock</h3>
            <?php foreach ($produits_alertes as $prod): ?>
                <div class="alerte-item <?= $prod['quantite_stock'] <= 0 ? 'critique' : 'bas' ?>">
                    <span><strong><?= htmlspecialchars($prod['nom']) ?></strong></span>
                    <span>Stock: <?= $prod['quantite_stock'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- GRAPH -->
        <div class="chart-box">
            <h3>📈 Ventes 7 jours</h3>
            <canvas id="ventesChart"></canvas>
        </div>

        <script>
            new Chart(document.getElementById('ventesChart'), {
                type: 'line',
                data: {
                    labels: <?= json_encode($labels_graph) ?>,
                    datasets: [{
                        label: "Ventes (€)",
                        data: <?= json_encode($data_graph) ?>,
                        borderColor: "rgb(0,150,80)",
                        backgroundColor: "rgba(0,150,80,0.2)",
                        fill: true,
                        tension: 0.4
                    }]
                }
            });
        </script>

        <!-- VENTES RÉCENTES -->
        <div class="chart-box">
            <h3>📋 Ventes récentes</h3>
            <table>
                <thead><tr><th>Date</th><th>Client</th><th>Montant</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($ventes_recentes as $vente): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($vente['date_vente'])) ?></td>
                    <td><?= htmlspecialchars($vente['client_nom'] ?? 'Client anonyme') ?></td>
                    <td><?= number_format($vente['total'],2,',',' ') ?> €</td>
                    <td><a href="../ventes/detailVente.php?id=<?= $vente['id'] ?>" class="btn btn-sm">Voir</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>
