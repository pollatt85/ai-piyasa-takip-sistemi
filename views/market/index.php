<div class="page-head">
    <div>
        <h1>Piyasa Haritası</h1>
        <p class="page-sub">Kategori → Sektör → Alt Sektör → Şirket hiyerarşisi</p>
    </div>
</div>

<div class="grid grid-2 mb">
    <div class="card">
        <h2>Hiyerarşi</h2>
        <div class="tree">
            <ul>
                <?php foreach ($tree as $cat): ?>
                <li><span class="t-cat"><?= e($cat['name']) ?></span>
                    <ul>
                        <?php foreach ($cat['sectors'] ?? [] as $sec): ?>
                        <li><span class="t-sec"><?= e($sec['name']) ?></span>
                            <ul>
                                <?php foreach ($sec['subs'] ?? [] as $sub): ?>
                                <li><?= e($sub['name']) ?>
                                    <span class="count <?= $sub['mature_count'] > 0 ? 'hot' : '' ?>">
                                        <?= $sub['signal_count'] ?> sinyal · <?= $sub['company_count'] ?> şirket
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div>
        <details class="card mb" open>
            <summary>+ Kategori</summary>
            <form method="post" action="<?= url('market/categories') ?>" class="form-row" style="margin-bottom:0">
                <div><input type="text" name="name" required placeholder="Kategori adı (örn. Sağlık)"></div>
                <div style="flex:0"><button class="btn btn-primary">Ekle</button></div>
            </form>
        </details>

        <details class="card mb">
            <summary>+ Sektör</summary>
            <form method="post" action="<?= url('market/sectors') ?>" class="form-row" style="margin-bottom:0">
                <div>
                    <select name="category_id" required>
                        <option value="">Kategori seç…</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><input type="text" name="name" required placeholder="Sektör adı"></div>
                <div style="flex:0"><button class="btn btn-primary">Ekle</button></div>
            </form>
        </details>

        <details class="card mb">
            <summary>+ Alt Sektör</summary>
            <form method="post" action="<?= url('market/sub-sectors') ?>">
                <div class="form-row">
                    <div>
                        <select name="sector_id" required>
                            <option value="">Sektör seç…</option>
                            <?php foreach ($sectors as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= e($s['category_name'] . ' → ' . $s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><input type="text" name="name" required placeholder="Alt sektör adı"></div>
                </div>
                <div class="form-row">
                    <div><input type="text" name="keywords" placeholder="Sınıflandırma anahtar kelimeleri (virgülle: randevu, booking, salon)"></div>
                    <div style="flex:0"><button class="btn btn-primary">Ekle</button></div>
                </div>
            </form>
        </details>

        <details class="card">
            <summary>+ Şirket / Varlık</summary>
            <form method="post" action="<?= url('market/companies') ?>">
                <div class="form-row">
                    <div>
                        <select name="sub_sector_id" required>
                            <option value="">Alt sektör seç…</option>
                            <?php foreach ($subSectors as $ss): ?>
                            <option value="<?= $ss['id'] ?>"><?= e($ss['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><input type="text" name="name" required placeholder="Şirket adı"></div>
                </div>
                <div class="form-row">
                    <div><input type="text" name="note" placeholder="Not (opsiyonel)"></div>
                    <div style="flex:0"><button class="btn btn-primary">Ekle</button></div>
                </div>
            </form>
        </details>
    </div>
</div>

<div class="card">
    <h2>Şirketler / Varlıklar</h2>
    <table>
        <thead><tr><th>Ad</th><th>Alt Sektör</th><th>Sektör</th><th>Not</th><th>Eklendi</th><th></th></tr></thead>
        <tbody>
        <?php if ($companies === []): ?>
            <tr><td colspan="6" class="page-sub">Henüz şirket kaydı yok.</td></tr>
        <?php endif; ?>
        <?php foreach ($companies as $co): ?>
            <tr>
                <td><strong><?= e($co['name']) ?></strong></td>
                <td><?= e($co['sub_sector_name']) ?></td>
                <td><?= e($co['sector_name']) ?></td>
                <td><?= e($co['note']) ?></td>
                <td><?= e($co['created_at']) ?></td>
                <td>
                    <form class="inline-form" method="post" action="<?= url('market/companies/' . $co['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')">
                        <button class="btn btn-sm btn-danger">Sil</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
