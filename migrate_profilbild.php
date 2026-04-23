<?php
require_once __DIR__ . '/config.php';
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec('ALTER TABLE account ADD COLUMN IF NOT EXISTS profilbild LONGBLOB');
    echo 'Spalte profilbild erfolgreich hinzugefügt (oder war bereits vorhanden).';
} catch (PDOException $e) {
    echo 'Fehler: ' . $e->getMessage();
}
