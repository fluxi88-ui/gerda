<?php
// login_log.php – Übersicht aller Login-Versuche
// Zugang nur für eingeloggte Admins (benutzername = 'admin' oder in ADMIN_USERS)
session_start();
require_once __DIR__ . '/config.php';

define('ADMIN_USERS', ['admin', 'gerda', 'Gerda']);

if (empty($_SESSION['benutzername']) || !in_array($_SESSION['benutzername'], ADMIN_USERS)) {
    http_response_code(403);
    die('Zugriff verweigert.');
}

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Datenbankfehler.');
}

// Filter
$filter_user = trim($_GET['user'] ?? '');
$filter_typ  = $_GET['typ'] ?? ''; // 'ok', 'fail', ''
$limit       = 200;

$where = [];
$params = [];
if ($filter_user !== '') {
    $where[] = 'benutzername LIKE ?';
    $params[] = '%' . $filter_user . '%';
}
if ($filter_typ === 'ok')   { $where[] = 'erfolgreich = 1'; }
if ($filter_typ === 'fail') { $where[] = 'erfolgreich = 0'; }
$sql = 'SELECT * FROM login_log' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY zeitpunkt DESC LIMIT ' . $limit;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Statistik
$stats = $pdo->query('SELECT erfolgreich, COUNT(*) as n FROM login_log GROUP BY erfolgreich')->fetchAll();
$gesamt_ok   = 0;
$gesamt_fail = 0;
foreach ($stats as $s) {
    if ($s['erfolgreich']) $gesamt_ok   = $s['n'];
    else                   $gesamt_fail = $s['n'];
}

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Login-Log | Konvoltic Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f6f4; }
        .log-page { max-width: 1100px; margin: 30px auto; padding: 0 20px 60px; }
        h1 { color: #1b5e20; margin-bottom: 6px; }
        .stats { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
        .stat-box { background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 14px 24px; text-align: center; min-width: 120px; }
        .stat-box .n { font-size: 2rem; font-weight: 700; }
        .stat-box.ok .n  { color: #2e7d32; }
        .stat-box.fail .n { color: #c62828; }
        .filter-bar { display: flex; gap: 10px; margin-bottom: 18px; flex-wrap: wrap; align-items: center; }
        .filter-bar input, .filter-bar select { padding: 7px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        .filter-bar button { background: #2e7d32; color: white; border: none; padding: 8px 18px; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .filter-bar a { color: #555; font-size: 13px; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        th { background: #1b5e20; color: white; padding: 10px 14px; text-align: left; font-size: 13px; }
        td { padding: 9px 14px; border-bottom: 1px solid #f0f0f0; font-size: 13px; vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f9fbe7; }
        .badge-ok   { background: #c8e6c9; color: #1b5e20; padding: 2px 8px; border-radius: 10px; font-size: 12px; }
        .badge-fail { background: #ffcdd2; color: #c62828; padding: 2px 8px; border-radius: 10px; font-size: 12px; }
        .ua { color: #999; font-size: 11px; max-width: 280px; word-break: break-all; }
        .back { display: inline-block; margin-bottom: 20px; color: #2e7d32; font-weight: bold; }
    </style>
</head>
<body>
<div class="log-page">
    <a href="account_form.php" class="back">← Zurück zu Mein Konto</a>
    <h1>🔐 Login-Log</h1>

    <div class="stats">
        <div class="stat-box ok">
            <div class="n"><?= $gesamt_ok ?></div>
            <div>Erfolgreiche Logins</div>
        </div>
        <div class="stat-box fail">
            <div class="n"><?= $gesamt_fail ?></div>
            <div>Fehlgeschlagene Versuche</div>
        </div>
        <div class="stat-box">
            <div class="n"><?= $gesamt_ok + $gesamt_fail ?></div>
            <div>Gesamt</div>
        </div>
    </div>

    <form method="get" class="filter-bar">
        <input type="text" name="user" placeholder="Benutzername suchen..."
               value="<?= esc($filter_user) ?>">
        <select name="typ">
            <option value="">Alle</option>
            <option value="ok"   <?= $filter_typ === 'ok'   ? 'selected' : '' ?>>Nur Erfolgreiche</option>
            <option value="fail" <?= $filter_typ === 'fail' ? 'selected' : '' ?>>Nur Fehlschläge</option>
        </select>
        <button type="submit">Filtern</button>
        <a href="login_log.php">Zurücksetzen</a>
    </form>

    <?php if (empty($logs)): ?>
        <p style="color:#888;">Keine Einträge gefunden.</p>
    <?php else: ?>
    <p style="color:#888; font-size:13px; margin-bottom:10px;">
        Zeige <?= count($logs) ?> Einträge (max. <?= $limit ?>)
    </p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Zeitpunkt</th>
                <th>Benutzername</th>
                <th>IP-Adresse</th>
                <th>Status</th>
                <th>Browser / User-Agent</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $row): ?>
            <tr>
                <td><?= (int)$row['log_id'] ?></td>
                <td><?= esc($row['zeitpunkt']) ?></td>
                <td><strong><?= esc($row['benutzername']) ?></strong></td>
                <td><?= esc($row['ip']) ?></td>
                <td>
                    <?php if ($row['erfolgreich']): ?>
                        <span class="badge-ok">✓ Erfolg</span>
                    <?php else: ?>
                        <span class="badge-fail">✗ Fehlschlag</span>
                    <?php endif; ?>
                </td>
                <td class="ua"><?= esc($row['user_agent'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
</body>
</html>
