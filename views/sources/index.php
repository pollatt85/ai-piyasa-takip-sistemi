<div class="page-head">
    <div>
        <h1>Kaynaklar</h1>
        <p class="page-sub">Sistemin taradığı aktif kaynaklar ve önerilen meslek forumları</p>
    </div>
</div>

<div class="card">
    <h2>Aktif Kaynaklar</h2>
    <table>
        <thead><tr><th>Kaynak</th><th>Bölge</th><th>URL</th><th>Durum</th></tr></thead>
        <tbody>
        <?php if ($feeds === []): ?>
            <tr><td colspan="4" class="page-sub">Henüz kaynak tanımlı değil.</td></tr>
        <?php endif; ?>
        <?php foreach ($feeds as $feed): ?>
            <tr>
                <td><span class="badge badge-source"><?= e($feed['source']) ?></span></td>
                <td><span class="badge badge-<?= e(strtolower($feed['region'])) ?>"><?= e($feed['region']) ?></span></td>
                <td><a href="<?= e($feed['url']) ?>" target="_blank" rel="noopener"><?= e($feed['url']) ?></a></td>
                <td><span class="badge badge-olgun_firsat">Aktif</span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Önerilen Meslek Forumları</h2>
    <p class="page-sub">Türkiye'deki meslek gruplarına özel forum toplulukları — referans amaçlı, taramaya dahil değil.</p>
    <?php if ($professionForums === []): ?>
        <p class="page-sub">Henüz öneri listelenmedi.</p>
    <?php endif; ?>
    <?php foreach ($professionForums as $profession => $sites): ?>
        <h3><?= e($profession) ?></h3>
        <table>
            <thead><tr><th>Site</th><th>URL</th><th>RSS</th><th>Durum</th></tr></thead>
            <tbody>
            <?php foreach ($sites as $site): ?>
                <tr>
                    <td><?= e($site['name']) ?></td>
                    <td><a href="<?= e($site['url']) ?>" target="_blank" rel="noopener"><?= e($site['url']) ?></a></td>
                    <td>
                        <?php if ($site['rss'] === true): ?>
                            <span class="badge badge-global">Var</span>
                        <?php elseif ($site['rss'] === false): ?>
                            <span class="badge badge-ham">Yok</span>
                        <?php else: ?>
                            <span class="badge badge-ham">Bilinmiyor</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($site['active'])): ?>
                            <span class="badge badge-olgun_firsat">Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-ham">Öneri</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>
