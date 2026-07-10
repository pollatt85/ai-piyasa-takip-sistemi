<?php
declare(strict_types=1);

/**
 * Tekil ham kanıt (hangi kaynak, hangi URL, ne zaman) — bir probleme bağlı. content_hash
 * UNIQUE olduğundan aynı içerik tekrar taranırsa yeniden eklenmez (mention sayısı şişmez).
 */
class ProblemMention extends Model
{
    protected string $table = 'problem_mentions';

    public static function hash(string $content): string
    {
        return sha1(mb_strtolower(trim(mb_substr($content, 0, 160))));
    }

    /** Bu içeriğin hash'i zaten kayıtlıysa bağlı problem id'sini döner, yoksa null. */
    public function existingProblemId(string $content): ?int
    {
        $id = $this->value('SELECT problem_id FROM problem_mentions WHERE content_hash = ?', [self::hash($content)]);
        return $id === null ? null : (int) $id;
    }

    /**
     * Kanıtı ekler. Zaten varsa (aynı hash) false döner — çağıran mention sayacını artırmaz.
     * Yeni eklendiyse true.
     */
    public function add(int $problemId, string $source, string $url, string $content, string $region): bool
    {
        $hash = self::hash($content);
        if ($this->value('SELECT 1 FROM problem_mentions WHERE content_hash = ?', [$hash]) !== null) {
            return false;
        }
        $this->insert([
            'problem_id' => $problemId,
            'source' => $source,
            'source_url' => $url,
            'content' => $content,
            'content_hash' => $hash,
            'region' => $region,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    /** Bir problemin tüm kanıtları (detay sayfası). */
    public function forProblem(int $problemId): array
    {
        return $this->rows(
            'SELECT * FROM problem_mentions WHERE problem_id = ? ORDER BY created_at DESC',
            [$problemId]
        );
    }
}
