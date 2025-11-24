<?php
// accueil.php — page d'accueil de Smart Stock
session_start();

if (isset($_SESSION['id_utilisateur'])) {
    header("Location: ../dashboard/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Stock - Bienvenue</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background-image: url('../../assets/images/fond-accueil.png');
            background-size: cover;
            background-position: top center;
            background-repeat: no-repeat;
            overflow: hidden;
            height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            padding-top: 80px;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0; 
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255,255,255,0.3);
            z-index: 0;
        }

        .bubble {
            position: absolute;
            border-radius: 50%;
            opacity: 0.3;
            animation: floatUp 10s infinite ease-in-out;
        }
        .bubble:nth-child(1) { width: 80px; height: 80px; background:#fa8c0f; top:30%; left:2%; animation-delay:0s; }
        .bubble:nth-child(2) { width: 50px; height:50px; background:#bc4002; top:50%; left:90%; animation-delay:2s; }
        .bubble:nth-child(3) { width:100px; height:100px; background:#ffb366; top:90%; left:40%; animation-delay:4s; }

        @keyframes floatUp {
            0% { transform: translateY(0); opacity:0.3; }
            50% { transform: translateY(-20px); opacity:0.5; }
            100% { transform: translateY(0); opacity:0.3; }
        }

        header {
            width: 100%;
            background-color: rgba(255, 212, 200, 0.2);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        .nav-link {
            text-decoration: none;
            color: #222;
            font-weight: 500;
            margin-right: 30px;
            transition: 0.3s;
        }
        .nav-link:hover { color: #fa8c0f; }

        .logo-navbar {
            width: 60px;
            height: auto;
            margin-right: 20px;
        }

        .btn-login {
            background-color: #fa8c0f;
            color: white;
            padding: 12px 28px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(250,140,15,0.4);
            transition: 0.3s;
        }
        .btn-login:hover {
            background-color: #bc4002;
            transform: translateY(-3px);
        }

        .main {
            flex: 1;
            display:flex;
            justify-content:center;
            align-items:center;
            z-index:2;
            padding:0 10%;
        }

        .text-section {
            text-align:center;
            max-width:520px;
            animation: fadeInLeft 1.2s ease;
        }

        .text-section h1 {
            font-size:3.5em;
            color:#222;
            margin-bottom:20px;
        }
        .text-section h1 span { color:#fa8c0f; }

        .btn-register {
            background:#f26906;
            color:white;
            padding:14px 35px;
            border-radius:35px;
            font-weight:bold;
            text-decoration:none;
            box-shadow:0 5px 15px rgba(249,118,4,0.4);
        }
        .btn-register:hover {
            background:#bc4002;
            transform:translateY(-3px);
        }
    </style>
</head>

<body>

    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>

    <header>
        <nav class="navbar">
            <div class="nav-left">
                <a href="accueil.php">
                    <img src="../../assets/images/logo_epicerie.png" class="logo-navbar">
                </a>
                <a href="accueil.php" class="nav-link">Accueil</a>
                <a href="services.php" class="nav-link">Nos services</a>
                <a href="#about" class="nav-link">À propos</a>
                <a href="#contact" class="nav-link">Nous contacter</a>
            </div>
            <div class="nav-right">
                <a href="../auth/auth.php?action=login" class="btn-login">Se connecter</a>
            </div>
        </nav>
    </header>

    <section class="main">
        <div class="text-section">
            <h1>Bienvenue dans <span>Smart Stock</span></h1>
            <p>Votre solution simple et efficace pour gérer le stock et les ventes.</p>
            <a href="../auth/auth.php?action=register" class="btn-register">S'inscrire</a>
        </div>
    </section>

</body>
</html>
