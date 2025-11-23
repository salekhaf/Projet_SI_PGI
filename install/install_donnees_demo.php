<?php
/**
 * Script d'installation des données de démonstration
 * Pour une épicerie (snack & nourriture)
 */

include('db_conn.php');

echo "<h2>📦 Installation des données de démonstration</h2>";
echo "<p>Insertion en cours...</p>";

// Désactiver les vérifications de clés étrangères temporairement
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

// =====================================================
// Catégories supplémentaires
// =====================================================
$categories = [
    'Boissons', 'Snacks', 'Produits frais', 'Sucreries', 
    'Conserves', 'Hygiène', 'Petit-déjeuner'
];

$categorie_ids = [];
foreach ($categories as $cat) {
    $stmt = mysqli_prepare($conn, "INSERT INTO categories (nom) VALUES (?) ON DUPLICATE KEY UPDATE nom=nom");
    mysqli_stmt_bind_param($stmt, "s", $cat);
    mysqli_stmt_execute($stmt);
    $categorie_ids[$cat] = mysqli_insert_id($conn);
    if ($categorie_ids[$cat] == 0) {
        // Récupérer l'ID existant
        $result = mysqli_query($conn, "SELECT id FROM categories WHERE nom = '$cat'");
        $row = mysqli_fetch_assoc($result);
        $categorie_ids[$cat] = $row['id'];
    }
    mysqli_stmt_close($stmt);
}
echo "✅ Catégories insérées<br>";

// =====================================================
// Fournisseurs
// =====================================================
$fournisseurs = [
    ['DistribSnack Maroc', '0522-123456', 'contact@distribsnack.ma', 'Casablanca, Bd Zerktouni'],
    ['BoissonPlus', '0522-234567', 'info@boissonplus.ma', 'Rabat, Hay Riad'],
    ['FreshFood Distribution', '0522-345678', 'ventes@freshfood.ma', 'Marrakech, Guéliz'],
    ['SweetCorp', '0522-456789', 'commercial@sweetcorp.ma', 'Fès, Centre-ville'],
    ['ConservPro', '0522-567890', 'contact@conservpro.ma', 'Tanger, Zone industrielle'],
    ['HygieneMaroc', '0522-678901', 'info@hygienemaroc.ma', 'Agadir, Anza'],
    ['CerealCo', '0522-789012', 'ventes@cerealco.ma', 'Meknès, Route de Fès']
];

$fournisseur_ids = [];
foreach ($fournisseurs as $four) {
    $stmt = mysqli_prepare($conn, "INSERT INTO fournisseurs (nom, telephone, email, adresse) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE nom=nom");
    mysqli_stmt_bind_param($stmt, "ssss", $four[0], $four[1], $four[2], $four[3]);
    mysqli_stmt_execute($stmt);
    $fournisseur_ids[] = mysqli_insert_id($conn);
    if (end($fournisseur_ids) == 0) {
        $result = mysqli_query($conn, "SELECT id FROM fournisseurs WHERE nom = '{$four[0]}'");
        $row = mysqli_fetch_assoc($result);
        $fournisseur_ids[count($fournisseur_ids) - 1] = $row['id'];
    }
    mysqli_stmt_close($stmt);
}
echo "✅ Fournisseurs insérés<br>";

