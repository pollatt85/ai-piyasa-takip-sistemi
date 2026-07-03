<?php
declare(strict_types=1);

class Task extends Model
{
    protected string $table = 'tasks';

    public function forProject(int $projectId): array
    {
        return $this->rows(
            'SELECT * FROM tasks WHERE project_id = ? ORDER BY phase, created_at',
            [$projectId]
        );
    }

    public function openTasks(int $limit = 10): array
    {
        return $this->rows(
            "SELECT t.*, p.title AS project_title
             FROM tasks t JOIN projects p ON p.id = t.project_id
             WHERE t.status = 'acik' AND p.status IN ('yeni', 'devam_eden')
             ORDER BY t.created_at DESC LIMIT " . (int) $limit
        );
    }

    /** Haftalık tamamlama %: bu hafta tamamlanan / (açık + bu hafta tamamlanan). */
    public function weeklyCompletion(): int
    {
        $since = date('Y-m-d H:i:s', strtotime('-7 days'));
        $done = (int) $this->value(
            'SELECT COUNT(DISTINCT task_id) FROM ticks WHERE created_at >= ?',
            [$since]
        );
        $open = (int) $this->value("SELECT COUNT(*) FROM tasks WHERE status = 'acik'");
        $total = $open + $done;
        return $total > 0 ? (int) round($done / $total * 100) : 0;
    }
}
