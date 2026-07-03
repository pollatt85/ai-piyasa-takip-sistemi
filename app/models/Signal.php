<?php
declare(strict_types=1);

class Signal extends Model
{
    protected string $table = 'signals';

    // --- Çeviri (MyMemory API, ücretsiz/key'siz) ---

    public function translate(int $id): array
    {
        $row = $this->find($id);
        if ($row === null) {
            return ['ok' => false, 'error' => 'not_found'];
        }
        if (!empty($row['content_tr'])) {
            return ['ok' => true, 'text' => $row['content_tr']];
        }
        $translated = $this->callMyMemory((string) $row['content']);
        if ($translated === null) {
            return ['ok' => false, 'error' => 'service_unavailable'];
        }
        $this->update($id, ['content_tr' => $translated]);
        return ['ok' => true, 'text' => $translated];
    }

    private function callMyMemory(string $text): ?string
    {
        $url = 'https://api.mymemory.translated.net/get?' . http_build_query([
            'q' => $text,
            'langpair' => 'en|tr',
        ]);
        $ctx = stream_context_create(['http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (PiyasaTakip/1.0; localhost)',
        ]]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            return null;
        }
        $data = json_decode($raw, true);
        $translated = $data['responseData']['translatedText'] ?? null;
        return is_string($translated) && $translated !== '' ? $translated : null;
    }

    // --- Skor (bkz. MD/docs/03-analiz-siniflandirma.md) ---

    public function computeScore(int $repeat, int $variety, int $manual): int
    {
        $w = config()['score'];
        return $repeat * $w['w_repeat'] + $variety * $w['w_variety'] + $manual * $w['w_manual'];
    }

    public function recompute(int $id): void
    {
        $row = $this->find($id);
        if ($row === null) {
            return;
        }
        $score = $this->computeScore((int) $row['repeat_count'], (int) $row['source_variety'], (int) $row['manual_score']);
        $status = $score >= config()['score']['threshold'] ? 'olgun_firsat' : 'ham';
        if ($status === 'olgun_firsat' && $row['status'] !== 'olgun_firsat') {
            (new Log())->add('firsat_olgunlasti', 'Fırsat olgunlaştı: ' . mb_substr((string) $row['content'], 0, 80), $id);
        }
        $this->update($id, ['score' => $score, 'status' => $status]);
    }

    /**
     * Tarama çıktısını ekler; aynı içerik (hash) varsa tekrar sayacını ve
     * kaynak çeşitliliğini günceller (bkz. MD/docs/02-firsat-tarama.md — olgunlaşma).
     */
    public function upsertFromScan(int $subSectorId, string $source, string $url, string $content, string $region): string
    {
        $hash = sha1(mb_strtolower(trim(mb_substr($content, 0, 160))));
        $existing = $this->row('SELECT * FROM signals WHERE content_hash = ?', [$hash]);

        if ($existing !== null) {
            $sources = array_filter(array_map('trim', explode(',', (string) $existing['source'])));
            if (!in_array($source, $sources, true)) {
                $sources[] = $source;
            }
            $this->update((int) $existing['id'], [
                'repeat_count' => (int) $existing['repeat_count'] + 1,
                'source' => implode(',', $sources),
                'source_variety' => count($sources),
            ]);
            $this->recompute((int) $existing['id']);
            return 'updated';
        }

        $id = $this->insert([
            'sub_sector_id' => $subSectorId,
            'source' => $source,
            'source_url' => $url,
            'content' => $content,
            'content_hash' => $hash,
            'region' => $region,
            'repeat_count' => 1,
            'source_variety' => 1,
            'manual_score' => 0,
            'score' => $this->computeScore(1, 1, 0),
            'status' => 'ham',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        (new Log())->add('sinyal_eklendi', 'Yeni sinyal: ' . mb_substr($content, 0, 80), $id);
        return 'new';
    }

    // --- Listeler ---

    public function filtered(array $f): array
    {
        $sql = 'SELECT sig.*, ss.name AS sub_sector_name, s.name AS sector_name
                FROM signals sig
                LEFT JOIN sub_sectors ss ON ss.id = sig.sub_sector_id
                LEFT JOIN sectors s ON s.id = ss.sector_id
                WHERE 1=1';
        $params = [];
        if (!empty($f['status'])) {
            $sql .= ' AND sig.status = ?';
            $params[] = $f['status'];
        }
        if (!empty($f['region'])) {
            $sql .= ' AND sig.region = ?';
            $params[] = $f['region'];
        }
        if (!empty($f['sector_id'])) {
            $sql .= ' AND s.id = ?';
            $params[] = $f['sector_id'];
        }
        if (!empty($f['favorites'])) {
            $sql .= ' AND sig.is_favorite = 1';
        }
        if (!empty($f['since'])) {
            $sql .= ' AND sig.created_at >= ?';
            $params[] = $f['since'];
        }
        $sql .= ' ORDER BY sig.score DESC, sig.created_at DESC LIMIT ' . (int) ($f['limit'] ?? 200);
        return $this->rows($sql, $params);
    }

    public function todayCount(): int
    {
        return (int) $this->value("SELECT COUNT(*) FROM signals WHERE created_at >= ?", [date('Y-m-d') . ' 00:00:00']);
    }

    public function weekMatureCount(): int
    {
        return (int) $this->value(
            "SELECT COUNT(*) FROM signals WHERE status = 'olgun_firsat' AND created_at >= ?",
            [date('Y-m-d H:i:s', strtotime('-7 days'))]
        );
    }

    public function sectorDistribution(): array
    {
        return $this->rows(
            'SELECT s.name, COUNT(sig.id) AS total
             FROM signals sig
             JOIN sub_sectors ss ON ss.id = sig.sub_sector_id
             JOIN sectors s ON s.id = ss.sector_id
             GROUP BY s.id ORDER BY total DESC'
        );
    }
}
