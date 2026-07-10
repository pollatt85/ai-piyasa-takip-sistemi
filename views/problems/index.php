<?php
/**
 * Fırsat panosu: her problem bir kart. Haber değil, fırsat gösterir (PDF #7):
 * PROBLEM, neden önemli, kaç kişi, tekrar sıklığı, AI çözebilir mi, rekabet,
 * SaaS/abonelik uygunluğu, MVP önerisi + süre, tahmini gelir.
 */
?>
<div class="page-head">
    <div>
        <h1>Fırsatlar</h1>
        <p class="page-sub">Kümelenmiş problemler — skor eşiğini (<?= config()['score']['threshold'] ?>) aşan "olgun fırsat" olur</p>
    </div>
</div>

<details class="card mb">
    <summary>+ Manuel Problem / Fırsat Ekle</summary>
    <form method="post" action="<?= url('problems') ?>">
        <div class="form-row">
            <div style="flex:3">
                <label>İçerik (problem / ihtiyaç tanımı)</label>
                <input type="text" name="content" required placeholder="Örn: Berberler için basit online randevu sistemi arayan çok kişi var">
            </div>
        </div>
        <div class="form-row">
            <div>
                <label>Alt Sektör</label>
                <select name="sub_sector_id">
                    <?php foreach ($subSectors as $ss): ?>
                    <option value="<?= $ss['id'] ?>"><?= e($ss['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Bölge</label>
                <select name="region"><option value="TR">TR</option><option value="Global">Global</option></select>
            </div>
            <div>
                <label>Kaynak URL (opsiyonel)</label>
                <input type="url" name="source_url" placeholder="https://...">
            </div>
        </div>
        <button class="btn btn-primary">Ekle</button>
    </form>
</details>

<div class="card mb">
    <form method="get" action="<?= url('problems') ?>" class="form-row" style="margin-bottom:0">
        <div>
            <label>Durum</label>
            <select name="status">
                <option value="">Tümü</option>
                <option value="ham" <?= $filters['status'] === 'ham' ? 'selected' : '' ?>>Ham</option>
                <option value="olgun_firsat" <?= $filters['status'] === 'olgun_firsat' ? 'selected' : '' ?>>Olgun Fırsat</option>
            </select>
        </div>
        <div>
            <label>Bölge</label>
            <select name="region">
                <option value="">Tümü</option>
                <option value="TR" <?= $filters['region'] === 'TR' ? 'selected' : '' ?>>TR</option>
                <option value="Global" <?= $filters['region'] === 'Global' ? 'selected' : '' ?>>Global</option>
            </select>
        </div>
        <div>
            <label>Sektör</label>
            <select name="sector_id">
                <option value="">Tümü</option>
                <?php foreach ($sectors as $sec): ?>
                <option value="<?= $sec['id'] ?>" <?= $filters['sector_id'] === (int) $sec['id'] ? 'selected' : '' ?>>
                    <?= e($sec['category_name'] . ' → ' . $sec['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="align-self:flex-end">
            <label style="display:inline"><input type="checkbox" name="favorites" value="1" <?= $filters['favorites'] ? 'checked' : '' ?>> Sadece favoriler</label>
        </div>
        <div style="align-self:flex-end; flex:0">
            <button class="btn btn-primary">Filtrele</button>
        </div>
    </form>
</div>

<?php if ($problems === []): ?>
    <div class="card"><p class="page-sub">Kayıt yok. Soldan tarama başlat veya manuel problem ekle.</p></div>
<?php endif; ?>

<div class="opportunity-grid">
<?php foreach ($problems as $p): ?>
    <?php
        $metrics = [
            ['Talep', $p['demand_score']],
            ['AI çözebilirlik', $p['ai_solvability']],
            ['Otomasyon', $p['automation_fit']],
            ['Abonelik/SaaS', $p['subscription_fit']],
            ['Rekabet', $p['competition_score']],
            ['Teknik zorluk', $p['technical_difficulty']],
            ['Yerel pazar', $p['local_opportunity']],
            ['Global pazar', $p['global_opportunity']],
        ];
    ?>
    <div class="card opp-card" data-problem-id="<?= $p['id'] ?>" data-original="<?= e($p['title']) ?>">
        <div class="opp-head">
            <span class="badge badge-score">skor <?= $p['total_score'] ?></span>
            <span class="badge badge-<?= e($p['status']) ?>"><?= $p['status'] === 'olgun_firsat' ? 'olgun fırsat' : 'ham' ?></span>
            <span class="badge badge-<?= strtolower($p['region']) ?>"><?= e($p['region']) ?></span>
            <?php if ($p['region'] === 'Global'): ?><button type="button" class="btn-translate" title="Türkçeye çevir">TR</button><?php endif; ?>
            <form class="inline-form" method="post" action="<?= url('problems/' . $p['id'] . '/favorite') ?>" style="margin-left:auto">
                <input type="hidden" name="back" value="problems">
                <button class="btn-star <?= $p['is_favorite'] ? 'on' : '' ?>" title="Favori">★</button>
            </form>
        </div>

        <h3 class="opp-title"><a class="js-signal-text" href="<?= url('problems/' . $p['id']) ?>"><?= e($p['title']) ?></a></h3>

        <?php if (!empty($p['why_summary'])): ?>
            <p class="opp-why"><?= e($p['why_summary']) ?></p>
        <?php endif; ?>

        <div class="opp-facts">
            <span title="Kaç kişi/kaynak"><strong><?= (int) $p['mention_count'] ?></strong> kişi · <?= (int) $p['source_variety'] ?> kaynak</span>
            <?php if (!empty($p['repeat_frequency'])): ?><span>🔁 <?= e($p['repeat_frequency']) ?></span><?php endif; ?>
            <?php if (!empty($p['trend_direction'])): ?><span>📈 <?= e($p['trend_direction']) ?></span><?php endif; ?>
            <?php if (!empty($p['revenue_potential'])): ?><span>💰 <?= e($p['revenue_potential']) ?></span><?php endif; ?>
            <?php if (!empty($p['mvp_weeks'])): ?><span>🛠 MVP ~<?= (int) $p['mvp_weeks'] ?> hafta</span><?php endif; ?>
            <?php if (!empty($p['sector_name'])): ?><span class="badge badge-source"><?= e($p['sector_name']) ?></span><?php endif; ?>
        </div>

        <div class="opp-metrics">
            <?php foreach ($metrics as [$label, $val]): if ($val === null) continue; ?>
            <div class="metric">
                <div class="metric-label"><?= e($label) ?></div>
                <div class="metric-bar"><div class="metric-fill" style="width:<?= (int) $val ?>%"></div></div>
                <div class="metric-val"><?= (int) $val ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($p['mvp_suggestion'])): ?>
            <p class="opp-mvp">💡 <?= e($p['mvp_suggestion']) ?></p>
        <?php endif; ?>

        <div class="opp-actions">
            <a class="btn btn-sm" href="<?= url('problems/' . $p['id']) ?>">Detay</a>
            <form class="inline-form" method="post" action="<?= url('problems/' . $p['id'] . '/to-project') ?>">
                <button class="btn btn-sm btn-primary">Projeye Çevir</button>
            </form>
            <form class="inline-form" method="post" action="<?= url('problems/' . $p['id'] . '/delete') ?>" onsubmit="return confirm('Fırsat silinsin mi?')">
                <button class="btn btn-sm btn-danger">Sil</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
</div>
