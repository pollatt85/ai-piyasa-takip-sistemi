<div class="page-head">
    <div>
        <h1>Sinyaller &amp; Fırsatlar</h1>
        <p class="page-sub">Taramadan gelen ihtiyaç sinyalleri — skor eşiğini (<?= config()['score']['threshold'] ?>) aşan "olgun fırsat" olur</p>
    </div>
</div>

<details class="card mb">
    <summary>+ Manuel Sinyal / Fırsat Ekle</summary>
    <form method="post" action="<?= url('signals') ?>">
        <div class="form-row">
            <div style="flex:3">
                <label>İçerik (ihtiyaç / fırsat tanımı)</label>
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
    <form method="get" action="<?= url('signals') ?>" class="form-row" style="margin-bottom:0">
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

<div class="card">
    <table>
        <thead>
            <tr>
                <th></th>
                <th>İçerik</th>
                <th>Sektör</th>
                <th>Kaynak</th>
                <th>Bölge</th>
                <th>Tekrar</th>
                <th>Skor</th>
                <th>Durum</th>
                <th>Manuel Puan</th>
                <th>Aksiyon</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($signals === []): ?>
            <tr><td colspan="10" class="page-sub">Kayıt yok. Tarama başlat veya manuel sinyal ekle.</td></tr>
        <?php endif; ?>
        <?php foreach ($signals as $s): ?>
            <tr data-signal-id="<?= $s['id'] ?>" data-original="<?= e($s['content']) ?>">
                <td>
                    <form class="inline-form" method="post" action="<?= url('signals/' . $s['id'] . '/favorite') ?>">
                        <input type="hidden" name="back" value="signals">
                        <button class="btn-star <?= $s['is_favorite'] ? 'on' : '' ?>">★</button>
                    </form>
                </td>
                <td style="max-width:340px">
                    <?php if ($s['source_url']): ?>
                        <a href="<?= e($s['source_url']) ?>" target="_blank" rel="noopener" title="<?= e($s['content']) ?>" class="js-signal-text"><?= e(mb_substr($s['content'], 0, 90)) ?></a>
                    <?php else: ?>
                        <span title="<?= e($s['content']) ?>" class="js-signal-text"><?= e(mb_substr($s['content'], 0, 90)) ?></span>
                    <?php endif; ?>
                </td>
                <td><?= e($s['sector_name'] ?? '—') ?><br><small class="page-sub"><?= e($s['sub_sector_name'] ?? '') ?></small></td>
                <td><span class="badge badge-source"><?= e($s['source']) ?></span></td>
                <td>
                    <span class="badge badge-<?= strtolower($s['region']) ?>"><?= e($s['region']) ?></span>
                    <?php if ($s['region'] === 'Global'): ?><button type="button" class="btn-translate" title="Türkçeye çevir">TR</button><?php endif; ?>
                </td>
                <td><?= $s['repeat_count'] ?>× / <?= $s['source_variety'] ?> kaynak</td>
                <td><span class="badge badge-score"><?= $s['score'] ?></span></td>
                <td><span class="badge badge-<?= e($s['status']) ?>"><?= $s['status'] === 'olgun_firsat' ? 'olgun fırsat' : 'ham' ?></span></td>
                <td>
                    <form class="inline-form" method="post" action="<?= url('signals/' . $s['id'] . '/score') ?>" style="display:flex;gap:4px">
                        <input type="hidden" name="back" value="signals">
                        <input type="number" name="manual_score" value="<?= $s['manual_score'] ?>" min="0" max="99" style="width:60px">
                        <button class="btn btn-sm">✓</button>
                    </form>
                </td>
                <td style="white-space:nowrap">
                    <form class="inline-form" method="post" action="<?= url('signals/' . $s['id'] . '/to-project') ?>">
                        <button class="btn btn-sm btn-primary">Projeye Çevir</button>
                    </form>
                    <form class="inline-form" method="post" action="<?= url('signals/' . $s['id'] . '/delete') ?>" onsubmit="return confirm('Sinyal silinsin mi?')">
                        <button class="btn btn-sm btn-danger">Sil</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
