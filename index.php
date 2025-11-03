<?php
// config.php — configuration de la base de données

$host = 'localhost';
$db   = 'gestion_stock';  // nom de ta base MySQL (à adapter si différent)
$user = 'root';           // utilisateur par défaut de XAMPP
$pass = '';               // mot de passe vide par défaut sous XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // affiche les erreurs PDO
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // sécurité contre les injections SQL
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}
?>


<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Gestion de stock - Supermarché</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
  <div class="header">
    <h1>Gestion de stock - Supermarché</h1>
    <div>
      <a class="btn btn-primary" href="add.php">+ Ajouter un produit</a>
    </div>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Nom</th>
        <th>Catégorie</th>
        <th>Quantité</th>
        <th>Prix unité</th>
        <th>Expire</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($products) === 0): ?>
        <tr><td colspan="6">Aucun produit trouvé.</td></tr>
      <?php else: ?>
        <?php foreach($products as $p): ?>
          <tr>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td><?php echo htmlspecialchars($p['category']); ?></td>
            <td><?php echo (int)$p['quantity']; ?></td>
            <td><?php echo number_format($p['unit_price'], 2, ',', ' '); ?> €</td>
            <td><?php echo $p['expiry_date'] ? htmlspecialchars($p['expiry_date']) : '-'; ?></td>
            <td class="actions small">
              <a class="btn btn-muted" href="edit.php?id=<?php echo $p['id']; ?>">Éditer</a>
              <form style="display:inline" method="post" action="delete.php" onsubmit="return confirm('Supprimer ce produit ?');">
                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                <button class="btn btn-danger">Supprimer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="footer">
    Tip: utilisez la recherche pour trouver rapidement des produits, triez par quantité pour repérer les ruptures.
  </div>
</div>
</body>
</html>
