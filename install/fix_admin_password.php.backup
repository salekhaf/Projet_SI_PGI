<?php
/**
 * Script pour corriger le mot de passe admin
 * Ce script met à jour le mot de passe admin pour qu'il soit compatible avec password_hash/password_verify
 * 
 * UTILISATION :
 * 1. Accédez à ce fichier via votre navigateur : http://localhost/epicerie/fix_admin_password.php
 * 2. Le mot de passe sera mis à jour automatiquement
 * 3. SUPPRIMEZ ce fichier après utilisation pour des raisons de sécurité
 */

include('db_conn.php');

$admin_email = 'admin@epicerie.com';
$admin_password = 'admin123';

// Vérifier si l'admin existe
$stmt = mysqli_prepare($conn, "SELECT id, nom, mot_de_passe FROM utilisateurs WHERE email = ? AND role = 'admin'");
mysqli_stmt_bind_param($stmt, "s", $admin_email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$admin) {
    echo "<!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <title>Admin introuvable</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 50px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #dc3545; }
            .info { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .btn { background: #fa8c0f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>⚠️ Compte admin introuvable</h1>
            <div class='info'>
                <p>Aucun compte admin trouvé avec l'email : <strong>$admin_email</strong></p>
                <p>Créez d'abord un compte admin avec <a href='create_admin.php'>create_admin.php</a></p>
            </div>
            <a href='create_admin.php' class='btn'>Créer un compte admin</a>
        </div>
    </body>
    </html>";
    exit;
}

// Vérifier si le mot de passe est déjà en bcrypt (commence par $2y$)
$current_hash = $admin['mot_de_passe'];
$needs_update = true;

if (strpos($current_hash, '$2y$') === 0) {
    // Le hash est déjà en bcrypt, vérifions s'il correspond au mot de passe
    if (password_verify($admin_password, $current_hash)) {
        $needs_update = false;
    }
}

if ($needs_update) {
    // Mettre à jour le mot de passe avec bcrypt
    $new_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    
    $stmt = mysqli_prepare($conn, "UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $new_hash, $admin['id']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <title>Mot de passe corrigé</title>
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
                <h1>✅ Mot de passe admin corrigé !</h1>
                
                <div class='success'>
                    <h2 style='margin-top: 0;'>🎉 Succès !</h2>
                    <p>Le mot de passe de l'administrateur a été mis à jour avec succès.</p>
                    <p><strong>Ancien format :</strong> MD5 (incompatible)</p>
                    <p><strong>Nouveau format :</strong> bcrypt (compatible)</p>
                </div>
                
                <div class='credentials'>
                    <h3 style='margin-top: 0;'>🔐 Identifiants de connexion :</h3>
                    <p><strong>Email :</strong> <code>$admin_email</code></p>
                    <p><strong>Mot de passe :</strong> <code>$admin_password</code></p>
                </div>
                
                <div class='warning'>
                    <h3 style='margin-top: 0;'>⚠️ IMPORTANT - SÉCURITÉ :</h3>
                    <ol>
                        <li><strong>Testez la connexion</strong> maintenant avec ces identifiants</li>
                        <li><strong>Changez le mot de passe</strong> après votre première connexion</li>
                        <li><strong>Supprimez ce fichier</strong> (<code>fix_admin_password.php</code>) après utilisation</li>
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
                    <p>Erreur lors de la mise à jour du mot de passe : " . mysqli_error($conn) . "</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "<!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <title>Mot de passe déjà correct</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 50px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #28a745; }
            .success { background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .info { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
            code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
            .btn { background: #fa8c0f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>✅ Mot de passe déjà correct</h1>
            <div class='success'>
                <p>Le mot de passe admin est déjà au bon format (bcrypt).</p>
            </div>
            <div class='info'>
                <h3>🔐 Identifiants :</h3>
                <p><strong>Email :</strong> <code>$admin_email</code></p>
                <p><strong>Mot de passe :</strong> <code>$admin_password</code></p>
            </div>
            <div class='info'>
                <p>Si vous ne pouvez toujours pas vous connecter :</p>
                <ul>
                    <li>Vérifiez que vous utilisez bien l'email : <code>$admin_email</code></li>
                    <li>Vérifiez que vous utilisez bien le mot de passe : <code>$admin_password</code></li>
                    <li>Videz le cache de votre navigateur</li>
                    <li>Essayez en navigation privée</li>
                </ul>
            </div>
            <a href='auth.php' class='btn'>Aller à la page de connexion</a>
        </div>
    </body>
    </html>";
}
?>



