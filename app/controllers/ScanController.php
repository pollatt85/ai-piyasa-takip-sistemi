<?php
declare(strict_types=1);

class ScanController extends Controller
{
    /** Dashboard "Tarama Başlat" aksiyonu. Tarama DB'ye yazar, arayüz okur. */
    public function run(): void
    {
        set_time_limit(180);
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
        if ($isAjax) {
            session_write_close(); // tarama boyunca scan/status kilitlenmesin
        }

        $stats = (new Scanner())->run();
        $message = sprintf(
            'Tarama tamamlandı: %d içerik tarandı, %d filtreden geçti, %d yeni sinyal, %d güncellendi.',
            $stats['fetched'], $stats['matched'], $stats['new'], $stats['updated']
        );
        if ($stats['failed_feeds'] !== []) {
            $message .= ' Ulaşılamayan kaynak: ' . count($stats['failed_feeds']);
        }

        if ($isAjax) {
            session_start();
            flash($message);
            session_write_close();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => true, 'message' => $message], JSON_UNESCAPED_UNICODE);
            return;
        }

        flash($message);
        $this->redirect('');
    }

    /** İlerleme çubuğu için tarama durumu (AJAX poll). */
    public function status(): void
    {
        session_write_close();
        header('Content-Type: application/json; charset=utf-8');
        $raw = @file_get_contents(Scanner::PROGRESS_FILE);
        echo $raw !== false ? $raw : json_encode(['running' => false]);
    }
}
