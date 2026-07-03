# 04 — Proje Takip Sistemi

> Bu belge, fırsatın **projeye dönüşmesi** ve projenin **faz/durum/not** yönetimini
> kapsar. Görev/tik detayı [05-tik-sistemi.md](05-tik-sistemi.md) içindedir.
> İlgili faz: **Faz 4**.

---

## 1. Fırsattan Projeye
Kullanıcı Fırsat Radarı'nda **"Projeye Çevir"** dediğinde fırsat bir `projects` kaydına
dönüşür. Otomatik taslak olarak aktarılanlar:
- Başlık (fırsat adından)
- Sektör (hiyerarşiden)
- Kaynak linkleri (sinyalin geldiği kaynaklar)

*Neden otomatik taslak:* Kullanıcı sıfırdan doldurmaz; bağlam kaybolmaz. Taslak sonradan
düzenlenebilir.

## 2. Proje Durumları
Bir proje şu durumlardan birindedir (Kanban kolonlarıyla birebir):

| Durum | Anlam |
|---|---|
| `yeni` | Yeni açılmış, henüz başlanmamış |
| `devam_eden` | Aktif geliştirme |
| `biten` | Tamamlandı |
| `iptal` | Vazgeçildi |

Dashboard Proje Durumu panelinde 4 kolon olarak görünür ([01-dashboard.md](01-dashboard.md)).

## 3. Faz Yönetimi
- Her proje **fazlara** bölünür (projenin kendi iç fazları — bu sistemin geliştirme
  fazlarıyla karıştırılmamalı).
- Fazlar **görevlere** bağlanır; görevler tik'lere ([05-tik-sistemi.md](05-tik-sistemi.md)).
- Proje kartında "mevcut faz" ve ilerleme çubuğu gösterilir.

## 4. Notlar
Projeye serbest not eklenebilir (kararlar, kaynak, yapılacaklar). Notlar proje kaydına
bağlıdır; token dostu tutulur (gereksiz metin şişirilmez).

## 5. Manuel Proje
Proje illa fırsattan doğmak zorunda değildir — kullanıcı manuel proje de açabilir. Bu
durumda başlık/sektör elle girilir, kaynak link opsiyoneldir.

## 6. Veri
`projects` tablosu; alanlar: başlık, sektör bağı, durum, mevcut faz, `region` (TR/Global),
kaynak linkleri, `created_at`. İlişkiler: `projects` → `tasks` → `ticks`. Şema:
[06-veritabani-semasi.md](06-veritabani-semasi.md).

---

## Loglama
Projeyle ilgili anlamlı olaylar (`fırsat projeye çevrildi`, `durum değişti`) `logs`
tablosuna yazılır; dashboard Son Aktiviteler'de görünür ([01-dashboard.md](01-dashboard.md)).
