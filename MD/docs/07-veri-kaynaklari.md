# 07 — Veri Kaynakları

> Bu belge, ham verinin **hangi kaynaklardan, hangi fazda ve hangi kısıtlarla** çekildiğini
> kapsar. Verinin filtrelenmesi/sınıflandırılması [02-firsat-tarama.md](02-firsat-tarama.md)
> ve [03-analiz-siniflandirma.md](03-analiz-siniflandirma.md) içindedir. İlgili faz: **Faz 7**.

---

## Kaynak Tablosu

| Kaynak | Durum | Faz |
|---|---|---|
| RSS (forum/haber siteleri) | Tamamen ücretsiz | Faz 1 kaynak |
| Reddit RSS (`reddit.com/r/xxx/.rss`) | Ücretsiz, API key gerektirmez | Faz 1 kaynak |
| R10.net (freelancer/webmaster forumu) | RSS yok — kategori sayfalarından HTML başlık scraping | Faz 1 kaynak (ek) |
| App Store müşteri yorumları (Apple resmi RSS) | Tamamen ücretsiz, key yok | Faz 1 kaynak (ek) |
| Google Maps yorumları (Places API, Enterprise+Atmosphere SKU) | Ayda 1.000 ücretsiz sorgu | Faz 2 kaynak |
| Google Custom Search JSON API | Günde 100 ücretsiz sorgu | Faz 2 kaynak |
| X / Instagram / Facebook resmi API'leri | Anlamlı ücretsiz kota yok | Opsiyonel / muhtemelen atlanacak |

> Not: Buradaki "Faz 1/Faz 2 kaynak" ifadesi, veri kaynağının **kendi entegrasyon
> sırasını** belirtir (önce ücretsiz/kolay olanlar). Sistem geliştirme fazı olan **Faz 7**
> altında hepsi ele alınır.

---

## Kaynak Detayları

### RSS (Faz 1 kaynak)
- Forum ve haber sitelerinin RSS feed'leri.
- Tamamen ücretsiz, key yok. En düşük riskli başlangıç kaynağı.

### Reddit RSS (Faz 1 kaynak)
- `reddit.com/r/<subreddit>/.rss` — API anahtarı gerektirmez.
- İhtiyaç/şikâyet kalıpları için verimli; ucuz filtreye çok uygun.

### R10.net (Faz 1 kaynak, ek)
- Türkiye'nin en büyük freelancer/webmaster forumu (SEO, yazılım, e-ticaret, iş
  ilanları) — proje fikri sinyali açısından zengin ama **RSS/Atom feed'i yok**,
  gönderi içeriği JS ile yükleniyor.
- `Scanner::fetchHtmlList()` kategori sayfalarını (`feeds[].type = 'html_list'`)
  çekip yalnızca thread başlıklarını (`div.title > a[rel=ugc]`) HTML olarak
  parse eder; gönderi gövdesi çekilmez (düşük veri zenginliği, kabul edilen risk).
- Kırılgan bir yöntemdir: R10 HTML yapısı değişirse o kaynak sessizce atlanır,
  tarama diğer feed'lerle devam eder (bkz. `Scanner::run()` try/catch-per-feed).

### App Store Müşteri Yorumları (Faz 1 kaynak, ek)
- Apple'ın resmi, ücretsiz, key gerektirmeyen Atom feed'i:
  `itunes.apple.com/{ülke}/rss/customerreviews/id={app_id}/sortby=mostrecent/xml`.
  Format zaten Reddit'in Atom yapısıyla aynı, `Scanner::fetchFeed()` değişiklik
  gerekmeden parse ediyor.
- Rakip uygulamalardaki 1 yıldızlı yorumlarda geçen "keşke şu özellik olsa",
  "alternatifi yok" gibi ifadeler rakip/boşluk analizi için değerli.
- Başlangıç seti: Trendyol, Hepsiburada (e-ticaret), Papara (ödeme/finans),
  Kolay Randevu (randevu) — `app/config.php` → `feeds` içinde `appstore-*` kaynak adıyla.

### Google Maps / Yandex Yorumları — Değerlendirildi, Eklenmedi
- **Google Places API:** Ücretsiz kota (ayda 1.000 sorgu) yeterli olsa da API'yi
  aktifleştirmek için Google Cloud projesine kredi kartı eklemek şart; ayrıca
  ToS review metnini Google Haritalar dışında saklamayı/göstermeyi yasaklıyor.
- **Yandex Maps:** Resmi bir review API'si yok. R10'un aksine, yorum verisi
  sayfanın statik HTML'inde de yok — tamamen dokümante edilmemiş bir JS/XHR
  API'siyle geliyor. Mevcut hafif PHP mimarisiyle (headless browser olmadan)
  pratik değil; bu yüzden eklenmedi.

### Bilinen Kısıt: TLS Sertifika Doğrulaması
- Bu ortamda (XAMPP/Windows) birçok kaynak (webrazzi, hukuki.net, meslek forumları,
  R10 dahil) TLS handshake'te ara sertifikayı göndermiyor; PHP'nin OpenSSL'i
  (tarayıcıların/Windows'un aksine AIA fetching yapmadığı için) eksik zinciri
  tamamlayamıyor ve güncel bir CA paketiyle bile doğrulama başarısız oluyor.
- `Scanner::httpContext()` bu yüzden tüm RSS/HTML çekimlerinde sertifika
  doğrulamasını kapatır (`verify_peer=false`). Kabul edilen risk: yalnızca herkese
  açık RSS/HTML içeriği okunuyor, kimlik bilgisi alışverişi yok, sonuç zaten aynı
  kural tabanlı filtre/sınıflandırma zincirinden geçiyor.

### Google Maps Yorumları (Faz 2 kaynak)
- Places API, Enterprise+Atmosphere SKU; **ayda 1.000 ücretsiz sorgu**.
- Yerel işletme ihtiyaçlarını (TR odaklı) yakalamak için değerli ama kotalı → dikkatli kullan.

### Google Custom Search JSON API (Faz 2 kaynak)
- **Günde 100 ücretsiz sorgu**. Belirli kalıpları web genelinde aramak için.
- Kota küçük → yalnızca hedefli sorgular.

### Sosyal Medya API'leri (opsiyonel)
- X/Instagram/Facebook resmi API'lerinde anlamlı ücretsiz kota yok → **muhtemelen atlanır**.

---

## Kritik Mimari Kural
**Tarama script'leri arayüzden ayrık çalışır.** Script veriyi **DB'ye yazar**; arayüz
**sadece okuyup gösterir**. Bu ayrım korunmalı ([../CODING_STANDARD.md](../CODING_STANDARD.md)).

- Script'ler `scripts/` altında; manuel veya zamanlanmış (Windows Görev Zamanlayıcı /
  manuel) tetiklenir — localhost'ta gerçek cron yok.
- Her çekilen sinyal `signals.source` alanına kaynağını yazar
  ([06-veritabani-semasi.md](06-veritabani-semasi.md)).

---

## Kota Dostu Prensip
Ücretsiz kotalar küçük (Maps 1.000/ay, Custom Search 100/gün). Bu yüzden:
- Önce ücretsiz/kotasız kaynaklar (RSS, Reddit) sömürülür.
- Kotalı kaynaklar yalnızca ucuz filtreden umut vaat eden konular için kullanılır
  (token + kota dostu, bkz. [../AI_RULES.md](../AI_RULES.md)).
