<?php
// auth.php
include('../../config/db_conn.php');
session_start();

$message = "";

// Fonction de sécurité pour afficher les messages
function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// -------------------- INSCRIPTION --------------------
if (isset($_POST['register'])) {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motdepasse = $_POST['motdepasse'] ?? '';

    if ($nom === '' || $email === '' || $motdepasse === '') {
        $message = "⚠️ Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "⚠️ Email invalide.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM utilisateurs WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if ($exists) {
            $message = "⚠️ Cet email est déjà utilisé.";
        } else {
            $motdepasse_hache = password_hash($motdepasse, PASSWORD_DEFAULT);
            $role = 'vendeur';
            $stmt = mysqli_prepare($conn, "INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $nom, $email, $motdepasse_hache, $role);
            if (mysqli_stmt_execute($stmt)) {
                $message = "✅ Inscription réussie ! Vous pouvez vous connecter.";
            } else {
                $message = "❌ Erreur lors de l'inscription : " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// -------------------- CONNEXION --------------------
if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $motdepasse = $_POST['motdepasse'] ?? '';

    if ($email === '' || $motdepasse === '') {
        $message = "⚠️ Veuillez renseigner tous les champs.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, nom, mot_de_passe, role FROM utilisateurs WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($motdepasse, $user['mot_de_passe'])) {
                $_SESSION['id_utilisateur'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['role'] = $user['role'];
                header("Location: ../dashboard/index.php");
                exit();
            } else {
                $message = "❌ Mot de passe incorrect.";
            }
        } else {
            $message = "⚠️ Aucun compte trouvé avec cet email.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Smart Stock - Connexion / Inscription</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background-image: url('../../assets/images/fond-auth.png');
        background-size: cover; /* ajuste pour remplir l'écran */
        background-color: #fffaf5; /* couleur de fond autour de l'image */
        background-position: center; /* centre l'image */
        background-repeat: no-repeat;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    /* Barre du haut */
        header {
            width: 100%;
            background-color: rgba(255, 212, 200, 0.9); /* couleur semi-transparente */
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

        .nav-left {
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
            width: 60px; /* taille du logo */
            height: auto;
            margin-right: 20px;
        }

        .logo-link {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        /* Décale le contenu vers le bas pour ne pas être masqué par la navbar */
        body {
            padding-top: 90px; 
        }


        /* Bulles en fond */
        .bubble {
            position: absolute;
            border-radius: 50%;
            opacity: 0.3;
            animation: floatUp 10s infinite ease-in-out;
        }
        .bubble:nth-child(1) { width: 100px; height: 100px; background: #fa8c0f; top: 30%; left: 10%; }
        .bubble:nth-child(2) { width: 60px; height: 60px; background: #bc4002; top: 30%; left: 80%; animation-delay: 2s; }
        .bubble:nth-child(3) { width: 120px; height: 120px; background: #ffb366; top: 90%; left: 40%; animation-delay: 4s; }

        @keyframes floatUp {
            0%, 100% { transform: translateY(0); opacity: 0.3; }
            50% { transform: translateY(-20px); opacity: 0.6; }
        }

        /* Conteneur principal */
        .auth-container {
            background: #fffaf5;
            padding: 40px 50px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 380px;
            text-align: center;
            z-index: 2;
            animation: fadeIn 1s ease;
        }

        h2 {
            color: #fa8c0f;
            margin-bottom: 25px;
            font-size: 2em;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #f3c7a1;
            border-radius: 30px;
            margin-bottom: 18px;
            font-size: 1em;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: #fa8c0f;
            box-shadow: 0 0 8px rgba(250,140,15,0.4);
        }

        button {
            background: #fa8c0f;
            color: white;
            border: none;
            border-radius: 30px;
            padding: 12px 35px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(250, 140, 15, 0.4);
        }

        button:hover {
            background: #bc4002;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(188, 64, 2, 0.5);
        }

        .message {
            background: rgba(255,255,255,0.7);
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            color: #333;
            font-size: 0.95em;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }

        .toggle {
            margin-top: 10px;
            color: #555;
        }

        .toggle a {
            color: #fa8c0f;
            text-decoration: none;
            font-weight: 600;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        
    </style>
</head>
<body>

    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>


    <div class="auth-container">
        <?php if (!empty($message)): ?>
            <div class="message"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if (!isset($_GET['action']) || $_GET['action'] === 'login'): ?>
            <h2>Connexion</h2>
            <form method="POST" action="">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="motdepasse" placeholder="Mot de passe" required>
                <button type="submit" name="login">Se connecter</button>
            </form>
            <div class="toggle">
                Pas encore de compte ? <a href="?action=register">S’inscrire</a>
            </div>
        <?php else: ?>
            <h2>Inscription</h2>
            <form method="POST" action="">
                <input type="text" name="nom" placeholder="Nom complet" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="motdepasse" placeholder="Mot de passe" minlength="6" required>
                <button type="submit" name="register">S’inscrire</button>
            </form>
            <div class="toggle">
                Déjà un compte ? <a href="?action=login">Se connecter</a>
            </div>
        <?php endif; ?>
    </div>

</body>
<header>
    <nav class="navbar">
        <div class="nav-left">
            <a href="../public/accueil.php" class="logo-link">
                <img src="../../assets/images/logo_epicerie.png" alt="Logo Smart Stock" class="logo-navbar">
            </a>
            <a href="../public/accueil.php" class="nav-link">Accueil</a>
            <a href="../public/services.php" class="nav-link">Nos services</a>
            <a href="#about" class="nav-link">À propos</a>
            <a href="#contact" class="nav-link">Nous contacter</a>
        </div>
    </nav>
</header>
</html>
