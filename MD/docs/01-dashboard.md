# 01 — Dashboard Modülü

> Dashboard, tüm modüllerin buluştuğu merkez paneldir. Bu belge **yalnızca dashboard'un
> içeriğini ve mantığını** kapsar; tarama/analiz/proje detayları kendi belgelerindedir.
> İlgili faz: **Faz 6**.

---

## Amaç
Kullanıcının sistemi tek ekrandan yönetmesini sağlamak: neyin tarandığı, hangi fırsatın
olgunlaştığı, projelerin nerede olduğu ve görevlerin ne kadar tamamlandığı bir bakışta
görünür. Dashboard **veri üretmez**, diğer modüllerin verisini okuyup özetler.

---

## Bileşenler

### 1. Üst Özet Kartları
Dört sayısal metrik (hızlı nabız):
- Bugün taranan sinyal sayısı
- Bu hafta olgunlaşan fırsat sayısı
- Aktif proje sayısı
- Haftalık görev tamamlama yüzdesi

*Neden:* Kullanıcı detaya inmeden sistemin "canlı" olup olmadığını görür.

### 2. Fırsat Radarı
- Yeni sinyaller (24s / 7g filtresi)
- Olgunlaşmış fırsatlar (skora göre sıralı) — skor mantığı: [03-analiz-siniflandirma.md](03-analiz-siniflandirma.md)
- Sektör/kategori hızlı filtre
- Her kartta **"Projeye Çevir"** butonu → [04-proje-takip.md](04-proje-takip.md)

*Neden:* En değerli çıktı (fırsatlar) en görünür yerde; aksiyon tek tık uzakta.

### 3. Favoriler
Yıldızlanan fırsatların toplandığı ayrı bölüm; hızlı erişim. Kullanıcı ilgilendiği
fırsatı kaybetmez.

### 4. Piyasa/Sektör Haritası
- Kategori→Sektör→Alt Sektör ağacı
- Dal başına sinyal/fırsat sayacı
- En "sıcak" sektörler vurgulu
- Sektör dağılımı **Chart.js** pasta/çubuk grafikle

*Neden:* Fırsatların hangi alanda yoğunlaştığını görsel olarak gösterir. Hiyerarşi:
[06-veritabani-semasi.md](06-veritabani-semasi.md).

### 5. Proje Durumu (Kanban mantığı)
Dört kolon: **Yeni Proje / Devam Eden / Biten / İptal Edilen**.
Her kartta: proje adı, mevcut faz, ilerleme çubuğu (%). Detay: [04-proje-takip.md](04-proje-takip.md).

### 6. Görev/Tik Özeti
- Bugün / bu hafta görevleri
- Dashboard'dan hızlı tikleme (checkbox)
- Haftalık tamamlama grafiği (Chart.js)

Tik/ilerleme mantığı: [05-tik-sistemi.md](05-tik-sistemi.md).

### 7. Son Aktiviteler
Kronolojik log akışı: "X sinyali eklendi", "Y fırsat projeye çevrildi", "Z görevi
tamamlandı". Kaynak: `logs` tablosu ([06-veritabani-semasi.md](06-veritabani-semasi.md)).

### 8. Hızlı Aksiyonlar
- Manuel tarama tetikle
- Manuel sinyal/fırsat ekle
- Yeni proje oluştur

*Neden:* Sık kullanılan işlemler menü aramadan erişilebilir olmalı.

---

## Tasarım Notu
Dashboard salt-okur + hafif aksiyon paneldir. Ağır hesaplama yapmaz; metrikler ilgili
modüllerin model katmanından hazır çekilir. Performans için sayaçlar mümkünse tek sorguda
toplanır (token/kaynak dostu, bkz. [../AI_RULES.md](../AI_RULES.md)).
