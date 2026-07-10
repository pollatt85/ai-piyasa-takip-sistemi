<?php
declare(strict_types=1);

/**
 * Hibrit kümeleme: aynı problemi tek "problems" kaydında toplar.
 *  1) Aynı içerik (hash) zaten kayıtlıysa → yeni sayılmaz (duplicate).
 *  2) Aynı alt sektör+bölgedeki adaylarla token benzerliği (Jaccard):
 *     >= high → doğrudan birleştir; low..high belirsiz bant + Groq slug eşleşmesi → birleştir.
 *  3) Eşleşme yoksa yeni problem.
 * Groq erişilemezse (analysis=null) yalnızca token benzerliğine düşer (fail-open).
 */
class ProblemCluster
{
    private const STOPWORDS = [
        've', 'ile', 'için', 'bir', 'bu', 'şu', 'çok', 'daha', 'ama', 'gibi', 'mi', 'mı',
        'var', 'yok', 'ben', 'sen', 'biz', 'nasıl', 'ne', 'kim', 'the', 'and', 'for', 'with',
        'you', 'are', 'how', 'what', 'any', 'can', 'need', 'want', 'this', 'that', 'have',
    ];

    public function __construct(
        private Problem $problems = new Problem(),
        private ProblemMention $mentions = new ProblemMention()
    ) {
    }

    /**
     * @return 'new'|'updated'|'duplicate'
     */
    public function assign(string $content, ?array $analysis, int $subSectorId, string $region, string $source, string $url): string
    {
        // 1) Bu tam içerik zaten bir probleme bağlıysa tekrar sayma.
        if ($this->mentions->existingProblemId($content) !== null) {
            return 'duplicate';
        }

        $refText = ($analysis['title'] ?? '') !== '' ? $analysis['title'] : $content;
        $tokens = $this->tokenize($refText);
        $matchId = $this->findMatch($tokens, $analysis, $subSectorId, $region);

        if ($matchId !== null) {
            $this->mentions->add($matchId, $source, $url, $content, $region);
            $this->problems->merge($matchId, $source);
            if ($analysis !== null) {
                $this->problems->applyAnalysis($matchId, $analysis);
            }
            return 'updated';
        }

        $fallbackTitle = trim(mb_substr($content, 0, 90));
        $id = $this->problems->createFromAnalysis($subSectorId, $region, $source, $fallbackTitle, $analysis);
        $this->mentions->add($id, $source, $url, $content, $region);
        return 'new';
    }

    /** En iyi adayı seç: yüksek benzerlik → doğrudan; belirsiz bant → Groq slug eşleşmesi. */
    private function findMatch(array $tokens, ?array $analysis, int $subSectorId, string $region): ?int
    {
        if ($tokens === []) {
            return null;
        }
        $cfg = config()['cluster'];
        $candidates = $this->problems->candidates($subSectorId, $region);

        $bestId = null;
        $bestSim = 0.0;
        foreach ($candidates as $c) {
            $sim = $this->jaccard($tokens, $this->tokenize((string) $c['title']));
            if ($sim > $bestSim) {
                $bestSim = $sim;
                $bestId = (int) $c['id'];
            }
        }

        if ($bestSim >= $cfg['high']) {
            return $bestId;
        }
        // Belirsiz bant: Groq slug'ı bir adayın cluster_key'iyle birebir eşleşiyorsa birleştir.
        $slug = $analysis['slug'] ?? '';
        if ($slug !== '' && $bestSim >= $cfg['low']) {
            foreach ($candidates as $c) {
                if ((string) $c['cluster_key'] === $slug) {
                    return (int) $c['id'];
                }
            }
        }
        return null;
    }

    /** @return string[] benzersiz, normalize, stopword'süz token kümesi */
    private function tokenize(string $text): array
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? '';
        $tokens = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_filter($tokens, fn(string $t): bool => mb_strlen($t) >= 3 && !in_array($t, self::STOPWORDS, true));
        return array_values(array_unique($tokens));
    }

    private function jaccard(array $a, array $b): float
    {
        if ($a === [] || $b === []) {
            return 0.0;
        }
        $inter = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));
        return $union === 0 ? 0.0 : $inter / $union;
    }
}