// =====================================================
// Produits
// =====================================================
$produits = [
    // Boissons
    ['Coca-Cola 33cl', $categorie_ids['Boissons'], 4.50, 6.00, 120, $fournisseur_ids[1]],
    ['Coca-Cola 1.5L', $categorie_ids['Boissons'], 8.00, 11.00, 80, $fournisseur_ids[1]],
    ['Pepsi 33cl', $categorie_ids['Boissons'], 4.50, 6.00, 100, $fournisseur_ids[1]],
    ['Sprite 33cl', $categorie_ids['Boissons'], 4.00, 5.50, 90, $fournisseur_ids[1]],
    ['Fanta Orange 33cl', $categorie_ids['Boissons'], 4.00, 5.50, 85, $fournisseur_ids[1]],
    ['Eau minérale Sidi Ali 1.5L', $categorie_ids['Boissons'], 3.00, 4.50, 150, $fournisseur_ids[1]],
    ['Jus d\'orange Jafal 1L', $categorie_ids['Boissons'], 6.00, 8.50, 60, $fournisseur_ids[1]],
    ['Thé Lipton 25 sachets', $categorie_ids['Boissons'], 12.00, 16.00, 40, $fournisseur_ids[1]],
    ['Café Nescafé 200g', $categorie_ids['Boissons'], 35.00, 45.00, 30, $fournisseur_ids[1]],
    
    // Snacks
    ['Chips Lay\'s Nature 150g', $categorie_ids['Snacks'], 5.00, 7.50, 200, $fournisseur_ids[0]],
    ['Chips Lay\'s Barbecue 150g', $categorie_ids['Snacks'], 5.00, 7.50, 180, $fournisseur_ids[0]],
    ['Chips Doritos Nacho 150g', $categorie_ids['Snacks'], 6.00, 8.50, 150, $fournisseur_ids[0]],
    ['Cacahuètes grillées 200g', $categorie_ids['Snacks'], 4.50, 6.50, 120, $fournisseur_ids[0]],
    ['Biscuits Oreo 154g', $categorie_ids['Snacks'], 8.00, 11.00, 100, $fournisseur_ids[0]],
    ['Biscuits Prince 200g', $categorie_ids['Snacks'], 6.00, 8.50, 110, $fournisseur_ids[0]],
    ['Popcorn salé 100g', $categorie_ids['Snacks'], 3.50, 5.00, 80, $fournisseur_ids[0]],
    
    // Produits frais
    ['Yaourt Danone Nature 4x125g', $categorie_ids['Produits frais'], 8.00, 11.00, 60, $fournisseur_ids[2]],
    ['Yaourt Danone Fruits 4x125g', $categorie_ids['Produits frais'], 8.50, 12.00, 55, $fournisseur_ids[2]],
    ['Lait Centrale 1L', $categorie_ids['Produits frais'], 6.50, 9.00, 70, $fournisseur_ids[2]],
    ['Fromage Kiri 8 portions', $categorie_ids['Produits frais'], 12.00, 16.00, 40, $fournisseur_ids[2]],
    ['Beurre 250g', $categorie_ids['Produits frais'], 15.00, 20.00, 35, $fournisseur_ids[2]],
    ['Œufs (douzaine)', $categorie_ids['Produits frais'], 18.00, 24.00, 25, $fournisseur_ids[2]],
    
    // Sucreries
    ['Chocolat Milka 100g', $categorie_ids['Sucreries'], 6.00, 8.50, 150, $fournisseur_ids[3]],
    ['Chocolat Cadbury 100g', $categorie_ids['Sucreries'], 6.50, 9.00, 140, $fournisseur_ids[3]],
    ['Bonbons Haribo 200g', $categorie_ids['Sucreries'], 5.00, 7.50, 130, $fournisseur_ids[3]],
    ['Chewing-gum Mentos', $categorie_ids['Sucreries'], 3.00, 4.50, 200, $fournisseur_ids[3]],
    ['Barre chocolatée Snickers', $categorie_ids['Sucreries'], 4.00, 6.00, 180, $fournisseur_ids[3]],
    ['Barre chocolatée Twix', $categorie_ids['Sucreries'], 4.00, 6.00, 170, $fournisseur_ids[3]],
    ['Bonbons Tic Tac', $categorie_ids['Sucreries'], 4.50, 6.50, 160, $fournisseur_ids[3]],
    
    // Conserves
    ['Thon en conserve 160g', $categorie_ids['Conserves'], 8.00, 11.00, 50, $fournisseur_ids[4]],
    ['Sardines à l\'huile 125g', $categorie_ids['Conserves'], 5.00, 7.00, 60, $fournisseur_ids[4]],
    ['Haricots verts 400g', $categorie_ids['Conserves'], 6.00, 8.50, 45, $fournisseur_ids[4]],
    ['Pois chiches 400g', $categorie_ids['Conserves'], 5.50, 7.50, 40, $fournisseur_ids[4]],
    ['Tomates pelées 400g', $categorie_ids['Conserves'], 4.50, 6.50, 55, $fournisseur_ids[4]],
    
    // Hygiène
    ['Savon de Marseille 200g', $categorie_ids['Hygiène'], 3.50, 5.00, 80, $fournisseur_ids[5]],
    ['Shampooing Head & Shoulders 400ml', $categorie_ids['Hygiène'], 25.00, 35.00, 30, $fournisseur_ids[5]],
    ['Dentifrice Colgate 100ml', $categorie_ids['Hygiène'], 8.00, 12.00, 50, $fournisseur_ids[5]],
    ['Papier toilette 4 rouleaux', $categorie_ids['Hygiène'], 12.00, 18.00, 40, $fournisseur_ids[5]],
    ['Serviettes hygiéniques', $categorie_ids['Hygiène'], 15.00, 22.00, 35, $fournisseur_ids[5]],
    
    // Petit-déjeuner
    ['Céréales Corn Flakes 500g', $categorie_ids['Petit-déjeuner'], 18.00, 25.00, 40, $fournisseur_ids[6]],
    ['Céréales Chocapic 375g', $categorie_ids['Petit-déjeuner'], 20.00, 28.00, 35, $fournisseur_ids[6]],
    ['Miel 500g', $categorie_ids['Petit-déjeuner'], 25.00, 35.00, 25, $fournisseur_ids[6]],
    ['Confiture Bonne Maman 370g', $categorie_ids['Petit-déjeuner'], 15.00, 22.00, 30, $fournisseur_ids[6]],
    ['Pain de mie 400g', $categorie_ids['Petit-déjeuner'], 4.50, 6.50, 20, $fournisseur_ids[6]]
];

