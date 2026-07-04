<?php
declare(strict_types=1);

class ScanController extends Controller
{
    /** Dashboard "Tarama Başlat" aksiyonu. Tarama DB'ye yazar, arayüz okur. */
    public function run(): void
    {
        set_time_limit(max(180, count(config()['feeds']) * 20));
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch') {
            $this->runStreaming();
            return;
        }
        $stats = (new Scanner())->run();
        flash($this->buildMessage($stats));
        $this->redirect('');
    }

    /**
     * İlerlemeyi NDJSON satırları olarak akıtır (ilerleme çubuğu bunu okur).
     * Ayrı bir durum endpoint'i yerine aynı bağlantı kullanılır; böylece
     * tek işçili PHP yerleşik sunucusunda da çalışır.
     */
    private function runStreaming(): void
    {
        session_write_close();
        header('Content-Type: application/x-ndjson; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        $emit = static function (array $event): void {
            echo json_encode($event, JSON_UNESCAPED_UNICODE) . "\n";
            flush();
        };

        $stats = (new Scanner())->run(
            static function (int $current, int $total, string $source) use ($emit): void {
                $emit(['type' => 'progress', 'current' => $current, 'total' => $total, 'source' => $source]);
            }
        );
        $emit(['type' => 'done', 'message' => $this->buildMessage($stats)]);
    }

    private function buildMessage(array $stats): string
    {
        $message = sprintf(
            'Tarama tamamlandı: %d içerik tarandı, %d filtreden geçti, %d yeni sinyal, %d güncellendi.',
            $stats['fetched'], $stats['matched'], $stats['new'], $stats['updated']
        );
        if ($stats['failed_feeds'] !== []) {
            $message .= ' Ulaşılamayan kaynak: ' . count($stats['failed_feeds']);
        }
        return $message;
    }
}
