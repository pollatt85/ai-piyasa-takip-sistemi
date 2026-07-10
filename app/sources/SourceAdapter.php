<?php
declare(strict_types=1);

/**
 * Kaynak adapter'ları için ortak taban (plug-in mimari — bkz. SourceRegistry).
 * Her adapter bir feed tanımını alır ve normalize edilmiş item dizisi döner:
 *   [ ['title' => ..., 'summary' => ..., 'link' => ...], ... ]  veya null (erişim hatası).
 * Yeni kaynak tipi = bu sınıftan türeyen yeni adapter + SourceRegistry haritasına 1 satır.
 */
abstract class SourceAdapter
{
    /** @return array<int, array{title: string, summary: string, link: string}>|null */
    abstract public function fetch(array $feed): ?array;

    /**
     * Birçok kaynak (webrazzi, hukuki.net, meslek forumları vb.) TLS handshake'te ara
     * sertifikayı göndermiyor; PHP'nin OpenSSL'i (tarayıcıların/Windows'un aksine) eksik
     * zinciri tamamlayamıyor ve güncel bir CA paketiyle bile doğrulama başarısız oluyor
     * (bkz. MD/docs/07-veri-kaynaklari.md). Bu yüzden sertifika doğrulaması kapatılır —
     * sadece herkese açık RSS/HTML içeriği okunuyor, kimlik bilgisi alışverişi yok ve
     * sonuç zaten aynı kural tabanlı filtre/sınıflandırma zincirinden geçiyor.
     */
    protected function httpContext()
    {
        return stream_context_create([
            'http' => [
                'timeout' => 12,
                'user_agent' => 'Mozilla/5.0 (PiyasaTakip/1.0; localhost)',
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
    }
}
