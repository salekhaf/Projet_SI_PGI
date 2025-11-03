<?php
// auth.php
// Page unique pour inscription / connexion
// - Utilise db_conn.php (doit définir $conn avec mysqli_connect)
// - Migre automatiquement les anciens MD5 -> password_hash() au 1er login réussi

include('db_conn.php');
session_start();

$message = "";

// Helper pour afficher proprement le message (sécurisé)
function e($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// -------------------- INSCRIPTION --------------------
if (isset($_POST['register'])) {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motdepasse = $_POST['motdepasse'] ?? '';

    // Vérifications basiques
    if ($nom === '' || $email === '' || $motdepasse === '') {
        $message = "⚠️ Veuillez remplir tous les champs de l'inscription.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "⚠️ Email invalide.";
    } else {
        // Vérifier si email existe déjà (requête préparée)
        $stmt = mysqli_prepare($conn, "SELECT id FROM utilisateurs WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if ($exists) {
            $message = "⚠️ Cet email est déjà utilisé.";
        } else {
            // Hachage sécurisé du mot de passe
            $motdepasse_hache = password_hash($motdepasse, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn, "INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
            $role = 'vendeur'; // rôle par défaut pour inscription publique
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
        $message = "⚠️ Veuillez renseigner l'email et le mot de passe.";
    } else {
        // Récupérer l'utilisateur par email (requête préparée)
        $stmt = mysqli_prepare($conn, "SELECT id, nom, mot_de_passe, role FROM utilisateurs WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        // Utiliser get_result si disponible pour simplicité
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            $id = $user['id'];
            $nom = $user['nom'];
            $hash_db = $user['mot_de_passe'];
            $role = $user['role'];
            $login_ok = false;

            // 1) Si hash moderne (password_hash) -> commence souvent par '$'
            if (is_string($hash_db) && strlen($hash_db) > 0 && $hash_db[0] === '$') {
                if (password_verify($motdepasse, $hash_db)) {
                    $login_ok = true;

                    // Optionnel : rehash si l'algorithme/coût a changé
                    if (password_needs_rehash($hash_db, PASSWORD_DEFAULT)) {
                        $new_hash = password_hash($motdepasse, PASSWORD_DEFAULT);
                        $stmt_up = mysqli_prepare($conn, "UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
                        mysqli_stmt_bind_param($stmt_up, "si", $new_hash, $id);
                        mysqli_stmt_execute($stmt_up);
                        mysqli_stmt_close($stmt_up);
                    }
                }
            } else {
                // 2) Possibilité d'ancien hash MD5 (32 hex chars)
                if (is_string($hash_db) && strlen($hash_db) === 32 && ctype_xdigit($hash_db)) {
                    if (md5($motdepasse) === $hash_db) {
                        $login_ok = true;

                        // Migration : remplacer MD5 par password_hash()
                        $new_hash = password_hash($motdepasse, PASSWORD_DEFAULT);
                        $stmt_up = mysqli_prepare($conn, "UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
                        mysqli_stmt_bind_param($stmt_up, "si", $new_hash, $id);
                        mysqli_stmt_execute($stmt_up);
                        mysqli_stmt_close($stmt_up);
                    }
                } else {
                    // Format inconnu -> sécurité : ne pas autoriser
                    $login_ok = false;
                }
            }

            if ($login_ok) {
                // Création de la session
                $_SESSION['id_utilisateur'] = $id;
                $_SESSION['nom'] = $nom;
                $_SESSION['role'] = $role;

                // Redirection vers le tableau de bord
                header("Location: index.php");
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
    <title>Connexion / Inscription - PGI Épicerie</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>

<div class="wrap">
    <?php if (!empty($message)): ?>
        <div class="message"><?php echo e($message); ?></div>
    <?php endif; ?>

    <?php if (!isset($_GET['action']) || $_GET['action'] === 'login'): ?>
        <h2>Connexion</h2>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="motdepasse" placeholder="Mot de passe" required>
            <button type="submit" name="login">Se connecter</button>
        </form>
        <div class="toggle">
            <p>Pas encore de compte ? <a href="?action=register">S'inscrire</a></p>
        </div>
        <p class="note">Pour un projet d'étude, les comptes sont hachés. Si vous avez un ancien hash MD5, il sera migré automatiquement lors du premier login réussi.</p>

    <?php else: ?>
        <h2>Inscription</h2>
        <form method="POST" action="">
            <input type="text" name="nom" placeholder="Nom complet" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="motdepasse" placeholder="Mot de passe (min 6 caractères)" minlength="6" required>
            <button type="submit" name="register">S'inscrire</button>
        </form>
        <div class="toggle">
            <p>Déjà un compte ? <a href="?action=login">Se connecter</a></p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
