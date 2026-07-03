<div class="page-head">
    <div>
        <h1>Projeler</h1>
        <p class="page-sub">Fırsattan dönüşen veya manuel açılan projeler — Kanban görünümü</p>
    </div>
</div>

<details class="card mb">
    <summary>+ Manuel Proje Aç</summary>
    <form method="post" action="<?= url('projects') ?>">
        <div class="form-row">
            <div style="flex:2">
                <label>Başlık</label>
                <input type="text" name="title" required placeholder="Proje adı">
            </div>
            <div>
                <label>Sektör</label>
                <select name="sector_id">
                    <option value="0">—</option>
                    <?php foreach ($sectors as $sec): ?>
                    <option value="<?= $sec['id'] ?>"><?= e($sec['category_name'] . ' → ' . $sec['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Bölge</label>
                <select name="region"><option value="TR">TR</option><option value="Global">Global</option></select>
            </div>
            <div>
                <label>Başlangıç Fazı</label>
                <input type="text" name="current_phase" value="Planlama">
            </div>
        </div>
        <div class="form-row">
            <div>
                <label>Kaynak Linkleri (opsiyonel)</label>
                <input type="text" name="source_links" placeholder="https://...">
            </div>
        </div>
        <button class="btn btn-primary">Oluştur</button>
    </form>
</details>

<div class="kanban">
    <?php foreach (Project::STATUSES as $key => $label): ?>
    <div class="kanban-col">
        <h3><?= e($label) ?> <span><?= count($kanban[$key]) ?></span></h3>
        <?php foreach ($kanban[$key] as $p): ?>
        <div class="kanban-card">
            <div class="k-title"><a href="<?= url('projects/' . $p['id']) ?>"><?= e($p['title']) ?></a></div>
            <div class="k-meta">
                <?= e($p['current_phase']) ?><?= $p['sector_name'] ? ' · ' . e($p['sector_name']) : '' ?>
                · <span class="badge badge-<?= strtolower($p['region']) ?>"><?= e($p['region']) ?></span>
            </div>
            <div class="progress"><div class="progress-bar" style="width:<?= $p['progress'] ?>%"></div></div>
            <div class="progress-text">%<?= $p['progress'] ?> (<?= $p['done_tasks'] ?>/<?= $p['total_tasks'] ?> görev)</div>
            <form method="post" action="<?= url('projects/' . $p['id'] . '/status') ?>" style="margin-top:8px; display:flex; gap:6px">
                <input type="hidden" name="back" value="projects">
                <select name="status">
                    <?php foreach (Project::STATUSES as $sKey => $sLabel): ?>
                    <option value="<?= $sKey ?>" <?= $sKey === $key ? 'selected' : '' ?>><?= e($sLabel) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm">Taşı</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>
