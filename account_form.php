<?php
// das hier ist die "mein konto" seite - die zeigt alle infos des eingeloggten users an
// eigentlich das herzstück vom ganzen account-system
if (session_status() === PHP_SESSION_NONE) session_start();

// kein login? dann wird nix angezeigt, tschüss!
if (empty($_SESSION['acc_id'])) {
    header('Location: login.html');
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
    die('Datenbankfehler.');
}

// alle user-daten aus der datenbank holen (join auf zwei tabellen weil warum einfach wenn's auch kompliziert geht)
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

// account existiert nicht mehr? session zerstören und weg damit
if (!$user) {
    session_destroy();
    header('Location: login.html');
    exit;
}

// erfolgsmeldung aus der session holen (kommt nachdem daten gespeichert wurden)
$erfolg = $_SESSION['acc_erfolg'] ?? '';
unset($_SESSION['acc_erfolg']); // danach direkt löschen damit die nicht ewig da steht

// kleine funktion die html-zeugs escaped, ganz wichtig sonst gibt's xss
function esc(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Konvoltic OG | Mein Konto</title>
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
                    <li><a href="Index.html">🏠 Home</a></li>
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
            <a href="Index.html">
                <img src="Images/KonvolticLogobeschnitten.png" alt="Konvoltic Logo" class="logo">
            </a>
        </div>
    </header>

    <main>
        <div class="acc-page">
            <h1>👤 Mein Konto</h1>
            <p class="acc-welcome">Willkommen, <strong><?= esc($user['vorname'] . ' ' . $user['nachname']) ?></strong>!</p>

            <?php if ($erfolg): ?>
                <div class="acc-success"><?= esc($erfolg) ?></div>
            <?php endif; ?>

            <?php if (!empty($user['profilbild'])): ?>
            <div style="text-align:center; margin-bottom:20px;">
                <img src="data:image/jpeg;base64,<?= base64_encode($user['profilbild']) ?>"
                     alt="Profilbild" style="width:120px;height:120px;object-fit:cover;border-radius:50%;border:3px solid #43a047;">
            </div>
            <?php endif; ?>

            <div class="acc-card">
                <div class="acc-card-header">
                    <span>👤 Persönliche Daten</span>
                    <a href="account_edit.php" class="acc-edit-btn">✏️ Daten bearbeiten</a>
                </div>
                <dl class="acc-dl">
                    <dt>Anrede</dt>
                    <dd><?= esc(ucfirst($user['anrede'] ?? '–')) ?></dd>

                    <dt>Vorname</dt>
                    <dd><?= esc($user['vorname'] ?: '–') ?></dd>

                    <dt>Nachname</dt>
                    <dd><?= esc($user['nachname'] ?: '–') ?></dd>

                    <dt>Geburtstag</dt>
                    <dd><?= $user['geburtstag'] ? esc(date('d.m.Y', strtotime($user['geburtstag']))) : '–' ?></dd>

                    <dt>Telefon</dt>
                    <dd><?= esc($user['telefon'] ?: '–') ?></dd>
                </dl>
            </div>

            <div class="acc-card">
                <div class="acc-card-header">
                    <span>🏠 Adresse</span>
                </div>
                <dl class="acc-dl">
                    <dt>Straße</dt>
                    <dd><?= esc($user['straße'] ?: '–') ?></dd>

                    <dt>PLZ / Ort</dt>
                    <dd><?= esc(($user['plz'] ? $user['plz'] . ' ' : '') . ($user['ort'] ?: '–')) ?></dd>
                </dl>
            </div>

            <div class="acc-card">
                <div class="acc-card-header">
                    <span>🔐 Zugangsdaten</span>
                </div>
                <dl class="acc-dl">
                    <dt>Benutzername</dt>
                    <dd><?= esc($user['benutzername'] ?: '–') ?></dd>

                    <dt>E-Mail</dt>
                    <dd><?= esc($user['e_mail'] ?: '–') ?></dd>
                </dl>
            </div>

        </div>
    </main>

    <footer>
        <div class="footer-content">
            <nav class="footer-nav">
                <a href="Index.html">Home</a>
                <a href="Impressum.html">Impressum</a>
            </nav>
            <p><small>&copy; 2026 by Gerda Wagner | Christoph Oberholzer | Leon Oswald | Konvoltic OG</small></p>
        </div>
    </footer>

</body>
</html>
