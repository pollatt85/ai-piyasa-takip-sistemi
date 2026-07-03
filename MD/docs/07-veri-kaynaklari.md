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
