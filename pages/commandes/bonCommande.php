<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

require(__DIR__ . '/fpdf/fpdf.php');
require('../../config/db_conn.php'); // Doit fournir $pdo

if (!isset($_GET['id'])) {
    die("ID de commande manquant");
}

$id_achat = intval($_GET['id']);

/* ============================================================
   RÉCUPÉRATION DE L’ACHAT
============================================================ */
$stmt = $pdo->prepare("
    SELECT a.id, a.date_achat, a.montant_total,
           f.nom AS fournisseur, f.email, f.telephone
    FROM achats a
    JOIN fournisseurs f ON a.id_fournisseur = f.id
    WHERE a.id = :id
");

$stmt->execute([':id' => $id_achat]);
$achat = $stmt->fetch();

if (!$achat) {
    die("Commande introuvable.");
}

/* ============================================================
   RÉCUPÉRATION DES DÉTAILS DE L’ACHAT
============================================================ */
$stmt = $pdo->prepare("
    SELECT p.nom, d.quantite, d.prix_achat
    FROM details_achat d
    JOIN produits p ON d.id_produit = p.id
    WHERE d.id_achat = :id
");

$stmt->execute([':id' => $id_achat]);
$details = $stmt->fetchAll();

/* ============================================================
   GÉNÉRATION DU PDF
============================================================ */
$pdf = new FPDF();
$pdf->AddPage();

// --- Titre ---
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Bon de Commande N° ' . $achat['id'], 0, 1, 'C');
$pdf->Ln(5);

// --- Date ---
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Date : ' . $achat['date_achat'], 0, 1);
$pdf->Ln(5);

// --- Fournisseur ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Fournisseur :', 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Nom : ' . $achat['fournisseur'], 0, 1);
$pdf->Cell(0, 8, 'Email : ' . $achat['email'], 0, 1);
$pdf->Cell(0, 8, 'Téléphone : ' . $achat['telephone'], 0, 1);
$pdf->Ln(8);

// --- Tableau Produits ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(90, 10, 'Produit', 1);
$pdf->Cell(30, 10, 'Quantité', 1, 0, 'C');
$pdf->Cell(35, 10, 'Prix (EUR)', 1, 0, 'R');
$pdf->Cell(35, 10, 'Sous-total', 1, 0, 'R');
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);

$total = 0;

foreach ($details as $d) {
    $sous_total = $d['quantite'] * $d['prix_achat'];
    $total += $sous_total;

    $pdf->Cell(90, 10, $d['nom'], 1);
    $pdf->Cell(30, 10, $d['quantite'], 1, 0, 'C');
    $pdf->Cell(35, 10, number_format($d['prix_achat'], 2, ',', ' '), 1, 0, 'R');
    $pdf->Cell(35, 10, number_format($sous_total, 2, ',', ' '), 1, 0, 'R');
    $pdf->Ln();
}

// --- Total ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(155, 10, 'Total', 1);
$pdf->Cell(35, 10, number_format($total, 2, ',', ' ') . ' EUR', 1, 0, 'R');

$pdf->Ln(20);

// --- Footer ---
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Document généré automatiquement par PGI Épicerie', 0, 1, 'C');

// --- Output ---
$pdf->Output("I", "Bon_Commande_" . $achat['id'] . ".pdf");
?>
