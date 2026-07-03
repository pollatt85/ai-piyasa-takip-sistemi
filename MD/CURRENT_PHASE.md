# CURRENT_PHASE — Aktif Faz Durumu

> Her session başında **MASTER.md ile birlikte** bu dosya yüklenir. Sistemin şu an
> hangi fazda olduğunu ve o fazda ne yapılacakları tek yerde tutar. Faz değiştikçe
> bu dosya güncellenir; eski durum [CHANGELOG.md](CHANGELOG.md) içine taşınır.

---

## Aktif Faz: **Faz 9 — Polish & Hosting Hazırlığı**

**Durum:** Faz 0–8 tamamlandı (2026-07-03). Sistem localhost'ta uçtan uca çalışıyor.

### Çalışan Sistem (özet)
- Hafif MVC: `public/index.php` → `Router` → controller → view (framework yok)
- SQLite: `data/app.sqlite`, 9 tablo (`scripts/migrate.php` ile kurulur)
- Tarama: `scripts/scan.php` (CLI) veya dashboard "Tarama Başlat" — RSS + Reddit RSS
- Sınıflandırma: **kural tabanlı** (alt sektör anahtar kelimeleri) — Claude API ücretli
  olduğundan bilinçli olarak kullanılmıyor; sistem %100 ücretsiz kaynakla çalışıyor
- Skor/eşik `app/config.php` içinde; eşiği aşan sinyal `olgun_firsat`
- Dashboard: özet kartları, Fırsat Radarı, Favoriler, sektör haritası + Chart.js
  grafikler, Kanban, görev/tik özeti, aktivite akışı

### Kurulum (yeni makine)
```
php scripts/migrate.php   # tabloları + başlangıç hiyerarşisini kurar
php scripts/scan.php      # ilk taramayı çalıştırır (opsiyonel)
```
XAMPP Apache ile: `http://localhost/ai-piyasa-takip-sistemi/` (kök .htaccess public/'e yönlendirir)
veya: `php -S localhost:8123 -t public public/router.php`

### Faz 9 Kalanlar
- [ ] Hosting'e taşıma provası (paylaşımlı hosting'de .htaccess + SQLite yolu)
- [ ] Zamanlanmış tarama: Windows Görev Zamanlayıcı'ya `php scripts/scan.php` tanımı
- [ ] Feed listesini genişletme / ince ayar (`app/config.php` → `feeds`)
- [ ] Skor ağırlıklarını gerçek veriyle kalibre etme

### Kapsam DIŞI (bkz. docs/09-genisleme-potansiyeli.md)
- Ücretli API'ler (Claude, Google Maps, Custom Search) — kullanıcı kararıyla atlandı
- Mobil uygulama, bildirim, multi-user

### İlgili Belgeler
- Genel referans: [MASTER.md](MASTER.md)
- Kod kuralları: [CODING_STANDARD.md](CODING_STANDARD.md)
