<?php
declare(strict_types=1);

/**
 * Gürültü elemesi: teşekkür/mizah/selamlaşma/reklam/spam/clickbait. Yanlış-pozitifi
 * azaltmak için yalnızca gürültünün BASKIN olduğu (kısa + kalıp içeren) içerikleri
 * reddeder; uzun metinler ProblemFocusFilter + LLM'e bırakılır. Kalıplar: config['noise_patterns'].
 */
class NoiseFilter implements Filter
{
    private const DOMINANT_WORD_LIMIT = 10; // bu kadar kelimeden kısa metinde tek kalıp yeter

    public function passes(array $item, array $feed): bool
    {
        $text = trim($item['title'] . ' ' . $item['summary']);
        $lower = mb_strtolower($text);
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $hits = 0;
        foreach (config()['noise_patterns'] ?? [] as $pattern) {
            if (mb_strpos($lower, $pattern) !== false) {
                $hits++;
            }
        }
        if ($hits === 0) {
            return true;
        }
        // Kısa metinde tek gürültü kalıbı → ele; uzun metinde ancak yoğun gürültü → ele.
        if (count($words) <= self::DOMINANT_WORD_LIMIT) {
            return false;
        }
        return $hits < 2;
    }
}
