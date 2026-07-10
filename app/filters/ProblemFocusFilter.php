<?php
declare(strict_types=1);

/**
 * Problem/ihtiyaç odağı zorunlu: pozitif kalıp sayısı − negatif (saf teknik destek)
 * kalıp sayısı, normal feed'de ≥1, strict (haber) feed'de ≥2 olmalı. Feed'in sektörü
 * varsa o sektöre özel ek kalıplar da uygulanır. Bu yoksa içerik problem değildir → elenir.
 * (Kalıplar: config['filter_patterns'], ['filter_patterns_negative'], ['sector_filter_patterns'].)
 */
class ProblemFocusFilter implements Filter
{
    public function passes(array $item, array $feed): bool
    {
        $cfg = config();
        $patterns = $cfg['filter_patterns'];
        if (!empty($feed['sector']) && !empty($cfg['sector_filter_patterns'][$feed['sector']])) {
            $patterns = array_merge($patterns, $cfg['sector_filter_patterns'][$feed['sector']]);
        }

        $lower = mb_strtolower($item['title'] . ' ' . $item['summary']);
        $positive = 0;
        foreach ($patterns as $p) {
            if (mb_strpos($lower, $p) !== false) {
                $positive++;
            }
        }
        if ($positive === 0) {
            return false;
        }
        $negative = 0;
        foreach ($cfg['filter_patterns_negative'] as $p) {
            if (mb_strpos($lower, $p) !== false) {
                $negative++;
            }
        }

        $minScore = !empty($feed['strict']) ? 2 : 1;
        return ($positive - $negative) >= $minScore;
    }
}
