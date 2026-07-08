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
- Tarama: `scripts/scan.php` (CLI) veya dashboard "Tarama Başlat" — RSS + Reddit RSS +
  R10.net (HTML başlık scraping, bkz. `Scanner::fetchHtmlList`) + App Store müşteri
  yorumları (Apple resmi Atom feed). Google Maps/Yandex değerlendirildi, ToS/teknik
  kısıtlar yüzünden eklenmedi (bkz. docs/07-veri-kaynaklari.md)
- Sınıflandırma: **kural tabanlı** (alt sektör anahtar kelimeleri, sektöre özel filtre
  kalıpları) + opsiyonel ücretsiz LLM ikinci aşama doğrulama (`app/services/AiClassifier.php`,
  varsayılan kapalı — bkz. `ai_classifier` config). Ücretli Claude API hâlâ
  kullanılmıyor; sistem %100 ücretsiz kaynak/kota ile çalışıyor
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

### GitHub Codespaces
- `.devcontainer/devcontainer.json` + `post-create.sh` ile hazır: Codespace açılınca
  gerekli PHP eklentileri kontrol edilir, `data/app.sqlite` yoksa `migrate.php` otomatik
  çalışır, sunucu `php -S 0.0.0.0:8123 -t public public/router.php` ile başlar (port
  8123 forward edilir).
- `data/app.sqlite` git'e commit edilmez (`.gitignore`) — Codespace'in kendi diskinde
  kalıcıdır. Codespace silinmediği sürece (uzun süre açılmazsa GitHub otomatik silebilir)
  veri korunur; günlük manuel kullanım hem taramayı yapar hem silinme sayacını sıfırlar.
- Kod değişikliği gerektiğinde Codespace içinde ayrı bir Claude Code oturumu açılıp
  doğrudan orada düzenlenip push edilebilir (PC'ye dönmeden).

### Faz 9 Kalanlar
- [ ] Hosting'e taşıma provası (paylaşımlı hosting'de .htaccess + SQLite yolu)
- [ ] Zamanlanmış tarama: Windows Görev Zamanlayıcı'ya `php scripts/scan.php` tanımı
- [ ] Feed listesini genişletme / ince ayar (`app/config.php` → `feeds`)
- [ ] Skor ağırlıklarını gerçek veriyle kalibre etme

### Kapsam DIŞI (bkz. docs/09-genisleme-potansiyeli.md)
- Ücretli API'ler (Claude, Google Maps, Custom Search) — kullanıcı kararıyla atlandı.
  İkinci aşama doğrulama için yalnızca kredi kartsız free-tier LLM (Groq) opsiyoneldir.
- Mobil uygulama, bildirim, multi-user

### İlgili Belgeler
- Genel referans: [MASTER.md](MASTER.md)
- Kod kuralları: [CODING_STANDARD.md](CODING_STANDARD.md)
