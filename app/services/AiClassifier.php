<?php
declare(strict_types=1);

/**
 * Kural bazlı filtreden geçen adaylar için ikinci aşama, ücretsiz LLM doğrulaması
 * (bkz. MD/docs/08-ai-entegrasyonu.md). Ham veri asla buraya gönderilmez — sadece
 * Scanner::passesFilter()'ı geçmiş metinler. Sağlayıcı devre dışıysa veya kota/ağ
 * hatası olursa fail-open davranır (null döner, kural tabanlı sonuç kullanılır);
 * tarama hiçbir zaman bu yüzden durmaz.
 */
class AiClassifier
{
    /** @return bool|null true=iş fikri sinyali, false=alakasız, null=doğrulanamadı (kural sonucuna güven) */
    public function verify(string $text): ?bool
    {
        $cfg = config()['ai_classifier'] ?? [];
        if (empty($cfg['enabled']) || empty($cfg['api_key'])) {
            return null;
        }

        $payload = json_encode([
            'model' => $cfg['model'],
            'temperature' => 0,
            'max_tokens' => 20,
            'messages' => [
                ['role' => 'system', 'content' => 'Bir metnin gerçek bir iş fikri/ihtiyaç sinyali mi yoksa '
                    . 'alakasız ya da saf teknik destek mesajı mı olduğuna karar ver. Sadece şu formatta '
                    . 'JSON döndür, başka hiçbir şey yazma: {"is_signal": true} veya {"is_signal": false}'],
                ['role' => 'user', 'content' => mb_substr($text, 0, 500)],
            ],
        ]);
        if ($payload === false) {
            return null;
        }

        $ctx = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAuthorization: Bearer {$cfg['api_key']}\r\n",
            'content' => $payload,
            'timeout' => 10,
            'ignore_errors' => true,
        ]]);
        $raw = @file_get_contents($cfg['endpoint'], false, $ctx);
        if ($raw === false) {
            return null;
        }

        $data = json_decode($raw, true);
        $content = $data['choices'][0]['message']['content'] ?? null;
        if (!is_string($content)) {
            return null;
        }
        $parsed = json_decode(trim($content), true);
        if (!is_array($parsed) || !array_key_exists('is_signal', $parsed)) {
            return null;
        }
        return (bool) $parsed['is_signal'];
    }
}
