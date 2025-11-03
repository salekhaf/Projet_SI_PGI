<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: auth.php");
    exit();
}

include('db_conn.php');

$role = $_SESSION['role'];
$id_user = $_SESSION['id_utilisateur'];
$message = "";

// Vérifier si l'utilisateur peut passer des commandes
$peut_commander = in_array($role, ['admin', 'responsable_approvisionnement']);

// --- AJOUT D'UNE COMMANDE ---
if ($peut_commander && isset($_POST['ajouter'])) {
    $id_fournisseur = intval($_POST['id_fournisseur']);
    $produits = $_POST['produit_id'];
    $quantites = $_POST['quantite'];
    $prix_achats = $_POST['prix_achat'];
    $total_general = 0;

    foreach ($produits as $i => $id_produit) {
        $qte = intval($quantites[$i]);
        $prix = floatval($prix_achats[$i]);
        $total_general += $prix * $qte;
    }

    // Insertion dans la table "achats"
    $sql_achat = "INSERT INTO achats (id_fournisseur, date_achat, montant_total) 
                  VALUES ($id_fournisseur, NOW(), $total_general)";
    if (mysqli_query($conn, $sql_achat)) {
        $id_achat = mysqli_insert_id($conn);

        // Insertion dans "details_achat" et mise à jour du stock
        foreach ($produits as $i => $id_produit) {
            $id_produit = intval($id_produit);
            $qte = intval($quantites[$i]);
            $prix = floatval($prix_achats[$i]);
            if ($id_produit > 0 && $qte > 0) {
                mysqli_query($conn, "INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat)
                                     VALUES ($id_achat, $id_produit, $qte, $prix)");
                mysqli_query($conn, "UPDATE produits 
                                     SET quantite_stock = quantite_stock + $qte,
                                         prix_achat = $prix
                                     WHERE id = $id_produit");
            }
        }
        $message = "✅ Commande enregistrée avec succès.";
    } else {
        $message = "❌ Erreur lors de l'enregistrement : " . mysqli_error($conn);
    }
}

// --- SUPPRESSION D'UNE COMMANDE ---
if ($peut_commander && isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    mysqli_query($conn, "DELETE FROM achats WHERE id = $id");
    header("Location: commandes.php");
    exit();
}

// --- LISTES ---
$fournisseurs = mysqli_query($conn, "SELECT id, nom FROM fournisseurs ORDER BY nom ASC");
$produits = mysqli_query($conn, "SELECT id, nom, prix_achat FROM produits ORDER BY nom ASC");
$achats = mysqli_query($conn, "
    SELECT a.id, a.date_achat, a.montant_total, f.nom AS fournisseur
    FROM achats a
    JOIN fournisseurs f ON a.id_fournisseur = f.id
    ORDER BY a.id DESC
");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Commandes fournisseurs - PGI Épicerie</title>
<style>
body {font-family: Arial; background: #f5f6fa; padding: 20px;}
.container {max-width: 1000px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);}
h1 {text-align: center; color: #007bff;}
table {width: 100%; border-collapse: collapse; margin-top: 15px;}
th, td {border: 1px solid #ccc; padding: 8px; text-align: center;}
th {background: #007bff; color: white;}
button {background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer;}
button:hover {background: #0056b3;}
a.btn {background: #dc3545; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none;}
a.btn:hover {background: #b02a37;}
a.btn-info {background: #17a2b8; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none;}
a.btn-info:hover {background: #138496;}
.total-box {font-weight: bold; text-align: right; margin-top: 10px;}
.total-cell {font-weight: bold; color: #007bff;}
</style>
<script>
function updateTotal(row) {
    const prix = parseFloat(row.querySelector('.prix').value) || 0;
    const qte = parseInt(row.querySelector('.qte').value) || 0;
    const total = prix * qte;
    row.querySelector('.total-cell').textContent = total.toFixed(2) + " €";
    updateGrandTotal();
}

function updateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.total-cell').forEach(cell => {
        total += parseFloat(cell.textContent) || 0;
    });
    document.getElementById('grand_total').textContent = total.toFixed(2) + " €";
}

function ajouterLigne() {
    const table = document.getElementById('table_produits');
    const clone = table.rows[1].cloneNode(true);
    clone.querySelectorAll('input').forEach(i => i.value = '');
    clone.querySelector('.total-cell').textContent = "0.00 €";
    table.appendChild(clone);
}

function setPrix(select) {
    const prix = select.selectedOptions[0].dataset.prix || 0;
    const row = select.closest('tr');
    row.querySelector('.prix').value = prix;
    updateTotal(row);
}
</script>
</head>
<body>
<div class="container">
<h1>📦 Commandes fournisseurs</h1>
<p><a href="index.php">⬅️ Retour au tableau de bord</a></p>

<?php if ($message): ?><p><strong><?= $message ?></strong></p><?php endif; ?>

<?php if ($peut_commander): ?>
<h3>Nouvelle commande</h3>
<form method="POST">
    <label>Fournisseur :</label>
    <select name="id_fournisseur" required>
        <option value="">-- Sélectionner un fournisseur --</option>
        <?php while ($f = mysqli_fetch_assoc($fournisseurs)): ?>
            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nom']) ?></option>
        <?php endwhile; ?>
    </select>

    <table id="table_produits">
        <tr><th>Produit</th><th>Prix achat (€)</th><th>Quantité</th><th>Total (€)</th></tr>
        <tr>
            <td>
                <select name="produit_id[]" onchange="setPrix(this)">
                    <option value="">-- Choisir un produit --</option>
                    <?php
                    mysqli_data_seek($produits, 0);
                    while ($p = mysqli_fetch_assoc($produits)): ?>
                        <option value="<?= $p['id'] ?>" data-prix="<?= $p['prix_achat'] ?>">
                            <?= htmlspecialchars($p['nom']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </td>
            <td><input type="number" class="prix" name="prix_achat[]" step="0.01" oninput="updateTotal(this.closest('tr'))"></td>
            <td><input type="number" class="qte" name="quantite[]" min="1" value="1" oninput="updateTotal(this.closest('tr'))"></td>
            <td class="total-cell">0.00 €</td>
        </tr>
    </table>

    <p><button type="button" onclick="ajouterLigne()">➕ Ajouter un produit</button></p>
    <div class="total-box">Total général : <span id="grand_total">0.00 €</span></div>
    <button type="submit" name="ajouter">Enregistrer la commande</button>
</form>
<?php else: ?>
<p>⚠️ Vous n’avez pas les droits pour créer ou supprimer des commandes.</p>
<?php endif; ?>

<h3>Liste des commandes</h3>
<table>
<tr>
    <th>ID</th>
    <th>Fournisseur</th>
    <th>Date</th>
    <th>Total (€)</th>
    <th>Détails</th>
    <?php if ($peut_commander): ?><th>Action</th><?php endif; ?>
</tr>
<?php while ($a = mysqli_fetch_assoc($achats)): ?>
<tr>
    <td><?= $a['id'] ?></td>
    <td><?= htmlspecialchars($a['fournisseur']) ?></td>
    <td><?= $a['date_achat'] ?></td>
    <td><?= number_format($a['montant_total'], 2, ',', ' ') ?></td>
    <td>
    <a href="detailCommande.php?id=<?= $a['id'] ?>" class="btn-info">🔍 Voir</a>
    <a href="bonCommande.php?id=<?= $a['id'] ?>" class="btn-info" style="background:#28a745;">📄 PDF</a>
    </td>

    <?php if ($peut_commander): ?>
        <td><a class="btn" href="?supprimer=<?= $a['id'] ?>">🗑️ Supprimer</a></td>
    <?php endif; ?>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>
