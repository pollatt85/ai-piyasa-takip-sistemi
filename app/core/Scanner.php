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
        $aiClassifier = new AiClassifier();
        $stats = ['fetched' => 0, 'matched' => 0, 'new' => 0, 'updated' => 0, 'failed_feeds' => []];
        $total = count($cfg['feeds']);

        foreach ($cfg['feeds'] as $i => $feed) {
            if ($onProgress !== null) {
                $onProgress($i, $total, $feed['source']);
            }
            if ($i > 0) {
                sleep(6); // Reddit art arda istekleri 429 ile keser
            }
            $type = $feed['type'] ?? 'rss';
            $items = $type === 'html_list' ? $this->fetchHtmlList($feed['url']) : $this->fetchFeed($feed['url']);
            if ($items === null) {
                sleep(8);
                $items = $type === 'html_list' ? $this->fetchHtmlList($feed['url']) : $this->fetchFeed($feed['url']);
            }
            if ($items === null) {
                $stats['failed_feeds'][] = $feed['url'];
                continue;
            }

            $patterns = $cfg['filter_patterns'];
            if (!empty($feed['sector']) && !empty($cfg['sector_filter_patterns'][$feed['sector']])) {
                $patterns = array_merge($patterns, $cfg['sector_filter_patterns'][$feed['sector']]);
            }

            foreach ($items as $item) {
                $stats['fetched']++;
                $text = $item['title'] . ' ' . $item['summary'];
                if (!$this->passesFilter($text, $patterns, $cfg['filter_patterns_negative'])) {
                    continue;
                }
                if ($aiClassifier->verify($text) === false) {
                    continue; // ücretsiz LLM bunun bir iş fikri sinyali olmadığını doğruladı
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

    /**
     * Birçok kaynak (webrazzi, hukuki.net, meslek forumları vb.) TLS handshake'te ara
     * sertifikayı göndermiyor; PHP'nin OpenSSL'i (tarayıcıların/Windows'un aksine) eksik
     * zinciri tamamlayamıyor ve güncel bir CA paketiyle bile doğrulama başarısız oluyor
     * (bkz. MD/docs/07-veri-kaynaklari.md). Bu yüzden sertifika doğrulaması kapatılır —
     * sadece herkese açık RSS/HTML içeriği okunuyor, kimlik bilgisi alışverişi yok ve
     * sonuç zaten aynı kural tabanlı filtre/sınıflandırma zincirinden geçiyor.
     */
    private function httpContext()
    {
        return stream_context_create([
            'http' => [
                'timeout' => 12,
                'user_agent' => 'Mozilla/5.0 (PiyasaTakip/1.0; localhost)',
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
    }

    /** RSS 2.0 ve Atom (Reddit) formatlarını okur. */
    private function fetchFeed(string $url): ?array
    {
        $xmlRaw = @file_get_contents($url, false, $this->httpContext());
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

    /**
     * RSS/Atom'u olmayan forum sayfalarından (ör. R10.net) thread başlıklarını çeker.
     * Kaynağın HTML yapısı değişirse sessizce null döner; tarama diğer feed'lerle devam eder.
     */
    private function fetchHtmlList(string $url): ?array
    {
        $html = @file_get_contents($url, false, $this->httpContext());
        if ($html === false) {
            return null;
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_use_internal_errors(false);
        $xpath = new DOMXPath($doc);
        $anchors = $xpath->query('//div[@class="title"]/a[@rel="ugc"]');
        if ($anchors === false || $anchors->length === 0) {
            return null;
        }

        $items = [];
        foreach ($anchors as $a) {
            $items[] = [
                'title' => trim($a->textContent),
                'summary' => '',
                'link' => trim($a->getAttribute('href')),
            ];
        }
        return $items;
    }

    /** Pozitif kalıp sayısı negatif (alakasız/teknik destek) kalıp sayısından fazlaysa geçer. */
    private function passesFilter(string $text, array $patterns, array $negativePatterns = []): bool
    {
        $lower = mb_strtolower($text);
        $positiveHits = 0;
        foreach ($patterns as $pattern) {
            if (mb_strpos($lower, $pattern) !== false) {
                $positiveHits++;
            }
        }
        if ($positiveHits === 0) {
            return false;
        }
        $negativeHits = 0;
        foreach ($negativePatterns as $pattern) {
            if (mb_strpos($lower, $pattern) !== false) {
                $negativeHits++;
            }
        }
        return $positiveHits > $negativeHits;
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
