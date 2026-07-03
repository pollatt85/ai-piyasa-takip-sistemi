<?php
declare(strict_types=1);

// PHP yerleşik sunucusu için: statik dosyalar doğrudan, kalan istekler front controller'a.
// Kullanım: php -S localhost:8123 -t public public/router.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}
require __DIR__ . '/index.php';
