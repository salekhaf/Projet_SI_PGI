<?php
// index.php — Tableau de bord principal du PGI Épicerie
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: auth.php");
    exit();
}

$nom = $_SESSION['nom'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - PGI Épicerie</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>

<header>
    🏪 PGI Épicerie – Tableau de bord
</header>

<div class="content">
    <div class="user-info">
        <h2>Bienvenue, <?php echo htmlspecialchars($nom); ?> 👋</h2>
        <p>Rôle : <strong><?php echo htmlspecialchars($role); ?></strong></p>
        <a href="logout.php" class="logout">🚪 Se déconnecter</a>
    </div>

    <h3 style="text-align:center;">Navigation principale</h3>

    <div class="menu">
        <a href="stock.php">🧃 Gérer le stock</a>
        <a href="ventes.php">💰 Gérer les ventes</a>
        <a href="clients.php">👥 Gérer les clients</a>
        <a href="fournisseurs.php">🚚 Gérer les fournisseurs</a>
        <?php if ($role === 'admin'): ?>
            <a href="utilisateurs.php">👨‍💼 Gérer les utilisateurs</a>
        <?php endif; ?>
        <?php if ($role === 'admin' || $role === 'responsable_approvisionnement'): ?>
            <a href="commandes.php">📦 Gérer les commandes</a>
        <?php endif; ?>
        <?php if ($role === 'admin' || $role === 'tresorier'): ?>
            <a href="tresorerie.php">💰 Trésorerie</a>
        <?php endif; ?>
    </div>
</div>


</body>
</html>
