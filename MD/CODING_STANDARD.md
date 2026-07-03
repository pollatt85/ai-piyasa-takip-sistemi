# CODING_STANDARD — Kod Standardı ve Dizin Düzeni

> Projenin kod stili, dizin yapısı ve isimlendirme kuralları. Amaç: hafif, framework'süz
> ama tutarlı bir MVC. Her katmanın sorumluluğu net, dosyalar küçük ve modüler.

---

## 1. Dizin Düzeni (hedef)

```
AI Piyasa Takip Sistemi/
├── public/
│   ├── index.php          # Tek giriş noktası (front controller)
│   └── assets/            # css, js, Chart.js
├── app/
│   ├── core/              # Router, Controller (taban), Model (taban), Database
│   ├── controllers/       # DashboardController, SignalController, ProjectController...
│   ├── models/            # Category, Sector, Signal, Project, Task, Tick, Log...
│   └── helpers/           # küçük yardımcı fonksiyonlar
├── views/                 # PHP şablonları (layout + partial)
├── data/
│   └── app.sqlite         # tek dosya veritabanı
├── scripts/               # tarama script'leri (arayüzden ayrık çalışır)
├── routes/
│   └── web.php            # route tanımları
└── MD/                    # dokümantasyon (bu klasör)
```

## 2. Mimari İlkeler
- **Framework yok.** Sadece `Router` + `Controller` + `Model` + `Database` çekirdeği.
- Tek giriş noktası: tüm istekler `public/index.php` üzerinden geçer (front controller).
- Katman sorumlulukları:
  - **Controller**: isteği alır, model'i çağırır, view'e veri verir. İş mantığı burada şişmez.
  - **Model**: DB erişimi ve iş kuralları. Doğrudan PDO değil, taban `Model` üzerinden.
  - **View**: sadece sunum; mantık minimum (döngü/koşul dışında iş yok).
- Tarama script'leri (`scripts/`) arayüzden bağımsızdır: **DB'ye yazar**, arayüz **sadece okur**.

## 3. İsimlendirme
| Öğe | Kural | Örnek |
|---|---|---|
| Sınıf | PascalCase | `SignalController`, `ProjectModel` |
| Metot / değişken | camelCase | `getMatureSignals()`, `$projectId` |
| Dosya (sınıf) | Sınıf adıyla aynı | `SignalController.php` |
| DB tablo | snake_case, çoğul | `sub_sectors`, `ticks` |
| DB kolon | snake_case | `created_at`, `region` |
| Route | kebab/düz | `/signals`, `/projects/{id}` |

## 4. Veritabanı Kuralları
- SQLite, PDO ile; hazır ifade (prepared statement) zorunlu — string birleştirme ile sorgu yok.
- Her tabloda `id` (PK, autoincrement) ve uygun yerlerde `created_at`.
- `region` alanı `signals` ve `projects` tablolarında: `TR` / `Global`.
- Şema detayı: [docs/06-veritabani-semasi.md](docs/06-veritabani-semasi.md).

## 5. PHP Stili
- PHP 8+ özellikleri serbest (typed properties, null-safe, match, enum).
- `declare(strict_types=1);` her PHP dosyasının başında.
- Tek sorumluluk: dosya/metot küçük tutulur; kopya kod helper'a taşınır.
- Yorum yalnızca gerektiğinde; kod kendini anlatmalı (token dostu, bkz. [AI_RULES.md](AI_RULES.md)).

## 6. Arayüz
- Responsive, mobil uyumlu; ağır CSS framework yok, hafif kendi stiliniz.
- Grafikler yalnızca **Chart.js** ile.
- View'ler `layout` + `partial` mantığıyla; tekrar eden başlık/menü partial'a alınır.
