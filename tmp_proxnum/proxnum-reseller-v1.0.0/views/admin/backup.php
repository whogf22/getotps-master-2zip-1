<?php
use Core\Helper;
$title = 'Backup & Restore';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Database Backups</h2>
        <button onclick="createBackup()" class="btn">💾 Create New Backup</button>
    </div>
    <div class="panel-body">
        <div style="padding: 20px; background: #fff3cd; border-radius: 6px; margin-bottom: 20px;">
            <strong>⚠️ Important:</strong> Regular backups are essential. We recommend scheduling automatic backups and storing them off-site.
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Filename</th>
                    <th>Size</th>
                    <th>Created By</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $backup): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($backup['filename']) ?></strong></td>
                    <td><?= number_format($backup['size'] / 1024 / 1024, 2) ?> MB</td>
                    <td><?= htmlspecialchars($backup['created_by_name']) ?></td>
                    <td><?= Helper::timeAgo($backup['created_at']) ?></td>
                    <td>
                        <a href="<?= $basePath ?>/admin/backup/download?id=<?= $backup['id'] ?>" class="btn-link">Download</a>
                        <a href="#" class="btn-link" onclick="restoreBackup(<?= $backup['id'] ?>)">Restore</a>
                        <a href="#" class="btn-link" onclick="deleteBackup(<?= $backup['id'] ?>)" style="color: #ef4444;">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($backups)): ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 40px;">No backups yet. Create your first backup!</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <div style="margin-top: 20px; text-align: center;">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="?page=<?= $i ?>" class="btn" style="<?= $i === $pagination['current_page'] ? 'opacity: 0.6;' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const basePath = '<?= $basePath ?>';

function createBackup() {
    if (!confirm('Create a new database backup? This may take a few moments.')) return;
    
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Creating backup...';
    
    fetch(basePath + '/admin/backup', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'create_backup'})
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        location.reload();
    })
    .catch(err => {
        alert('Error creating backup');
        btn.disabled = false;
        btn.textContent = '💾 Create New Backup';
    });
}

function restoreBackup(id) {
    if (!confirm('⚠️ CAUTION: This will restore your database to this backup point. Current data will be lost. Continue?')) return;
    alert('Restore functionality would go here');
}

function deleteBackup(id) {
    if (!confirm('Delete this backup?')) return;
    alert('Delete functionality would go here');
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
