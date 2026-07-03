<div class="page-head">
    <div>
        <h1>Aktiviteler</h1>
        <p class="page-sub">Sistem aktivite kaydı — son 100 olay</p>
    </div>
</div>

<div class="card">
    <table>
        <thead><tr><th>Zaman</th><th>Tür</th><th>Mesaj</th></tr></thead>
        <tbody>
        <?php if ($logs === []): ?>
            <tr><td colspan="3" class="page-sub">Henüz aktivite yok.</td></tr>
        <?php endif; ?>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td style="white-space:nowrap"><?= e($log['created_at']) ?></td>
                <td><span class="badge badge-source"><?= e($log['type']) ?></span></td>
                <td><?= e($log['message']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
