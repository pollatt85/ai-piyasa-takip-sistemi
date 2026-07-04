<?php
declare(strict_types=1);

class SourceController extends Controller
{
    public function index(): void
    {
        $cfg = config();
        $this->view('sources/index', [
            'title' => 'Kaynaklar',
            'feeds' => $cfg['feeds'] ?? [],
            'professionForums' => $cfg['profession_forums'] ?? [],
        ]);
    }
}
