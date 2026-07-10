<?php
/** Problem detayı: 12 boyut + kanıt (hangi kaynak, ne zaman) dökümü. */
$dims = [
    'Talep Puanı' => $problem['demand_score'],
    'Rekabet Puanı' => $problem['competition_score'],
    'AI ile Çözülebilirlik' => $problem['ai_solvability'],
    'Otomasyona Uygunluk' => $problem['automation_fit'],
    'Teknik Zorluk' => $problem['technical_difficulty'],
    'Abonelik Modeli Uygunluğu' => $problem['subscription_fit'],
    'Yerel Pazar Fırsatı' => $problem['local_opportunity'],
    'Global Pazar Fırsatı' => $problem['global_opportunity'],
];
$texts = [
    'MVP Geliştirme Süresi' => !empty($problem['mvp_weeks']) ? $problem['mvp_weeks'] . ' hafta' : '',
    'Tahmini Gelir Potansiyeli' => $problem['revenue_potential'],
    'Tekrar Etme Sıklığı' => $problem['repeat_frequency'],
    'Trend Yönü' => $problem['trend_direction'],
];
?>
<div class="page-head">
    <div>
        <h1><?= e($problem['title']) ?></h1>
        <p class="page-sub">
            <?= e($problem['sector_name'] ?? '—') ?><?= $problem['sub_sector_name'] ? ' → ' . e($problem['sub_sector_name']) : '' ?>
            · <?= (int) $problem['mention_count'] ?> kişi · <?= (int) $problem['source_variety'] ?> kaynak
            · ilk: <?= e(mb_substr((string) $problem['first_seen'], 0, 16)) ?> · son: <?= e(mb_substr((string) $problem['last_seen'], 0, 16)) ?>
        </p>
    </div>
    <div>
        <a class="btn" href="<?= url('problems') ?>">← Fırsatlar</a>
        <form class="inline-form" method="post" action="<?= url('problems/' . $problem['id'] . '/to-project') ?>">
            <button class="btn btn-primary">Projeye Çevir</button>
        </form>
    </div>
</div>

<div class="grid grid-2 mb">
    <div class="card">
        <h2>Neden önemli?</h2>
        <p><?= e($problem['why_summary'] ?: 'AI analizi henüz üretilmedi (Groq kapalı/erişilemez olabilir).') ?></p>
        <?php if (!empty($problem['mvp_suggestion'])): ?>
            <h2 style="margin-top:16px">💡 MVP Önerisi</h2>
            <p><?= e($problem['mvp_suggestion']) ?></p>
        <?php endif; ?>
        <div class="opp-facts" style="margin-top:14px">
            <span class="badge badge-score">skor <?= $problem['total_score'] ?></span>
            <span class="badge badge-<?= e($problem['status']) ?>"><?= $problem['status'] === 'olgun_firsat' ? 'olgun fırsat' : 'ham' ?></span>
            <span class="badge badge-<?= strtolower($problem['region']) ?>"><?= e($problem['region']) ?></span>
            <?php foreach ($texts as $label => $val): if ($val === '' || $val === null) continue; ?>
                <span><?= e($label) ?>: <strong><?= e((string) $val) ?></strong></span>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <h2>📊 AI Değerlendirmesi (12 boyut)</h2>
        <div class="opp-metrics">
            <?php foreach ($dims as $label => $val): ?>
            <div class="metric">
                <div class="metric-label"><?= e($label) ?></div>
                <div class="metric-bar"><div class="metric-fill" style="width:<?= (int) $val ?>%"></div></div>
                <div class="metric-val"><?= $val === null ? '—' : (int) $val ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card">
    <h2>🔎 Kanıtlar (<?= count($mentions) ?>) — hangi kaynakta, ne zaman</h2>
    <table>
        <thead><tr><th>Kaynak</th><th>İçerik</th><th>Bölge</th><th>Tarih</th></tr></thead>
        <tbody>
        <?php if ($mentions === []): ?>
            <tr><td colspan="4" class="page-sub">Kanıt bulunamadı.</td></tr>
        <?php endif; ?>
        <?php foreach ($mentions as $m): ?>
            <tr>
                <td><span class="badge badge-source"><?= e($m['source']) ?></span></td>
                <td style="max-width:520px">
                    <?php if (!empty($m['source_url'])): ?>
                        <a href="<?= e($m['source_url']) ?>" target="_blank" rel="noopener"><?= e(mb_substr((string) $m['content'], 0, 160)) ?></a>
                    <?php else: ?>
                        <?= e(mb_substr((string) $m['content'], 0, 160)) ?>
                    <?php endif; ?>
                </td>
                <td><span class="badge badge-<?= strtolower($m['region']) ?>"><?= e($m['region']) ?></span></td>
                <td class="page-sub"><?= e(mb_substr((string) $m['created_at'], 0, 16)) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
