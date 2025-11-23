<?php
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
    <title>Inscription - Smart Stock</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at 20% 30%, #fffaf5 0%, #fdf0e6 60%, #fff 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Bulles animées */
        .bubble {
            position: absolute;
            border-radius: 50%;
            opacity: 0.3;
            animation: floatUp 10s infinite ease-in-out;
        }
        .bubble:nth-child(1) { width: 100px; height: 100px; background: #fa8c0f; top: 70%; left: 15%; }
        .bubble:nth-child(2) { width: 60px; height: 60px; background: #bc4002; top: 30%; left: 80%; animation-delay: 2s; }
        .bubble:nth-child(3) { width: 110px; height: 110px; background: #ffb366; top: 90%; left: 40%; animation-delay: 4s; }

        @keyframes floatUp {
            0%, 100% { transform: translateY(0); opacity: 0.3; }
            50% { transform: translateY(-20px); opacity: 0.6; }
        }

        /* Formulaire */
        .register-container {
            background: #fffaf5;
            padding: 40px 50px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 400px;
            text-align: center;
            z-index: 2;
            animation: fadeIn 1s ease;
        }

        .register-container h2 {
            color: #fa8c0f;
            margin-bottom: 25px;
            font-size: 2em;
        }

        .register-container input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #f3c7a1;
            border-radius: 30px;
            margin-bottom: 18px;
            font-size: 1em;
            outline: none;
            transition: 0.3s;
        }

        .register-container input:focus {
            border-color: #fa8c0f;
            box-shadow: 0 0 8px rgba(250,140,15,0.4);
        }

        .btn-submit {
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

        .btn-submit:hover {
            background: #bc4002;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(188, 64, 2, 0.5);
        }

        .login-link {
            margin-top: 15px;
            display: block;
            color: #555;
        }

        .login-link a {
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

    <div class="register-container">
        <h2>Créer un compte</h2>
        <form method="POST" action="traitement_register.php">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="email" name="email" placeholder="Adresse e-mail" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" class="btn-submit">S’inscrire</button>
        </form>
        <div class="login-link">
            Déjà inscrit ? <a href="login.php">Connectez-vous</a>
        </div>
    </div>

</body>
</html>
