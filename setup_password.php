<?php

require_once 'config.php';

// Setam parola '1234'
$parola_universala = '1234';

$hash_corect = password_hash($parola_universala, PASSWORD_BCRYPT);

try {

    $stmt = $pdo->prepare("UPDATE utilizatori SET parola = :parola");
    $stmt->execute(['parola' => $hash_corect]);
    
    $randuri_modificate = $stmt->rowCount();

    echo "<div style='font-family: Arial; padding: 20px; text-align: center;'>";
    echo "<h1 style='color: green;'>SUCCES!</h1>";
    echo "<p>Am modificat <b>{$randuri_modificate}</b> conturi în baza de date.</p>";
    echo "<p>Acum parola este <strong>1234</strong> pentru TOȚI utilizatorii (admin, popescu.ion, petrea.andrei, etc).</p>";
    echo "<br><br><a href='index.php' style='padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>Mergi la Autentificare</a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "Eroare: " . $e->getMessage();
}
?>