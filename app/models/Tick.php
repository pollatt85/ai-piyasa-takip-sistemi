<?php
declare(strict_types=1);

class Tick extends Model
{
    protected string $table = 'ticks';

    /** Son 7 günün gün bazlı tik sayıları (Chart.js haftalık grafik verisi). */
    public function weeklySeries(): array
    {
        $rows = $this->rows(
            "SELECT date(created_at) AS day, COUNT(*) AS total
             FROM ticks WHERE created_at >= ?
             GROUP BY day",
            [date('Y-m-d', strtotime('-6 days')) . ' 00:00:00']
        );
        $byDay = array_column($rows, 'total', 'day');
        $series = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $series[] = ['day' => date('d.m', strtotime($day)), 'total' => (int) ($byDay[$day] ?? 0)];
        }
        return $series;
    }
}
