<?php
declare(strict_types=1);

class SubSector extends Model
{
    protected string $table = 'sub_sectors';

    public function allWithKeywords(): array
    {
        return $this->rows("SELECT id, name, keywords FROM sub_sectors WHERE keywords != ''");
    }

    public function fallbackId(): int
    {
        $id = $this->value("SELECT id FROM sub_sectors WHERE is_fallback = 1 LIMIT 1");
        return $id === null ? 0 : (int) $id;
    }

    /**
     * Kategori→Sektör→Alt Sektör ağacı; dal başına sinyal/fırsat/şirket sayaçları
     * tek sorguda toplanır (bkz. MD/docs/01-dashboard.md — Piyasa Haritası).
     */
    public function tree(): array
    {
        $rows = $this->rows(
            "SELECT c.id AS category_id, c.name AS category_name,
                    s.id AS sector_id, s.name AS sector_name,
                    ss.id AS sub_sector_id, ss.name AS sub_sector_name,
                    COUNT(p.id) AS signal_count,
                    SUM(CASE WHEN p.status = 'olgun_firsat' THEN 1 ELSE 0 END) AS mature_count,
                    (SELECT COUNT(*) FROM companies co WHERE co.sub_sector_id = ss.id) AS company_count
             FROM categories c
             LEFT JOIN sectors s ON s.category_id = c.id
             LEFT JOIN sub_sectors ss ON ss.sector_id = s.id
             LEFT JOIN problems p ON p.sub_sector_id = ss.id
             GROUP BY c.id, s.id, ss.id
             ORDER BY c.name, s.name, ss.name"
        );

        $tree = [];
        foreach ($rows as $r) {
            $cId = (int) $r['category_id'];
            $tree[$cId]['name'] = $r['category_name'];
            if ($r['sector_id'] === null) {
                $tree[$cId]['sectors'] = $tree[$cId]['sectors'] ?? [];
                continue;
            }
            $sId = (int) $r['sector_id'];
            $tree[$cId]['sectors'][$sId]['name'] = $r['sector_name'];
            $tree[$cId]['sectors'][$sId]['id'] = $sId;
            if ($r['sub_sector_id'] === null) {
                $tree[$cId]['sectors'][$sId]['subs'] = $tree[$cId]['sectors'][$sId]['subs'] ?? [];
                continue;
            }
            $tree[$cId]['sectors'][$sId]['subs'][] = [
                'id' => (int) $r['sub_sector_id'],
                'name' => $r['sub_sector_name'],
                'signal_count' => (int) $r['signal_count'],
                'mature_count' => (int) ($r['mature_count'] ?? 0),
                'company_count' => (int) $r['company_count'],
            ];
        }
        return $tree;
    }
}
