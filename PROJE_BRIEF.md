# AI Piyasa Takip Sistemi — Kurulum Brief'i (Claude Code için)

> Bu dosya, `C:\xampp\htdocs\AI Piyasa Takip Sistemi` klasöründe Claude Code'a verilecek
> tam talimat dokümanıdır. Amaç: projenin `MD/` alt yapısını, aşağıda tanımlanan mimariye
> ve karara göre, detaylı açıklamalarla eksiksiz oluşturmak.

---

## 0. Claude Code İçin Görev Tanımı

Aşağıdaki bilgileri kullanarak `C:\xampp\htdocs\AI Piyasa Takip Sistemi\MD\` klasörü altında
**14 markdown dosyası** oluştur. Her dosya bu brief'teki ilgili bölümü baz alarak,
kısa bir madde listesi değil, **detaylı açıklamalı, gerekçeli** içerik içermeli
(neden bu şekilde tasarlandığı, hangi tablo/kural neyi çözüyor gibi).

Klasör yapısı:

```
AI Piyasa Takip Sistemi/
└── MD/
    ├── MASTER.md
    ├── CURRENT_PHASE.md
    ├── CHANGELOG.md
    ├── AI_RULES.md
    ├── CODING_STANDARD.md
    └── docs/
        ├── 01-dashboard.md
        ├── 02-firsat-tarama.md
        ├── 03-analiz-siniflandirma.md
        ├── 04-proje-takip.md
        ├── 05-tik-sistemi.md
        ├── 06-veritabani-semasi.md
        ├── 07-veri-kaynaklari.md
        ├── 08-ai-entegrasyonu.md
        └── 09-genisleme-potansiyeli.md
