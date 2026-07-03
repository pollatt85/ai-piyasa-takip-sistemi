<?php
declare(strict_types=1);

abstract class Controller
{
    protected function view(string $template, array $data = []): void
    {
        extract($data);
        ob_start();
        require BASE_PATH . '/views/' . $template . '.php';
        $content = ob_get_clean();
        require BASE_PATH . '/views/layout.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function input(string $key, ?string $default = null): ?string
    {
        $v = $_POST[$key] ?? $default;
        return is_string($v) ? trim($v) : $v;
    }
}
