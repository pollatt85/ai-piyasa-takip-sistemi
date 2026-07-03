# AI_RULES — AI Ajanı Çalışma Kuralları

> Bu dosya, projede çalışan AI ajanının (Claude Code vb.) uyması gereken kuralları tanımlar.
> Amaç: **token dostu, faz disiplinli, tutarlı** bir geliştirme süreci. Kurallar
> doğruluktan ödün vermeden maliyeti düşürmek içindir.

---

## 1. Faz Disiplini
- Aynı anda birden fazla faz işlenmez. Yalnızca [CURRENT_PHASE.md](CURRENT_PHASE.md) içindeki aktif faz üzerinde çalışılır.
- Bir fazın kapsam dışı işleri, o faz açıkça belirtmedikçe yapılmaz (scope creep yasak).
- Faz tamamlanınca [CHANGELOG.md](CHANGELOG.md) güncellenir, sonra sıradaki faza geçilir.

## 2. Context Yükleme Kuralı
Her session'da yalnızca şunlar yüklenir:
```
MASTER.md + CURRENT_PHASE.md + o fazla ilgili docs/ dosyası
```
- Tüm `docs/` dosyaları asla aynı anda yüklenmez.
- İlgisiz eski context taşınmaz; konu değişince `/clear`, kısmi koruma gerekince `/compact` önerilir.

## 3. Token Dostu Prensipler
- Gereksiz veri saklanmaz, JSON şişirilmez, tekrarlı veri üretilmez.
- Değişmeyen dosya yeniden yazılmaz; sadece gereken satır düzenlenir.
- Kullanıcı istemedikçe: örnek verme, alternatif sunma, uzun açıklama yazma.
- Geniş tarama yerine hedefli (isim/desen) arama yapılır; dosyanın tamamı yerine ilgili bölüm okunur.

## 4. Model Seçim Stratejisi
| İş tipi | Model |
|---|---|
| CRUD, iskelet, formatlama, isim değişikliği | Hızlı/ucuz |
| Genel geliştirme, hata ayıklama, refactor | Orta |
| Mimari analiz, karmaşık sınıflandırma mantığı, derin inceleme | Güçlü |

Model geçişi **otomatik değildir**; her fazın başında kullanıcı seçer. Mimari/kapsamlı
planlama gerektiren görevde ajan, güçlü model önerisini kullanıcıya hatırlatır.

## 5. Kod Değişiklik Kuralları
- Sadece istenen değişiklik yapılır; istenmeyen refactoring yapılmaz.
- Kod stili için [CODING_STANDARD.md](CODING_STANDARD.md) birincil referanstır.
- Tarama script'leri ile arayüz ayrık kalır: tarama DB'ye yazar, arayüz sadece okur/gösterir (bkz. [docs/07-veri-kaynaklari.md](docs/07-veri-kaynaklari.md)).

## 6. Belge Güncelleme Kuralı
- Yeni bir modül/karar netleştiğinde ilgili `docs/` dosyası güncellenir, dağınık notlar bırakılmaz.
- Her `docs/` dosyası **yalnızca kendi konusuna** odaklanır; başka modülü anlatmaz.

## 7. Öncelik Sırası
```
1) Doğruluk  2) Verimlilik  3) Kısalık  4) Netlik  5) Ek detay
```
Her görevde sor: *"Aynı doğruluğu koruyarak bunu daha az token ile yapabilir miyim?"*
