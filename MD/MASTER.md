# MASTER — AI Piyasa Takip Sistemi

> Bu dosya projenin **tek referans noktasıdır**. Her session'da context'e ilk yüklenir.
> Amaç: sistemin ne olduğu, nasıl çalıştığı ve hangi belgenin neyi kapsadığı hakkında
> tam ama öz bir harita sunmak. Detay için ilgili `docs/` dosyasına yönlendirir.

---

## 1. Proje Nedir?

Kişisel kullanım için geliştirilen, **AI destekli piyasa fırsatı keşif ve proje takip
platformu**. Sistem interneti (RSS, Reddit RSS, açık API'ler) tarar; insanların tekrar
tekrar ihtiyaç duyduğu ama çözüm bulamadığı şeyleri tespit eder, bunları sektör bazlı
sınıflandırır, olgunlaşan sinyalleri **"fırsat"** olarak işaretler. Kullanıcı bir fırsatı
seçip gerçek bir projeye çevirir ve geliştirme sürecini aynı sistem üzerinden **faz faz,
görev görev** takip eder.

**Bu bir borsa/finans takip yazılımı DEĞİLDİR.** Amaç: en kısa sürede AI ile geliştirilip
müşteriye satılabilecek fikirleri bulmak ve üretim sürecini yönetmek. Öncelik bölgesi
**Türkiye**; fikir olgunlaşırsa `region` alanı ile global pazara genişletilebilir.

---

## 2. Teknoloji Stack (özet)

| Katman | Teknoloji |
|---|---|
| Backend | PHP 8+ |
| Veritabanı | SQLite (tek dosya) |
| Ortam | Localhost (XAMPP) → ileride hosting |
| Mimari | Hafif MVC (framework yok: router + controller + model) |
| Arayüz | Responsive, mobil uyumlu |
| Grafik | Chart.js |
| AI | Claude API (sınıflandırma/özetleme) |

Detay yok — stack sabit. Tartışma gerekmez.

---

## 3. Uçtan Uca Akış

```
[TARAMA] → [FİLTRELEME] → [AI SINIFLANDIRMA] → [FIRSAT HAVUZU] → [PROJE/TİK TAKİBİ] → [DASHBOARD]
```

Her adım ayrı bir belgede detaylanır (aşağıdaki harita).

---

## 4. Veri Hiyerarşisi

```
Kategori → Sektör → Alt Sektör → Şirket/Fırsat
```
Örnek: `Teknoloji → Yapay Zeka → AI Agent Şirketleri → [Fırsat: Otomatik Randevu Botu]`

Ayrıntı: [06-veritabani-semasi.md](docs/06-veritabani-semasi.md)

---

## 5. Belge Haritası (hangi dosya neyi kapsar)

| Dosya | İçerik |
|---|---|
| [MASTER.md](MASTER.md) | Bu dosya — genel özet + tüm modüllere referans |
| [CURRENT_PHASE.md](CURRENT_PHASE.md) | Aktif faz durumu (şu an: Faz 0 — Kurulum) |
| [CHANGELOG.md](CHANGELOG.md) | Tarih + faz + değişiklik kaydı |
| [AI_RULES.md](AI_RULES.md) | AI ajanına çalışma kuralları (token dostu, faz disiplini) |
| [CODING_STANDARD.md](CODING_STANDARD.md) | Kod stili, dizin düzeni, isimlendirme |
| [docs/01-dashboard.md](docs/01-dashboard.md) | Dashboard modülü (merkez panel) |
| [docs/02-firsat-tarama.md](docs/02-firsat-tarama.md) | Tarama + filtreleme + fırsat havuzu |
| [docs/03-analiz-siniflandirma.md](docs/03-analiz-siniflandirma.md) | Skor mantığı, hiyerarşiye oturtma |
| [docs/04-proje-takip.md](docs/04-proje-takip.md) | Proje/faz/durum yönetimi |
| [docs/05-tik-sistemi.md](docs/05-tik-sistemi.md) | Görev/tik sistemi, ilerleme yüzdesi |
| [docs/06-veritabani-semasi.md](docs/06-veritabani-semasi.md) | 9 tablo, ilişkiler, migration |
| [docs/07-veri-kaynaklari.md](docs/07-veri-kaynaklari.md) | RSS, Reddit, Maps, Custom Search |
| [docs/08-ai-entegrasyonu.md](docs/08-ai-entegrasyonu.md) | Claude API entegrasyon mantığı |
| [docs/09-genisleme-potansiyeli.md](docs/09-genisleme-potansiyeli.md) | Gelecek kapsam (şimdilik dışı) |

---

## 6. Faz Planı (özet)

| Faz | İçerik |
|---|---|
| Faz 0 | Kurulum — klasör, SQLite bağlantısı, MVC iskeleti, routing |
| Faz 1 | Veritabanı & Modeller — 9 tablo, ilişkiler, migration |
| Faz 2 | Piyasa Takip — Kategori→Sektör→Alt Sektör→Şirket CRUD |
| Faz 3 | Analiz & Sınıflandırma — hiyerarşiye oturtma, skor |
| Faz 4 | Proje Takip — proje/faz/durum/notlar |
| Faz 5 | Tik/Görev — görev, tamamlama, ilerleme % |
| Faz 6 | Dashboard — merkez panel |
| Faz 7 | Veri Kaynakları — RSS, Reddit, sonra Maps/Custom Search |
| Faz 8 | AI Entegrasyonu — Claude API sınıflandırma/özetleme |
| Faz 9 | Polish & Hosting hazırlığı |

Aktif fazın ne olduğu **daima** [CURRENT_PHASE.md](CURRENT_PHASE.md) içindedir.

---

## 7. Session Çalışma Kuralı (kritik)

Her faza **yeni bir chat/session** ile başlanır. Context'e yalnızca şunlar yüklenir:

```
MASTER.md  +  CURRENT_PHASE.md  +  o fazla ilgili docs/ dosyası
```

Tüm dosyalar asla aynı anda yüklenmez. Bu, token maliyetini düşük tutmanın temel kuralıdır.
Detay: [AI_RULES.md](AI_RULES.md)
