# 03 — Analiz & Sınıflandırma (Hiyerarşiye Oturtma + Skor)

> Bu belge, filtreden geçen ham veriyi **hiyerarşiye yerleştirme** ve **fırsat skoru**
> mantığını kapsar. Tarama/filtreleme akışı [02-firsat-tarama.md](02-firsat-tarama.md),
> AI çağrısının teknik detayı [08-ai-entegrasyonu.md](08-ai-entegrasyonu.md) içindedir.
> İlgili faz: **Faz 3**.

---

## 1. Hiyerarşiye Oturtma
AI sınıflandırma çıktısı, veri hiyerarşisine yerleştirilir:

```
Kategori → Sektör → Alt Sektör → Şirket/Fırsat
```

- AI'dan gelen sektör/alt sektör önerisi mevcut kayıtlarla eşleştirilir; yoksa yeni kayıt
  önerilir (kullanıcı onayıyla veya otomatik).
- Sonuç `signals` tablosuna yazılır; sinyal ilgili alt sektöre bağlanır.
- Tablolar: `categories`, `sectors`, `sub_sectors`, `companies`, `signals`
  ([06-veritabani-semasi.md](06-veritabani-semasi.md)).

*Neden hiyerarşi:* Fırsatların hangi alanda yoğunlaştığını görmek (sektör haritası,
[01-dashboard.md](01-dashboard.md)) ancak tutarlı bir sınıflandırma ağacıyla mümkün.

## 2. Skor Mantığı
Sinyalin "olgunlaşmış fırsat" olup olmadığını belirleyen skor üç girdiden beslenir:

| Girdi | Ne ölçer | Etki |
|---|---|---|
| Tekrar sıklığı | Aynı ihtiyacın kaç kez görüldüğü | Ana ağırlık |
| Kaynak çeşitliliği | Kaç farklı kaynakta görüldüğü | Tek kaynak yanlılığını kırar |
| Manuel puan (ops.) | Kullanıcının elle verdiği önem | İnce ayar / override |

Kavramsal formül (uygulamada sabitler ayarlanabilir):
```
skor = (tekrar_sayısı * w1) + (farklı_kaynak_sayısı * w2) + (manuel_puan * w3)
```
Skor bir **eşiği** aşınca sinyal `signals.status` = `olgun_firsat` olur ve Fırsat
Radarı'nda skora göre sıralı görünür.

*Neden bu üç girdi:*
- Sadece tekrar → tek bir yankı odası (echo chamber) yanıltabilir; kaynak çeşitliliği bunu dengeler.
- Manuel puan → sistemin kaçırdığı ama kullanıcının değerli bulduğu sinyali kurtarır.

## 3. AI'ya Sorulan Sorular
Sınıflandırma sırasında Claude'a sorulanlar:
1. Bu bir **iş fırsatı mı**?
2. Hangi **sektör / alt sektör**?
3. **AI ile çözülebilir mi** (projeye uygunluk)?
4. **Ne sıklıkla** tekrarlanıyor (sinyal gücü)?

Çıktı yapılandırılmış (JSON) alınır ve `signals` alanlarına map edilir. Token dostu:
yalnızca filtreden geçen veri gönderilir ([../AI_RULES.md](../AI_RULES.md)).

## 4. Region
Sınıflandırmada `region` (TR/Global) atanır. Varsayılan `TR`; içerik açıkça global bir
ihtiyaca işaret ediyorsa `Global`.

---

## Not
Bu belge **mantığı** tanımlar; ağırlıklar (w1/w2/w3) ve eşik değeri Faz 3 uygulamasında
config olarak tutulur, kod içine gömülmez — ileride ayarlanabilir kalır.
