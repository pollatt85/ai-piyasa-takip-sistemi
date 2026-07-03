# CURRENT_PHASE — Aktif Faz Durumu

> Her session başında **MASTER.md ile birlikte** bu dosya yüklenir. Sistemin şu an
> hangi fazda olduğunu ve o fazda ne yapılacağını tek yerde tutar. Faz değiştikçe
> bu dosya güncellenir; eski durum [CHANGELOG.md](CHANGELOG.md) içine taşınır.

---

## Aktif Faz: **Faz 0 — Kurulum**

**Durum:** Başlamaya hazır (MD yapısı tamamlandı, kod henüz yok).

### Bu Fazın Amacı
Projenin iskeletini kurmak. Henüz iş mantığı yok; sadece üzerine inşa edilecek zemin.

### Yapılacaklar (Faz 0 kapsamı)
- [ ] Klasör yapısı: `public/`, `app/` (controllers, models, core), `views/`, `data/`, `routes/`
- [ ] SQLite bağlantısı: tek dosya `data/app.sqlite`, PDO ile bağlantı sınıfı
- [ ] Hafif MVC iskeleti: `Router`, `Controller` taban sınıfı, `Model` taban sınıfı
- [ ] Routing: `public/index.php` tek giriş noktası (front controller)
- [ ] Basit bir test route'u (örn. `/` → "çalışıyor" yanıtı) ile iskeletin doğrulanması

### Bu Fazın Kapsam DIŞI
- Tablo oluşturma / migration → **Faz 1**
- Herhangi bir CRUD, tarama, dashboard → sonraki fazlar

### İlgili Belgeler
- Mimari/kod kuralları: [CODING_STANDARD.md](CODING_STANDARD.md)
- Genel referans: [MASTER.md](MASTER.md)

### Önerilen Model
İskelet kurma tekrarlayan/mekanik iş → **hızlı/ucuz model** yeterli.
Mimari karar gerekiyorsa güçlü modele geç (bkz. [AI_RULES.md](AI_RULES.md)).

---

## Faz Geçiş Kuralı
Faz 0 tamamlanınca:
1. Yapılanları [CHANGELOG.md](CHANGELOG.md) içine işle.
2. Bu dosyayı **Faz 1 — Veritabanı & Modeller** olarak güncelle.
3. Yeni session aç; MASTER + bu dosya + [docs/06-veritabani-semasi.md](docs/06-veritabani-semasi.md) yükle.
