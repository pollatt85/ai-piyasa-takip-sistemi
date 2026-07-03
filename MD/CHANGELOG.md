# CHANGELOG — Değişiklik Kaydı

> Her faz tamamlandığında veya anlamlı bir değişiklik yapıldığında buraya bir satır eklenir.
> En yeni kayıt en üstte. Tarih formatı: `YYYY-MM-DD`.

| Tarih | Faz | Değişiklik |
|---|---|---|
| 2026-07-03 | Faz 8 | AI entegrasyonu kapsam değişikliği: Claude API ücretli olduğu için sınıflandırma **kural tabanlı** yapıldı (alt sektör anahtar kelimeleri, `sub_sectors.keywords`). Sistem tamamen ücretsiz çalışıyor. |
| 2026-07-03 | Faz 7 | Veri kaynakları: RSS + Reddit RSS entegrasyonu (`app/core/Scanner.php`, `scripts/scan.php`). Ücretli/kotalı kaynaklar (Google Maps, Custom Search) bilinçli atlandı. Reddit 429 için bekleme + yeniden deneme eklendi. |
| 2026-07-03 | Faz 6 | Dashboard: özet kartları, Fırsat Radarı (24s/7g), Favoriler, sektör haritası, Kanban, görev/tik özeti, son aktiviteler; Chart.js ile sektör dağılımı ve haftalık tamamlama grafikleri (yerel asset). |
| 2026-07-03 | Faz 4-5 | Proje takip (Kanban, durum/faz/not yönetimi, fırsattan projeye çevirme) ve görev/tik sistemi (hızlı tikleme, ilerleme %). |
| 2026-07-03 | Faz 2-3 | Piyasa hiyerarşisi CRUD (kategori→sektör→alt sektör→şirket) ve skor mantığı: `skor = tekrar*w1 + kaynak_çeşitliliği*w2 + manuel*w3`, eşik config'te (`app/config.php`). |
| 2026-07-03 | Faz 0-1 | MVC iskeleti (Router/Controller/Model/Database), SQLite bağlantısı, 9 tablo + migration + başlangıç hiyerarşisi (`scripts/migrate.php`). |
| 2026-07-01 | Faz 0 | MD/ dokümantasyon yapısı ve 14 dosya oluşturuldu (kurulum brief'ine göre). |

<!--
Şablon satırı (kopyala, en üste ekle):
| YYYY-MM-DD | Faz X | Kısa açıklama |
-->
