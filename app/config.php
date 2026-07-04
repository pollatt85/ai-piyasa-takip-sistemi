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
        // Türkiye — meslek forumları (2026-07-04 tarihinde tek tek RSS doğrulaması yapıldı)
        ['url' => 'https://www.hukuki.net/external.php?type=RSS2',                 'source' => 'hukuki-net',      'region' => 'TR'],
        ['url' => 'https://mediforum.net/syndication.php?fid=68',                  'source' => 'mediforum',       'region' => 'TR'],
        ['url' => 'https://www.drtus.com/forum/app.php/feed/forum/51',             'source' => 'drtus',           'region' => 'TR'],
        ['url' => 'https://www.doktorforum.com.tr/forums/-/index.rss',             'source' => 'doktorforum',     'region' => 'TR'],
        ['url' => 'https://doktorlarsitesi.net/feed/',                             'source' => 'doktorlarsitesi', 'region' => 'TR'],
        ['url' => 'https://ogretmenforum.com/forumlar/-/index.rss',                'source' => 'ogretmenforum',   'region' => 'TR'],
        ['url' => 'https://forum.ogretmen.net/latest.rss',                         'source' => 'ogretmen-net',    'region' => 'TR'],
        ['url' => 'https://milliegitimakademileri.com/feed/',                      'source' => 'mea',             'region' => 'TR'],
        ['url' => 'https://www.egitimhane.com/rss.xml',                            'source' => 'egitimhane',      'region' => 'TR'],
        ['url' => 'https://www.tmmob.org.tr/rss.xml',                              'source' => 'tmmob',           'region' => 'TR'],
        ['url' => 'https://veterinerhekim.com.tr/feed/',                           'source' => 'veterinerhekim',  'region' => 'TR'],
        ['url' => 'https://www.gencveteriner.com/index.php?action=.xml;type=rss2', 'source' => 'gencveteriner',   'region' => 'TR'],
    ],

    // Türkiye'deki meslek gruplarına özel forum toplulukları — referans amaçlı.
    // 2026-07-04'te her URL tek tek kontrol edildi: RSS/Atom akışı bulunanlar
    // yukarıdaki 'feeds' dizisine taşınıp taramaya dahil edildi (active => true);
    // RSS'i olmayan veya erişilemeyen (DNS/timeout) siteler listeden silindi.
    'profession_forums' => [
        'Hukuk' => [
            ['name' => 'Hukuki.NET Forumları', 'url' => 'https://www.hukuki.net/forum.php', 'rss' => true, 'active' => true],
        ],
        'Tıp / Sağlık' => [
            ['name' => 'MediFORUM (Hekim)',         'url' => 'https://mediforum.net/forum-hekim.html',         'rss' => true, 'active' => true],
            ['name' => 'DrTus.com Forum',           'url' => 'https://www.drtus.com/forum/viewforum.php?f=51', 'rss' => true, 'active' => true],
            ['name' => 'Doktor Forum Sitesi',       'url' => 'https://www.doktorforum.com.tr/',                'rss' => true, 'active' => true],
            ['name' => 'DoktorlarSitesi.Net Forum', 'url' => 'https://doktorlarsitesi.net/forum-3/',           'rss' => true, 'active' => true],
        ],
        'Muhasebe / Mali Müşavirlik' => [
            ['name' => 'Alomaliye Forum', 'url' => 'https://forum.alomaliye.com/', 'rss' => true, 'active' => true],
        ],
        'Eğitim / Öğretmenlik' => [
            ['name' => 'Öğretmen Forum',               'url' => 'https://ogretmenforum.com/',                     'rss' => true, 'active' => true],
            ['name' => 'EğitimHane',                   'url' => 'https://www.egitimhane.com/index.php?ind=forum', 'rss' => true, 'active' => true],
            ['name' => 'Öğretmen Forum (Memurlar)',    'url' => 'https://forum.ogretmen.net/',                    'rss' => true, 'active' => true],
            ['name' => 'Milli Eğitim Akademisi Forum', 'url' => 'https://milliegitimakademileri.com/forum/',      'rss' => true, 'active' => true],
        ],
        'Mühendislik' => [
            ['name' => 'TMMOB', 'url' => 'https://www.tmmob.org.tr/', 'rss' => true, 'active' => true],
        ],
        'Veterinerlik' => [
            ['name' => 'VeterinerHekim.com.tr', 'url' => 'https://veterinerhekim.com.tr/', 'rss' => true, 'active' => true],
            ['name' => 'GençVeteriner',         'url' => 'https://www.gencveteriner.com/', 'rss' => true, 'active' => true],
        ],
        'Teknoloji / Yazılım' => [
            ['name' => 'Technopat Forum',    'url' => 'https://www.technopat.net/sosyal/forums/', 'rss' => true, 'active' => true],
            ['name' => 'ShiftDelete Forum',  'url' => 'https://forum.shiftdelete.net/',            'rss' => true, 'active' => true],
            ['name' => 'DonanımHaber Forum', 'url' => 'https://forum.donanimhaber.com/',           'rss' => true, 'active' => true],
        ],
    ],
];
