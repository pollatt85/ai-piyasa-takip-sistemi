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
}
