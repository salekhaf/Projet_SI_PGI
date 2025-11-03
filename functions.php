<?php
// Inclusion de la configuration et connexion PDO
require_once __DIR__ . '/config.php';

/**
 * Récupère la liste des produits (avec recherche et tri optionnels)
 */
function getProducts($search = '', $sort = 'name', $order = 'ASC') {
    global $pdo;

    $allowedSort = ['name','category','quantity','unit_price','expiry_date','created_at'];
    if (!in_array($sort, $allowedSort)) $sort = 'name';
    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

    $sql = "SELECT * FROM products";
    $params = [];

    if ($search !== '') {
        $sql .= " WHERE name LIKE :s OR category LIKE :s";
        $params[':s'] = "%{$search}%";
    }

    $sql .= " ORDER BY {$sort} {$order}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/**
 * Récupère un produit par son ID
 */
function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * Ajoute un produit
 */
function addProduct($data) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO products (name, category, quantity, unit_price, expiry_date)
        VALUES (:name, :category, :quantity, :unit_price, :expiry_date)
    ");

    return $stmt->execute([
        ':name' => $data['name'],
        ':category' => $data['category'] ?: null,
        ':quantity' => (int)$data['quantity'],
        ':unit_price' => number_format((float)$data['unit_price'], 2, '.', ''),
        ':expiry_date' => $data['expiry_date'] ?: null
    ]);
}

/**
 * Met à jour un produit
 */
function updateProduct($id, $data) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE products 
        SET name = :name, category = :category, quantity = :quantity, 
            unit_price = :unit_price, expiry_date = :expiry_date 
        WHERE id = :id
    ");

    return $stmt->execute([
        ':name' => $data['name'],
        ':category' => $data['category'] ?: null,
        ':quantity' => (int)$data['quantity'],
        ':unit_price' => number_format((float)$data['unit_price'], 2, '.', ''),
        ':expiry_date' => $data['expiry_date'] ?: null,
        ':id' => $id
    ]);
}

/**
 * Supprime un produit
 */
function deleteProduct($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}
?>
