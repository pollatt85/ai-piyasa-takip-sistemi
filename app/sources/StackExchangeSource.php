<?php
declare(strict_types=1);

/**
 * Stack Exchange API'sinden (api.stackexchange.com) soruları SORULMA tarihine göre
 * çeker. SE'nin RSS /feeds ucu son aktiviteye göre sıralı olduğundan eski (ama bugün
 * aktif) sorular döner; yaş filtresi bunları eler. Bu adapter sort=creation kullanarak
 * gerçekten yeni sorulan "şu işi yapan yazılım var mı" sinyallerini getirir.
 * Public API, key gerektirmez (kota: 300 istek/gün/IP; key ile 10k). Yanıt JSON.
 * feed['url'] doğrudan bir https://api.stackexchange.com/2.3/questions?...&sort=creation
 * adresidir (site & pagesize & filter=withbody parametreleri config'te verilir).
 */
class StackExchangeSource extends SourceAdapter
{
    public function fetch(array $feed): ?array
    {
        $raw = @file_get_contents($feed['url'], false, $this->httpContext());
        if ($raw === false) {
            return null;
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
            return null;
        }

        $items = [];
        foreach ($data['items'] as $it) {
            $body = isset($it['body']) ? strip_tags((string) $it['body']) : '';
            $items[] = [
                'title' => trim(html_entity_decode((string) ($it['title'] ?? ''))),
                'summary' => trim(mb_substr(html_entity_decode($body), 0, 600)),
                'link' => trim((string) ($it['link'] ?? '')),
                // creation_date zaten unix timestamp — yaş filtresi bunu kullanır.
                'date' => isset($it['creation_date']) ? (int) $it['creation_date'] : null,
            ];
        }
        return $items;
    }
}
