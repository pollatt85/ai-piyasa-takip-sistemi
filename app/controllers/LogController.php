<?php
declare(strict_types=1);

class LogController extends Controller
{
    public function index(): void
    {
        $this->view('logs/index', [
            'title' => 'Aktiviteler',
            'logs' => (new Log())->latest(100),
        ]);
    }
}
