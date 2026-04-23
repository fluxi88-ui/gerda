<?php
// hallo! das hier ist die seite die die daten vom formular entgegennimmt und speichert
// klingt einfach, ist aber irgendwie kompliziert geworden lol
session_start();

// wenn der user nicht eingeloggt ist schicken wir ihn weg, cya!
if (empty($_SESSION['acc_id'])) {
    header('Location: login.html');
    exit;
}

// wenn jemand versucht die seite direkt aufzurufen ohne formular -> nope
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: account_form.php');
    exit;
}

// alle felder aus dem formular holen und trimmen (leerzeichen am rand sind ekelig)
$vorname      = trim($_POST['vorname']      ?? '');
$nachname     = trim($_POST['nachname']     ?? '');
$anrede       = trim($_POST['anrede']       ?? '');
$geburtstag   = trim($_POST['geburtstag']   ?? '');
$telefon      = trim($_POST['telefon']      ?? '');
$strasse      = trim($_POST['strasse']      ?? '');
$ort          = trim($_POST['ort']          ?? '');
$plz          = trim($_POST['plz']          ?? '');
$email        = trim($_POST['email']        ?? '');
$passwort_neu = trim($_POST['passwort_neu'] ?? '');
$passwort_neu2 = trim($_POST['passwort_neu2'] ?? ''); // ja das 2 am ende ist absicht

// hier wird geprüft ob der user keinen blödsinn eingegeben hat
$fehler = [];
if (empty($vorname))  $fehler[] = 'Vorname darf nicht leer sein.'; // wer hat denn keinen vornamen??
if (empty($nachname)) $fehler[] = 'Nachname darf nicht leer sein.';
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $fehler[] = 'Ungültige E-Mail-Adresse.'; // "test@" ist keine email!!
if (!empty($plz) && !preg_match('/^[0-9]{4}$/', $plz)) $fehler[] = 'PLZ muss 4 Stellen haben.';
if (!empty($passwort_neu) && strlen($passwort_neu) < 8) $fehler[] = 'Neues Passwort muss mindestens 8 Zeichen haben.';
if ($passwort_neu !== $passwort_neu2) $fehler[] = 'Die neuen Passwörter stimmen nicht überein.'; // tipp-fehler lol

// wenn irgendwas nicht stimmt -> fehler in die session und zurück zum formular
if (!empty($fehler)) {
    $_SESSION['acc_fehler'] = implode(' | ', $fehler);
    header('Location: account_edit.php');
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
    $_SESSION['acc_fehler'] = 'Datenbankfehler.';
    header('Location: account_form.php');
    exit;
}

// kn_id holen - die brauchen wir um die kunde-tabelle zu updaten
    // (account und kunde sind zwei tabellen, don't ask)
$stmt = $pdo->prepare('SELECT kn_id FROM account WHERE acc_id = ?');
$stmt->execute([$_SESSION['acc_id']]);
$row = $stmt->fetch();
$kn_id = $row['kn_id'];

$geburtstag_db = !empty($geburtstag) ? $geburtstag : null;
$plz_db        = !empty($plz) ? (int)$plz : null;

try {
    $pdo->beginTransaction();

    $pdo->prepare('UPDATE kunde SET anrede=?, vorname=?, nachname=?, geburtstag=?, telefon=?, ort=?, plz=?, straße=? WHERE kn_id=?')
        ->execute([$anrede, $vorname, $nachname, $geburtstag_db, $telefon, $ort, $plz_db, $strasse, $kn_id]);

    $email_db = !empty($email) ? $email : null;
    $pdo->prepare('UPDATE account SET e_mail=? WHERE acc_id=?')
        ->execute([$email_db, $_SESSION['acc_id']]);

    if (!empty($passwort_neu)) {
        $hash = password_hash($passwort_neu . PEPPER, PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE account SET passwort=? WHERE acc_id=?')
            ->execute([$hash, $_SESSION['acc_id']]);
    }

    // Profilbild – nur aktualisieren wenn eine neue Datei hochgeladen wurde
    if (!empty($_FILES['profilbild']['tmp_name']) && is_uploaded_file($_FILES['profilbild']['tmp_name'])) {
        $erlaubte_typen = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($_FILES['profilbild']['tmp_name']);
        if (!in_array($mime, $erlaubte_typen)) {
            $pdo->rollBack();
            $_SESSION['acc_fehler'] = 'Nur JPG, PNG, GIF oder WEBP als Profilbild erlaubt.';
            header('Location: account_edit.php');
            exit;
        }
        if ($_FILES['profilbild']['size'] > 2 * 1024 * 1024) {
            $pdo->rollBack();
            $_SESSION['acc_fehler'] = 'Profilbild darf maximal 2 MB groß sein.';
            header('Location: account_edit.php');
            exit;
        }
        $bild = file_get_contents($_FILES['profilbild']['tmp_name']);
        $pdo->prepare('UPDATE account SET profilbild=? WHERE acc_id=?')
            ->execute([$bild, $_SESSION['acc_id']]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['acc_fehler'] = 'Speichern fehlgeschlagen.';
    header('Location: account_edit.php');
    exit;
}

// alles gut!! user zurück zur kontoübersicht schicken
$_SESSION['acc_erfolg'] = 'Daten erfolgreich gespeichert.';
header('Location: account_form.php');
exit;
