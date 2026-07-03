# 06 — Veritabanı Şeması (SQLite)

> Bu belge **yalnızca şemayı ve ilişkileri** kapsar. Dashboard/analiz/proje mantığı kendi
> belgelerindedir. İlgili faz: **Faz 1** (tablolar + migration).

---

## Genel
- Motor: **SQLite** (tek dosya `data/app.sqlite`), PDO + prepared statement.
- Her tabloda `id` (PK, autoincrement) ve uygun yerde `created_at`.
- İsimlendirme: tablo snake_case çoğul, kolon snake_case ([../CODING_STANDARD.md](../CODING_STANDARD.md)).

---

## 9 Tablo

| Tablo | Amaç |
|---|---|
| `categories` | En üst kategori (örn. Teknoloji) |
| `sectors` | Kategoriye bağlı sektör (örn. Yapay Zeka) |
| `sub_sectors` | Sektöre bağlı alt sektör |
| `companies` | Alt sektöre bağlı şirket/varlık kaydı |
| `signals` | Taramadan gelen ham/işlenmiş ihtiyaç sinyalleri |
| `projects` | Fırsattan dönüşen veya manuel açılan projeler |
| `tasks` | Projelere bağlı görevler (fazlarla ilişkili) |
| `ticks` | Görev tamamlama kayıtları (ilerleme hesabı) |
| `logs` | Sistem aktivite kaydı |

---

## İlişkiler

```
categories 1───∞ sectors 1───∞ sub_sectors 1───∞ companies
                                     │
                                     └───∞ signals   (alt sektöre bağlı)

projects 1───∞ tasks 1───∞ ticks

logs  (bağımsız aktivite akışı; ilgili varlığa referans tutabilir)
```

- Bir kategori → çok sektör
- Bir sektör → çok alt sektör
- Bir alt sektör → çok şirket/fırsat ve çok sinyal
- Bir proje → çok görev; bir görev → çok tik

---

## Alan Notları (kritik olanlar)

### `signals`
- `sub_sector_id` (FK), `source` (kaynak: rss/reddit/maps/custom_search/manuel)
- `source_url`, `content` (özet/başlık), `region` (**TR/Global**)
- `repeat_count`, `source_variety`, `manual_score` → skor girdileri
- `score`, `status` (`ham` / `olgun_firsat`), `created_at`
- Skor mantığı: [03-analiz-siniflandirma.md](03-analiz-siniflandirma.md)

### `projects`
- `title`, `sector_id` (veya sub_sector bağı), `status` (`yeni`/`devam_eden`/`biten`/`iptal`)
- `current_phase`, `region` (**TR/Global**), `source_links`, `notes`, `created_at`
- Detay: [04-proje-takip.md](04-proje-takip.md)

### `tasks`
- `project_id` (FK), `phase` (proje iç fazı), `title`, `description`, `status`, `created_at`

### `ticks`
- `task_id` (FK), `created_at` — ilerleme % hesabı için ([05-tik-sistemi.md](05-tik-sistemi.md))

### `logs`
- `type` (sinyal_eklendi / firsat_projeye / gorev_tamamlandi ...), `message`,
  `ref_id` (opsiyonel ilgili kayıt), `created_at`
- Dashboard Son Aktiviteler kaynağı ([01-dashboard.md](01-dashboard.md))

---

## Tasarım Gerekçesi
- **`region` yalnızca `signals` ve `projects`'te**: bölgesellik sinyal ve proje düzeyinde
  anlamlı; kategori/sektör evrensel kalır.
- **`ticks` ayrı tablo**: ilerleme somut kayıtlardan hesaplansın; görev üzerinde tek bir
  boolean yerine tarihli tik geçmişi tutulur (haftalık grafik mümkün olur).
- **`logs` bağımsız**: modüller birbirine sıkı bağlanmadan aktivite akışı üretilir; token
  dostu, tek okuma ile Son Aktiviteler beslenir.
