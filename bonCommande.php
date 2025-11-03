<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: auth.php");
    exit();
}

require(__DIR__ . '/fpdf/fpdf.php');
include('db_conn.php');

if (!isset($_GET['id'])) {
    die("ID de commande manquant");
}

$id_achat = intval($_GET['id']);

// --- Récupération des infos de la commande ---
$sql = "SELECT a.id, a.date_achat, a.montant_total,
               f.nom AS fournisseur, f.email, f.telephone
        FROM achats a
        JOIN fournisseurs f ON a.id_fournisseur = f.id
        WHERE a.id = $id_achat";
$res = mysqli_query($conn, $sql);
$achat = mysqli_fetch_assoc($res);
if (!$achat) die("Commande introuvable.");

// --- Détails produits ---
$details = mysqli_query($conn, "
    SELECT p.nom, d.quantite, d.prix_achat
    FROM details_achat d
    JOIN produits p ON d.id_produit = p.id
    WHERE d.id_achat = $id_achat
");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Bon de Commande N ' . $achat['id'], 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Date : ' . $achat['date_achat'], 0, 1);
$pdf->Ln(5);

// --- Fournisseur ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Fournisseur :', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Nom : ' . $achat['fournisseur'], 0, 1);
$pdf->Cell(0, 8, 'Email : ' . $achat['email'], 0, 1);
$pdf->Cell(0, 8, 'Telephone : ' . $achat['telephone'], 0, 1);
$pdf->Ln(8);

// --- Tableau Produits ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(90, 10, 'Produit', 1);
$pdf->Cell(30, 10, 'Quantite', 1);
$pdf->Cell(35, 10, 'Prix (EUR)', 1);
$pdf->Cell(35, 10, 'Sous-total (EUR)', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
$total = 0;
while ($d = mysqli_fetch_assoc($details)) {
    $sous_total = $d['quantite'] * $d['prix_achat'];
    $total += $sous_total;
    $pdf->Cell(90, 10, $d['nom'], 1);
    $pdf->Cell(30, 10, $d['quantite'], 1, 0, 'C');
    $pdf->Cell(35, 10, number_format($d['prix_achat'], 2, ',', ' '), 1, 0, 'R');
    $pdf->Cell(35, 10, number_format($sous_total, 2, ',', ' '), 1, 0, 'R');
    $pdf->Ln();
}

// --- Total général ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(155, 10, 'Total', 1);
$pdf->Cell(35, 10, number_format($total, 2, ',', ' ') . ' EUR', 1, 0, 'R');

$pdf->Ln(20);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Document genere automatiquement par PGI Epicerie', 0, 1, 'C');

// --- Sortie du PDF ---
$pdf->Output("I", "Bon_Commande_" . $achat['id'] . ".pdf");
?>
