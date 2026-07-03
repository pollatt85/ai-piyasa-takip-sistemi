<?php
declare(strict_types=1);
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$nav = [
    '' => ['Dashboard', '◈'],
    'signals' => ['Sinyaller', '⚡'],
    'projects' => ['Projeler', '▤'],
    'market' => ['Piyasa Haritası', '◉'],
    'logs' => ['Aktiviteler', '≡'],
];
$flashData = flash();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Dashboard') ?> — AI Piyasa Takip</title>
<link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
<script src="<?= url('assets/js/chart.umd.min.js') ?>"></script>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand">📡 <span>Piyasa Takip</span></div>
        <nav>
            <?php foreach ($nav as $seg => [$label, $icon]):
                $isActive = $seg === ''
                    ? !preg_match('#/(signals|projects|market|logs)#', $reqPath)
                    : (bool) preg_match('#/' . $seg . '(/|$)#', $reqPath);
            ?>
            <a href="<?= url($seg) ?>" class="<?= $isActive ? 'active' : '' ?>">
                <span class="nav-icon"><?= $icon ?></span> <?= e($label) ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <form method="post" action="<?= url('scan/run') ?>" class="sidebar-scan">
            <button type="submit" class="btn btn-primary btn-block">⟳ Tarama Başlat</button>
            <p class="scan-note">RSS + Reddit (ücretsiz kaynaklar)</p>
        </form>
    </aside>
    <main class="main">
        <?php if ($flashData !== null): ?>
        <div class="flash flash-<?= e($flashData['type']) ?>"><?= e($flashData['message']) ?></div>
        <?php endif; ?>
        <?= $content ?>
    </main>
</div>
</body>
</html>
