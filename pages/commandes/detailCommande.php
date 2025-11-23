    <?php
    session_start();
    if (!isset($_SESSION['id_utilisateur'])) {
        header("Location: ../auth/auth.php");
        exit();
    }

    include('../../config/db_conn.php');

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
    $res = $conn->query($sql);
    $achat = (is_object($res) && method_exists($res, 'fetch_assoc') ? $res->fetch_assoc() : mysqli_fetch_assoc($res));

    if (!$achat) {
        die("❌ Commande introuvable.");
    }

    // Récupération des produits achetés
    $details = $conn->query("
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
    <title>📦 Détails commande #<?= $id_achat ?> - Smart Stock</title>
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
        <h1>📦 Détails de la commande #<?= $achat['id'] ?></h1>
            
            <p style="margin-bottom: 25px;">
                <a href="commandes.php" class="btn btn-secondary">⬅️ Retour aux commandes</a>
            </p>

            <div class="card" style="margin-bottom: 25px;">
        <h3>Fournisseur</h3>
        <p><strong>Nom :</strong> <?= htmlspecialchars($achat['fournisseur']) ?><br>
        <strong>Email :</strong> <?= htmlspecialchars($achat['email'] ?? '—') ?><br>
        <strong>Téléphone :</strong> <?= htmlspecialchars($achat['telephone'] ?? '—') ?></p>
            </div>

            <div class="card" style="margin-bottom: 25px;">
        <h3>Informations commande</h3>
                <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($achat['date_achat'])) ?><br>
                <strong>Total :</strong> <span style="font-weight: bold; color: var(--primary-color); font-size: 1.2em;">
                    <?= number_format($achat['montant_total'], 2, ',', ' ') ?> €
                </span></p>
            </div>

        <h3>Produits achetés</h3>
        <table>
                <thead>
            <tr>
                <th>Produit</th>
                <th>Prix unitaire (€)</th>
                <th>Quantité</th>
                <th>Sous-total (€)</th>
            </tr>
                </thead>
                <tbody>
            <?php $total = 0; while ($d = (is_object($details) && method_exists($details, 'fetch_assoc') ? $details->fetch_assoc() : mysqli_fetch_assoc($details))): 
                $sous_total = $d['prix_achat'] * $d['quantite'];
                $total += $sous_total;
            ?>
            <tr>
                <td><?= htmlspecialchars($d['produit']) ?></td>
                <td><?= number_format($d['prix_achat'], 2, ',', ' ') ?></td>
                <td><?= $d['quantite'] ?></td>
                        <td style="font-weight: bold; color: var(--primary-color);">
                            <?= number_format($sous_total, 2, ',', ' ') ?> €
                        </td>
            </tr>
            <?php endwhile; ?>
                </tbody>
        </table>

            <div style="text-align: right; font-weight: bold; font-size: 1.3em; margin-top: 20px; color: var(--primary-color);">
                Total commande : <strong><?= number_format($total, 2, ',', ' ') ?> €</strong>
            </div>
        </div>
    </div>
    </body>
    </html>
