<?php
$title = 'System Updates';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .updates-container {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    .version-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .version-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .version-info h2 {
        margin: 0;
        color: #333;
        font-size: 24px;
    }
    
    .version-number {
        display: inline-block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        margin-top: 0.5rem;
    }
    
    .update-badge {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
    }
    
    .update-badge.available {
        background: #10b981;
        color: white;
    }
    
    .update-badge.critical {
        background: #ef4444;
        color: white;
        animation: pulse 2s infinite;
    }
    
    .update-badge.up-to-date {
        background: #e5e7eb;
        color: #6b7280;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .update-details {
        background: #f9fafb;
        border-left: 4px solid #667eea;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    
    .update-details h3 {
        margin: 0 0 1rem 0;
        color: #333;
        font-size: 18px;
    }
    
    .changelog {
        background: white;
        padding: 1rem;
        border-radius: 6px;
        margin-top: 1rem;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .changelog ul {
        margin: 0;
        padding-left: 1.5rem;
    }
    
    .changelog li {
        margin-bottom: 0.5rem;
        color: #555;
    }
    
    .update-metadata {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .metadata-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #666;
        font-size: 14px;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
    }
    
    .btn-secondary {
        background: #6b7280;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #4b5563;
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    
    .btn-danger:hover {
        background: #dc2626;
    }
    
    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .progress-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    .progress-overlay.active {
        display: flex;
    }
    
    .progress-content {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        text-align: center;
        min-width: 400px;
    }
    
    .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f4f6;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .backups-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .backups-section h2 {
        margin: 0 0 1.5rem 0;
        color: #333;
    }
    
    .backup-item {
        background: #f9fafb;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .backup-info {
        flex: 1;
    }
    
    .backup-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.25rem;
    }
    
    .backup-meta {
        font-size: 13px;
        color: #666;
    }
    
    .backup-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 13px;
    }
    
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }
    
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
    
    .alert-warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #9ca3af;
    }
</style>

<div class="updates-container">
    <div id="alert-container"></div>
    
    <!-- Current Version & Update Info -->
    <div class="version-card">
        <div class="version-header">
            <div class="version-info">
                <h2>Current Version</h2>
                <span class="version-number">v<?= htmlspecialchars($current_version) ?></span>
            </div>
            <div>
                <?php if ($update_info['update_available']): ?>
                    <span class="update-badge <?= $update_info['critical'] ? 'critical' : 'available' ?>">
                        <?= $update_info['critical'] ? '🚨 Critical Update Available' : '🎉 Update Available' ?>
                    </span>
                <?php else: ?>
                    <span class="update-badge up-to-date">✓ Up to Date</span>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($update_info['update_available']): ?>
            <div class="update-details">
                <h3>Version <?= htmlspecialchars($update_info['latest_version']) ?> Available</h3>
                
                <div class="update-metadata">
                    <div class="metadata-item">
                        📅 Released: <?= htmlspecialchars($update_info['release_date']) ?>
                    </div>
                    <div class="metadata-item">
                        📦 Size: <?= \Core\UpdateManager::formatSize($update_info['size']) ?>
                    </div>
                    <?php if ($update_info['critical']): ?>
                        <div class="metadata-item" style="color: #ef4444;">
                            ⚠️ Critical security update
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($update_info['changelog'])): ?>
                    <div class="changelog">
                        <strong>What's New:</strong>
                        <?= $update_info['changelog'] ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="installUpdate('<?= htmlspecialchars($update_info['download_url']) ?>')">
                    🚀 Install Update Now
                </button>
                <button class="btn btn-secondary" onclick="createBackup()">
                    💾 Create Backup First
                </button>
                <button class="btn btn-secondary" onclick="checkForUpdates()">
                    🔄 Recheck
                </button>
            </div>
        <?php else: ?>
            <div class="action-buttons">
                <button class="btn btn-secondary" onclick="checkForUpdates()">
                    🔄 Check for Updates
                </button>
                <button class="btn btn-secondary" onclick="createBackup()">
                    💾 Create Backup
                </button>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Backups Section -->
    <div class="backups-section">
        <h2>📦 Available Backups</h2>
        
        <div id="backups-list">
            <?php if (empty($backups)): ?>
                <div class="empty-state">
                    <div style="font-size: 48px; margin-bottom: 1rem;">📦</div>
                    <p>No backups available yet</p>
                    <button class="btn btn-secondary" onclick="createBackup()">Create First Backup</button>
                </div>
            <?php else: ?>
                <?php foreach ($backups as $backup): ?>
                    <div class="backup-item">
                        <div class="backup-info">
                            <div class="backup-name"><?= htmlspecialchars($backup['name']) ?></div>
                            <div class="backup-meta">
                                Version: <?= htmlspecialchars($backup['version']) ?> | 
                                Date: <?= htmlspecialchars($backup['date']) ?> | 
                                Size: <?= \Core\UpdateManager::formatSize($backup['size']) ?>
                            </div>
                        </div>
                        <div class="backup-actions">
                            <button class="btn btn-sm btn-secondary" onclick="restoreBackup('<?= htmlspecialchars($backup['name']) ?>')">
                                ↩️ Restore
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteBackup('<?= htmlspecialchars($backup['name']) ?>')">
                                🗑️ Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Progress Overlay -->
