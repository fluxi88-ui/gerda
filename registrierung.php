<?php
session_start();

// Nur verarbeiten wenn POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registrierung_form.php');
    exit;
}

// Eingaben bereinigen
$anrede    = trim($_POST['anrede']    ?? '');
$vorname   = trim($_POST['vorname']   ?? '');
$nachname  = trim($_POST['nachname']  ?? '');
$geburtstag = trim($_POST['geburtstag'] ?? '');
$benutzername = trim($_POST['benutzername'] ?? '');
$email     = trim($_POST['email']     ?? '');
$passwort  = trim($_POST['passwort']  ?? '');
$passwort2 = trim($_POST['passwort2'] ?? '');
$strasse   = trim($_POST['strasse']   ?? '');
$hausnummer = trim($_POST['hausnummer'] ?? '');
$wohnort   = trim($_POST['wohnort']   ?? '');
$plz       = trim($_POST['plz']       ?? '');
$bundesland = trim($_POST['bundesland'] ?? '');
$telefon   = trim($_POST['telefon']   ?? '');

// Pflichtfelder prüfen
$fehler = [];

if (empty($anrede))   $fehler[] = 'Bitte eine Anrede wählen.';
if (empty($vorname))  $fehler[] = 'Vorname ist ein Pflichtfeld.';
if (empty($nachname)) $fehler[] = 'Nachname ist ein Pflichtfeld.';
if (empty($passwort)) $fehler[] = 'Passwort ist ein Pflichtfeld.';
if (empty($email)) $fehler[] = 'E-Mail ist ein Pflichtfeld.';
if (empty($plz)) $fehler[] = 'PLZ ist ein Pflichtfeld.';
if (empty($wohnort))  $fehler[] = 'Wohnort ist ein Pflichtfeld.';

if ($passwort !== $passwort2) {
    $fehler[] = 'Die Passwörter stimmen nicht überein.';
}

if (strlen($passwort) < 8) {
    $fehler[] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $fehler[] = 'Bitte eine gültige E-Mail-Adresse eingeben.';
}

if (!empty($plz) && !preg_match('/^[0-9]{4}$/', $plz)) {
    $fehler[] = 'Bitte eine gültige österreichische Postleitzahl eingeben (4 Stellen).';
}

if (!empty($fehler)) {
    $_SESSION['fehler'] = $fehler;
    $_SESSION['reg_daten'] = [
        'anrede'      => $anrede,
        'vorname'     => $vorname,
        'nachname'    => $nachname,
        'geburtstag'  => $geburtstag,
        'benutzername'=> $benutzername,
        'email'       => $email,
        'strasse'     => $strasse,
        'hausnummer'  => $hausnummer,
        'wohnort'     => $wohnort,
        'plz'         => $plz,
        'bundesland'  => $bundesland,
        'telefon'     => $telefon,
    ];
    header('Location: registrierung_form.php');
    exit;
}

// Dokument-Datei einlesen (optional)
$dokument_db = null;
if (!empty($_FILES['dokument']['tmp_name']) && is_uploaded_file($_FILES['dokument']['tmp_name'])) {
    $erlaubte_typen = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($_FILES['dokument']['type'], $erlaubte_typen)) {
        $_SESSION['fehler'] = ['Nur PDF, JPG, PNG oder GIF erlaubt.'];
        header('Location: registrierung_form.php');
        exit;
    }
    if ($_FILES['dokument']['size'] > 2 * 1024 * 1024) {
        $_SESSION['fehler'] = ['Datei darf maximal 2 MB groß sein.'];
        header('Location: registrierung_form.php');
        exit;
    }
    $dokument_db = file_get_contents($_FILES['dokument']['tmp_name']);
}

require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    $_SESSION['fehler'] = ['Datenbankverbindung fehlgeschlagen. Bitte später erneut versuchen.'];
    header('Location: registrierung_form.php');
    exit;
}

// E-Mail auf Duplikat prüfen
if (!empty($email)) {
    $stmt = $pdo->prepare('SELECT acc_id FROM account WHERE e_mail = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['fehler'] = ['Diese E-Mail-Adresse ist bereits registriert.'];
        header('Location: registrierung_form.php');
        exit;
    }
}

// Passwort hashen (Pepper + bcrypt Salt)
$passwort_hash = password_hash($passwort . PEPPER, PASSWORD_BCRYPT);

// Geburtstag: leerer String → NULL
$geburtstag_db = (!empty($geburtstag)) ? $geburtstag : null;
$plz_db        = (!empty($plz)) ? (int)$plz : null;

try {
    $pdo->beginTransaction();

    // Kunde anlegen
    $stmt = $pdo->prepare('INSERT INTO kunde (anrede, vorname, nachname, geburtstag, telefon, ort, plz, straße)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$anrede, $vorname, $nachname, $geburtstag_db, $telefon, $wohnort, $plz_db, $strasse]);
    $kn_id = $pdo->lastInsertId();


    $stmt = $pdo->prepare('INSERT INTO account (kn_id, benutzername, passwort, e_mail)
                           VALUES (?, ?, ?, ?)');
    $stmt->execute([$kn_id, $benutzername, $passwort_hash, $email]);

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['fehler'] = ['DB-Fehler: ' . $e->getMessage()];
    header('Location: registrierung_form.php');
    exit;
}

$_SESSION['erfolg'] = 'Registrierung erfolgreich! Sie können sich jetzt anmelden.';
header('Location: login.html');
exit;
