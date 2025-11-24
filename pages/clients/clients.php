<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

require '../../config/db_conn.php';
require '../../includes/historique_helper.php';

$role = $_SESSION['role'];
$id_utilisateur = $_SESSION['id_utilisateur'];
$peut_modifier = in_array($role, ['admin', 'vendeur']);
$message = "";

// --- PAGINATION ---
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// --- RECHERCHE ---
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';

/* ============================================================
   AJOUT D'UN CLIENT
============================================================ */
if (isset($_POST['ajouter'])) {

    $nom = trim($_POST['nom']);
    $tel = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    if ($nom === "") {
        $message = "⚠️ Le nom du client est obligatoire.";
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO clients (nom, telephone, email, adresse)
            VALUES (:nom, :tel, :email, :adresse)
        ");

        if ($stmt->execute([
            ':nom' => $nom,
            ':tel' => $tel,
            ':email' => $email,
            ':adresse' => $adresse
        ])) {

            $id_client = db_last_id($pdo);

            enregistrer_historique($pdo, $id_utilisateur, 'ajout', 'clients', $id_client, 
                "Ajout du client : $nom");

            $message = "✅ Client ajouté avec succès.";
        } else {
            $message = "❌ Erreur lors de l'ajout du client.";
        }
    }
}

/* ============================================================
   MODIFICATION D'UN CLIENT
============================================================ */
if ($peut_modifier && isset($_POST['modifier'])) {

    $id = intval($_POST['id']);
    $nom = trim($_POST['nom']);
    $tel = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    // Ancienne valeur pour historique
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $ancien = $stmt->fetch();

    $stmt = $pdo->prepare("
        UPDATE clients
        SET nom = :nom, telephone = :tel, email = :email, adresse = :adresse
        WHERE id = :id
    ");

    if ($stmt->execute([
        ':nom' => $nom,
        ':tel' => $tel,
        ':email' => $email,
        ':adresse' => $adresse,
        ':id' => $id
    ])) {
        enregistrer_historique(
            $pdo, $id_utilisateur, 'modification', 'clients', $id, 
            "Modification du client : $nom", 
            $ancien, 
            ['nom' => $nom, 'telephone' => $tel, 'email' => $email]
        );

        $message = "✅ Client modifié avec succès.";
    } else {
        $message = "❌ Erreur lors de la modification.";
    }
}

/* ============================================================
   SUPPRESSION D'UN CLIENT
============================================================ */
if ($peut_modifier && isset($_GET['supprimer'])) {

    $id = intval($_GET['supprimer']);

    // Valeur avant suppression
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $client = $stmt->fetch();

    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = :id");
    $stmt->execute([':id' => $id]);

    enregistrer_historique(
        $pdo, $id_utilisateur, 'suppression', 'clients', $id, 
        "Suppression du client : " . $client['nom'], 
        $client, null
    );

    header("Location: clients.php");
    exit();
}

/* ============================================================
   EXPORT CSV
============================================================ */
if (isset($_GET['export']) && $_GET['export'] === 'csv') {

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=clients_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($output, ['ID', 'Nom', 'Téléphone', 'Email', 'Adresse'], ';');

    $stmt = $pdo->query("SELECT * FROM clients ORDER BY nom ASC");

    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['nom'],
            $row['telephone'],
            $row['email'],
            $row['adresse']
        ], ';');
    }

    exit();
}

/* ============================================================
   RECHERCHE + PAGINATION
============================================================ */

$where_clause = "";
$params = [];

if ($recherche !== "") {
    $where_clause = "WHERE nom ILIKE :search OR telephone ILIKE :search OR email ILIKE :search";
    $params[':search'] = "%$recherche%";
}

// TOTAL LIGNES
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM clients $where_clause");
$stmt->execute($params);
$total_rows = $stmt->fetch()['total'];
$total_pages = ceil($total_rows / $per_page);

// RÉCUP CLIENTS
$stmt = $pdo->prepare("
    SELECT * FROM clients 
    $where_clause
    ORDER BY nom ASC 
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$result = $stmt->fetchAll();

// RECUP POUR MODIFICATION
$client_edit = null;

if ($peut_modifier && isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
    $stmt->execute([':id' => intval($_GET['edit'])]);
    $client_edit = $stmt->fetch();
}
?>
