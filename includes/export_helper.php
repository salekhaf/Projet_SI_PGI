<?php
/**
 * EXPORT EXCEL via PDO
 * Génère un vrai fichier Excel compatible (format html/table)
 * fonctionne pour MySQL & PostgreSQL
 */

function export_excel_pdo(PDO $pdo, string $query, array $params, string $filename, array $headers)
{
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"{$filename}.xls\"");
    
    echo "<table border='1'>";

    // Entêtes
    echo "<tr>";
    foreach ($headers as $h) {
        echo "<th>" . htmlspecialchars($h) . "</th>";
    }
    echo "</tr>";

    // Exécution
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    // Lignes
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars($cell ?? '') . "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
    exit;
}
?>
