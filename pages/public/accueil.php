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
            /* Nouvelle image de fond */
            background-image: url('../../assets/images/fond-accueil.png');
            background-size: cover; /* couvre tout l'écran */
            background-position: top center; /* centre l'image */
            background-repeat: no-repeat; /* pas de répétition */
            overflow: hidden;
            height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            padding-top: 80px; /* pour la navbar */
        }

        /* Overlay semi-transparent sur le fond */
        body::before {
            content: '';
            position: absolute;
            top: 0; 
            left: 0;
            width: 100%; 
            height: 100%;
            background-color: rgba(255,255,255,0.3); /* ajuster l'opacité selon besoin */
            z-index: 0; /* derrière tout le contenu */
        }

        /* Petites bulles animées en fond */
        .bubble {
            position: absolute;
            border-radius: 50%;
            opacity: 0.3;
            animation: floatUp 10s infinite ease-in-out;
        }
        .bubble:nth-child(1) { width: 80px; height: 80px; background: #fa8c0f; top: 30%; left: 2%; animation-delay: 0s; }
        .bubble:nth-child(2) { width: 50px; height: 50px; background: #bc4002; top: 50%; left: 90%; animation-delay: 2s; }
        .bubble:nth-child(3) { width: 100px; height: 100px; background: #ffb366; top: 90%; left: 40%; animation-delay: 4s; }

        @keyframes floatUp {
            0% { transform: translateY(0); opacity: 0.3; }
            50% { transform: translateY(-20px); opacity: 0.5; }
            100% { transform: translateY(0); opacity: 0.3; }
        }

        /* Barre du haut */
        /* Barre du haut complète */
header {
    width: 100%;
    background-color: rgba(255, 212, 200, 0.2); /* blanc à 80% d'opacité */
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

.nav-left, .nav-right {
    display: flex;
    align-items: center;
}

.nav-link {
    text-decoration: none;
    color: #222;
    font-weight: 500;
    margin-right: 30px;
    transition: color 0.3s;
}

.nav-link:hover {
    color: #fa8c0f;
}
.logo-navbar {
    width: 60px; /* ajuster selon la taille souhaitée */
    height: auto;
    margin-right: 20px; /* espace entre logo et liens */
}

.logo-link {
    display: flex;
    align-items: center;
    text-decoration: none;
}

/* Garder le bouton Se connecter */
.btn-login {
    background-color: #fa8c0f;
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 30px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.3s ease;
    box-shadow: 0 5px 15px rgba(250, 140, 15, 0.4);
}

.btn-login:hover {
    background-color: #bc4002;
    box-shadow: 0 8px 25px rgba(188, 64, 2, 0.5);
    transform: translateY(-3px);
}

/* Ajustement du body pour ne pas cacher le contenu derrière la navbar */
body {
    padding-top: 80px; /* hauteur approximative de la navbar */
}


        /* Section principale */
        .main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 10%;
            z-index: 2;
        }

        /* Texte à gauche */
       .text-section {
            flex: 1;
            max-width: 520px;
            animation: fadeInLeft 1.2s ease;
            margin-top: 0;
            text-align: center;
        }

        .text-section h1 {
            font-size: 3.5em;
            color: #222;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        .user-info {
        text-align: center;
        margin-top: 10px;
        margin-bottom: 40px;
        z-index: 2;
        color: #333;
        }


        .text-section span {
            color: #fa8c0f;
        }

        .text-section p {
            font-size: 1.3em;
            color: #555;
            margin-bottom: 40px;
            line-height: 1.5;
        }

        .btn-register {
            background-color: #f26906ff;
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 35px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s ease;
            box-shadow: 0 5px 15px rgba(249, 118, 4, 0.4);
            
        }

        .btn-register:hover {
            background-color: #bc4002;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(188, 64, 2, 0.5);
        }

        /* Logo à droite */
        .logo-section {
            flex: 1;
            text-align: right;
            animation: fadeInRight 1.2s ease;
        }

        .logo-section img {
            width: 500px;
            height: auto;
            filter: drop-shadow(0px 10px 25px rgba(0,0,0,0.15));
            animation: floatLogo 4s ease-in-out infinite;
        }

        /* Animations */
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-40px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(40px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes floatLogo {
            0% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 900px) {
            .main {
                flex-direction: column-reverse;
                text-align: center;
                padding-top: 80px;
            }

            .logo-section img {
                width: 280px;
            }

            .text-section h1 {
                font-size: 2.2em;
            }
        }
    </style>
</head>
<body>

    <!-- Bulles décoratives -->
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>

    <!-- Barre du haut -->
    <header>
    <nav class="navbar">
        <div class="nav-left">
            <a href="accueil.php" class="logo-link">
                <img src="../../assets/images/logo_epicerie.png" alt="Logo Smart Stock" class="logo-navbar">
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


    <!-- Contenu principal -->
    <section class="main">
        <div class="text-section">
            <h1>Bienvenue dans <span>Smart Stock</span></h1>
            <p>Votre solution simple et efficace pour gérer le stock et les ventes.</p>
            <a href="../auth/auth.php?action=register" class="btn-register">S'inscrire</a>
        </div>
    </section>

</body>
</html>
