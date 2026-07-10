<?php
declare(strict_types=1);

/**
 * GitHub search API'sinden (issues/discussions başlıkları) ilgili ihtiyaç/talep
 * sinyallerini çeker. Public API, key gerektirmez (rate-limitli: aranmış istek/dakika).
 * feed['url'] doğrudan bir https://api.github.com/search/issues?q=... adresidir.
 * Yanıt RSS değil JSON olduğu için ayrı adapter — plug-in mimarinin örneği.
 */
class GithubSource extends SourceAdapter
{
    public function fetch(array $feed): ?array
    {
        // GitHub API User-Agent zorunlu kılar + JSON Accept başlığı ister.
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 12,
                'header' => "Accept: application/vnd.github+json\r\n",
                'user_agent' => 'PiyasaTakip/1.0 (localhost)',
                'ignore_errors' => true,
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);
        $raw = @file_get_contents($feed['url'], false, $ctx);
        if ($raw === false) {
            return null;
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
            return null;
        }

        $items = [];
        foreach ($data['items'] as $it) {
            $items[] = [
                'title' => trim((string) ($it['title'] ?? '')),
                'summary' => trim(mb_substr(strip_tags((string) ($it['body'] ?? '')), 0, 600)),
                'link' => trim((string) ($it['html_url'] ?? '')),
            ];
        }
        return $items;
    }
}
