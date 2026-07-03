<?php
declare(strict_types=1);

class Project extends Model
{
    protected string $table = 'projects';

    public const STATUSES = ['yeni' => 'Yeni Proje', 'devam_eden' => 'Devam Eden', 'biten' => 'Biten', 'iptal' => 'İptal Edilen'];

    /**
     * Kanban verisi: projeler + görev sayıları tek sorguda (görev başına sorgu yok,
     * bkz. MD/docs/05-tik-sistemi.md — token dostu not).
     */
    public function kanban(): array
    {
        $rows = $this->rows(
            "SELECT p.*, s.name AS sector_name,
                    COALESCE(t.total, 0) AS total_tasks,
                    COALESCE(t.done, 0) AS done_tasks
             FROM projects p
             LEFT JOIN sectors s ON s.id = p.sector_id
             LEFT JOIN (
                 SELECT project_id, COUNT(*) AS total,
                        SUM(CASE WHEN status = 'tamamlandi' THEN 1 ELSE 0 END) AS done
                 FROM tasks GROUP BY project_id
             ) t ON t.project_id = p.id
             ORDER BY p.created_at DESC"
        );
        $groups = array_fill_keys(array_keys(self::STATUSES), []);
        foreach ($rows as $r) {
            $r['progress'] = $r['total_tasks'] > 0 ? (int) round($r['done_tasks'] / $r['total_tasks'] * 100) : 0;
            $groups[$r['status']][] = $r;
        }
        return $groups;
    }

    public function activeCount(): int
    {
        return (int) $this->value("SELECT COUNT(*) FROM projects WHERE status IN ('yeni', 'devam_eden')");
    }

    public function findWithSector(int $id): ?array
    {
        return $this->row(
            'SELECT p.*, s.name AS sector_name FROM projects p
             LEFT JOIN sectors s ON s.id = p.sector_id WHERE p.id = ?',
            [$id]
        );
    }

    /** Fırsatı projeye çevirir: başlık, sektör ve kaynak linkleri otomatik taslak. */
    public function createFromSignal(array $signal): int
    {
        $sectorId = null;
        if (!empty($signal['sub_sector_id'])) {
            $sectorId = $this->value(
                'SELECT sector_id FROM sub_sectors WHERE id = ?',
                [(int) $signal['sub_sector_id']]
            );
        }
        $id = $this->insert([
            'title' => mb_substr((string) $signal['content'], 0, 120),
            'sector_id' => $sectorId,
            'status' => 'yeni',
            'current_phase' => 'Planlama',
            'region' => $signal['region'] ?? 'TR',
            'source_links' => (string) ($signal['source_url'] ?? ''),
            'notes' => '',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        (new Log())->add('firsat_projeye', 'Fırsat projeye çevrildi: ' . mb_substr((string) $signal['content'], 0, 80), $id);
        return $id;
    }
}
