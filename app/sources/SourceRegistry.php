<?php
declare(strict_types=1);

/**
 * feed['type'] → adapter eşlemesi (plug-in kayıt noktası). Scanner buradan adapter
 * çözer; yeni kaynak tipi eklemek için tek yapılacak: adapter sınıfını yaz + $map'e ekle.
 */
class SourceRegistry
{
    /** @var array<string, SourceAdapter> */
    private array $cache = [];

    /** type => adapter sınıf adı */
    private const MAP = [
        'rss' => RssSource::class,
        'html_list' => HtmlListSource::class,
        'github' => GithubSource::class,
    ];

    /** feed'in type'ına uygun adapter; bilinmeyen/eksik tip → null (feed atlanır). */
    public function resolve(array $feed): ?SourceAdapter
    {
        $type = $feed['type'] ?? 'rss';
        $class = self::MAP[$type] ?? null;
        if ($class === null || !class_exists($class)) {
            return null;
        }
        return $this->cache[$type] ??= new $class();
    }
}
