<?php
declare(strict_types=1);

/**
 * Kümelenmiş problem/fırsat kaydı (aynı problem = tek satır). Ham kanıtlar
 * problem_mentions'da tutulur; kümeleme ProblemCluster tarafından yürütülür.
 * Skor: AI boyutları (0-100) ağırlıklı taban + kanıt (kaç kişi/kaç kaynak) + manuel.
 */
class Problem extends Model
{
    protected string $table = 'problems';

    /** 12 boyuttan AI tarafından doldurulan alanlar (applyAnalysis'te set edilir). */
    public const AI_FIELDS = [
        'title', 'why_summary', 'mvp_suggestion', 'mvp_weeks', 'revenue_potential',
        'repeat_frequency', 'trend_direction', 'demand_score', 'competition_score',
        'ai_solvability', 'automation_fit', 'technical_difficulty', 'subscription_fit',
        'local_opportunity', 'global_opportunity',
    ];

    // --- Skor ---

    public function computeScore(array $row): int
    {
        $cfg = config()['score'];
        $dims = $cfg['dims'];
        $weightSum = array_sum($dims) ?: 1.0;

        // AI henüz puanlamadıysa (null) o boyut skora katkı vermez. Özellikle
        // competition_inverse: rekabet null iken "0 rakip = 100 puan" sayılmamalı.
        $base = 0.0;
        foreach ($dims as $key => $weight) {
            $srcKey = $key === 'competition_inverse' ? 'competition_score' : $key;
            if (!isset($row[$srcKey]) || $row[$srcKey] === null || $row[$srcKey] === '') {
                continue;
            }
            $value = $key === 'competition_inverse' ? 100 - (int) $row[$srcKey] : (int) $row[$srcKey];
            $base += ($weight / $weightSum) * $value;
        }

        $mention = min((int) ($row['mention_count'] ?? 1), (int) $cfg['mention_cap']) * (int) $cfg['w_mention'];
        $variety = (int) ($row['source_variety'] ?? 1) * (int) $cfg['w_variety'];
        $manual = (int) ($row['manual_score'] ?? 0) * (int) $cfg['w_manual'];

        return (int) round($base + $mention + $variety + $manual);
    }

    public function recompute(int $id): void
    {
        $row = $this->find($id);
        if ($row === null) {
            return;
        }
        $score = $this->computeScore($row);
        $status = $score >= config()['score']['threshold'] ? 'olgun_firsat' : 'ham';
        if ($status === 'olgun_firsat' && $row['status'] !== 'olgun_firsat') {
            (new Log())->add('firsat_olgunlasti', 'Fırsat olgunlaştı: ' . mb_substr((string) $row['title'], 0, 80), $id);
        }
        $this->update($id, ['total_score' => $score, 'status' => $status]);
    }

    // --- Kümeleme yardımcıları ---

    /** Aynı alt sektör + bölgedeki son problemler (kümeleme adayları). */
    public function candidates(int $subSectorId, string $region, int $limit = 400): array
    {
        return $this->rows(
            'SELECT id, title, cluster_key, source_variety, sources FROM problems
             WHERE sub_sector_id = ? AND region = ? ORDER BY id DESC LIMIT ' . (int) $limit,
            [$subSectorId, $region]
        );
    }

    /** Yeni problem oluşturur (ilk kanıt). AI analizi varsa boyutlar da yazılır. */
    public function createFromAnalysis(int $subSectorId, string $region, string $source, string $fallbackTitle, ?array $analysis): int
    {
        $now = date('Y-m-d H:i:s');
        $data = [
            'sub_sector_id' => $subSectorId ?: null,
            'region' => $region,
            'cluster_key' => $analysis['slug'] ?? '',
            'title' => ($analysis['title'] ?? '') !== '' ? $analysis['title'] : $fallbackTitle,
            'mention_count' => 1,
            'source_variety' => 1,
            'sources' => $source,
            'manual_score' => 0,
            'status' => 'ham',
            'first_seen' => $now,
            'last_seen' => $now,
            'created_at' => $now,
        ];
        $data = array_merge($data, $this->analysisFields($analysis));
        $id = $this->insert($data);
        (new Log())->add('sinyal_eklendi', 'Yeni problem: ' . mb_substr($data['title'], 0, 80), $id);
        $this->recompute($id); // total_score + status (güçlü AI boyutlarıyla doğrudan olgun doğabilir)
        return $id;
    }

    /** Var olan probleme yeni kanıt işle: sayaç/kaynak/last_seen güncelle, skoru yeniden hesapla. */
    public function merge(int $id, string $source): void
    {
        $row = $this->find($id);
        if ($row === null) {
            return;
        }
        $sources = array_filter(array_map('trim', explode(',', (string) $row['sources'])));
        if (!in_array($source, $sources, true)) {
            $sources[] = $source;
        }
        $updated = array_merge($row, [
            'mention_count' => (int) $row['mention_count'] + 1,
            'sources' => implode(',', $sources),
            'source_variety' => count($sources),
            'last_seen' => date('Y-m-d H:i:s'),
        ]);
        $status = $this->computeScore($updated) >= config()['score']['threshold'] ? 'olgun_firsat' : 'ham';
        if ($status === 'olgun_firsat' && $row['status'] !== 'olgun_firsat') {
            (new Log())->add('firsat_olgunlasti', 'Fırsat olgunlaştı: ' . mb_substr((string) $row['title'], 0, 80), $id);
        }
        $this->update($id, [
            'mention_count' => $updated['mention_count'],
            'sources' => $updated['sources'],
            'source_variety' => $updated['source_variety'],
            'last_seen' => $updated['last_seen'],
            'total_score' => $this->computeScore($updated),
            'status' => $status,
        ]);
    }

