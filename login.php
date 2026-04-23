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

require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    header('Location: login.html?error=2');
    exit;
}

// IP ermitteln (auch hinter Proxies / Azure)
function get_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return 'unbekannt';
}

function log_versuch(PDO $pdo, string $user, bool $erfolg): void {
    $ip         = get_ip();
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
    $pdo->prepare('INSERT INTO login_log (benutzername, ip, erfolgreich, user_agent) VALUES (?, ?, ?, ?)')
        ->execute([$user, $ip, $erfolg ? 1 : 0, $user_agent]);
}

// suche nur per benutzername
$stmt = $pdo->prepare('SELECT acc_id, passwort FROM account WHERE benutzername = ? LIMIT 1');
$stmt->execute([$username]);
$account = $stmt->fetch();

// passwort prüfen - wenn kein account oder falsches passwort: error
// wir sagen nicht ob user oder passwort falsch war - sicherheit!!
if (!$account || !password_verify($passwort . PEPPER, $account['passwort'])) {
    log_versuch($pdo, $username, false);
    header('Location: login.html?error=1');
    exit;
}

// login erfolgreich!! user-id in die session speichern
log_versuch($pdo, $username, true);
$_SESSION['acc_id']      = $account['acc_id'];
$_SESSION['benutzername'] = $username;

// und ab zur hauptseite!
header('Location: Index.html');
exit;