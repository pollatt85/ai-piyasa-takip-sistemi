# 08 — AI Entegrasyonu (Claude API / Ücretsiz LLM)

> Bu belge, sistemin **nerede ve nasıl LLM kullandığını** kapsar. Skor/sınıflandırma
> mantığı [03-analiz-siniflandirma.md](03-analiz-siniflandirma.md) içindedir.
> İlgili faz: **Faz 8**.
>
> **Uygulama notu:** Claude API ücretli olduğu için birincil sınıflandırma hâlâ
> kural tabanlı (`Scanner::classify`). Bunun yerine `app/services/AiClassifier.php`
> ile kredi kartsız free-tier bir sağlayıcı (Groq) kullanılarak, kural filtresinden
> geçen adaylara **ikinci aşama evet/hayır doğrulaması** eklendi (aşağıdaki "1.
> Sınıflandırma" bölümünde tarif edilen tam JSON sınıflandırması değil, daha dar
> kapsamlı bir doğrulama adımı). Varsayılan kapalıdır (`ai_classifier.enabled=false`).

---

## Temel İlke
Sistem AI olmadan da çalışabilir (manuel sinyal/fırsat girişi mümkün). AI, süreci
hızlandıran bir **katma değer katmanıdır**, zorunlu bağımlılık değil.

---

## Claude'un Kullanıldığı Noktalar

### 1. Sınıflandırma (ana kullanım)
Ucuz filtreden geçen ham veri Claude'a gönderilir; yapılandırılmış (JSON) cevap alınır:
- İş fırsatı mı?
- Hangi sektör / alt sektör?
- AI ile çözülebilir mi?
- Ne sıklıkla tekrarlanıyor?

Çıktı `signals` alanlarına map edilir ([06-veritabani-semasi.md](06-veritabani-semasi.md)).

### 2. Kod Üretimi
Claude Code ile **faz bazlı** kod üretimi (bu dokümantasyon yapısının tüm amacı).

### 3. Dashboard Veri Yorumlama / Özetleme
Metriklerin veya trendlerin kısa doğal-dil özeti (opsiyonel, hafif).

### 4. Refactor
Orta karmaşıklıktaki kod iyileştirmeleri.

---

## Token Dostu Entegrasyon Kuralları
- **Sadece filtreden geçen veri** AI'ya gider; ham yığın asla gönderilmez
  ([02-firsat-tarama.md](02-firsat-tarama.md)).
- İstek/yanıt minimal tutulur; gereksiz alan istenmez, JSON şişirilmez.
- Çıktı yapılandırılmış (JSON) alınır → deterministik map, ekstra tur gerekmez.
- Toplu (batch) sınıflandırma mümkünse tek çağrıda birden çok sinyal.

## Model Seçimi
- Sınıflandırma/özetleme gibi tanımlı işler → uygun maliyetli model yeterli olabilir.
- Karmaşık/kararsız sınıflandırma → güçlü model.
- Seçim ilkesi: [../AI_RULES.md](../AI_RULES.md).

## Güvenlik / Yapı
- API anahtarı kod içine gömülmez; ortam değişkeni / config dosyasında tutulur (repo'ya
  girmez).
- AI çağrısı ayrı bir servis/helper katmanında; controller doğrudan API'ye gitmez
  ([../CODING_STANDARD.md](../CODING_STANDARD.md)).

---

## Not
Model ID'leri ve API detayları uygulama anında güncel dokümana göre seçilir; bu belge
**entegrasyon mantığını** tanımlar, sürüm sabitlemez.
