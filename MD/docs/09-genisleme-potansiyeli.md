# 09 — Gelecek Genişleme Potansiyeli

> Bu belge, **şimdilik kapsam DIŞI** olan ama mimarinin izin verdiği genişleme yönlerini
> kaydeder. Amaç: bugün kurulan yapının bu yönlere kapı bırakması; hiçbiri Faz 0–9'a dahil
> değildir.

---

## Kapsam Dışı (şimdilik) Başlıklar

### 1. Mobil Uygulama
Arayüz zaten responsive/mobil uyumlu tasarlanıyor ([../CODING_STANDARD.md](../CODING_STANDARD.md));
ileride native/PWA mobil katman eklenebilir. Bugün: sadece web.

### 2. AI Otomatik Analiz Agent'ı
Sınıflandırmayı ve fırsat değerlendirmesini tetiklemeden yürüten otonom agent. Bugünkü
sistem **manuel/tetiklemeli**; agent mantığı sonraya bırakıldı ([08-ai-entegrasyonu.md](08-ai-entegrasyonu.md)).

### 3. Bildirim Sistemi
Yeni olgun fırsat / görev hatırlatması için e-posta/push bildirim. Bugün: dashboard'dan
görsel takip yeterli.

### 4. Otomatik Veri Çekme Botu (cron/scheduler)
Localhost'ta gerçek cron yok; tarama manuel/zamanlanmış script ile. Hosting'e taşındığında
gerçek cron ile otomatik tarama mümkün olur ([07-veri-kaynaklari.md](07-veri-kaynaklari.md)).

### 5. Global Pazar Genişlemesi
`region` alanı (TR/Global) bugünden şemada var ([06-veritabani-semasi.md](06-veritabani-semasi.md));
fikir olgunlaşırsa global veri kaynakları ve filtreler eklenebilir. Öncelik şimdilik TR.

### 6. Multi-User Sistem (opsiyonel)
Şu an tek kullanıcı (kişisel). İleride kullanıcı/oturum katmanı eklenebilir; mevcut şema
buna göre ağırlaştırılmıyor (token/kaynak dostu).

---

## Neden Şimdi Değil?
Öncelik: **en kısa sürede çalışan, satılabilir fikir üreten çekirdek**. Yukarıdaki
başlıklar değer katar ama çekirdek olgunlaşmadan maliyet/karmaşıklık getirir. Mimari bu
yönlere **kapı bırakır** (region alanı, ayrık tarama script'i, modüler MVC), ama bunları
uygulamaz.

---

## İzlenecek İlke
Bir genişleme gündeme gelince: önce çekirdeğin tamamlandığından emin ol, sonra yeni fazı
[../CURRENT_PHASE.md](../CURRENT_PHASE.md) + [../CHANGELOG.md](../CHANGELOG.md) disiplinine
ekleyerek işle. Kapsamı sessizce genişletme ([../AI_RULES.md](../AI_RULES.md)).
