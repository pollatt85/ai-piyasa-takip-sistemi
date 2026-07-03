<?php
declare(strict_types=1);

class ScanController extends Controller
{
    /** Dashboard "Manuel tarama tetikle" aksiyonu. Tarama DB'ye yazar, arayüz okur. */
    public function run(): void
    {
        set_time_limit(120);
        $stats = (new Scanner())->run();
        $message = sprintf(
            'Tarama tamamlandı: %d içerik tarandı, %d filtreden geçti, %d yeni sinyal, %d güncellendi.',
            $stats['fetched'], $stats['matched'], $stats['new'], $stats['updated']
        );
        if ($stats['failed_feeds'] !== []) {
            $message .= ' Ulaşılamayan kaynak: ' . count($stats['failed_feeds']);
        }
        flash($message);
        $this->redirect('');
    }
}
