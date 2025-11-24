<?php
// auth.php (VERSION PDO)
require_once('../../config/db_conn.php'); // fournit $pdo
session_start();

$message = "";

// Fonction sécurisée pour afficher du texte
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

        // Vérification email déjà existant
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $message = "⚠️ Cet email est déjà utilisé.";
        } else {
            $motdepasse_hache = password_hash($motdepasse, PASSWORD_DEFAULT);
            $role = 'vendeur';

            $stmt = $pdo->prepare("
                INSERT INTO utilisateurs (nom, email, mot_de_passe, role)
                VALUES (:nom, :email, :mdp, :role)
            ");

            if ($stmt->execute([
                ':nom'  => $nom,
                ':email'=> $email,
                ':mdp'  => $motdepasse_hache,
                ':role' => $role
            ])) {
                $message = "✅ Inscription réussie ! Vous pouvez vous connecter.";
            } else {
                $message = "❌ Erreur lors de l'inscription.";
            }
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
        $stmt = $pdo->prepare("
            SELECT id, nom, mot_de_passe, role
            FROM utilisateurs
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
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
        /* ==== STYLE D’ORIGINE CONSERVÉ ==== */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-image: url('../../assets/images/fond-auth.png');
            background-size: cover;
            background-color: #fffaf5;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding-top: 90px;
        }

        header {
            width: 100%;
            background-color: rgba(255, 212, 200, 0.9);
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
        .nav-left { display: flex; align-items: center; }
        .nav-link {
            text-decoration: none;
            color: #222;
            font-weight: 500;
            margin-right: 30px;
            transition: color 0.3s;
        }
        .nav-link:hover { color: #fa8c0f; }

        .logo-navbar { width: 60px; margin-right: 20px; }

        .auth-container {
            background: #fffaf5;
            padding: 40px 50px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 380px;
            text-align: center;
        }

        h2 { color: #fa8c0f; margin-bottom: 25px; font-size: 2em; }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #f3c7a1;
            border-radius: 30px;
            margin-bottom: 18px;
            font-size: 1em;
        }

        button {
            background: #fa8c0f;
            color: white;
            border: none;
            border-radius: 30px;
            padding: 12px 35px;
            font-weight: bold;
            cursor: pointer;
        }

        .message {
            background: rgba(255,255,255,0.7);
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: .95em;
        }
    </style>
</head>
<body>

    <div class="auth-container">

        <?php if (!empty($message)): ?>
            <div class="message"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if (!isset($_GET['action']) || $_GET['action'] === 'login'): ?>
            
            <h2>Connexion</h2>
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="motdepasse" placeholder="Mot de passe" required>
                <button type="submit" name="login">Se connecter</button>
            </form>

            <div class="toggle">
                Pas encore de compte ? <a href="?action=register">S’inscrire</a>
            </div>

        <?php else: ?>

            <h2>Inscription</h2>
            <form method="POST">
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
</html>
