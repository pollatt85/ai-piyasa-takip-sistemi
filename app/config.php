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

    // Kural bazlı filtreden geçen adaylar için ikinci aşama ücretsiz LLM doğrulaması
    // (bkz. app/services/AiClassifier.php, MD/docs/08-ai-entegrasyonu.md). Ücretli
    // Claude API yerine kredi kartsız free-tier bir sağlayıcı (Groq) kullanılır.
    // 'enabled' => false + boş api_key iken hiç ağ çağrısı yapılmaz (varsayılan kapalı,
    // sıfır maliyet). Devreye almak için GROQ_API_KEY ortam değişkeni tanımlanıp
    // 'enabled' => true yapılır.
    'ai_classifier' => [
        'enabled' => false,
        'endpoint' => 'https://api.groq.com/openai/v1/chat/completions',
        'model' => 'llama-3.1-8b-instant',
        'api_key' => getenv('GROQ_API_KEY') ?: '',
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

    // Saf teknik destek/donanım arızası kalıpları — iş fikri sinyali DEĞİL.
    // passesFilter() pozitif kalıp sayısı bunlardan fazlaysa içeriği eler
    // (bkz. Scanner::passesFilter — tarama sonrası alakasız yorum şikâyeti).
    'filter_patterns_negative' => [
        'format attım', 'windows açılmıyor', 'bilgisayarım açılmıyor', 'açılmıyor donuyor',
        'sürücü hatası', 'driver hatası', 'mavi ekran', 'ekran kartı arızası',
        'bios ayarları', 'windows kurulumu', 'virüs bulaştı', 'lisans anahtarı',
        'wifi bağlanmıyor', 'modem ayarları', 'yazıcı bağlanmıyor', 'ses gelmiyor',
        'mikrofon çalışmıyor', 'kamera görüntü vermiyor', 'telefon açılmıyor',
        'batarya şişti', 'ekran kırıldı', 'root atarken hata', 'flash yaparken hata',
        'anakart arızası', 'psu arızası', 'fan sesi geliyor', 'bilgisayar kasması',
    ],

    // Meslek grubuna özel ek ihtiyaç/şikâyet kalıpları — 'sector_filter_patterns' anahtarları
    // 'profession_forums' altındaki grup adlarıyla ve feeds[].sector değeriyle eşleşir.
    // Global filter_patterns'a bu grubun feed'lerinde ek olarak uygulanır.
    'sector_filter_patterns' => [
        'Hukuk' => [
            'dilekçe nasıl yazılır', 'avukat tutmadan', 'avukatsız nasıl', 'dava açmak istiyorum',
            'hangi avukata gitsem', 'sözleşme hazırlatmak istiyorum',
        ],
        'Tıp / Sağlık' => [
            'randevu alamıyorum', 'reçete yenileme', 'hangi doktora gitsem', 'sonuçlarımı nereden takip',
            'muayene sırası', 'sevk almadan',
        ],
        'Muhasebe / Mali Müşavirlik' => [
            'beyanname nasıl', 'e-fatura sorun', 'muhasebeciye ihtiyacım', 'defter tutmak istiyorum',
            'ba bs bildirimi', 'kdv iadesi nasıl',
        ],
        'Eğitim / Öğretmenlik' => [
            'öğrenci takibi nasıl', 'veli ile iletişim', 'ödev takibi için', 'sınav sonuçlarını nasıl',
            'ders programı hazırlama', 'online sınav yapmak istiyorum',
        ],
        'Mühendislik' => [
            'proje takibi için', 'saha ekibini nasıl', 'metraj hesaplama', 'teklif hazırlama programı',
        ],
        'Veterinerlik' => [
            'aşı takibi nasıl', 'hasta kaydı için', 'randevu sistemi arıyorum', 'hayvan sahibine hatırlatma',
        ],
        'Teknoloji / Yazılım' => [
            'freelance iş arıyorum', 'proje teklifi nasıl', 'müşteri bulamıyorum', 'iş takibi için program',
        ],
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
        ['url' => 'https://www.technopat.net/sosyal/forums/-/index.rss', 'source' => 'technopat', 'region' => 'TR', 'sector' => 'Teknoloji / Yazılım'],
        ['url' => 'https://forum.shiftdelete.net/forums/-/index.rss',    'source' => 'sdn-forum', 'region' => 'TR', 'sector' => 'Teknoloji / Yazılım'],
        ['url' => 'https://forum.alomaliye.com/forums/-/index.rss',      'source' => 'alomaliye', 'region' => 'TR', 'sector' => 'Muhasebe / Mali Müşavirlik'],
        // Türkiye — meslek forumları (2026-07-04 tarihinde tek tek RSS doğrulaması yapıldı)
        ['url' => 'https://www.hukuki.net/external.php?type=RSS2',                 'source' => 'hukuki-net',      'region' => 'TR', 'sector' => 'Hukuk'],
        ['url' => 'https://mediforum.net/syndication.php?fid=68',                  'source' => 'mediforum',       'region' => 'TR', 'sector' => 'Tıp / Sağlık'],
        ['url' => 'https://www.drtus.com/forum/app.php/feed/forum/51',             'source' => 'drtus',           'region' => 'TR', 'sector' => 'Tıp / Sağlık'],
        ['url' => 'https://www.doktorforum.com.tr/forums/-/index.rss',             'source' => 'doktorforum',     'region' => 'TR', 'sector' => 'Tıp / Sağlık'],
        ['url' => 'https://doktorlarsitesi.net/feed/',                             'source' => 'doktorlarsitesi', 'region' => 'TR', 'sector' => 'Tıp / Sağlık'],
        ['url' => 'https://ogretmenforum.com/forumlar/-/index.rss',                'source' => 'ogretmenforum',   'region' => 'TR', 'sector' => 'Eğitim / Öğretmenlik'],
        ['url' => 'https://forum.ogretmen.net/latest.rss',                         'source' => 'ogretmen-net',    'region' => 'TR', 'sector' => 'Eğitim / Öğretmenlik'],
        ['url' => 'https://milliegitimakademileri.com/feed/',                      'source' => 'mea',             'region' => 'TR', 'sector' => 'Eğitim / Öğretmenlik'],
        ['url' => 'https://www.egitimhane.com/rss.xml',                            'source' => 'egitimhane',      'region' => 'TR', 'sector' => 'Eğitim / Öğretmenlik'],
        ['url' => 'https://www.tmmob.org.tr/rss.xml',                              'source' => 'tmmob',           'region' => 'TR', 'sector' => 'Mühendislik'],
        ['url' => 'https://veterinerhekim.com.tr/feed/',                           'source' => 'veterinerhekim',  'region' => 'TR', 'sector' => 'Veterinerlik'],
        ['url' => 'https://www.gencveteriner.com/index.php?action=.xml;type=rss2', 'source' => 'gencveteriner',   'region' => 'TR', 'sector' => 'Veterinerlik'],
        // R10.net — RSS yok (JS ile yüklenen freelancer/webmaster forumu), kategori
        // sayfalarından thread başlıkları HTML olarak çekilir (bkz. Scanner::fetchHtmlList).
        ['url' => 'https://www.r10.net/seo-danismanligi/',              'source' => 'r10', 'region' => 'TR', 'type' => 'html_list'],
        ['url' => 'https://www.r10.net/yazilim-kodlama-is-ilanlari/',   'source' => 'r10', 'region' => 'TR', 'type' => 'html_list'],
        ['url' => 'https://www.r10.net/ecommerce-yazilimlari/',         'source' => 'r10', 'region' => 'TR', 'type' => 'html_list'],
        ['url' => 'https://www.r10.net/script-satisi/',                 'source' => 'r10', 'region' => 'TR', 'type' => 'html_list'],
        // App Store müşteri yorumları — Apple'ın resmi, ücretsiz, key gerektirmeyen
        // RSS'i (Atom formatında, fetchFeed() zaten Atom'u destekliyor). Rakip
        // uygulamalarda "keşke şu özellik olsa / alternatif yok" gibi sinyaller için.
        ['url' => 'https://itunes.apple.com/tr/rss/customerreviews/id=524362642/sortby=mostrecent/xml',  'source' => 'appstore-trendyol',    'region' => 'TR'],
        ['url' => 'https://itunes.apple.com/tr/rss/customerreviews/id=481035064/sortby=mostrecent/xml',  'source' => 'appstore-hepsiburada', 'region' => 'TR'],
        ['url' => 'https://itunes.apple.com/tr/rss/customerreviews/id=1146507477/sortby=mostrecent/xml', 'source' => 'appstore-papara',      'region' => 'TR'],
        ['url' => 'https://itunes.apple.com/tr/rss/customerreviews/id=1003826863/sortby=mostrecent/xml', 'source' => 'appstore-kolayrandevu','region' => 'TR'],
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
