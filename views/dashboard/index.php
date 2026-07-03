<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p class="page-sub">Piyasa fırsatı keşif ve proje takip merkezi</p>
    </div>
    <div>
        <a class="btn" href="<?= url('signals') ?>">+ Manuel Sinyal</a>
        <a class="btn" href="<?= url('projects') ?>">+ Yeni Proje</a>
    </div>
</div>

<!-- Üst özet kartları -->
<div class="grid grid-4 mb">
    <div class="card stat accent-1">
        <div class="stat-label">Bugün Taranan Sinyal</div>
        <div class="stat-value"><?= $todaySignals ?></div>
    </div>
    <div class="card stat accent-2">
        <div class="stat-label">Bu Hafta Olgunlaşan Fırsat</div>
        <div class="stat-value"><?= $weekMature ?></div>
    </div>
    <div class="card stat accent-3">
        <div class="stat-label">Aktif Proje</div>
        <div class="stat-value"><?= $activeProjects ?></div>
    </div>
    <div class="card stat accent-4">
        <div class="stat-label">Haftalık Görev Tamamlama</div>
        <div class="stat-value">%<?= $weeklyCompletion ?></div>
    </div>
</div>

<!-- Fırsat Radarı -->
<div class="grid grid-2 mb">
    <div class="card">
        <h2>
            ⚡ Yeni Sinyaller
            <span>
                <a class="btn btn-sm <?= $radarRange === '24s' ? 'btn-primary' : '' ?>" href="<?= url('?range=24s') ?>">24s</a>
                <a class="btn btn-sm <?= $radarRange === '7g' ? 'btn-primary' : '' ?>" href="<?= url('?range=7g') ?>">7g</a>
            </span>
        </h2>
        <?php if ($newSignals === []): ?>
            <p class="page-sub">Bu aralıkta sinyal yok. Soldan tarama başlatabilirsin.</p>
        <?php endif; ?>
        <?php foreach ($newSignals as $s): ?>
        <div class="signal-item">
            <div class="signal-body">
                <span class="signal-content" title="<?= e($s['content']) ?>"><?= e(mb_substr($s['content'], 0, 90)) ?></span>
                <div class="signal-meta">
                    <span class="badge badge-<?= strtolower($s['region']) ?>"><?= e($s['region']) ?></span>
                    <span class="badge badge-source"><?= e($s['source']) ?></span>
                    <span class="badge badge-score">skor <?= $s['score'] ?></span>
                    <span class="time"><?= timeAgo($s['created_at']) ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <h2>🎯 Olgunlaşmış Fırsatlar <a class="btn btn-sm" href="<?= url('signals?status=olgun_firsat') ?>">tümü</a></h2>
        <?php if ($matureSignals === []): ?>
            <p class="page-sub">Henüz olgunlaşan fırsat yok. Skor eşiği: <?= config()['score']['threshold'] ?>.</p>
        <?php endif; ?>
        <?php foreach ($matureSignals as $s): ?>
        <div class="signal-item">
            <div class="signal-body">
                <span class="signal-content" title="<?= e($s['content']) ?>"><?= e(mb_substr($s['content'], 0, 80)) ?></span>
                <div class="signal-meta">
                    <span class="badge badge-score">skor <?= $s['score'] ?></span>
                    <span class="badge badge-<?= strtolower($s['region']) ?>"><?= e($s['region']) ?></span>
                    <?php if ($s['sector_name']): ?><span class="badge badge-source"><?= e($s['sector_name']) ?></span><?php endif; ?>
                </div>
            </div>
            <div class="signal-actions">
                <form class="inline-form" method="post" action="<?= url('signals/' . $s['id'] . '/favorite') ?>">
                    <input type="hidden" name="back" value="">
                    <button class="btn-star <?= $s['is_favorite'] ? 'on' : '' ?>" title="Favori">★</button>
                </form>
                <form class="inline-form" method="post" action="<?= url('signals/' . $s['id'] . '/to-project') ?>">
                    <button class="btn btn-sm btn-primary">Projeye Çevir</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Favoriler + Grafikler -->
