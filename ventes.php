<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: auth.php");
    exit();
}

include('db_conn.php');
$message = "";

// --- AJOUTER UNE VENTE ---
if (isset($_POST['ajouter'])) {
    $id_client = $_POST['id_client'] !== "" ? intval($_POST['id_client']) : "NULL";
    $id_utilisateur = $_SESSION['id_utilisateur'];
    $produits = $_POST['produit_id'];
    $quantites = $_POST['quantite'];
    $totaux = $_POST['prix_total'];
    $total_general = 0;

    // Validation du stock côté serveur
    $stock_ok = true;
    $erreurs_stock = [];

    for ($i = 0; $i < count($produits); $i++) {
        $id_produit = intval($produits[$i]);
        $qte = intval($quantites[$i]);
        if ($id_produit > 0 && $qte > 0) {
            $req = mysqli_query($conn, "SELECT nom, quantite_stock FROM produits WHERE id = $id_produit");
            $p = mysqli_fetch_assoc($req);
            if ($qte > $p['quantite_stock']) {
                $stock_ok = false;
                $erreurs_stock[] = "❌ Le produit <strong>" . htmlspecialchars($p['nom']) . "</strong> n’a que <strong>" . $p['quantite_stock'] . "</strong> unités en stock.";
            }
        }
    }

    if (!$stock_ok) {
        $message = implode("<br>", $erreurs_stock);
    } else {
        // Calcul du total
        foreach ($totaux as $t) $total_general += floatval($t);

        // Insérer la vente
        $sql_vente = "INSERT INTO ventes (id_client, id_utilisateur, total) VALUES ($id_client, $id_utilisateur, $total_general)";
        if (mysqli_query($conn, $sql_vente)) {
            $id_vente = mysqli_insert_id($conn);

            // Détails + mise à jour du stock
            for ($i = 0; $i < count($produits); $i++) {
                $id_produit = intval($produits[$i]);
                $qte = intval($quantites[$i]);
                if ($qte > 0 && $id_produit > 0) {
                    $req_p = mysqli_query($conn, "SELECT prix_vente, quantite_stock FROM produits WHERE id = $id_produit");
                    $p = mysqli_fetch_assoc($req_p);
                    $prix_unitaire = $p['prix_vente'];
                    $nouveau_stock = $p['quantite_stock'] - $qte;

                    mysqli_query($conn, "INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire)
                                         VALUES ($id_vente, $id_produit, $qte, $prix_unitaire)");

                    mysqli_query($conn, "UPDATE produits SET quantite_stock = $nouveau_stock WHERE id = $id_produit");
                }
            }
            $message = "✅ Vente enregistrée avec succès.";
        } else {
            $message = "❌ Erreur lors de l’enregistrement : " . mysqli_error($conn);
        }
    }
}

// --- SUPPRESSION D’UNE VENTE ---
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    mysqli_query($conn, "DELETE FROM ventes WHERE id = $id");
    header("Location: ventes.php");
    exit();
}

