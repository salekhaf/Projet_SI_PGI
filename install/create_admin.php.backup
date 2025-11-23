<?php
/**
 * Script pour créer un compte administrateur
 * À exécuter une seule fois pour créer le premier compte admin
 * 
 * UTILISATION :
 * 1. Accédez à ce fichier via votre navigateur : http://localhost/epicerie/create_admin.php
 * 2. Le compte admin sera créé automatiquement
 * 3. SUPPRIMEZ ce fichier après utilisation pour des raisons de sécurité
 */

include('db_conn.php');

$admin_email = 'admin@epicerie.com';
$admin_password = 'admin123'; // Changez ce mot de passe après la première connexion !
$admin_nom = 'Administrateur Principal';

// Vérifier si un admin existe déjà
$check = mysqli_query($conn, "SELECT id, email, mot_de_passe FROM utilisateurs WHERE role = 'admin' LIMIT 1");

if (mysqli_num_rows($check) > 0) {
    $existing_admin = mysqli_fetch_assoc($check);
    $needs_fix = true;
    
    // Vérifier si le mot de passe est en bcrypt
    if (strpos($existing_admin['mot_de_passe'], '$2y$') === 0) {
        // Tester si le mot de passe fonctionne
        if (password_verify($admin_password, $existing_admin['mot_de_passe'])) {
            $needs_fix = false;
        }
    }
    
    if ($needs_fix) {
        // Le mot de passe doit être corrigé
        echo "<!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <title>Corriger le mot de passe admin</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 50px; background: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #ffc107; }
                .warning { background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107; }
                .btn { background: #fa8c0f; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; font-weight: bold; }
                .btn:hover { background: #bc4002; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>⚠️ Problème de mot de passe détecté</h1>
                <div class='warning'>
                    <p><strong>Un compte admin existe déjà, mais le mot de passe est au mauvais format.</strong></p>
                    <p>Le système utilise <code>password_hash</code> (bcrypt) mais votre base de données contient probablement un hash MD5.</p>
                    <p>Cliquez sur le bouton ci-dessous pour corriger le mot de passe :</p>
                </div>
                <div style='text-align: center;'>
                    <a href='fix_admin_password.php' class='btn'>🔧 Corriger le mot de passe admin</a>
                </div>
            </div>
        </body>
        </html>";
        exit;
    }
    echo "<!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <title>Admin existe déjà</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 50px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #dc3545; }
            .info { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .success { background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; }
            code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>⚠️ Compte admin déjà existant</h1>
            <div class='info'>
                <p><strong>Un compte administrateur existe déjà dans la base de données.</strong></p>
                <p>Si vous avez oublié les identifiants, vous pouvez :</p>
                <ol>
                    <li>Vérifier dans la base de données la table <code>utilisateurs</code></li>
                    <li>Ou modifier directement le rôle d'un utilisateur existant via phpMyAdmin</li>
                    <li>Ou utiliser le script SQL ci-dessous pour réinitialiser le mot de passe</li>
                </ol>
            </div>
            <div class='success'>
                <h3>📋 Identifiants par défaut (si le compte existe) :</h3>
                <p><strong>Email :</strong> <code>admin@epicerie.com</code></p>
                <p><strong>Mot de passe :</strong> <code>admin123</code></p>
                <p><em>⚠️ Si le mot de passe ne fonctionne pas, utilisez le script SQL ci-dessous.</em></p>
            </div>
            <div class='info'>
                <h3>🔧 Script SQL pour réinitialiser le mot de passe admin :</h3>
                <pre style='background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto;'>
-- Exécutez ce script dans phpMyAdmin ou MySQL
UPDATE utilisateurs 
SET mot_de_passe = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'admin@epicerie.com' AND role = 'admin';

-- Le mot de passe sera : admin123
                </pre>
            </div>
            <p style='margin-top: 30px;'>
                <a href='auth.php' style='background: #fa8c0f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Aller à la page de connexion</a>
            </p>
        </div>
    </body>
    </html>";
    exit;
}

// Créer le compte admin
$motdepasse_hache = password_hash($admin_password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, 'admin')");
mysqli_stmt_bind_param($stmt, "sss", $admin_nom, $admin_email, $motdepasse_hache);

if (mysqli_stmt_execute($stmt)) {
    echo "<!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <title>Admin créé avec succès</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
            h1 { color: #28a745; text-align: center; }
            .success { background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745; }
            .credentials { background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107; }
            .warning { background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #dc3545; }
            code { background: #f4f4f4; padding: 5px 10px; border-radius: 5px; font-size: 1.1em; }
            .btn { background: #fa8c0f; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; font-weight: bold; }
            .btn:hover { background: #bc4002; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>✅ Compte administrateur créé avec succès !</h1>
            
            <div class='success'>
                <h2 style='margin-top: 0;'>🎉 Félicitations !</h2>
                <p>Le compte administrateur a été créé dans la base de données.</p>
            </div>
            
            <div class='credentials'>
                <h3 style='margin-top: 0;'>🔐 Identifiants de connexion :</h3>
                <p><strong>Email :</strong> <code>$admin_email</code></p>
                <p><strong>Mot de passe :</strong> <code>$admin_password</code></p>
            </div>
            
            <div class='warning'>
                <h3 style='margin-top: 0;'>⚠️ IMPORTANT - SÉCURITÉ :</h3>
                <ol>
                    <li><strong>Changez le mot de passe</strong> après votre première connexion</li>
                    <li><strong>Supprimez ce fichier</strong> (<code>create_admin.php</code>) après utilisation</li>
                    <li>Ne partagez jamais ces identifiants</li>
                </ol>
            </div>
            
            <div style='text-align: center;'>
                <a href='auth.php' class='btn'>Se connecter maintenant</a>
            </div>
        </div>
    </body>
    </html>";
} else {
    echo "<!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <title>Erreur</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 50px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #dc3545; }
            .error { background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>❌ Erreur</h1>
            <div class='error'>
                <p>Erreur lors de la création du compte admin : " . mysqli_error($conn) . "</p>
            </div>
        </div>
    </body>
    </html>";
}

mysqli_stmt_close($stmt);
?>

