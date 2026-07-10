<?php
declare(strict_types=1);

// Skor ağırlıkları ve eşik, kod içine gömülmez (bkz. MD/docs/03-analiz-siniflandirma.md)
return [
    // Fırsat skoru: AI boyutları (0-100) ağırlıklı bileşke (0-100 taban) + kanıt
    // katkısı (kaç kişi/kaç kaynak) + manuel ince ayar. Groq yoksa boyutlar boş
    // gelir → taban 0, skor yalnızca kanıt/manuel ile oluşur (fail-open).
    // Boyut ağırlıkları toplamı 1.0 olacak şekilde normalize edilir (bkz. Problem::computeScore).
    'score' => [
        'dims' => [
            'demand_score' => 0.28,        // Talep — en önemli
            'ai_solvability' => 0.18,      // AI ile çözülebilirlik
            'automation_fit' => 0.14,      // Otomasyona uygunluk
            'subscription_fit' => 0.14,    // Abonelik modeli uygunluğu
            'competition_inverse' => 0.12, // (100 - Rekabet) — az rakip = yüksek puan
            'global_opportunity' => 0.08,  // Global pazar
            'local_opportunity' => 0.06,   // Yerel pazar
        ],
        'w_mention' => 3,   // kanıt: her ek kişi/tekrar (cap'li)
        'mention_cap' => 10,
        'w_variety' => 5,   // kanıt: kaynak çeşitliliği — yankı odasını kırar
        'w_manual' => 1,    // manuel puan — ince ayar
        'threshold' => 55,  // eşik: üzeri "olgun_firsat" (0-100+ ölçekte)
    ],

    // Kural bazlı filtreden geçen adaylar için ikinci aşama ücretsiz LLM analizi
    // (bkz. app/services/AiClassifier.php, MD/docs/08-ai-entegrasyonu.md). Ücretsiz
    // free-tier sağlayıcı: Google Gemini'nin OpenAI-uyumlu endpoint'i (kod OpenAI
    // formatında kaldığından yalnızca endpoint+model değişir). GEMINI_API_KEY ortam
    // değişkeni öncelikli; tanımlı değilse aşağıdaki key kullanılır (Codespaces'te
    // repo Secret olarak da verilebilir). Key/kota yoksa fail-open: kural sonucu kullanılır.
    'ai_classifier' => [
        'enabled' => true,
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions',
        'model' => 'gemini-2.5-flash',
        'api_key' => getenv('GEMINI_API_KEY') ?: '',
    ],

    // --- Kalite filtresi (kayıttan ÖNCE eleme; bkz. app/filters/*) ---

    // Anlamlı bir problem ifadesi için minimum kelime sayısı. Altındakiler
    // ("Ben de", "Katılıyorum", "Harika olmuş") DB'ye hiç yazılmaz (LengthFilter).
    'min_content_words' => 5,

    // Gürültü kalıpları: teşekkür/mizah/selamlaşma/reklam/spam/clickbait.
    // NoiseFilter bu kalıplardan biri baskınsa içeriği reddeder (küçük harf aranır).
    'noise_patterns' => [
        // teşekkür / onay / boş katılım
        'teşekkür', 'teşekkürler', 'sağ ol', 'eyvallah', 'eline sağlık', 'emeğine sağlık',
        'katılıyorum', 'aynen', 'ben de', 'harika olmuş', 'çok güzel', 'süpermiş', 'helal',
        'thanks', 'thank you', 'thx', 'agreed', 'same here', 'me too', 'nice one', 'lol', 'haha',
        // selamlaşma / sohbet
        'günaydın', 'iyi geceler', 'iyi akşamlar', 'hoş geldin', 'nasılsınız', 'kolay gelsin',
        // reklam / spam / satış
        'indirim', 'kampanya', 'satılık', 'sipariş için', 'whatsapp hattı', 'dm at', 'takipçi',
        'bedava kazan', 'para kazan', 'tıkla kazan', 'çekiliş', 'sponsorlu', 'reklam',
        'buy now', 'discount', 'promo code', 'click here', 'subscribe', 'giveaway',
    ],

    // --- Kümeleme eşikleri (hibrit; bkz. app/services/ProblemCluster.php) ---
    // token benzerliği >= high → doğrudan birleştir; low..high arası belirsiz bant →
    // Groq kanonik slug ile karar; < low → yeni problem.
    'cluster' => [
        'high' => 0.55,
        'low' => 0.30,
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
    // Her feed 'type' alabilir: 'rss' (varsayılan, RSS 2.0 + Atom), 'html_list'
    // (RSS'siz forum HTML'i), 'github' (GitHub search API). Yeni kaynak tipi =
    // app/sources/ altında yeni adapter + SourceRegistry haritasına 1 satır.
    'feeds' => [
        // Global — iş/girişim/SaaS toplulukları (Reddit RSS, key'siz)
        ['url' => 'https://www.reddit.com/r/Entrepreneur/.rss',  'source' => 'reddit-entrepreneur', 'region' => 'Global'],
        ['url' => 'https://www.reddit.com/r/smallbusiness/.rss', 'source' => 'reddit-smallbiz',     'region' => 'Global'],
        ['url' => 'https://www.reddit.com/r/SideProject/.rss',   'source' => 'reddit-sideproject',  'region' => 'Global'],
        ['url' => 'https://www.reddit.com/r/SaaS/.rss',          'source' => 'reddit-saas',         'region' => 'Global'],
        ['url' => 'https://www.reddit.com/r/startups/.rss',      'source' => 'reddit-startups',     'region' => 'Global'],
        ['url' => 'https://www.reddit.com/r/nocode/.rss',        'source' => 'reddit-nocode',       'region' => 'Global'],
        // Hacker News — "Ask HN" gerçek ihtiyaç/soru akışı (hnrss.org, ücretsiz)
        ['url' => 'https://hnrss.org/ask',                       'source' => 'hackernews-ask',      'region' => 'Global'],
        // Product Hunt — yeni ürün lansmanları (rakip/pazar sinyali) → strict (haber gibi)
        ['url' => 'https://www.producthunt.com/feed',            'source' => 'producthunt',         'region' => 'Global', 'strict' => true],
        // Google News RSS (TR) — problem/ihtiyaç odaklı arama; haber gürültüsü için strict
        ['url' => 'https://news.google.com/rss/search?q=%22yaz%C4%B1l%C4%B1m+aray%C4%B1%C5%9F%C4%B1%22+OR+%22dijitalle%C5%9Fme+sorunu%22+OR+%22otomasyon+ihtiyac%C4%B1%22&hl=tr&gl=TR&ceid=TR:tr', 'source' => 'googlenews-tr', 'region' => 'TR', 'strict' => true],
        // Türkiye — genel
        ['url' => 'https://www.reddit.com/r/Turkey/.rss',        'source' => 'reddit',       'region' => 'TR'],
        // Haber siteleri ihtiyaç değil haber başlığı üretir → 'strict' => true:
        // en az 2 pozitif kalıp + LLM onayı şart (bkz. Scanner::run, gürültü azaltma).
        ['url' => 'https://webrazzi.com/feed/',                  'source' => 'webrazzi',     'region' => 'TR', 'strict' => true],
        ['url' => 'https://www.chip.com.tr/rss',                 'source' => 'chip',         'region' => 'TR', 'strict' => true],
        ['url' => 'https://www.donanimhaber.com/rss/tum/',       'source' => 'donanimhaber', 'region' => 'TR', 'strict' => true],
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
        ['url' => 'https://milliegitimakademileri.com/feed/',                      'source' => 'mea',             'region' => 'TR', 'sector' => 'Eğitim / Öğretmenlik', 'strict' => true],
        ['url' => 'https://www.egitimhane.com/rss.xml',                            'source' => 'egitimhane',      'region' => 'TR', 'sector' => 'Eğitim / Öğretmenlik'],
        ['url' => 'https://www.tmmob.org.tr/rss.xml',                              'source' => 'tmmob',           'region' => 'TR', 'sector' => 'Mühendislik', 'strict' => true],
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
        // YouTube kanal RSS örneği (key gerektirmez; kanal channel_id ile eklenir,
        // fetchFeed Atom'u zaten okur). Kanal kimliğini bulup açman yeterli:
        // ['url' => 'https://www.youtube.com/feeds/videos.xml?channel_id=UCxxxxxxxxxxxx', 'source' => 'youtube-girisim', 'region' => 'TR', 'strict' => true],
        // GitHub — otomasyon/araç ihtiyacı sinyalleri (public search API, key'siz, rate-limitli).
        ['url' => 'https://api.github.com/search/issues?q=%22looking+for+a+tool%22+in:title+state:open&sort=reactions&order=desc&per_page=25', 'source' => 'github', 'region' => 'Global', 'type' => 'github'],
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