<div class="progress-overlay" id="progress-overlay">
    <div class="progress-content">
        <div class="spinner"></div>
        <h3 id="progress-title">Processing...</h3>
        <p id="progress-message">Please wait, do not close this window.</p>
    </div>
</div>

<script>
    const basePath = '<?= Helper::url('') ?>';
    const csrfToken = '<?= $csrf_token ?>';
    
    function showProgress(title, message) {
        document.getElementById('progress-title').textContent = title;
        document.getElementById('progress-message').textContent = message;
        document.getElementById('progress-overlay').classList.add('active');
    }
    
    function hideProgress() {
        document.getElementById('progress-overlay').classList.remove('active');
    }
    
    function showAlert(type, message) {
        const alertHtml = `<div class="alert alert-${type}">${message}</div>`;
        document.getElementById('alert-container').innerHTML = alertHtml;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        setTimeout(() => {
            document.getElementById('alert-container').innerHTML = '';
        }, 5000);
    }
    
    function checkForUpdates() {
        showProgress('Checking for Updates', 'Contacting update server...');
        
        fetch(basePath + '/admin/checkUpdates', {
            credentials: 'same-origin'
        })
            .then(r => r.json())
            .then(data => {
                hideProgress();
                
                if (data.update_available) {
                    showAlert('success', 'Update available: Version ' + data.latest_version);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('success', 'You\'re running the latest version!');
                }
            })
            .catch(error => {
                hideProgress();
                showAlert('error', 'Failed to check for updates: ' + error.message);
            });
    }
    
    function createBackup() {
        if (!confirm('Create a backup of the current system?')) return;
        
        showProgress('Creating Backup', 'Backing up files and database...');
        
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        
        fetch(basePath + '/admin/createBackup', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(r => r.json())
            .then(data => {
                hideProgress();
                
                if (data.success) {
                    showAlert('success', 'Backup created successfully!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', 'Backup failed: ' + data.error);
                }
            })
            .catch(error => {
                hideProgress();
                showAlert('error', 'Backup failed: ' + error.message);
            });
    }
    
    function installUpdate(downloadUrl) {
        if (!confirm('This will update your system. A backup will be created automatically. Continue?')) return;
        
        showProgress('Installing Update', 'Step 1/4: Creating backup...');
        
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('download_url', downloadUrl);
        
        // Set a timer to reload after 15 seconds (update process might take time)
        let reloadTimer = setTimeout(() => {
            showProgress('Installing Update', 'Finalizing installation...');
            setTimeout(() => {
                location.reload();
            }, 2000);
        }, 15000); // Wait 15 seconds for update to complete
        
        fetch(basePath + '/admin/installUpdate', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(r => {
                if (r.ok) {
                    return r.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            // Response isn't JSON, update might have interrupted PHP
                            return { success: true, interrupted: true };
                        }
                    });
                }
                throw new Error('Server error');
            })
            .then(data => {
                clearTimeout(reloadTimer);
                
                if (data.success || data.interrupted) {
                    showAlert('success', 'Update installed successfully!' + (data.version ? ' Updated to version ' + data.version : ''));
                    setTimeout(() => location.reload(), 2000);
                } else {
                    hideProgress();
                    showAlert('error', 'Update failed: ' + data.error);
                }
            })
            .catch(error => {
                // Let the timer handle reload - update might have succeeded
                console.log('Update response error (may be normal):', error);
            });
    }
            });
    }
    
    function restoreBackup(backupName) {
        if (!confirm('Restore from backup "' + backupName + '"? This will replace all current files.')) return;
        
        showProgress('Restoring Backup', 'Rolling back to previous version...');
        
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('backup_name', backupName);
        
        // Start rollback and set a timer to reload the page
        // (response might not come back if PHP files are overwritten during process)
        let reloadTimer = setTimeout(() => {
            showProgress('Restoring Backup', 'Finalizing rollback...');
            setTimeout(() => {
                location.reload();
            }, 2000);
        }, 5000); // Wait 5 seconds, then reload
        
        fetch(basePath + '/admin/rollback', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(r => {
                // Check if response is valid
                if (r.ok) {
                    return r.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            // Response isn't JSON, likely rollback interrupted PHP
                            return { success: true, interrupted: true };
                        }
                    });
                }
                throw new Error('Server error');
            })
            .then(data => {
                clearTimeout(reloadTimer);
                
                if (data.success || data.interrupted) {
                    showAlert('success', 'Rollback completed successfully!');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    hideProgress();
                    showAlert('error', 'Rollback failed: ' + data.error);
                }
            })
            .catch(error => {
                // Don't clear the timer - let it reload anyway
                // Rollback might have succeeded but response was lost
                console.log('Rollback response error (may be normal):', error);
            });
    }
    
    function deleteBackup(backupName) {
        if (!confirm('Permanently delete backup "' + backupName + '"?')) return;
        
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('backup_name', backupName);
        
        fetch(basePath + '/admin/deleteBackup', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Backup deleted successfully');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('error', 'Delete failed: ' + data.error);
                }
            })
            .catch(error => {
                showAlert('error', 'Delete failed: ' + error.message);
            });
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