    /** Problemin AI alanları boşsa sonradan gelen analizle doldur (fail-open ilk kayıtlar için). */
    public function applyAnalysis(int $id, array $analysis): void
    {
        $row = $this->find($id);
        if ($row === null || $row['demand_score'] !== null) {
            return; // zaten analiz edilmiş
        }
        $fields = $this->analysisFields($analysis);
        if ($fields === []) {
            return;
        }
        if (($analysis['title'] ?? '') !== '') {
            $fields['title'] = $analysis['title'];
            $fields['cluster_key'] = $analysis['slug'] ?? $row['cluster_key'];
        }
        $this->update($id, $fields);
        $this->recompute($id); // total_score + status (AI boyutları geldi, eşiği aşabilir)
    }

    /** Analiz dizisinden yalnızca DB kolonu olan boyut alanlarını süz. */
    private function analysisFields(?array $analysis): array
    {
        if ($analysis === null) {
            return [];
        }
        $map = [
            'why' => 'why_summary',
            'mvp_suggestion' => 'mvp_suggestion',
            'mvp_weeks' => 'mvp_weeks',
            'revenue_potential' => 'revenue_potential',
            'repeat_frequency' => 'repeat_frequency',
            'trend_direction' => 'trend_direction',
            'demand_score' => 'demand_score',
            'competition_score' => 'competition_score',
            'ai_solvability' => 'ai_solvability',
            'automation_fit' => 'automation_fit',
            'technical_difficulty' => 'technical_difficulty',
            'subscription_fit' => 'subscription_fit',
            'local_opportunity' => 'local_opportunity',
            'global_opportunity' => 'global_opportunity',
        ];
        $fields = [];
        foreach ($map as $from => $to) {
            if (array_key_exists($from, $analysis) && $analysis[$from] !== null && $analysis[$from] !== '') {
                $fields[$to] = $analysis[$from];
            }
        }
        return $fields;
    }

    // --- Listeler & istatistik (dashboard) ---

    public function filtered(array $f): array
    {
        $sql = 'SELECT p.*, ss.name AS sub_sector_name, s.name AS sector_name
                FROM problems p
                LEFT JOIN sub_sectors ss ON ss.id = p.sub_sector_id
                LEFT JOIN sectors s ON s.id = ss.sector_id
                WHERE 1=1';
        $params = [];
        if (!empty($f['status'])) {
            $sql .= ' AND p.status = ?';
            $params[] = $f['status'];
        }
        if (!empty($f['region'])) {
            $sql .= ' AND p.region = ?';
            $params[] = $f['region'];
        }
        if (!empty($f['sector_id'])) {
            $sql .= ' AND s.id = ?';
            $params[] = $f['sector_id'];
        }
        if (!empty($f['favorites'])) {
            $sql .= ' AND p.is_favorite = 1';
        }
        if (!empty($f['since'])) {
            $sql .= ' AND p.last_seen >= ?';
            $params[] = $f['since'];
        }
        $sql .= ' ORDER BY p.total_score DESC, p.last_seen DESC LIMIT ' . (int) ($f['limit'] ?? 200);
        return $this->rows($sql, $params);
    }

    public function findWithSector(int $id): ?array
    {
        return $this->row(
            'SELECT p.*, ss.name AS sub_sector_name, s.name AS sector_name
             FROM problems p
             LEFT JOIN sub_sectors ss ON ss.id = p.sub_sector_id
             LEFT JOIN sectors s ON s.id = ss.sector_id
             WHERE p.id = ?',
            [$id]
        );
    }

    public function todayCount(): int
    {
        return (int) $this->value('SELECT COUNT(*) FROM problems WHERE created_at >= ?', [date('Y-m-d') . ' 00:00:00']);
    }

    public function weekMatureCount(): int
    {
        return (int) $this->value(
            "SELECT COUNT(*) FROM problems WHERE status = 'olgun_firsat' AND last_seen >= ?",
            [date('Y-m-d H:i:s', strtotime('-7 days'))]
        );
    }

    public function sectorDistribution(): array
    {
        return $this->rows(
            'SELECT s.name, COUNT(p.id) AS total
             FROM problems p
             JOIN sub_sectors ss ON ss.id = p.sub_sector_id
             JOIN sectors s ON s.id = ss.sector_id
             GROUP BY s.id ORDER BY total DESC'
        );
    }

    // --- Çeviri (başlık; MyMemory API, ücretsiz/key'siz) ---

    public function translate(int $id): array
    {
        $row = $this->find($id);
        if ($row === null) {
            return ['ok' => false, 'error' => 'not_found'];
        }
        if (!empty($row['content_tr'])) {
            return ['ok' => true, 'text' => $row['content_tr']];
        }
        $url = 'https://api.mymemory.translated.net/get?' . http_build_query([
            'q' => (string) $row['title'],
            'langpair' => 'en|tr',
        ]);
        $ctx = stream_context_create(['http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (PiyasaTakip/1.0; localhost)',
        ]]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            return ['ok' => false, 'error' => 'service_unavailable'];
        }
        $data = json_decode($raw, true);
        $translated = $data['responseData']['translatedText'] ?? null;
        if (!is_string($translated) || $translated === '') {
            return ['ok' => false, 'error' => 'service_unavailable'];
        }
        $this->update($id, ['content_tr' => $translated]);
        return ['ok' => true, 'text' => $translated];
    }
}
