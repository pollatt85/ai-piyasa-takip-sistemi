<?php
declare(strict_types=1);

function config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require BASE_PATH . '/app/config.php';
    }
    return $config;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $base . '/' . ltrim($path, '/');
}

function flash(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return null;
    }
    $current = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $current;
}

function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'az önce';
    if ($diff < 3600) return intdiv($diff, 60) . ' dk önce';
    if ($diff < 86400) return intdiv($diff, 3600) . ' sa önce';
    return intdiv($diff, 86400) . ' gün önce';
}
