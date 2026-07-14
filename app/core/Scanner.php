<?php
declare(strict_types=1);

/**
 * Tarama orkestratörü (tamamen ücretsiz kaynaklar). Akış:
 *   SourceRegistry (plug-in adapter) → ham item → FilterPipeline (kayıt ÖNCESİ kalite eleme)
 *   → AiClassifier::analyze (Groq: sinyal + kanonik başlık + 12 boyut) → ProblemCluster (hibrit
 *   kümeleme) → problems/problem_mentions.
 * Fetch/filtre/kümeleme mantığı ilgili sınıflara taşındı; bu sınıf yalnızca akışı yönetir.
 * Hem scripts/scan.php (CLI) hem ScanController (manuel tetikleme) bunu çağırır.
 */
class Scanner
{
    /** @param callable|null $onProgress fn(int $current, int $total, string $source) — feed başına ilerleme */
    public function run(?callable $onProgress = null): array
    {
        $cfg = config();
        $registry = new SourceRegistry();
        $pipeline = new FilterPipeline();
        $aiClassifier = new AiClassifier();
        $cluster = new ProblemCluster();
        $subSectors = (new SubSector())->allWithKeywords();
        $fallbackId = (new SubSector())->fallbackId();

        $stats = ['fetched' => 0, 'matched' => 0, 'new' => 0, 'updated' => 0, 'duplicate' => 0, 'failed_feeds' => []];
        $total = count($cfg['feeds']);

        foreach ($cfg['feeds'] as $i => $feed) {
            if ($onProgress !== null) {
                $onProgress($i, $total, $feed['source']);
            }
            if ($i > 0) {
                sleep(6); // Reddit art arda istekleri 429 ile keser
            }

            $adapter = $registry->resolve($feed);
            if ($adapter === null) {
                $stats['failed_feeds'][] = $feed['url'];
                continue;
            }
            $items = $adapter->fetch($feed);
            if ($items === null) {
                sleep(8);
                $items = $adapter->fetch($feed);
            }
            if ($items === null) {
                $stats['failed_feeds'][] = $feed['url'];
                continue;
            }

            $strict = !empty($feed['strict']);

            $maxAgeDays = (int) ($cfg['max_age_days'] ?? 365);
            $ageCutoff = time() - $maxAgeDays * 86400;

            foreach ($items as $item) {
                $stats['fetched']++;

                // 0) Yaş filtresi — max_age_days'ten eski problem/fırsatı AI'a hiç gönderme
                // (token tasarrufu). Tarih yoksa (R10 HTML vb.) elenmez. Atom'da 'published'
                // = sorulma tarihi kullanıldığından "eski ama bugün aktif" soru da elenir.
                if (!empty($item['date']) && $item['date'] < $ageCutoff) {
                    continue;
                }

                // 1) Kayıt öncesi kalite filtresi — gürültü DB'ye hiç girmez.
                if (!$pipeline->passes($item, $feed)) {
                    continue;
                }

                // 2) Ücretsiz LLM analizi (sinyal mi + kanonik başlık + 12 boyut).
                $text = $item['title'] . ' ' . $item['summary'];
                $analysis = $aiClassifier->analyze($text);
                if ($analysis !== null && !$analysis['is_signal']) {
                    continue; // LLM bunun bir problem sinyali olmadığını doğruladı
                }
                if ($strict && $analysis === null) {
                    continue; // haber/lansman kaynağında LLM onayı alınamadıysa riske girme
                }

                $stats['matched']++;

                // 3) Alt sektör (keyword) + hibrit kümeleme.
                $subSectorId = $this->classify($text, $subSectors, $fallbackId);
                $content = trim(mb_substr($item['title'], 0, 200) . ' — ' . mb_substr($item['summary'], 0, 300), " —");
                $result = $cluster->assign($content, $analysis, $subSectorId, $feed['region'], $feed['source'], $item['link']);
                $stats[$result]++;
            }
        }

        (new Log())->add('tarama', sprintf(
            'Tarama bitti: %d içerik, %d eşleşme, %d yeni, %d güncellenen, %d tekrar',
            $stats['fetched'], $stats['matched'], $stats['new'], $stats['updated'], $stats['duplicate']
        ));
        return $stats;
    }

    /** Alt sektör anahtar kelimeleriyle eşleştirir; eşleşme yoksa fallback. */
    private function classify(string $text, array $subSectors, int $fallbackId): int
    {
        $lower = mb_strtolower($text);
        $bestId = $fallbackId;
        $bestHits = 0;
        foreach ($subSectors as $ss) {
            $hits = 0;
            foreach (array_filter(array_map('trim', explode(',', (string) $ss['keywords']))) as $kw) {
                if (mb_strpos($lower, mb_strtolower($kw)) !== false) {
                    $hits++;
                }
            }
            if ($hits > $bestHits) {
                $bestHits = $hits;
                $bestId = (int) $ss['id'];
            }
        }
        return $bestId;
    }
}
