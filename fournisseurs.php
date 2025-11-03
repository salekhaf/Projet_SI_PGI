<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: auth.php");
    exit();
}

include('db_conn.php');

$role = $_SESSION['role'];
$message = "";

// ✅ Autoriser ajout/suppression uniquement pour admin ou responsable approvisionnement
$peut_modifier = in_array($role, ['admin', 'responsable_approvisionnement']);

// --- AJOUT D'UN FOURNISSEUR (seulement si autorisé) ---
if ($peut_modifier && isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    if ($nom !== "") {
        $sql = "INSERT INTO fournisseurs (nom, telephone, email, adresse)
                VALUES ('$nom', '$telephone', '$email', '$adresse')";
        if (mysqli_query($conn, $sql)) {
            $message = "✅ Fournisseur ajouté avec succès.";
        } else {
            $message = "❌ Erreur lors de l'ajout : " . mysqli_error($conn);
        }
    } else {
        $message = "⚠️ Le nom du fournisseur est obligatoire.";
    }
}

// --- SUPPRESSION D'UN FOURNISSEUR (seulement si autorisé) ---
if ($peut_modifier && isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    mysqli_query($conn, "DELETE FROM fournisseurs WHERE id = $id");
    header("Location: fournisseurs.php");
    exit();
}

// --- LISTE DES FOURNISSEURS ---
$result = mysqli_query($conn, "SELECT * FROM fournisseurs ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Fournisseurs - PGI Épicerie</title>
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
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
h1 { text-align: center; color: #007bff; }
a { text-decoration: none; color: #007bff; }
a:hover { text-decoration: underline; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
th { background: #007bff; color: white; }
form input, form textarea { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; }
button {
    background: #007bff; color: white; border: none;
    padding: 8px 15px; border-radius: 5px; cursor: pointer;
}
button:hover { background: #0056b3; }
a.btn {
    background: #dc3545; color: white;
    padding: 5px 10px; border-radius: 5px; text-decoration: none;
}
a.btn:hover { background: #b02a37; }
.message { text-align: center; margin-bottom: 10px; color: #333; font-weight: bold; }
.note {
    font-size: 14px;
    color: #555;
    margin-bottom: 15px;
    background: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 10px;
}
</style>
</head>
<body>
<div class="container">
    <h1>🚚 Gestion des fournisseurs</h1>
    <p><a href="index.php">⬅️ Retour au tableau de bord</a></p>

    <?php if ($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <?php if ($peut_modifier): ?>
        <h3>Ajouter un fournisseur</h3>
        <form method="POST">
            <input type="text" name="nom" placeholder="Nom du fournisseur" required>
            <input type="text" name="telephone" placeholder="Téléphone">
            <input type="email" name="email" placeholder="Email">
            <textarea name="adresse" placeholder="Adresse"></textarea>
            <button type="submit" name="ajouter">Ajouter</button>
        </form>
    <?php else: ?>
        <p class="note">ℹ️ Vous n’avez pas les droits pour ajouter ou supprimer des fournisseurs.<br>
        Seuls les <strong>admins</strong> et les <strong>responsables approvisionnement</strong> peuvent le faire.</p>
    <?php endif; ?>

    <h3>Liste des fournisseurs</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Téléphone</th>
            <th>Email</th>
            <th>Adresse</th>
            <?php if ($peut_modifier): ?><th>Action</th><?php endif; ?>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['nom']) ?></td>
            <td><?= htmlspecialchars($row['telephone']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['adresse']) ?></td>
            <?php if ($peut_modifier): ?>
                <td><a class="btn" href="?supprimer=<?= $row['id'] ?>">Supprimer</a></td>
            <?php endif; ?>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
