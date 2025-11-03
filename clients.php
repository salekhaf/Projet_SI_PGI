<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) { header("Location: auth.php"); exit(); }
include('db_conn.php');

if (isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom']);
    $tel = trim($_POST['telephone']);
    mysqli_query($conn, "INSERT INTO clients (nom, telephone) VALUES ('$nom', '$tel')");
}

$result = mysqli_query($conn, "SELECT * FROM clients ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><title>Clients - PGI Épicerie</title>

</head>
<body>
<div class="container">
<h1>👥 Gestion des clients</h1>
<p><a href="index.php">⬅️ Retour</a></p>
<form method="POST">
<input type="text" name="nom" placeholder="Nom" required>
<input type="text" name="telephone" placeholder="Téléphone">
<button name="ajouter">Ajouter</button>
</form>
<table>
<tr><th>ID</th><th>Nom</th><th>Téléphone</th></tr>
<?php while($row=mysqli_fetch_assoc($result)): ?>
<tr><td><?= $row['id'] ?></td><td><?= htmlspecialchars($row['nom']) ?></td><td><?= $row['telephone'] ?></td></tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>
