<?php
session_start();
if (!isset($_SESSION['id_utilisateur']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include('db_conn.php');
$message = "";

// --- Changement de rôle ---
if (isset($_POST['changer_role'])) {
    $id_cible = intval($_POST['id_utilisateur']);
    $nouveau_role = trim($_POST['nouveau_role']);

    $roles_autorises = ['admin', 'responsable_approvisionnement', 'vendeur', 'caissier'];

    if (in_array($nouveau_role, $roles_autorises)) {
        $sql = "UPDATE utilisateurs SET role = '$nouveau_role' WHERE id = $id_cible";
        if (mysqli_query($conn, $sql)) {
            $message = "✅ Rôle mis à jour avec succès pour l’utilisateur #$id_cible.";
        } else {
            $message = "❌ Erreur SQL : " . mysqli_error($conn);
        }
    } else {
        $message = "⚠️ Rôle non autorisé.";
    }
}

// --- Liste des utilisateurs ---
$result = mysqli_query($conn, "SELECT id, nom, email, role FROM utilisateurs ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des utilisateurs - PGI Épicerie</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f6fa;
    padding: 20px;
}
.container {
    max-width: 900px;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
h1 { text-align: center; color: #007bff; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
th { background: #007bff; color: white; }
select { padding: 5px; border-radius: 5px; }
button {
    padding: 5px 10px; border: none; border-radius: 5px;
    background: #007bff; color: white; cursor: pointer;
}
button:hover { background: #0056b3; }
.message {
    text-align: center;
    font-weight: bold;
    margin-bottom: 15px;
}
</style>
</head>
<body>
<div class="container">
    <h1>👨‍💼 Gestion des utilisateurs</h1>
    <p><a href="index.php">⬅️ Retour au tableau de bord</a></p>

    <?php if ($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle actuel</th>
            <th>Changer de rôle</th>
        </tr>
        <?php while ($user = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['nom']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><strong><?= htmlspecialchars($user['role'] ?: "—") ?></strong></td>
            <td>
                <?php if ($user['id'] != $_SESSION['id_utilisateur']): ?>
                <form method="POST">
                    <input type="hidden" name="id_utilisateur" value="<?= $user['id'] ?>">
                    <select name="nouveau_role">
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="responsable_approvisionnement" <?= $user['role'] == 'responsable_approvisionnement' ? 'selected' : '' ?>>Responsable approvisionnement</option>
                        <option value="vendeur" <?= $user['role'] == 'vendeur' ? 'selected' : '' ?>>Vendeur</option>
                    </select>
                    <button type="submit" name="changer_role">Modifier</button>
                </form>
                <?php else: ?>
                    <em>(Vous)</em>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
