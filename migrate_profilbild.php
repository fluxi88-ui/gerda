<?php
require_once __DIR__ . '/config.php';
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // profilbild Spalte
    $pdo->exec('ALTER TABLE account ADD COLUMN IF NOT EXISTS profilbild LONGBLOB');
    echo '✓ Spalte profilbild OK<br>';

    // login_log Tabelle
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_log (
        log_id      INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
        benutzername VARCHAR(150) NOT NULL,
        ip           VARCHAR(45)  NOT NULL,
        zeitpunkt    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        erfolgreich  TINYINT(1)   NOT NULL,
        user_agent   VARCHAR(500)
    )");
    echo '✓ Tabelle login_log OK<br>';

    echo '<br><strong>Migration abgeschlossen.</strong>';
} catch (PDOException $e) {
    echo 'Fehler: ' . $e->getMessage();
}