$produit_ids = [];
foreach ($produits as $prod) {
    $stmt = mysqli_prepare($conn, "INSERT INTO produits (nom, id_categorie, prix_achat, prix_vente, quantite_stock, fournisseur_id) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE nom=nom");
    mysqli_stmt_bind_param($stmt, "sidddi", $prod[0], $prod[1], $prod[2], $prod[3], $prod[4], $prod[5]);
    mysqli_stmt_execute($stmt);
    $produit_ids[] = mysqli_insert_id($conn);
    if (end($produit_ids) == 0) {
        $result = mysqli_query($conn, "SELECT id FROM produits WHERE nom = '{$prod[0]}'");
        $row = mysqli_fetch_assoc($result);
        $produit_ids[count($produit_ids) - 1] = $row['id'];
    }
    mysqli_stmt_close($stmt);
}
echo "✅ Produits insérés (" . count($produit_ids) . " produits)<br>";

// =====================================================
// Clients (prénoms variés - diversité culturelle)
// =====================================================
$clients = [
    ['Marie Dupont', '0612-345678', 'marie.dupont@email.com', 'Casablanca, Hay Hassani'],
    ['Jean Martin', '0612-456789', 'jean.martin@email.com', 'Rabat, Agdal'],
    ['Sofia Russo', '0612-567890', 'sofia.russo@email.com', 'Marrakech, Guéliz'],
    ['Liam O\'Connor', '0612-678901', 'liam.oconnor@email.com', 'Fès, Centre-ville'],
    ['Amina Diallo', '0612-789012', 'amina.diallo@email.com', 'Tanger, Marshan'],
    ['Carlos Mendes', '0612-890123', 'carlos.mendes@email.com', 'Agadir, Hay Mohammadi'],
    ['Jin Park', '0612-901234', 'jin.park@email.com', 'Meknès, Médina'],
    ['Olga Ivanova', '0613-012345', 'olga.ivanova@email.com', 'Casablanca, Maarif'],
    ['David Cohen', '0613-123456', 'david.cohen@email.com', 'Rabat, Hay Riad'],
    ['Isabella Rodriguez', '0613-234567', 'isabella.rodriguez@email.com', 'Marrakech, Hivernage'],
    ['Noah Williams', '0613-345678', 'noah.williams@email.com', 'Fès, Jnan El Ouard'],
    ['Fatima Benali', '0613-456789', 'fatima.benali@email.com', 'Tanger, Beni Makada'],
    ['Hiroshi Tanaka', '0613-567890', 'hiroshi.tanaka@email.com', 'Agadir, Talborjt'],
    ['Elena Popescu', '0613-678901', 'elena.popescu@email.com', 'Meknès, Zerhoun'],
    ['Ahmed Hassan', '0613-789012', 'ahmed.hassan@email.com', 'Casablanca, Anfa'],
    ['Lucas Schneider', '0613-890123', 'lucas.schneider@email.com', 'Rabat, Hay Riad'],
    ['Emma Johansson', '0613-901234', 'emma.johansson@email.com', 'Marrakech, Sidi Ghanem'],
    ['Victor Silva', '0614-012345', 'victor.silva@email.com', 'Fès, Saiss'],
    ['Sara Haddad', '0614-123456', 'sara.haddad@email.com', 'Tanger, Charf'],
    ['Arjun Patel', '0614-234567', 'arjun.patel@email.com', 'Agadir, Inezgane']
];

