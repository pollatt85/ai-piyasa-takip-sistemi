<?php
declare(strict_types=1);

/**
 * Kural filtresini geçen adaylar için ikinci aşama, ücretsiz LLM analizi (Groq free-tier;
 * bkz. MD/docs/08-ai-entegrasyonu.md). Ham veri asla buraya gönderilmez — sadece
 * FilterPipeline'ı geçmiş metinler. Tek çağrıda: sinyal mi + kanonik başlık/slug (kümeleme
 * anahtarı) + neden önemli + MVP önerisi + 12 fırsat boyutu üretilir.
 *
 * Sağlayıcı devre dışıysa veya kota/ağ/parse hatası olursa fail-open: null döner,
 * sistem kural sonucu + boş boyutlarla devam eder (strict feed'de null'un elemeye
 * dönüşmesi Scanner'ın kararıdır). Tarama hiçbir zaman bu yüzden durmaz.
 */
class AiClassifier
{
    /** 0-100 arası tam sayı boyutlar. */
    private const SCORE_DIMS = [
        'demand_score', 'competition_score', 'ai_solvability', 'automation_fit',
        'technical_difficulty', 'subscription_fit', 'local_opportunity', 'global_opportunity',
    ];

    /**
     * @return array{
     *   is_signal: bool, title: string, slug: string, why: string,
     *   mvp_suggestion: string, mvp_weeks: ?int, revenue_potential: string,
     *   repeat_frequency: string, trend_direction: string,
     *   demand_score: ?int, competition_score: ?int, ai_solvability: ?int,
     *   automation_fit: ?int, technical_difficulty: ?int, subscription_fit: ?int,
     *   local_opportunity: ?int, global_opportunity: ?int
     * }|null  null = değerlendirilemedi (kapalı/kota/ağ/parse hatası)
     */
    public function analyze(string $text): ?array
    {
        $cfg = config()['ai_classifier'] ?? [];
        if (empty($cfg['enabled']) || empty($cfg['api_key'])) {
            return null;
        }

        $system = 'Sana bir forum/yorum/haber metni verilecek. Bunun bir yazılım/tasarım ürünüyle '
            . 'karşılanabilecek GERÇEK bir iş problemi/ihtiyacı olup olmadığını değerlendir. Haber '
            . 'başlığı, satış/reklam ilanı, saf donanım/teknik destek, teşekkür veya gündelik sohbet '
            . 'ise is_signal=false. Sadece aşağıdaki JSON şemasını döndür, başka HİÇBİR şey yazma. '
            . 'Puanlar 0-100 tam sayı. trend_direction sadece "yukarı"|"yatay"|"aşağı". '
            . 'repeat_frequency kısa (ör. "günlük","haftalık","nadir"). revenue_potential kısa '
            . '(ör. "Düşük","Orta","Yüksek" veya "₺10K-50K/ay"). mvp_weeks tahmini hafta (tam sayı). '
            . 'title kısa kanonik problem başlığı (Türkçe), slug title\'ın kısa-çizgili küçük harf hali. '
            . 'Şema: {"is_signal":true,"title":"","slug":"","why":"","mvp_suggestion":"","mvp_weeks":4,'
            . '"demand_score":0,"competition_score":0,"ai_solvability":0,"automation_fit":0,'
            . '"technical_difficulty":0,"revenue_potential":"","subscription_fit":0,'
            . '"local_opportunity":0,"global_opportunity":0,"repeat_frequency":"","trend_direction":"yatay"} '
            . 'Sinyal değilse: {"is_signal":false} yeterli.';

        $payload = json_encode([
            'model' => $cfg['model'],
            'temperature' => 0,
            'max_tokens' => 600,
            // Gemini 2.5 bir "thinking" modeli: iç düşünme tokenları max_tokens bütçesini
            // tüketip JSON'u yarıda keser. 'none' ile düşünme kapatılır → temiz, hızlı çıktı.
            // (OpenAI-uyumlu diğer sağlayıcılar bu alanı yok sayar.)
            'reasoning_effort' => 'none',
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => mb_substr($text, 0, 600)],
            ],
        ], JSON_UNESCAPED_UNICODE);
        if ($payload === false) {
            return null;
        }

        $raw = $this->post($cfg['endpoint'], $cfg['api_key'], $payload);
        if ($raw === null) {
            return null;
        }

        $data = json_decode($raw, true);
        $content = $data['choices'][0]['message']['content'] ?? null;
        if (!is_string($content)) {
            return null;
        }
        $parsed = json_decode($this->extractJson($content), true);
        if (!is_array($parsed) || !array_key_exists('is_signal', $parsed)) {
            return null;
        }

        return $this->normalize($parsed, $text);
    }

    /** LLM bazen JSON'u metne sarar; ilk {...} bloğunu çıkar. */
    private function extractJson(string $content): string
    {
        $content = trim($content);
        if (preg_match('/\{.*\}/s', $content, $m)) {
            return $m[0];
        }
        return $content;
    }

    /** Ham LLM çıktısını şemaya oturt: puanları 0-100 clamp, metinleri trim, slug üret. */
    private function normalize(array $p, string $text): array
    {
        $out = [
            'is_signal' => (bool) $p['is_signal'],
            'title' => trim((string) ($p['title'] ?? '')),
            'slug' => $this->slugify((string) ($p['slug'] ?? ($p['title'] ?? ''))),
            'why' => trim((string) ($p['why'] ?? '')),
            'mvp_suggestion' => trim((string) ($p['mvp_suggestion'] ?? '')),
            'mvp_weeks' => isset($p['mvp_weeks']) && is_numeric($p['mvp_weeks']) ? max(0, (int) $p['mvp_weeks']) : null,
            'revenue_potential' => trim((string) ($p['revenue_potential'] ?? '')),
            'repeat_frequency' => trim((string) ($p['repeat_frequency'] ?? '')),
            'trend_direction' => $this->normTrend((string) ($p['trend_direction'] ?? '')),
        ];
        foreach (self::SCORE_DIMS as $dim) {
            $out[$dim] = isset($p[$dim]) && is_numeric($p[$dim]) ? max(0, min(100, (int) $p[$dim])) : null;
        }
        // title boşsa (ör. is_signal=false) metnin başından türet — kümeleme yine çalışsın.
        if ($out['title'] === '') {
            $out['title'] = trim(mb_substr($text, 0, 90));
        }
        if ($out['slug'] === '') {
            $out['slug'] = $this->slugify($out['title']);
        }
        return $out;
    }

    private function normTrend(string $t): string
    {
        $t = mb_strtolower(trim($t));
        return in_array($t, ['yukarı', 'yatay', 'aşağı'], true) ? $t : 'yatay';
    }

    private function slugify(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $tr = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u'];
        $s = strtr($s, $tr);
        $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
        return trim($s, '-');
    }

    /** POST isteği; 429 (dakikalık kota) yanıtında 3 sn bekleyip bir kez daha dener. */
    private function post(string $endpoint, string $apiKey, string $payload): ?string
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $ctx = stream_context_create(['http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAuthorization: Bearer {$apiKey}\r\n",
                'content' => $payload,
                'timeout' => 12,
                'ignore_errors' => true,
            ]]);
            $raw = @file_get_contents($endpoint, false, $ctx);
            if ($raw === false) {
                return null;
            }
            $status = 0;
            foreach ($http_response_header ?? [] as $h) {
                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m)) {
                    $status = (int) $m[1];
                }
            }
            if ($status !== 429) {
                return $raw;
            }
            sleep(3);
        }
        return null;
    }
}
