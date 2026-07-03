<?php
declare(strict_types=1);

// Skor ağırlıkları ve eşik, kod içine gömülmez (bkz. MD/docs/03-analiz-siniflandirma.md)
return [
    'score' => [
        'w_repeat' => 2,   // tekrar sıklığı — ana ağırlık
        'w_variety' => 3,  // kaynak çeşitliliği — yankı odasını kırar
        'w_manual' => 1,   // manuel puan — ince ayar
        'threshold' => 10, // eşik: üzeri "olgun_firsat"
    ],

    // Ucuz ön filtre: ihtiyaç/şikâyet kalıpları (küçük harf aranır)
    'filter_patterns' => [
        'nasıl yapılır', 'nasıl yaparım', 'nasıl bulabilirim', 'çözüm var mı',
        'keşke', 'tavsiye', 'öneri', 'önerir misiniz', 'arıyorum', 'var mı bilen',
        'yardım', 'sorun yaşıyorum', 'bulamıyorum',
        'how do i', 'how to', 'how can i', 'is there a tool', 'is there an app',
        'is there a way', 'looking for', 'recommend', 'wish there was',
        'any solution', 'alternative to', 'struggling with', 'need help',
        'anyone know', 'best way to', 'need advice', 'what do you use',
    ],

    // Ücretsiz, API key gerektirmeyen kaynaklar (RSS + Reddit RSS).
    // Ücretli/kotalı kaynaklar (Google Maps, Custom Search) bilinçli olarak dışarıda.
    'feeds' => [
        ['url' => 'https://www.reddit.com/r/Entrepreneur/.rss',  'source' => 'reddit', 'region' => 'Global'],
        ['url' => 'https://www.reddit.com/r/smallbusiness/.rss', 'source' => 'reddit', 'region' => 'Global'],
        ['url' => 'https://www.reddit.com/r/SideProject/.rss',   'source' => 'reddit', 'region' => 'Global'],
        ['url' => 'https://www.reddit.com/r/Turkey/.rss',        'source' => 'reddit', 'region' => 'TR'],
        ['url' => 'https://webrazzi.com/feed/',                  'source' => 'rss',    'region' => 'TR'],
    ],
];
