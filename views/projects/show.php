<div class="page-head">
    <div>
        <h1><?= e($project['title']) ?></h1>
        <p class="page-sub">
            <?= e($project['sector_name'] ?? 'Sektör atanmadı') ?>
            · <span class="badge badge-<?= strtolower($project['region']) ?>"><?= e($project['region']) ?></span>
            · <span class="badge badge-source"><?= e(Project::STATUSES[$project['status']] ?? $project['status']) ?></span>
            · <?= e($project['created_at']) ?>
        </p>
    </div>
    <div style="display:flex; gap:8px; align-items:center">
        <form method="post" action="<?= url('projects/' . $project['id'] . '/status') ?>" style="display:flex; gap:6px">
            <input type="hidden" name="back" value="projects/<?= $project['id'] ?>">
            <select name="status">
                <?php foreach (Project::STATUSES as $sKey => $sLabel): ?>
                <option value="<?= $sKey ?>" <?= $sKey === $project['status'] ? 'selected' : '' ?>><?= e($sLabel) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn">Durum Değiştir</button>
        </form>
        <form method="post" action="<?= url('projects/' . $project['id'] . '/delete') ?>" onsubmit="return confirm('Proje ve görevleri silinsin mi?')">
            <button class="btn btn-danger">Sil</button>
        </form>
    </div>
</div>

<div class="card mb">
    <h2>İlerleme</h2>
    <div class="progress" style="height:10px"><div class="progress-bar" style="width:<?= $progress ?>%"></div></div>
    <div class="progress-text">%<?= $progress ?> tamamlandı (<?= count(array_filter($tasks, fn($t) => $t['status'] === 'tamamlandi')) ?>/<?= count($tasks) ?> görev)</div>
</div>

<div class="grid grid-2 mb">
    <div class="card">
        <h2>☑ Görevler</h2>
        <?php
        $byPhase = [];
        foreach ($tasks as $t) {
            $byPhase[$t['phase']][] = $t;
        }
        ?>
        <?php if ($tasks === []): ?><p class="page-sub">Henüz görev yok.</p><?php endif; ?>
        <?php foreach ($byPhase as $phase => $phaseTasks): ?>
        <p style="font-weight:700; margin:10px 0 4px"><?= e((string) $phase) ?></p>
        <?php foreach ($phaseTasks as $t): ?>
        <div class="task-item <?= $t['status'] === 'tamamlandi' ? 'done' : '' ?>">
            <form class="inline-form" method="post" action="<?= url('tasks/' . $t['id'] . '/toggle') ?>">
                <input type="hidden" name="back" value="projects/<?= $project['id'] ?>">
                <button class="btn btn-sm"><?= $t['status'] === 'tamamlandi' ? '☑' : '☐' ?></button>
            </form>
            <div style="flex:1">
                <div class="t-title"><?= e($t['title']) ?></div>
                <?php if ($t['description']): ?><div class="t-project"><?= e($t['description']) ?></div><?php endif; ?>
            </div>
            <form class="inline-form" method="post" action="<?= url('tasks/' . $t['id'] . '/delete') ?>" onsubmit="return confirm('Görev silinsin mi?')">
                <button class="btn btn-sm btn-danger">×</button>
            </form>
        </div>
        <?php endforeach; ?>
        <?php endforeach; ?>

        <form method="post" action="<?= url('projects/' . $project['id'] . '/tasks') ?>" style="margin-top:14px">
            <div class="form-row">
                <div style="flex:2">
                    <label>Yeni Görev</label>
                    <input type="text" name="title" required placeholder="Görev başlığı">
                </div>
                <div>
                    <label>Faz</label>
                    <input type="text" name="phase" value="<?= e($project['current_phase']) ?>" list="phases">
                    <datalist id="phases">
                        <option value="Planlama"><option value="Geliştirme"><option value="Test"><option value="Yayın">
                    </datalist>
                </div>
            </div>
            <div class="form-row">
                <div><input type="text" name="description" placeholder="Açıklama (opsiyonel)"></div>
                <div style="flex:0"><button class="btn btn-primary">Ekle</button></div>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Proje Bilgileri</h2>
        <form method="post" action="<?= url('projects/' . $project['id'] . '/details') ?>">
            <div class="form-row">
                <div>
                    <label>Mevcut Faz</label>
                    <input type="text" name="current_phase" value="<?= e($project['current_phase']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div>
                    <label>Notlar (kararlar, kaynaklar, yapılacaklar)</label>
                    <textarea name="notes" rows="7"><?= e($project['notes']) ?></textarea>
                </div>
            </div>
            <button class="btn btn-primary">Kaydet</button>
        </form>

        <?php if ($project['source_links']): ?>
        <h2 style="margin-top:18px">Kaynak Linkleri</h2>
        <?php foreach (array_filter(array_map('trim', explode(',', $project['source_links']))) as $link): ?>
        <p><a href="<?= e($link) ?>" target="_blank" rel="noopener"><?= e(mb_substr($link, 0, 70)) ?></a></p>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
