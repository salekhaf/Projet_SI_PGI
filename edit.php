<?php
require_once __DIR__ . '/functions.php';

// Récupère l'id depuis GET (ou redirige si absent)
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Récupère le produit depuis la BDD
$product = getProductById($id);
if (!$product) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $quantity = $_POST['quantity'] ?? 0;
    $unit_price = $_POST['unit_price'] ?? '0.00';
    $expiry_date = $_POST['expiry_date'] ?? null;

    // Validation basique
    if ($name === '') {
        $errors[] = 'Le nom est obligatoire.';
    }
    if (!is_numeric($quantity) || (int)$quantity < 0) {
        $errors[] = 'Quantité invalide.';
    }
    // Autorise virgule ou point comme séparateur décimal
    $unit_price = str_replace(',', '.', $unit_price);
    if (!is_numeric($unit_price) || (float)$unit_price < 0) {
        $errors[] = 'Prix invalide.';
    }

    // Si tout est OK, mise à jour
    if (empty($errors)) {
        $ok = updateProduct($id, [
            'name' => $name,
            'category' => $category,
            'quantity' => (int)$quantity,
            'unit_price' => number_format((float)$unit_price, 2, '.', ''),
            'expiry_date' => $expiry_date ?: null
        ]);

        if ($ok) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Erreur lors de la mise à jour.';
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Éditer un produit</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
  <div class="header">
    <h1>Éditer: <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <div><a class="btn" href="index.php">← Retour</a></div>
  </div>

  <?php if (!empty($errors)): ?>
    <div style="background:#fee2e2;padding:10px;border-radius:6px;margin-bottom:10px;">
      <?php foreach ($errors as $e): ?>
        <div><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <div class="form-row">
      <label>Nom</label>
      <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? $product['name'], ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="form-row">
      <label>Catégorie</label>
      <input type="text" name="category" value="<?php echo htmlspecialchars($_POST['category'] ?? $product['category'], ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="form-row">
      <label>Quantité</label>
      <input type="number" name="quantity" min="0" value="<?php echo htmlspecialchars($_POST['quantity'] ?? $product['quantity'], ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="form-row">
      <label>Prix par unité (€)</label>
      <input type="text" name="unit_price" value="<?php echo htmlspecialchars($_POST['unit_price'] ?? $product['unit_price'], ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="form-row">
      <label>Date de péremption (optionnelle)</label>
      <input type="date" name="expiry_date" value="<?php echo htmlspecialchars($_POST['expiry_date'] ?? $product['expiry_date'], ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <button class="btn btn-primary">Enregistrer</button>
  </form>
</div>
</body>
</html>
