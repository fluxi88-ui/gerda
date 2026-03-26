<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$username  = trim($_POST['username']  ?? '');
$passwort  = trim($_POST['password']  ?? '');

if (empty($username) || empty($passwort)) {
    header('Location: login.html?error=1');
    exit;
}

$host   = 'localhost';
$dbname = 'Konvoltic';
$dbuser = 'root';
$dbpass = '';

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

// Suche per Benutzername ODER E-Mail
$stmt = $pdo->prepare('SELECT acc_id, passwort FROM account WHERE benutzername = ? OR e_mail = ? LIMIT 1');
$stmt->execute([$username, $username]);
$account = $stmt->fetch();

if (!$account || !password_verify($passwort . PEPPER, $account['passwort'])) {
    header('Location: login.html?error=1');
    exit;
}

// Login erfolgreich
$_SESSION['acc_id']      = $account['acc_id'];
$_SESSION['benutzername'] = $username;

header('Location: Website.html');
exit;