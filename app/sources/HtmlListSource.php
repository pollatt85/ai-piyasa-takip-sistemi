<?php
declare(strict_types=1);

/**
 * RSS/Atom'u olmayan forum sayfalarından (ör. R10.net) thread başlıklarını çeker.
 * Kaynağın HTML yapısı değişirse sessizce null döner; tarama diğer feed'lerle devam eder.
 */
class HtmlListSource extends SourceAdapter
{
    public function fetch(array $feed): ?array
    {
        $html = @file_get_contents($feed['url'], false, $this->httpContext());
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
}