// --- RÉCUPÉRATION DES DONNÉES ---
$ventes = mysqli_query($conn, "SELECT v.id, v.total, v.date_vente, c.nom AS client, u.nom AS vendeur
                               FROM ventes v
                               LEFT JOIN clients c ON v.id_client = c.id
                               LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id
                               ORDER BY v.id DESC");
$clients = mysqli_query($conn, "SELECT id, nom FROM clients ORDER BY nom ASC");
$produits = mysqli_query($conn, "SELECT id, nom, prix_vente, quantite_stock FROM produits ORDER BY nom ASC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ventes - PGI Épicerie</title>
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
.message {text-align: center; margin-bottom: 15px; color: #333;}
.error {color: #b02a37; font-weight: bold;}
.total-box {font-weight: bold; text-align: right; margin-top: 10px;}
select, input {padding: 6px;}
</style>
<script>
function updateTotal(row) {
    let prix = parseFloat(row.querySelector('.prix').value);
    let qte = parseInt(row.querySelector('.qte').value);
    let stock = parseInt(row.querySelector('.qte').max);
    if (qte > stock) {
        alert("❌ Quantité demandée supérieure au stock disponible (" + stock + ").");
        row.querySelector('.qte').value = stock;
        qte = stock;
    }
    if (!isNaN(prix) && !isNaN(qte)) {
        row.querySelector('.total').value = (prix * qte).toFixed(2);
        updateGrandTotal();
    }
}
function updateGrandTotal() {
    let totaux = document.querySelectorAll('.total');
    let somme = 0;
    totaux.forEach(t => somme += parseFloat(t.value || 0));
    document.getElementById('grand_total').innerText = somme.toFixed(2) + " €";
}
function ajouterLigne() {
    const table = document.getElementById('table_produits');
    const clone = table.rows[1].cloneNode(true);
    clone.querySelectorAll('input').forEach(i => i.value = '');
    clone.querySelector('select').selectedIndex = 0;
    table.appendChild(clone);
}
function setPrixStock(select) {
    const option = select.selectedOptions[0];
    const prix = option.dataset.prix;
    const stock = option.dataset.stock;
    const row = select.closest('tr');
    row.querySelector('.prix').value = prix;
    const qteInput = row.querySelector('.qte');
    qteInput.max = stock;
    qteInput.placeholder = "max " + stock;
    updateTotal(row);
}
</script>
</head>
<body>
<div class="container">
<h1>💰 Gestion des ventes</h1>
<p><a href="index.php">⬅️ Retour au tableau de bord</a></p>

<?php if ($message): ?>
    <p class="message"><?= $message ?></p>
<?php endif; ?>

<h3>Nouvelle vente</h3>
<form method="POST">
    <label>Client :</label>
    <select name="id_client">
        <option value="">-- Aucun client --</option>
        <?php while ($c = mysqli_fetch_assoc($clients)): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
        <?php endwhile; ?>
    </select>

    <table id="table_produits">
        <tr><th>Produit</th><th>Prix (€)</th><th>Quantité</th><th>Total (€)</th></tr>
        <tr>
            <td>
                <select name="produit_id[]" onchange="setPrixStock(this)">
                    <option value="">-- Sélectionner --</option>
                    <?php
                    mysqli_data_seek($produits, 0);
                    while ($p = mysqli_fetch_assoc($produits)): ?>
                        <option value="<?= $p['id'] ?>" data-prix="<?= $p['prix_vente'] ?>" data-stock="<?= $p['quantite_stock'] ?>">
                            <?= htmlspecialchars($p['nom']) ?> (<?= $p['quantite_stock'] ?> en stock)
                        </option>
                    <?php endwhile; ?>
                </select>
            </td>
            <td><input type="number" class="prix" name="prix_unitaire[]" readonly></td>
            <td><input type="number" class="qte" name="quantite[]" min="1" oninput="updateTotal(this.closest('tr'))"></td>
            <td><input type="number" class="total" name="prix_total[]" step="0.01" readonly></td>
        </tr>
    </table>

    <p><button type="button" onclick="ajouterLigne()">➕ Ajouter un produit</button></p>
    <div class="total-box">Total général : <span id="grand_total">0.00 €</span></div>
    <button type="submit" name="ajouter">Enregistrer la vente</button>
</form>

<h3>Liste des ventes</h3>
<table>
<tr><th>ID</th><th>Client</th><th>Vendeur</th><th>Date</th><th>Total (€)</th><th>Action</th></tr>
<?php while ($v = mysqli_fetch_assoc($ventes)): ?>
<tr>
    <td><?= $v['id'] ?></td>
    <td><?= htmlspecialchars($v['client'] ?? "N/A") ?></td>
    <td><?= htmlspecialchars($v['vendeur'] ?? "N/A") ?></td>
    <td><?= $v['date_vente'] ?></td>
    <td><?= number_format($v['total'], 2, ',', ' ') ?></td>
    <td>
    <a class="btn" href="detailVente.php?id=<?= $v['id'] ?>">Détails</a>
    <a class="btn" style="background:#dc3545" href="?supprimer=<?= $v['id'] ?>">Supprimer</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>
