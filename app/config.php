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
        // Türk forum dili (ihtiyaç/şikâyet kalıpları)
        'hangisini önerirsiniz', 'hangisi daha iyi', 'hangisini alsam', 'sorun',
        'hata alıyorum', 'hata veriyor', 'çözemedim', 'yapamıyorum', 'çalışmıyor',
        'ne kullanıyorsunuz', 'program arıyorum', 'uygulama var mı', 'program var mı',
        'nereden bulabilirim', 'fikir verir misiniz', 'ne yapmalıyım', 'yardımcı olur musunuz',
        'nasıl çözerim', 'öneriniz var mı', 'deneyimi olan var mı', 'kullanan var mı',
        'how do i', 'how to', 'how can i', 'is there a tool', 'is there an app',
        'is there a way', 'looking for', 'recommend', 'wish there was',
        'any solution', 'alternative to', 'struggling with', 'need help',
        'anyone know', 'best way to', 'need advice', 'what do you use',
    ],

    // Ücretsiz, API key gerektirmeyen kaynaklar (RSS + Reddit RSS).
    // Ücretli/kotalı kaynaklar (Google Maps, Custom Search) bilinçli olarak dışarıda.
    'feeds' => [
        // Global (Reddit)
        ['url' => 'https://www.reddit.com/r/Entrepreneur/.rss',  'source' => 'reddit', 'region' => 'Global'],
        ['url' => 'https://www.reddit.com/r/smallbusiness/.rss', 'source' => 'reddit', 'region' => 'Global'],
        ['url' => 'https://www.reddit.com/r/SideProject/.rss',   'source' => 'reddit', 'region' => 'Global'],
        // Türkiye — genel
        ['url' => 'https://www.reddit.com/r/Turkey/.rss',        'source' => 'reddit',       'region' => 'TR'],
        ['url' => 'https://webrazzi.com/feed/',                  'source' => 'webrazzi',     'region' => 'TR'],
        ['url' => 'https://www.chip.com.tr/rss',                 'source' => 'chip',         'region' => 'TR'],
        ['url' => 'https://www.donanimhaber.com/rss/tum/',       'source' => 'donanimhaber', 'region' => 'TR'],
        // Türkiye — meslek/uzmanlık forumları (yeni konu akışları)
        ['url' => 'https://www.technopat.net/sosyal/forums/-/index.rss', 'source' => 'technopat', 'region' => 'TR'],
        ['url' => 'https://forum.shiftdelete.net/forums/-/index.rss',    'source' => 'sdn-forum', 'region' => 'TR'],
        ['url' => 'https://forum.alomaliye.com/forums/-/index.rss',      'source' => 'alomaliye', 'region' => 'TR'],
    ],
];