$client_ids = [];
foreach ($clients as $client) {
    $stmt = mysqli_prepare($conn, "INSERT INTO clients (nom, telephone, email, adresse) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE nom=nom");
    mysqli_stmt_bind_param($stmt, "ssss", $client[0], $client[1], $client[2], $client[3]);
    mysqli_stmt_execute($stmt);
    $client_ids[] = mysqli_insert_id($conn);
    if (end($client_ids) == 0) {
        $result = mysqli_query($conn, "SELECT id FROM clients WHERE nom = '{$client[0]}'");
        $row = mysqli_fetch_assoc($result);
        $client_ids[count($client_ids) - 1] = $row['id'];
    }
    mysqli_stmt_close($stmt);
}
echo "✅ Clients insérés (" . count($client_ids) . " clients)<br>";

// =====================================================
// Vérifier qu'un vendeur existe (id_utilisateur = 2)
// =====================================================
$result = mysqli_query($conn, "SELECT id FROM utilisateurs WHERE id = 2");
if (mysqli_num_rows($result) == 0) {
    // Créer un vendeur par défaut
    $stmt = mysqli_prepare($conn, "INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES ('Vendeur 1', 'vendeur1@epicerie.com', MD5('vendeur123'), 'vendeur')");
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
$id_vendeur = 2;

// =====================================================
// Achats (Commandes fournisseurs) - 15 achats
// =====================================================
$achats = [
    [1, 30, 850.00, [[10, 50, 5.00], [11, 40, 5.00], [12, 30, 6.00]]],
    [2, 28, 1200.00, [[1, 100, 4.50], [2, 50, 8.00], [3, 80, 4.50]]],
    [3, 25, 650.00, [[18, 30, 8.00], [19, 25, 8.50], [20, 40, 6.50]]],
    [4, 22, 950.00, [[24, 80, 6.00], [25, 70, 6.50], [26, 60, 4.00]]],
    [5, 20, 550.00, [[30, 30, 8.00], [31, 35, 5.00], [32, 25, 6.00]]],
    [6, 18, 800.00, [[35, 20, 3.50], [36, 15, 25.00], [37, 25, 8.00]]],
    [7, 15, 1100.00, [[40, 20, 18.00], [41, 15, 20.00], [42, 12, 25.00]]],
    [2, 12, 900.00, [[4, 60, 4.00], [5, 55, 4.00], [6, 80, 3.00]]],
    [1, 10, 700.00, [[13, 40, 4.50], [14, 35, 8.00], [15, 30, 6.00]]],
    [3, 8, 600.00, [[21, 20, 12.00], [22, 15, 15.00], [23, 10, 18.00]]],
    [4, 6, 750.00, [[27, 50, 4.00], [28, 45, 4.00], [29, 40, 4.50]]],
    [5, 5, 500.00, [[33, 20, 5.50], [34, 25, 4.50]]],
    [6, 4, 650.00, [[38, 20, 12.00], [39, 15, 15.00]]],
    [7, 3, 550.00, [[43, 12, 15.00], [44, 10, 4.50]]],
    [2, 1, 1000.00, [[7, 30, 6.00], [8, 20, 12.00], [9, 15, 35.00]]]
];

foreach ($achats as $achat) {
    $date_achat = date('Y-m-d H:i:s', strtotime("-{$achat[1]} days"));
    $stmt = mysqli_prepare($conn, "INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isd", $achat[0], $date_achat, $achat[2]);
    mysqli_stmt_execute($stmt);
    $id_achat = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    foreach ($achat[3] as $detail) {
        $id_prod = $produit_ids[$detail[0] - 1];
        $stmt = mysqli_prepare($conn, "INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiid", $id_achat, $id_prod, $detail[1], $detail[2]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Mettre à jour le stock
        mysqli_query($conn, "UPDATE produits SET quantite_stock = quantite_stock + {$detail[1]}, prix_achat = {$detail[2]} WHERE id = $id_prod");
    }
}
echo "✅ Achats insérés (15 achats)<br>";

// =====================================================
// Ventes - 25 ventes réparties sur les 30 derniers jours
// =====================================================
$ventes = [
    [1, 29, 45.50, [[1, 3, 6.00], [10, 2, 7.50], [24, 1, 8.50]]],
    [2, 28, 32.00, [[2, 2, 11.00], [18, 1, 11.00]]],
    [3, 27, 67.50, [[24, 2, 8.50], [25, 3, 9.00], [26, 2, 6.00], [27, 1, 8.50]]],
    [4, 26, 28.50, [[4, 2, 5.50], [5, 2, 5.50], [6, 1, 4.50]]],
    [5, 25, 55.00, [[18, 2, 11.00], [19, 1, 12.00], [20, 2, 9.00], [21, 1, 16.00]]],
    [6, 24, 42.00, [[30, 2, 11.00], [31, 2, 7.00], [32, 1, 8.50]]],
    [7, 23, 38.50, [[11, 3, 7.50], [12, 2, 8.50]]],
    [8, 22, 89.00, [[24, 3, 8.50], [25, 2, 9.00], [36, 1, 35.00], [37, 2, 12.00]]],
    [9, 21, 51.00, [[1, 4, 6.00], [3, 3, 6.00], [10, 2, 7.50]]],
    [10, 20, 64.50, [[40, 1, 25.00], [41, 1, 28.00], [42, 1, 35.00]]],
    [11, 18, 35.00, [[13, 2, 6.50], [14, 1, 11.00], [15, 1, 8.50]]],
    [12, 17, 47.50, [[2, 2, 11.00], [6, 3, 4.50], [7, 1, 8.50]]],
    [13, 16, 72.00, [[18, 3, 11.00], [19, 2, 12.00], [20, 1, 9.00], [21, 1, 16.00]]],
    [14, 15, 29.00, [[4, 3, 5.50], [5, 2, 5.50]]],
    [15, 14, 58.50, [[24, 2, 8.50], [25, 2, 9.00], [26, 3, 6.00], [27, 1, 8.50]]],
    [16, 12, 41.50, [[11, 2, 7.50], [12, 2, 8.50], [13, 1, 6.50]]],
    [17, 11, 95.00, [[1, 5, 6.00], [2, 3, 11.00], [36, 1, 35.00], [37, 1, 12.00]]],
    [18, 10, 33.50, [[30, 1, 11.00], [31, 2, 7.00], [32, 1, 8.50]]],
    [19, 9, 52.00, [[18, 2, 11.00], [19, 1, 12.00], [20, 2, 9.00]]],
    [20, 8, 44.00, [[24, 2, 8.50], [25, 1, 9.00], [26, 2, 6.00], [27, 1, 8.50]]],
    [1, 7, 38.00, [[4, 3, 5.50], [5, 2, 5.50], [6, 1, 4.50]]],
    [2, 6, 61.50, [[40, 1, 25.00], [41, 1, 28.00], [43, 1, 22.00]]],
    [3, 5, 49.00, [[1, 4, 6.00], [3, 3, 6.00], [10, 2, 7.50]]],
    [4, 3, 56.00, [[18, 2, 11.00], [19, 2, 12.00], [20, 1, 9.00]]],
    [5, 1, 42.50, [[11, 3, 7.50], [12, 2, 8.50]]]
];

foreach ($ventes as $vente) {
    $date_vente = date('Y-m-d H:i:s', strtotime("-{$vente[1]} days"));
    $id_client = $client_ids[$vente[0] - 1];
    
    $stmt = mysqli_prepare($conn, "INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iisd", $id_client, $id_vendeur, $date_vente, $vente[2]);
    mysqli_stmt_execute($stmt);
    $id_vente = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    foreach ($vente[3] as $detail) {
        $id_prod = $produit_ids[$detail[0] - 1];
        $stmt = mysqli_prepare($conn, "INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiid", $id_vente, $id_prod, $detail[1], $detail[2]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Mettre à jour le stock
        mysqli_query($conn, "UPDATE produits SET quantite_stock = quantite_stock - {$detail[1]} WHERE id = $id_prod");
    }
}
echo "✅ Ventes insérées (25 ventes)<br>";

// Réactiver les vérifications de clés étrangères
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

echo "<h3 style='color: green;'>✅ Installation terminée avec succès !</h3>";
echo "<p><strong>Résumé :</strong></p>";
echo "<ul>";
echo "<li>" . count($categorie_ids) . " catégories</li>";
echo "<li>" . count($fournisseur_ids) . " fournisseurs</li>";
echo "<li>" . count($produit_ids) . " produits</li>";
echo "<li>" . count($client_ids) . " clients</li>";
echo "<li>15 achats</li>";
echo "<li>25 ventes</li>";
echo "</ul>";
echo "<p><a href='index.php'>Retour au tableau de bord</a></p>";
?>

