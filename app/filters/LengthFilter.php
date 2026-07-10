<?php
declare(strict_types=1);

/**
 * Çok kısa içerikleri eler ("Ben de", "Katılıyorum", "Harika olmuş", boş yorum).
 * Eşik: config['min_content_words'].
 */
class LengthFilter implements Filter
{
    public function passes(array $item, array $feed): bool
    {
        $text = trim($item['title'] . ' ' . $item['summary']);
        $min = (int) (config()['min_content_words'] ?? 5);
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        return count($words) >= $min;
    }
}
