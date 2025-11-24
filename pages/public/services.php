<?php
session_start();
$nom = $_SESSION['nom'] ?? null;
$role = $_SESSION['role'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🌟 Nos services - Smart Stock</title>

<style>
/* === STYLE GLOBAL === */
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background-image: url('../../assets/images/fond-auth.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    color: #333;
    padding-top: 130px;
}

/* === Navbar === */
header {
    width: 100%;
    background-color: rgba(246,157,94,0.5);
    position: fixed;
    top: 0;
    left: 0;
    z-index: 10;
}
.navbar {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 40px;
}
.nav-left {
    display: flex;
    align-items: center;
    gap: 25px;
}
.nav-link {
    text-decoration: none;
    color: #222;
    font-weight: 500;
    transition: 0.3s;
}
.nav-link:hover { color: #fa8c0f; }

.logo-navbar {
    width: 60px;
    height: auto;
}

/* === Contenu === */
.container {
    max-width: 1100px;
    margin: 60px auto;
    background: transparent;
    padding: 40px;
}

h1, h2, h3 {
    text-align: center;
    color: #271958;
    margin-bottom: 15px;
}

p {
    line-height: 1.7;
    font-size: 1.05em;
    color: #011118;
}

/* === Cards === */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px,1fr));
    gap: 25px;
    margin: 40px 0;
}

.card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(250,140,15,0.3);
}

.card .icon {
    font-size: 2.5em;
    color: #fa8c0f;
    margin-bottom: 10px;
}

/* === Section finale === */
.final-section {
    background: linear-gradient(135deg,#ad9ee6,rgb(104,94,161));
    color: white;
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.final-section h3 {
    color: #ffb366;
    margin-bottom: 10px;
}
</style>

</head>
<body>

<header>
    <nav class="navbar">
        <div class="nav-left">
            <a href="../../index.php" class="logo-link">
                <img src="../../assets/images/logo_epicerie.png" class="logo-navbar">
            </a>
            <a href="accueil.php" class="nav-link">Accueil</a>
            <a href="services.php" class="nav-link">Nos services</a>
            <a href="#about" class="nav-link">À propos</a>
            <a href="#contact" class="nav-link">Nous contacter</a>
        </div>
    </nav>
</header>

<div class="container">
    <h1>🌟 Organisez votre gestion commerciale avec Smart Stock</h1>
    <p style="text-align:center;">
        Une solution complète et intuitive conçue pour les commerces de proximité.
    </p>

    <div class="cards">
        <div class="card">
            <div class="icon">📦</div>
            <h3>Gestion du stock</h3>
            <p>Surveillez vos produits en temps réel et recevez des alertes de rupture.</p>
        </div>
        <div class="card">
            <div class="icon">💰</div>
            <h3>Gestion des ventes</h3>
            <p>Enregistrez chaque vente et suivez automatiquement l’évolution du stock.</p>
        </div>
        <div class="card">
            <div class="icon">👥</div>
            <h3>Gestion des clients</h3>
            <p>Centralisez les informations clients et leur historique d'achat.</p>
        </div>
        <div class="card">
            <div class="icon">🚚</div>
            <h3>Gestion des fournisseurs</h3>
            <p>Gérez vos partenaires et vos commandes fournisseurs simplement.</p>
        </div>
    </div>

    <div class="final-section">
        <h2>⚡ Ce que Smart Stock vous permet</h2>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin:30px 0;">
            <div>
                <h3>📈 Augmentez votre chiffre d'affaires</h3>
                <p>Statistiques, analyses et automatisation pour booster vos performances.</p>
            </div>
            <div>
                <h3>👥 Travaillez mieux en équipe</h3>
                <p>Rôles, permissions, historique complet. Gestion avancée des accès.</p>
            </div>
        </div>

        <h3>Avec Smart Stock, pilotez votre activité en toute sérénité 🚀</h3>
    </div>

</div>

</body>
</html>
