<?php
declare(strict_types=1);

// Kullanım: php scripts/scan.php — RSS + Reddit RSS taraması (arayüzden ayrık, DB'ye yazar).
// Zamanlanmış çalıştırma: Windows Görev Zamanlayıcı ile bu komut tanımlanabilir.
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

$stats = (new Scanner())->run();

printf(
    "Tarama bitti: %d icerik, %d filtre eslesmesi, %d yeni sinyal, %d guncellenen.\n",
    $stats['fetched'],
    $stats['matched'],
    $stats['new'],
    $stats['updated']
);
foreach ($stats['failed_feeds'] as $url) {
    echo "Ulasilamadi: {$url}\n";
}
