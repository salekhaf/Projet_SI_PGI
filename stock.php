<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: auth.php");
    exit();
}

include('db_conn.php');

$role = $_SESSION['role'];
$peut_ajouter = in_array($role, ['admin', 'responsable_approvisionnement']);
$message = "";

// --- AJOUT D'UN NOUVEAU PRODUIT ---
if ($peut_ajouter && isset($_POST['ajouter_produit'])) {
    $nom = trim($_POST['nom']);
    $prix_achat = floatval($_POST['prix_achat']);
    $prix_vente = floatval($_POST['prix_vente']);
    $quantite_stock = intval($_POST['quantite_stock']);

    if (!empty($nom) && $prix_achat > 0 && $prix_vente > 0) {
        // Vérification si le produit existe déjà (même nom, insensible à la casse)
        $check = mysqli_query($conn, "SELECT id FROM produits WHERE LOWER(nom) = LOWER('$nom')");
        if (mysqli_num_rows($check) > 0) {
            $message = "⚠️ Ce produit existe déjà dans la base.";
        } else {
            $sql = "INSERT INTO produits (nom, prix_achat, prix_vente, quantite_stock)
                    VALUES ('$nom', $prix_achat, $prix_vente, $quantite_stock)";
            if (mysqli_query($conn, $sql)) {
                $message = "✅ Nouveau produit ajouté avec succès.";
            } else {
                $message = "❌ Erreur SQL : " . mysqli_error($conn);
            }
        }
    } else {
        $message = "⚠️ Veuillez remplir tous les champs correctement.";
    }
}

// --- LISTER LES PRODUITS ---
$result = mysqli_query($conn, "SELECT * FROM produits ORDER BY nom ASC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>📦 Gestion du stock - PGI Épicerie</title>
<style>
body {font-family: Arial, sans-serif; background: #f5f6fa; padding: 20px;}
.container {max-width: 900px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);}
h1 {text-align: center; color: #007bff;}
table {width: 100%; border-collapse: collapse; margin-top: 20px;}
th, td {border: 1px solid #ccc; padding: 8px; text-align: center;}
th {background: #007bff; color: white;}
.low-stock {background: #fff3cd; color: #856404;}
.critical-stock {background: #f8d7da; color: #721c24; font-weight: bold;}
a {text-decoration: none; color: #007bff;}
a:hover {text-decoration: underline;}
.message {text-align: center; margin-bottom: 10px; font-weight: bold;}
form.add-form {margin-bottom: 20px; padding: 15px; background: #e9f7ef; border-radius: 8px;}
form.add-form input {padding: 6px; margin: 5px; border-radius: 5px; border: 1px solid #ccc;}
form.add-form button {background: #007bff; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer;}
form.add-form button:hover {background: #0056b3;}
</style>
</head>
<body>
<div class="container">
    <h1>📦 Gestion du stock</h1>
    <p><a href="index.php">⬅️ Retour au tableau de bord</a></p>

    <?php if ($message): ?><p class="message"><?= $message ?></p><?php endif; ?>

    <?php if ($peut_ajouter): ?>
    <h3>➕ Ajouter une nouvelle référence</h3>
    <form method="POST" class="add-form">
        <input type="text" name="nom" placeholder="Nom du produit" required>
        <input type="number" name="prix_achat" step="0.01" placeholder="Prix d'achat (€)" required>
        <input type="number" name="prix_vente" step="0.01" placeholder="Prix de vente (€)" required>
        <input type="number" name="quantite_stock" min="0" placeholder="Quantité initiale" value="0">
        <button type="submit" name="ajouter_produit">Ajouter</button>
    </form>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Produit</th>
            <th>Prix d’achat (€)</th>
            <th>Prix de vente (€)</th>
            <th>Quantité en stock</th>
        </tr>
        <?php while ($p = mysqli_fetch_assoc($result)): ?>
            <?php
                $class = "";
                if ($p['quantite_stock'] <= 0) $class = "critical-stock";
                elseif ($p['quantite_stock'] < 10) $class = "low-stock";
            ?>
            <tr class="<?= $class ?>">
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nom']) ?></td>
                <td><?= number_format($p['prix_achat'], 2, ',', ' ') ?></td>
                <td><?= number_format($p['prix_vente'], 2, ',', ' ') ?></td>
                <td><?= $p['quantite_stock'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