```

**Çalışma kuralı (sonraki fazlar için de geçerli):** Her faza yeni bir chat/session ile
başlanır; sadece `MASTER.md` + `CURRENT_PHASE.md` + o fazla ilgili `docs/` dosyası
context'e yüklenir. Tüm dosyalar birden asla aynı anda yüklenmez.

---

## 1. Genel Amaç

Kişisel kullanım için geliştirilen, AI destekli bir **piyasa fırsatı keşif ve proje
takip platformu**. Sistem interneti (RSS, Reddit, açık API'ler) tarayarak insanların
tekrar tekrar ihtiyaç duyduğu ama çözüm bulamadığı şeyleri tespit eder, bunları
sektör bazlı sınıflandırır, olgunlaşan sinyalleri "fırsat" olarak işaretler.
Kullanıcı bir fırsatı seçip gerçek bir projeye çevirir ve geliştirme sürecini
aynı sistem üzerinden faz faz, görev görev takip eder.

**Bu bir borsa/finans takip yazılımı değildir.** Amaç en kısa sürede AI ile
geliştirilip müşteriye satılabilecek fikirleri bulmak ve üretim sürecini
yönetmektir. Öncelik bölgesi Türkiye; fikir olgunlaşırsa `region` alanı ile
global pazara da genişletilebilir.

---

## 2. Teknoloji Stack

| Katman | Teknoloji |
|---|---|
| Backend | PHP 8+ |
| Veritabanı | SQLite (tek dosya, kolay taşınabilir) |
| Ortam | Localhost (XAMPP) → ileride hostinge taşınabilir |
| Mimari | Hafif MVC (framework yok, router + controller + model sınıfları) |
| Arayüz | Responsive, mobil uyumlu |
| Grafik/Görselleştirme | Chart.js (hafif, ücretsiz) |
| Harici bağımlılık | Minimum, token ve kaynak dostu |
| AI Entegrasyonu | Claude API (sınıflandırma/özetleme adımlarında) |

---

## 3. Sistem Mimarisi — Uçtan Uca Akış

```
[TARAMA] → [FİLTRELEME] → [AI SINIFLANDIRMA] → [FIRSAT HAVUZU] → [PROJE/TİK TAKİBİ] → [DASHBOARD]
```

1. **Tarama**: RSS feed'ler + Reddit RSS + (Faz 2'de) Google Maps yorumları ve Google
   Custom Search üzerinden ham veri toplanır. Manuel tetiklenir veya zamanlanmış
   script ile (gerçek cron değil, localhost kısıtı).
2. **Filtreleme**: Anahtar kelime/kalıp bazlı ucuz bir ön filtre, veriyi büyük oranda
   azaltır ("nasıl yapılır", "X için çözüm var mı" gibi kalıplar). Pahalı AI analizine
   sadece kalan veri gider — token dostu yapı.
3. **AI Sınıflandırma**: Filtreden geçen veri Claude'a gönderilir; "iş fırsatı mı,
   hangi sektör, AI ile çözülebilir mi, ne sıklıkla tekrarlanıyor" sorularına cevap
   alınır. Sonuç Kategori→Sektör→Alt Sektör hiyerarşisine yerleştirilip `signals`
   tablosuna yazılır.
4. **Fırsat Havuzu**: Aynı sinyal tekrar tekrar görülürse (tekrar sıklığı + kaynak
   çeşitliliği + opsiyonel manuel puan) "olgunlaşmış fırsat" statüsüne çıkar.
5. **Proje/Tik Takibi**: Fırsat "Projeye Çevir" ile proje kaydına dönüşür (başlık,
   sektör, kaynak linkleri otomatik taslak olarak aktarılır). Proje fazlara,
   fazlar görevlere, görevler tik'lere bağlanır.
6. **Dashboard**: Tüm modüllerin buluştuğu merkez panel.

---

## 4. Veri Hiyerarşisi

```
Kategori → Sektör → Alt Sektör → Şirket/Fırsat
```
Örnek: `Teknoloji → Yapay Zeka → AI Agent Şirketleri → [Fırsat: Otomatik Randevu Botu]`

---

## 5. Veritabanı Şeması (SQLite)

| Tablo | Amaç |
|---|---|
| `categories` | En üst seviye kategori (örn. Teknoloji) |
| `sectors` | Kategoriye bağlı sektör (örn. Yapay Zeka) |
| `sub_sectors` | Sektöre bağlı alt sektör |
| `companies` | Alt sektöre bağlı şirket/varlık kaydı |
| `signals` | Taramadan gelen ham/işlenmiş ihtiyaç sinyalleri (kaynak, tarih, region, skor) |
| `projects` | Fırsattan dönüştürülmüş veya manuel açılmış projeler (durum: yeni/devam eden/biten/iptal) |
| `tasks` | Projelere bağlı görevler, fazlarla ilişkili |
| `ticks` | Görev tamamlama kayıtları, ilerleme yüzdesi hesaplaması için |
| `logs` | Sistem aktivite kaydı (sinyal eklendi, fırsat projeye çevrildi, görev tamamlandı vb.) |

**İlişkiler**: Bir kategori→çok sektör, bir sektör→çok alt sektör, alt sektör→çok
şirket/fırsat. Projeler görevlerle, görevler tik'lerle bağlanır. `signals` ve
`projects` tablolarında `region` (TR/Global) alanı bulunur.

---

## 6. Faz Planı

| Faz | İçerik |
|---|---|
| Faz 0 | Kurulum — klasör yapısı, SQLite bağlantısı, hafif MVC iskeleti, routing |
| Faz 1 | Veritabanı & Modeller — 9 tablo, ilişkiler, migration |
| Faz 2 | Piyasa Takip Modülü — Kategori→Sektör→Alt Sektör→Şirket CRUD |
| Faz 3 | Analiz & Sınıflandırma — ham veriyi hiyerarşiye oturtma, skor mantığı |
| Faz 4 | Proje Takip Sistemi — proje oluşturma, faz/durum yönetimi, notlar |
| Faz 5 | Tik/Görev Sistemi — görev oluşturma, tamamlama, ilerleme yüzdesi |
| Faz 6 | Dashboard — tüm modülleri birleştiren merkez panel |
| Faz 7 | Veri Kaynakları Entegrasyonu — RSS, Reddit RSS (Faz 1 kaynak), sonra Maps/Custom Search (Faz 2 kaynak) |
| Faz 8 | AI Entegrasyonu — Claude API ile sınıflandırma/özetleme |
| Faz 9 | Polish & Hosting Hazırlığı — responsive düzenlemeler, taşıma prep |

**Model stratejisi (her fazın başında hatırlatılacak):**
- Basit/tekrarlayan işlemler (CRUD, iskelet kurma) → hızlı/ucuz model
- Analiz, mimari kararlar, karmaşık sınıflandırma mantığı → güçlü model
- Refactor / orta karmaşıklık → orta model

---

## 7. Dashboard İçeriği (Detaylı)

**Üst özet kartları**: bugün taranan sinyal sayısı, bu hafta olgunlaşan fırsat sayısı,
aktif proje sayısı, haftalık görev tamamlama yüzdesi.

**Fırsat Radarı**: yeni sinyaller (24s/7g filtre), olgunlaşmış fırsatlar (skor sıralı),
sektör/kategori hızlı filtre, her kartta "Projeye Çevir" butonu.

**Favoriler**: yıldızlanan fırsatların toplandığı ayrı bölüm, hızlı erişim.

**Piyasa/Sektör Haritası**: Kategori→Sektör→Alt Sektör ağacı, dal başına sinyal/fırsat
sayacı, en "sıcak" sektörler vurgulu; sektör dağılımı pasta/çubuk grafikle (Chart.js).

**Proje Durumu (Kanban mantığı)**: 4 kolon — Yeni Proje / Devam Eden / Biten /
İptal Edilen. Her kartta proje adı, mevcut faz, ilerleme çubuğu (%).

**Görev/Tik Özeti**: bugün/bu hafta görevleri, dashboard'dan hızlı tikleme (checkbox),
haftalık tamamlama grafiği.

**Son Aktiviteler**: kronolojik log akışı ("X sinyali eklendi", "Y fırsat projeye
çevrildi", "Z görevi tamamlandı").

**Hızlı Aksiyonlar**: manuel tarama tetikle, manuel sinyal/fırsat ekle, yeni proje
oluştur.

---

## 8. Veri Kaynakları

| Kaynak | Durum | Faz |
|---|---|---|
| RSS (forum/haber siteleri) | Tamamen ücretsiz | Faz 1 kaynak |
| Reddit RSS (`reddit.com/r/xxx/.rss`) | Tamamen ücretsiz, API key gerektirmez | Faz 1 kaynak |
| Google Maps yorumları (Places API, Enterprise+Atmosphere SKU) | Ayda 1.000 ücretsiz sorgu | Faz 2 kaynak |
| Google Custom Search JSON API | Günde 100 ücretsiz sorgu | Faz 2 kaynak |
| X/Instagram/Facebook resmi API'leri | Anlamlı ücretsiz kota yok | Opsiyonel / muhtemelen atlanacak |

**Önemli**: Tarama script'leri arayüzden ayrık çalışır — tarama DB'ye yazar, arayüz
sadece okuyup gösterir. Bu ayrım korunmalı.

---

## 9. AI Entegrasyon Mantığı

Sistem AI ile çalışmak zorunda değildir ama şu noktalarda Claude kullanılır:
- Filtreden geçen ham verinin sınıflandırılması (sektör ataması, fırsat skoru)
- Kod üretimi (Claude Code, faz bazlı)
- Dashboard veri yorumlama / özetleme
- Refactor işlemleri

---

## 10. Token Dostu Yapı Prensipleri

- Gereksiz veri saklanmaz, JSON şişirilmez
- Minimal veri yapısı, tekrarlı veri yok
- Modüler dosya/kod yapısı
- Her session'da sadece ilgili MD dosyaları yüklenir

---

## 11. Gelecek Genişleme Potansiyeli (Şimdilik Kapsam Dışı)

Mobil uygulama, AI otomatik analiz agent'ı, bildirim sistemi, otomatik veri çekme
botu (cron/scheduler ile), global pazar genişlemesi, multi-user sistem (opsiyonel).

---

## 12. Claude Code'a Son Talimat

> Yukarıdaki 12 bölümü kullanarak `MD/` klasörünü ve içindeki 14 dosyayı oluştur.
> Her dosya sadece kendi konusuna odaklansın (örn. `06-veritabani-semasi.md` içinde
> sadece şema ve ilişkiler detaylandırılsın, dashboard anlatılmasın). `MASTER.md`
> genel özet + tüm modüllere referans niteliğinde olsun. `CURRENT_PHASE.md` şu an
> "Faz 0 — Kurulum" olarak işaretlensin. `CHANGELOG.md` boş bir şablon olarak
> başlasın (tarih + faz + değişiklik sütunlu). Dosyalar tamamlandıktan sonra
> Faz 0'a (klasör yapısı, SQLite bağlantısı, routing iskeleti) başlanabilir.
