<?php
declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

spl_autoload_register(function (string $class): void {
    foreach (['core', 'controllers', 'models', 'services'] as $dir) {
        $file = BASE_PATH . '/app/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require BASE_PATH . '/app/helpers/functions.php';
