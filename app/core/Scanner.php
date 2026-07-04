<?php
declare(strict_types=1);

/**
 * Tarama + ucuz filtre + kural tabanlı sınıflandırma (tamamen ücretsiz kaynaklar).
 * Hem scripts/scan.php (CLI) hem ScanController (manuel tetikleme) bunu çağırır;
 * her iki durumda da sonuç DB'ye yazılır, arayüz sadece okur.
 */
class Scanner
{
    /** @param callable|null $onProgress fn(int $current, int $total, string $source) — feed başına ilerleme bildirimi */
    public function run(?callable $onProgress = null): array
    {
        $cfg = config();
        $signal = new Signal();
        $subSectors = (new SubSector())->allWithKeywords();
        $fallbackId = (new SubSector())->fallbackId();
        $stats = ['fetched' => 0, 'matched' => 0, 'new' => 0, 'updated' => 0, 'failed_feeds' => []];
        $total = count($cfg['feeds']);

        foreach ($cfg['feeds'] as $i => $feed) {
            if ($onProgress !== null) {
                $onProgress($i, $total, $feed['source']);
            }
            if ($i > 0) {
                sleep(6); // Reddit art arda istekleri 429 ile keser
            }
            $items = $this->fetchFeed($feed['url']);
            if ($items === null) {
                sleep(8);
                $items = $this->fetchFeed($feed['url']);
            }
            if ($items === null) {
                $stats['failed_feeds'][] = $feed['url'];
                continue;
            }
            foreach ($items as $item) {
                $stats['fetched']++;
                $text = $item['title'] . ' ' . $item['summary'];
                if (!$this->passesFilter($text, $cfg['filter_patterns'])) {
                    continue;
                }
                $stats['matched']++;
                $subSectorId = $this->classify($text, $subSectors, $fallbackId);
                $content = trim(mb_substr($item['title'], 0, 200) . ' — ' . mb_substr($item['summary'], 0, 300), " —");
                $result = $signal->upsertFromScan($subSectorId, $feed['source'], $item['link'], $content, $feed['region']);
                $stats[$result === 'new' ? 'new' : 'updated']++;
            }
        }

        (new Log())->add('tarama', sprintf(
            'Tarama bitti: %d içerik, %d eşleşme, %d yeni, %d güncellenen sinyal',
            $stats['fetched'], $stats['matched'], $stats['new'], $stats['updated']
        ));
        return $stats;
    }

    /** RSS 2.0 ve Atom (Reddit) formatlarını okur. */
    private function fetchFeed(string $url): ?array
    {
        $ctx = stream_context_create(['http' => [
            'timeout' => 12,
            'user_agent' => 'Mozilla/5.0 (PiyasaTakip/1.0; localhost)',
        ]]);
        $xmlRaw = @file_get_contents($url, false, $ctx);
        if ($xmlRaw === false) {
            return null;
        }
        $xml = @simplexml_load_string($xmlRaw);
        if ($xml === false) {
            return null;
        }

        $items = [];
        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $it) {
                $items[] = [
                    'title' => trim((string) $it->title),
                    'summary' => trim(strip_tags(html_entity_decode((string) $it->description))),
                    'link' => trim((string) $it->link),
                ];
            }
        } elseif (isset($xml->entry)) {
            foreach ($xml->entry as $entry) {
                $link = '';
                foreach ($entry->link as $l) {
                    $link = (string) $l['href'];
                    break;
                }
                $items[] = [
                    'title' => trim((string) $entry->title),
                    'summary' => trim(mb_substr(strip_tags(html_entity_decode((string) $entry->content)), 0, 600)),
                    'link' => $link,
                ];
            }
        }
        return $items;
    }

    private function passesFilter(string $text, array $patterns): bool
    {
        $lower = mb_strtolower($text);
        foreach ($patterns as $pattern) {
            if (mb_strpos($lower, $pattern) !== false) {
                return true;
            }
        }
        return false;
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
