<?php
session_start();
$nom = isset($_SESSION['nom']) ? $_SESSION['nom'] : null;
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
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
    padding-top: 130px; /* espace sous la navbar */
}

/* === Navbar === */
header {
    width: 100%;
    background-color: rgba(246, 157, 94, 0.5);
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
    transition: color 0.3s;
}
.nav-link:hover { color: #fa8c0f; }
.logo-navbar { width: 60px; height: auto; }
.logo-link { display: flex; align-items: center; }
.logout {
    background: #ce2204ff;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    box-shadow: 0 5px 15px rgba(13,8,2,0.4);
    transition: 0.3s;
}
.logout:hover {
    background: #2d1a65ff;
    transform: translateY(-2px);
}

/* === Contenu principal === */
.container {
    max-width: 1100px;
    margin: 60px auto;
    background: transparent;
    border-radius: 0;
    padding: 40px;
    box-shadow: none;
    /*animation: fadeIn 0.8s ease;*/
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

h1, h2, h3 {
    text-align: center;
    color: #271958ff;
    margin-bottom: 15px;
}

p {
    line-height: 1.7;
    font-size: 1.05em;
    color: #011118ff;
}

/* === Cards === */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin: 40px 0;
}
.card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    padding: 25px;
    text-align: center;
    transition: all 0.3s ease;
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
.card h3 {
    color: #224d86ff;
    margin-bottom: 10px;
}

/* === Section finale === */
.final-section {
    background: linear-gradient(135deg, #ad9ee6ff,rgb(104, 94, 161));
    color: white;
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.final-section h3 { color: #ffb366; margin-bottom: 10px; }

</style>
</head>
<body>

<header>
    <nav class="navbar">
        <div class="nav-left">
            <a href="../../index.php" class="logo-link"><img src="../../assets/images/logo_epicerie.png" class="logo-navbar" alt="Logo"></a>
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
        Une solution complète et intuitive conçue pour les épiceries, les magasins et les commerces de proximité.
    </p>

    <div class="cards">
        <div class="card">
            <div class="icon">📦</div>
            <h3>Gestion du stock</h3>
            <p>Surveillez vos produits en temps réel, ajustez vos niveaux et recevez des alertes de rupture pour garder un inventaire toujours à jour.</p>
        </div>
        <div class="card">
            <div class="icon">💰</div>
            <h3>Gestion des ventes</h3>
            <p>Enregistrez vos ventes rapidement. Les stocks se mettent automatiquement à jour et chaque transaction reste consultable.</p>
        </div>
        <div class="card">
            <div class="icon">👥</div>
            <h3>Gestion des clients</h3>
            <p>Regroupez les informations clients et suivez leur historique d'achats pour mieux personnaliser votre service.</p>
        </div>
        <div class="card">
            <div class="icon">🚚</div>
            <h3>Gestion des fournisseurs</h3>
            <p>Gérez vos partenaires, leurs coordonnées et vos commandes fournisseurs depuis un espace centralisé.</p>
        </div>
    </div>

    <div class="final-section">
        <h2>⚡ Ce que Smart Stock vous permet</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
            <div>
                <h3 style="color: #ffb366; margin-bottom: 10px;">📈 Augmentez votre chiffre d'affaires</h3>
                <p>Tout a été pensé pour vous faire gagner du temps, réduire les erreurs et booster vos ventes. Statistiques en temps réel et graphiques pour prendre les bonnes décisions.</p>
            </div>
            <div>
                <h3 style="color: #ffb366; margin-bottom: 10px;">👥 Travaillez mieux en équipe</h3>
                <p>Système de rôles et permissions avancé. Gestion des demandes d'accès pour une collaboration optimale. Historique complet de toutes les actions.</p>
            </div>
        </div>
        <h3 style="margin-top: 30px;">Avec Smart Stock, pilotez votre activité en toute sérénité 🚀</h3>
    </div>
</div>

</body>
</html>
