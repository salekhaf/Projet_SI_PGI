<?php
/**
 * Export CSV via PDO
 * Compatible PostgreSQL et MySQL
 */

function export_excel_pdo(PDO $pdo, string $query, array $params, string $filename, array $headers)
{
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename={$filename}.csv");

    $output = fopen('php://output', 'w');

    // Écrit les entêtes du CSV
    fputcsv($output, $headers, ';');

    // Préparation
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    // Export ligne par ligne
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row, ';');
    }

    fclose($output);
    exit;
}