<div class="grid grid-2 mb">
    <div class="card">
        <h2>★ Favoriler <a class="btn btn-sm" href="<?= url('signals?favorites=1') ?>">tümü</a></h2>
        <?php if ($favorites === []): ?>
            <p class="page-sub">Favori fırsat yok. Fırsat kartındaki ★ ile ekleyebilirsin.</p>
        <?php endif; ?>
        <?php foreach ($favorites as $s): ?>
        <div class="signal-item">
            <div class="signal-body">
                <span class="signal-content"><?= e(mb_substr($s['content'], 0, 90)) ?></span>
                <div class="signal-meta">
                    <span class="badge badge-score">skor <?= $s['score'] ?></span>
                    <span class="badge badge-<?= e($s['status']) ?>"><?= $s['status'] === 'olgun_firsat' ? 'olgun fırsat' : 'ham' ?></span>
                </div>
            </div>
            <div class="signal-actions">
                <form class="inline-form" method="post" action="<?= url('signals/' . $s['id'] . '/to-project') ?>">
                    <button class="btn btn-sm">Projeye Çevir</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <h2>📊 Sektör Dağılımı (sinyal)</h2>
        <div class="chart-box"><canvas id="sectorChart"></canvas></div>
    </div>
</div>

<!-- Piyasa Haritası + Görevler -->
<div class="grid grid-2 mb">
    <div class="card">
        <h2>◉ Piyasa / Sektör Haritası <a class="btn btn-sm" href="<?= url('market') ?>">yönet</a></h2>
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
                                        <?= $sub['signal_count'] ?> sinyal<?= $sub['mature_count'] > 0 ? ' · ' . $sub['mature_count'] . ' fırsat' : '' ?>
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

    <div class="card">
        <h2>☑ Görev / Tik Özeti</h2>
        <?php if ($openTasks === []): ?>
            <p class="page-sub">Açık görev yok.</p>
        <?php endif; ?>
        <?php foreach ($openTasks as $t): ?>
        <div class="task-item">
            <form class="inline-form" method="post" action="<?= url('tasks/' . $t['id'] . '/toggle') ?>">
                <input type="hidden" name="back" value="">
                <button class="btn btn-sm" title="Tamamla">☐</button>
            </form>
            <div>
                <div class="t-title"><?= e($t['title']) ?></div>
                <div class="t-project"><?= e($t['project_title']) ?> · <?= e($t['phase']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="chart-box" style="height:170px; margin-top:14px;"><canvas id="weeklyChart"></canvas></div>
    </div>
</div>

<!-- Proje Durumu (Kanban) -->
<div class="card mb">
    <h2>▤ Proje Durumu <a class="btn btn-sm" href="<?= url('projects') ?>">yönet</a></h2>
    <div class="kanban">
        <?php foreach (Project::STATUSES as $key => $label): ?>
        <div class="kanban-col">
            <h3><?= e($label) ?> <span><?= count($kanban[$key]) ?></span></h3>
            <?php foreach ($kanban[$key] as $p): ?>
            <div class="kanban-card">
                <div class="k-title"><a href="<?= url('projects/' . $p['id']) ?>"><?= e($p['title']) ?></a></div>
                <div class="k-meta"><?= e($p['current_phase']) ?><?= $p['sector_name'] ? ' · ' . e($p['sector_name']) : '' ?></div>
                <div class="progress"><div class="progress-bar" style="width:<?= $p['progress'] ?>%"></div></div>
                <div class="progress-text">%<?= $p['progress'] ?> (<?= $p['done_tasks'] ?>/<?= $p['total_tasks'] ?> görev)</div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Son Aktiviteler -->
<div class="card">
    <h2>≡ Son Aktiviteler <a class="btn btn-sm" href="<?= url('logs') ?>">tümü</a></h2>
    <ul class="activity">
        <?php if ($activities === []): ?><li><span class="page-sub">Henüz aktivite yok.</span></li><?php endif; ?>
        <?php foreach ($activities as $log): ?>
        <li><span class="a-time"><?= timeAgo($log['created_at']) ?></span> <?= e($log['message']) ?></li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
const sectorData = <?= json_encode($sectorDistribution, JSON_UNESCAPED_UNICODE) ?>;
const weeklyData = <?= json_encode($weeklyTicks, JSON_UNESCAPED_UNICODE) ?>;

new Chart(document.getElementById('sectorChart'), {
    type: 'doughnut',
    data: {
        labels: sectorData.length ? sectorData.map(r => r.name) : ['Veri yok'],
        datasets: [{
            data: sectorData.length ? sectorData.map(r => r.total) : [1],
            backgroundColor: ['#4f46e5', '#0ea5e9', '#16a34a', '#d97706', '#dc2626', '#7c3aed', '#0d9488', '#64748b'],
        }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
});

new Chart(document.getElementById('weeklyChart'), {
    type: 'bar',
    data: {
        labels: weeklyData.map(r => r.day),
        datasets: [{ label: 'Tamamlanan görev', data: weeklyData.map(r => r.total), backgroundColor: '#16a34a', borderRadius: 4 }]
    },
    options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
});
</script>
