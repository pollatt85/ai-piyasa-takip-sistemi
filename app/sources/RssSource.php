<?php
declare(strict_types=1);

/**
 * RSS 2.0 ve Atom akışlarını okur. Reddit, Google News, Hacker News (hnrss),
 * Product Hunt, YouTube kanal RSS ve App Store yorum RSS'i hepsi bu adapter'ı kullanır.
 */
class RssSource extends SourceAdapter
{
    public function fetch(array $feed): ?array
    {
        $xmlRaw = @file_get_contents($feed['url'], false, $this->httpContext());
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
                    'date' => isset($it->pubDate) ? (strtotime((string) $it->pubDate) ?: null) : null,
                ];
            }
        } elseif (isset($xml->entry)) {
            foreach ($xml->entry as $entry) {
                $link = '';
                foreach ($entry->link as $l) {
                    $link = (string) $l['href'];
                    break;
                }
                $link = $this->normalizeAppleReviewUrl($link);
                // Yaş için 'published' (gönderi/sorulma tarihi) tercih edilir; yoksa
                // 'updated' (son aktivite). Böylece SE'de "eski ama bugün aktif" soru,
                // sorulma tarihine göre elenir (bkz. Scanner yaş filtresi).
                $dateStr = isset($entry->published) ? (string) $entry->published
                    : (isset($entry->updated) ? (string) $entry->updated : '');
                $items[] = [
                    'title' => trim((string) $entry->title),
                    'summary' => trim(mb_substr(strip_tags(html_entity_decode((string) $entry->content)), 0, 600)),
                    'link' => $link,
                    'date' => $dateStr !== '' ? (strtotime($dateStr) ?: null) : null,
                ];
            }
        }
        return $items;
    }

    /**
     * App Store yorum feed'lerindeki eski iTunes deep-link'ini
     * (itunes.apple.com/<region>/review?id=<APP_ID>&type=Purple Software)
     * tarayıcıda açılan modern App Store URL'ine çevirir:
     * https://apps.apple.com/<region>/app/id<APP_ID>
     * Apple review kalıbına uymayan linkler (YouTube vb.) değişmeden döner.
     */
    private function normalizeAppleReviewUrl(string $url): string
    {
        if (preg_match('~itunes\.apple\.com/([a-z]{2})/review\?id=(\d+)~', $url, $m)) {
            return "https://apps.apple.com/{$m[1]}/app/id{$m[2]}";
        }
        return $url;
    }
}
