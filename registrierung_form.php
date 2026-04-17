<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$d = $_SESSION['reg_daten'] ?? [];
unset($_SESSION['reg_daten']);
function rv(string $key, array $d): string {
    return htmlspecialchars($d[$key] ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Konvoltic OG | Registrierung</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="registrierung.css">
</head>

<body>

    <nav class="top-bar">
        <div class="auth-buttons">
            <?php if (empty($_SESSION['acc_id'])): ?>
                <a href="login.html" class="login-btn">Login</a>
                <a href="registrierung_form.php" class="register-btn">Registrieren</a>
            <?php else: ?>
                <a href="account_form.php" class="home-btn">Mein Konto</a>
                <a href="logout.php" class="logout-btn">Abmelden</a>
            <?php endif; ?>
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
        <div class="form-page">
            <h1>📋 Konto erstellen</h1>
            <p class="form-intro">Werden Sie Teil der Konvoltic-Community und profitieren Sie von exklusiven Angeboten, persönlichem Support und einem nachhaltigen Kundenkonto.</p>

            <?php if (!empty($_SESSION['fehler'])): ?>
                <div class="fehler-box">
                    <ul style="margin:0; padding-left:18px;">
                        <?php foreach ($_SESSION['fehler'] as $f): ?>
                            <li><?= htmlspecialchars($f, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach;
                        unset($_SESSION['fehler']); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="registrierung.php" enctype="multipart/form-data">

                <!-- Abschnitt 1: Konto -->
                <fieldset>
                    <legend>👤 Konto</legend>

                    <div class="form-group">
                        <label>Anrede *</label>
                        <div class="radio-group">
                            <label><input type="radio" name="anrede" value="herr" autofocus required <?= ($d['anrede'] ?? '') === 'herr' ? 'checked' : '' ?>> Herr</label>
                            <label><input type="radio" name="anrede" value="frau" <?= ($d['anrede'] ?? '') === 'frau' ? 'checked' : '' ?>> Frau</label>
                            <label><input type="radio" name="anrede" value="divers" <?= ($d['anrede'] ?? '') === 'divers' ? 'checked' : '' ?>> Divers</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="vorname">Vorname *</label>
                        <input type="text" id="vorname" name="vorname"
                            placeholder="Vorname"
                            value="<?= rv('vorname', $d) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="nachname">Nachname *</label>
                        <input type="text" id="nachname" name="nachname"
                            placeholder="Nachname"
                            value="<?= rv('nachname', $d) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="geburtstag">Geburtstag</label>
                        <input type="date" id="geburtstag" name="geburtstag"
                            value="<?= rv('geburtstag', $d) ?>">
                    </div>

                    <div class ="form-group">
                        <label for="benutzername">Benutzername *</label>
                        <input type="text" id="benutzername" name="benutzername"
                            placeholder="z. B. Mastemind"
                            value="<?= rv('benutzername', $d) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-Mail *</label>
                        <input type="email" id="email" name="email"
                            placeholder="z. B. max.muster@beispiel.at"
                            value="<?= rv('email', $d) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="passwort">Passwort *</label>
                        <input type="password" id="passwort" name="passwort"
                            placeholder="Mindestens 8 Zeichen"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="passwort2">Passwort bestätigen *</label>
                        <input type="password" id="passwort2" name="passwort2"
                            placeholder="Passwort wiederholen"
                            required>
                        <span id="pw-fehler" class="pw-fehler"></span>
                    </div>
                </fieldset>

                <!-- Abschnitt 2: Adresse -->
                <fieldset>
                    <legend>🏠 Adresse</legend>

                    <div class="form-group">
                        <label for="strasse">Straße</label>
                        <input type="text" id="strasse" name="strasse"
                            placeholder="z. B. Musterstraße"
                            value="<?= rv('strasse', $d) ?>">
                    </div>

                    <div class="form-group">
                        <label for="hausnummer">Hausnummer</label>
                        <input type="text" id="hausnummer" name="hausnummer"
                            placeholder="z. B. 12"
                            value="<?= rv('hausnummer', $d) ?>">
                    </div>

                    <div class="form-group">
                        <label for="wohnort">Wohnort *</label>
                        <input type="text" id="wohnort" name="wohnort"
                            placeholder="z. B. Wien"
                            value="<?= rv('wohnort', $d) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="plz">PLZ</label>
                        <input type="text" id="plz" name="plz"
                            placeholder="z. B. 1010"
                            value="<?= rv('plz', $d) ?>"
                            pattern="[0-9]{4}"
                            title="Bitte eine 4-stellige Postleitzahl eingeben">
                    </div>

                    <div class="form-group">
                        <label for="bundesland">Bundesland</label>
                        <select id="bundesland" name="bundesland">
                            <option value="">– Bitte wählen –</option>
                            <option value="burgenland" <?= ($d['bundesland'] ?? '') === 'burgenland' ? 'selected' : '' ?>>Burgenland</option>
                            <option value="kaernten" <?= ($d['bundesland'] ?? '') === 'kaernten' ? 'selected' : '' ?>>Kärnten</option>
                            <option value="niederoesterreich" <?= ($d['bundesland'] ?? '') === 'niederoesterreich' ? 'selected' : '' ?>>Niederösterreich</option>
                            <option value="oberoesterreich" <?= ($d['bundesland'] ?? '') === 'oberoesterreich' ? 'selected' : '' ?>>Oberösterreich</option>
                            <option value="salzburg" <?= ($d['bundesland'] ?? '') === 'salzburg' ? 'selected' : '' ?>>Salzburg</option>
                            <option value="steiermark" <?= ($d['bundesland'] ?? '') === 'steiermark' ? 'selected' : '' ?>>Steiermark</option>
                            <option value="tirol" <?= ($d['bundesland'] ?? '') === 'tirol' ? 'selected' : '' ?>>Tirol</option>
                            <option value="vorarlberg" <?= ($d['bundesland'] ?? '') === 'vorarlberg' ? 'selected' : '' ?>>Vorarlberg</option>
                            <option value="wien" <?= ($d['bundesland'] ?? '') === 'wien' ? 'selected' : '' ?>>Wien</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="telefon">Telefonnummer</label>
                        <input type="tel" id="telefon" name="telefon"
                            placeholder="z. B. +43 660 1234567"
                            value="<?= rv('telefon', $d) ?>">
                    </div>
                </fieldset>

                <!-- Abschnitt 3: Datei -->
                <fieldset>
                    <legend>📎 Dokument</legend>

                    <div class="form-group">
                        <label for="dokument">Dokument hochladen</label>
                        <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
                        <input type="file" id="dokument" name="dokument"
                               accept="application/pdf,image/jpeg,image/png,image/gif">
                        <small style="color:#888">Erlaubt: PDF, JPG, PNG, GIF – max. 2 MB</small>
                    </div>

                </fieldset>

                <p class="pflicht-hinweis">* Pflichtfelder</p>

                <div class="form-buttons">
                    <button type="submit" class="btn-submit">Konto erstellen →</button>
                    <button type="reset" class="btn-reset">Zurücksetzen</button>
                </div>

            </form>

            <p style="margin-top: 24px; color: #555;">Bereits registriert? <a href="login.html">Jetzt anmelden →</a></p>
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