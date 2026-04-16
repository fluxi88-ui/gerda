<?php
// login.php - hier passiert die ganze login-magie
// der user gibt passwort ein, wir checken's, fertig (hoffentlich)
session_start();

// wenn jemand versucht die seite direkt aufzurufen (ohne formular) -> weg damit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

// benutzername und passwort aus dem formular holen
$username  = trim($_POST['username']  ?? '');
$passwort  = trim($_POST['password']  ?? '');

// wenn eines davon leer ist können wir gleich aufhören
if (empty($username) || empty($passwort)) {
    header('Location: login.html?error=1');
    exit;
}

$host   = 'moneyboykonvoltic.mysql.database.azure.com';
$dbname = 'konvolticdatenbank';
$dbuser = 'Einhorn';
$dbpass = 'H3l3N4!!!!';

// der pepper ist ein geheimes extra-gewürz für das passwort-hashing
// damit auch wenn die db geklaut wird niemand einfach so die passwörter knackt
define('PEPPER', 'K0nv0lt!c#P3pp3r_2026');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    header('Location: login.html?error=2');
    exit;
}

// suche per benutzername ODER e-mail - der user kann beides verwenden zum einloggen
// sehr fancy, sehr cool, sehr aufwendig
$stmt = $pdo->prepare('SELECT acc_id, passwort FROM account WHERE benutzername = ? OR e_mail = ? LIMIT 1');
$stmt->execute([$username, $username]);
$account = $stmt->fetch();

// passwort prüfen - wenn kein account oder falsches passwort: error
// wir sagen nicht ob user oder passwort falsch war - sicherheit!!
if (!$account || !password_verify($passwort . PEPPER, $account['passwort'])) {
    header('Location: login.html?error=1');
    exit;
}

// login erfolgreich!! user-id in die session speichern
$_SESSION['acc_id']      = $account['acc_id'];
$_SESSION['benutzername'] = $username;

// und ab zur hauptseite!
header('Location: Website.html');
exit;