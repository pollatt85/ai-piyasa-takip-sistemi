# 02 — Fırsat Tarama (Tarama + Filtreleme + Fırsat Havuzu)

> Bu belge sinyalin nasıl **toplandığını, ucuz filtreden geçirildiğini ve olgunlaşan
> fırsata dönüştüğünü** kapsar. Ham veri kaynaklarının teknik detayı
> [07-veri-kaynaklari.md](07-veri-kaynaklari.md), AI sınıflandırması
> [03-analiz-siniflandirma.md](03-analiz-siniflandirma.md) içindedir. İlgili faz: **Faz 7**
> (kaynaklar), **Faz 3** (skor).

---

## Akıştaki Yeri

```
[TARAMA] → [FİLTRELEME] → [AI SINIFLANDIRMA] → [FIRSAT HAVUZU] → ...
```

## 1. Tarama
- Kaynaklar: RSS feed'ler + Reddit RSS (Faz 1 kaynak), sonra Google Maps yorumları +
  Custom Search (Faz 2 kaynak).
- Tetikleme: **manuel** veya zamanlanmış script (gerçek cron değil — localhost kısıtı;
  Windows Görev Zamanlayıcı / manuel çalıştırma).
- **Kritik ayrım:** tarama script'i arayüzden ayrıktır. Script ham veriyi **DB'ye yazar**;
  arayüz sadece okur. Bu ayrım korunmalı ([../CODING_STANDARD.md](../CODING_STANDARD.md)).

## 2. Filtreleme (ucuz ön filtre)
Ham veri, pahalı AI'ya gitmeden önce **anahtar kelime/kalıp** bazlı elenir:
- "nasıl yapılır", "X için çözüm var mı", "keşke ... olsa", "tavsiye" gibi ihtiyaç kalıpları.
- Amaç: veriyi büyük oranda azaltıp yalnızca umut vaat edeni AI'ya göndermek.

*Neden:* AI analizi token maliyetlidir. Ucuz string filtre, maliyetin çoğunu keser
(token dostu yapı, bkz. [../AI_RULES.md](../AI_RULES.md)).

## 3. AI Sınıflandırma (özet)
Filtreden geçen veri Claude'a gider; "iş fırsatı mı, hangi sektör, AI ile çözülebilir mi,
ne sıklıkla tekrarlanıyor" sorularına cevap alınır ve `signals` tablosuna yazılır.
Detay: [03-analiz-siniflandirma.md](03-analiz-siniflandirma.md) ve
[08-ai-entegrasyonu.md](08-ai-entegrasyonu.md).

## 4. Fırsat Havuzu (olgunlaşma)
Bir sinyalin "fırsat" statüsüne çıkması tek görülmeye bağlı değildir. Olgunlaşma girdileri:
- **Tekrar sıklığı** — aynı ihtiyaç kaç kez göründü
- **Kaynak çeşitliliği** — farklı kaynaklarda mı görülüyor (tek forum değil)
- **Opsiyonel manuel puan** — kullanıcı önemli buluyorsa elle yükseltebilir

Bu girdiler bir skora dönüşür; eşiği aşan sinyal **"olgunlaşmış fırsat"** olur. Skor
formülü ve eşik: [03-analiz-siniflandirma.md](03-analiz-siniflandirma.md).

## 5. Fırsattan Sonra
Olgunlaşan fırsat dashboard'daki **Fırsat Radarı**'nda görünür ([01-dashboard.md](01-dashboard.md)).
Kullanıcı **"Projeye Çevir"** ile fırsatı projeye dönüştürür; başlık, sektör ve kaynak
linkleri otomatik taslak olarak aktarılır ([04-proje-takip.md](04-proje-takip.md)).

---

## Region Notu
Her sinyalde `region` alanı bulunur (`TR` / `Global`). Öncelik Türkiye; fikir olgunlaşırsa
global pazara genişletilebilir. Alan `signals` tablosunda tutulur
([06-veritabani-semasi.md](06-veritabani-semasi.md)).
