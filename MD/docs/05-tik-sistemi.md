# 05 — Tik / Görev Sistemi

> Bu belge, projelere bağlı **görevlerin** oluşturulması, tamamlanması ve **ilerleme
> yüzdesi** hesabını kapsar. Proje/faz yapısı [04-proje-takip.md](04-proje-takip.md)
> içindedir. İlgili faz: **Faz 5**.

---

## 1. Yapı
```
Proje → Faz → Görev (task) → Tik (tick)
```
- **Görev (`tasks`)**: yapılacak somut iş; bir projeye ve o projenin bir fazına bağlı.
- **Tik (`ticks`)**: görev tamamlama kaydı; ilerleme yüzdesi bunlardan hesaplanır.

## 2. Görev Oluşturma
- Görev bir projeye + faza bağlanır.
- Alanlar: başlık, (ops.) açıklama, durum (açık/tamamlandı), `created_at`.
- Görevler dashboard Görev/Tik Özeti'nde bugün/bu hafta filtresiyle görünür
  ([01-dashboard.md](01-dashboard.md)).

## 3. Tamamlama (Tikleme)
- Görev tamamlandığında bir `tick` kaydı oluşur (veya görev durumu `tamamlandı` olur).
- Dashboard'dan **hızlı tikleme** (checkbox) desteklenir — detaya girmeden.
- Tik kaydı log'a düşer ("Z görevi tamamlandı").

## 4. İlerleme Yüzdesi
Bir fazın/projenin ilerlemesi tik'lerden hesaplanır:
```
ilerleme_% = (tamamlanan_görev_sayısı / toplam_görev_sayısı) * 100
```
- Proje kartındaki ilerleme çubuğu bu değeri gösterir ([04-proje-takip.md](04-proje-takip.md)).
- Haftalık tamamlama yüzdesi dashboard üst kartında özetlenir; haftalık grafik Chart.js ile.

*Neden tik tabanlı:* İlerleme sübjektif tahminle değil, **somut tamamlanan görev**le
ölçülür; dashboard metrikleri güvenilir olur.

## 5. Veri
- `tasks`: projeye/faza bağlı görevler.
- `ticks`: tamamlama kayıtları (ilerleme hesabı için).
- İlişki: `projects` → `tasks` → `ticks`. Şema: [06-veritabani-semasi.md](06-veritabani-semasi.md).

---

## Token Dostu Not
İlerleme hesabı mümkünse tek toplu sorguyla yapılır (görev başına ayrı sorgu değil).
Gereksiz tik kaydı tutulmaz ([../AI_RULES.md](../AI_RULES.md)).
