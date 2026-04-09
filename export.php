<?php
// export.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    die("Acces respins.");
}

require_once 'config.php';

$tabel = isset($_GET['tabel']) ? $_GET['tabel'] : 'studenti';

// Setam headerele pentru a forta descarcarea ca fisier CSV (deschis nativ de Excel)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=export_' . $tabel . '_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Fixam diacriticele in Excel (BOM)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

if ($tabel === 'studenti') {
    fputcsv($output, ['Nr. Matricol', 'Nume', 'Prenume', 'Specializare', 'An Studiu', 'Grupa', 'Finantare']);
    $stmt = $pdo->query("SELECT matricol, nume, prenume, specializare, an_studiu, grupa, finantare FROM studenti ORDER BY nume ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) fputcsv($output, $row);
} 
elseif ($tabel === 'profesori') {
    fputcsv($output, ['Nume', 'Prenume', 'Departament']);
    $stmt = $pdo->query("SELECT nume, prenume, departament FROM profesori ORDER BY nume ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) fputcsv($output, $row);
}
elseif ($tabel === 'discipline') {
    fputcsv($output, ['Denumire', 'An Studiu', 'Semestru', 'Credite']);
    $stmt = $pdo->query("SELECT denumire, an_studiu, semestru, credite FROM discipline ORDER BY denumire ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) fputcsv($output, $row);
}

fclose($output);
exit;
?>