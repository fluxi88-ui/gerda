<?php
// das ist die bearbeitungsseite - hier kann man seine daten ändern
// (falls man zb. umgezogen ist oder den vornamen hasst)
if (session_status() === PHP_SESSION_NONE) session_start(); // session starten falls noch nicht passiert

// nicht eingeloggt? dann tschüss!
if (empty($_SESSION['acc_id'])) {
    header('Location: login.html');
    exit;
}

// datenbank zeug - klassisch localhost mit root und kein passwort, sehr sicher :)
$host   = 'moneyboykonvoltic.mysql.database.azure.com';
$dbname = 'konvolticdatenbank';
$dbuser = 'Einhorn';
$dbpass = 'H3l3N4!!!!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Datenbankfehler.');
}

// jetzt holen wir alle daten des users aus der db - benutzername, email, und ganzen kram
$stmt = $pdo->prepare('
    SELECT a.benutzername, a.e_mail,
           k.anrede, k.vorname, k.nachname, k.geburtstag,
           k.telefon, k.ort, k.plz, k.straße
    FROM account a
    JOIN kunde k ON a.kn_id = k.kn_id
    WHERE a.acc_id = ?
');
$stmt->execute([$_SESSION['acc_id']]);
$user = $stmt->fetch();

// wenn kein user gefunden wurde (zb gelöscht?) dann rauswerfen
if (!$user) {
    session_destroy();
    header('Location: login.html');
    exit;
}

// fehler aus der session holen falls beim letzten speichern was schiefgelaufen ist
$fehler = $_SESSION['acc_fehler'] ?? '';
unset($_SESSION['acc_fehler']); // gleich wieder löschen sonst bleibt der ewig

// kleine helferfunktion gegen xss - html-zeichen escaped damit niemand scripte einschleust
function esc(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Konvoltic OG | Daten bearbeiten</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="account.css">
</head>
<body>

    <nav class="top-bar">
        <div class="auth-buttons">
            <a href="account_form.php" class="home-btn">Mein Konto</a>
            <a href="logout.php" class="logout-btn">Abmelden</a>
        </div>
        <div class="meta-nav">
            <div class="custom-dropdown">
                <button class="dropdown-toggle">
                    <span>☰ Menü</span>
                    <span class="dropdown-arrow">▾</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="Website.html">🏠 Home</a></li>
                    <li><a href="Kreislauf.html">♻️ Kreislauf</a></li>
                    <li><a href="Produkte.html">📦 Produkte</a></li>
                    <li><a href="Loesungen.html">🔧 Lösungen</a></li>
                    <li><a href="Impressum.html">📄 Impressum</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header>
        <div class="logo-container">
            <a href="Website.html">
                <img src="Images/KonvolticLogobeschnitten.png" alt="Konvoltic Logo" class="logo">
            </a>
        </div>
    </header>

    <main>
        <div class="acc-page">
            <h1>Daten bearbeiten</h1>
            <p class="acc-welcome"><a href="account_form.php">← Zurück zu Mein Konto</a></p>

            <?php if ($fehler): ?>
                <div class="acc-error"><?= esc($fehler) ?></div>
            <?php endif; ?>

            <form method="post" action="account.php" enctype="multipart/form-data">

                <fieldset>
                    <legend>👤 Persönliche Daten</legend>

                    <div class="form-group">
                        <label>Anrede</label>
                        <div class="radio-group">
                            <label><input type="radio" name="anrede" value="herr" <?= $user['anrede'] === 'herr' ? 'checked' : '' ?>> Herr</label>
                            <label><input type="radio" name="anrede" value="frau" <?= $user['anrede'] === 'frau' ? 'checked' : '' ?>> Frau</label>
                            <label><input type="radio" name="anrede" value="divers" <?= $user['anrede'] === 'divers' ? 'checked' : '' ?>> Divers</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="vorname">Vorname *</label>
                        <input type="text" id="vorname" name="vorname"
                               value="<?= esc($user['vorname']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="nachname">Nachname *</label>
                        <input type="text" id="nachname" name="nachname"
                               value="<?= esc($user['nachname']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="geburtstag">Geburtstag</label>
                        <input type="date" id="geburtstag" name="geburtstag"
                               value="<?= esc($user['geburtstag'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="telefon">Telefon</label>
                        <input type="tel" id="telefon" name="telefon"
                               value="<?= esc($user['telefon'] ?? '') ?>">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>🏠 Adresse</legend>

                    <div class="form-group">
                        <label for="strasse">Straße</label>
                        <input type="text" id="strasse" name="strasse"
                               value="<?= esc($user['straße'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="ort">Wohnort</label>
                        <input type="text" id="ort" name="ort"
                               value="<?= esc($user['ort'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="plz">PLZ</label>
                        <input type="text" id="plz" name="plz"
                               value="<?= esc($user['plz'] ?? '') ?>"
                               pattern="[0-9]{4}" title="4-stellige Postleitzahl">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>🔐 Zugangsdaten</legend>

                    <div class="form-group">
                        <label for="email">E-Mail</label>
                        <input type="email" id="email" name="email"
                               value="<?= esc($user['e_mail'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="passwort_neu">Neues Passwort</label>
                        <input type="password" id="passwort_neu" name="passwort_neu"
                               placeholder="Leer lassen = nicht ändern">
                    </div>

                    <div class="form-group">
                        <label for="passwort_neu2">Neues Passwort bestätigen</label>
                        <input type="password" id="passwort_neu2" name="passwort_neu2"
                               placeholder="Passwort wiederholen">
                    </div>
                </fieldset>

                <p class="pflicht-hinweis">* Pflichtfelder</p>

                <div class="form-buttons">
                    <button type="submit" class="btn-submit">Änderungen speichern →</button>
                    <a href="account_form.php" class="btn-cancel">Abbrechen</a>
                </div>

            </form>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <nav class="footer-nav">
                <a href="Website.html">Home</a>
                <a href="Impressum.html">Impressum</a>
            </nav>
            <p><small>&copy; 2026 by Gerda Wagner | Christoph Oberholzer | Leon Oswald | Konvoltic OG</small></p>
        </div>
    </footer>

</body>
</html>
