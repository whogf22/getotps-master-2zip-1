<?php
use Core\Helper;
$title = 'Updates';
include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-updates">
    <div class="updates-container">
        <!-- Page Header -->
        <div class="page-head">
            <div class="head-left">
                <div class="head-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                    </svg>
                </div>
                <div>
                    <h1>Updates</h1>
                    <p>Manage system-wide communications</p>
                </div>
            </div>
            <button onclick="showCreateModal()" class="btn-create">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>New update</span>
            </button>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-value"><?= count($announcements) ?></span>
                <span class="stat-label">Total updates</span>
            </div>
            <div class="stat-item">
                <?php 
                    $activeCount = 0;
                    foreach ($announcements as $a) { if ($a['active']) $activeCount++; }
                ?>
                <span class="stat-value"><?= $activeCount ?></span>
                <span class="stat-label">Active</span>
            </div>
            <div class="stat-item">
                <?php 
                    $highPriority = 0;
                    foreach ($announcements as $a) { if ($a['priority'] > 0) $highPriority++; }
                ?>
                <span class="stat-value"><?= $highPriority ?></span>
                <span class="stat-label">High priority</span>
            </div>
        </div>

        <!-- Updates Grid -->
        <div class="updates-grid">
            <?php if (!empty($announcements)): ?>
                <?php foreach ($announcements as $announcement): ?>
                <div class="update-card update-<?= htmlspecialchars($announcement['type']) ?>" data-id="<?= $announcement['id'] ?>">
                    <div class="update-card-header">
                        <div class="update-type-badge">
                            <?php
                            $icons = [
                                'info' => 'ℹ️',
                                'warning' => '⚠️',
                                'success' => '',
                                'critical' => '🚨'
                            ];
                            echo $icons[$announcement['type']] ?? 'ℹ️';
                            ?>
                            <span><?= ucfirst(htmlspecialchars($announcement['type'])) ?></span>
                        </div>
                        <div class="update-status">
                            <?php if ($announcement['priority'] > 0): ?>
                            <span class="priority-indicator" title="High priority">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                </svg>
                            </span>
                            <?php endif; ?>
                            <label class="toggle">
                                <input type="checkbox" <?= $announcement['active'] ? 'checked' : '' ?> onchange="toggleAnnouncement(<?= $announcement['id'] ?>, this.checked)">
                                <span class="toggle-track"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="update-card-body">
                        <h3><?= htmlspecialchars($announcement['title']) ?></h3>
                        <div class="update-content">
                            <?= nl2br(htmlspecialchars($announcement['content'])) ?>
                        </div>
                    </div>
                    
                    <div class="update-card-footer">
                        <div class="update-meta">
                            <span class="meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <?= htmlspecialchars($announcement['created_by_name'] ?? 'System') ?>
                            </span>
                            <span class="meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <?= Helper::timeAgo($announcement['created_at']) ?>
                            </span>
                        </div>
                        <button class="btn-delete" onclick="deleteAnnouncement(<?= $announcement['id'] ?>)" title="Delete update">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                    </div>
                    <h3>No updates yet</h3>
                    <p>Create your first system update to communicate with all users</p>
                    <button onclick="showCreateModal()" class="btn-create">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Create update</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <div style="margin-top: 20px; text-align: center; padding: 20px;">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="?page=<?= $i ?>" class="btn" style="<?= $i === $pagination['current_page'] ? 'opacity: 0.6;' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="announcementModal" class="modal" style="display: none;">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Create system update</h2>
            <button onclick="closeModal()" class="modal-close" title="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <form id="announcementForm" onsubmit="submitAnnouncement(event)">
            <input type="hidden" name="csrf_token" value="<?= Helper::getCsrf() ?>">
            <input type="hidden" name="action" value="create">
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-input" required placeholder="e.g., Scheduled Maintenance">
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" class="form-input" rows="5" required placeholder="Enter the update details..."></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select id="type" name="type" class="form-input">
                            <option value="info">Info ℹ️</option>
                            <option value="success">Success </option>
                            <option value="warning">Warning ⚠️</option>
                            <option value="critical">Critical 🚨</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority" class="form-input">
                            <option value="0">Normal</option>
                            <option value="1">High ⭐</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Create update</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    :root {
        --primary-dark: #0a2540;
        --primary-light: #4361ee;
        --accent-teal: #1e7e6c;
        --accent-gold: #c9a03d;
        --success: #10b981;
        --success-light: #e3f9ee;
        --success-dark: #0b7e55;
        --warning: #f59e0b;
        --warning-light: #fff3d4;
        --warning-dark: #b45b0a;
        --danger: #ef4444;
        --danger-light: #fee9e7;
        --danger-dark: #b91c1c;
        --info: #3b82f6;
        --info-light: #dbeafe;
        --info-dark: #1e40af;
        --neutral-50: #f8fafc;
        --neutral-100: #f1f5f9;
        --neutral-200: #e2e8f0;
        --neutral-300: #cbd5e1;
        --neutral-400: #94a3b8;
        --neutral-500: #64748b;
        --neutral-600: #475569;
        --neutral-700: #334155;
        --neutral-800: #1e293b;
        --neutral-900: #0f172a;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #f3f6fd;
        color: var(--neutral-800);
    }

    .admin-updates {
        padding: 2rem;
        min-height: 100vh;
    }

    .updates-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Page Head */
    .page-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .head-left {
        display: flex;
        align-items: center;
        gap: 1.2rem;
    }

    .head-icon {
        width: 56px;
        height: 56px;
        background: white;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-light);
        box-shadow: 0 8px 16px -8px rgba(0,0,0,0.08);
    }

    .head-left h1 {
        font-size: 2rem;
        font-weight: 600;
        color: var(--neutral-900);
        margin: 0;
        line-height: 1.2;
    }

    .head-left p {
        color: var(--neutral-500);
        margin: 0.2rem 0 0 0;
    }

    .btn-create {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 1.5rem;
        background: var(--primary-dark);
        color: white;
        border: none;
        border-radius: 40px;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(10,37,64,0.15);
    }

    .btn-create:hover {
        background: #1a3b5d;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(10,37,64,0.2);
    }

    /* Stats Bar */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-item {
        background: white;
        border-radius: 16px;
        padding: 1.2rem;
        text-align: center;
        border: 1px solid var(--neutral-200);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .stat-value {
        display: block;
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary-dark);
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.8rem;
        color: var(--neutral-500);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    /* Updates Grid */
    .updates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .update-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--neutral-200);
        overflow: hidden;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
    }

    .update-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -8px rgba(0,0,0,0.1);
        border-color: var(--neutral-300);
    }

    .update-info { border-top: 4px solid var(--info-dark); }
    .update-success { border-top: 4px solid var(--success-dark); }
    .update-warning { border-top: 4px solid var(--warning-dark); }
    .update-critical { border-top: 4px solid var(--danger-dark); }

    .update-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.2rem 1.5rem;
        background: var(--neutral-50);
        border-bottom: 1px solid var(--neutral-200);
    }

    .update-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 1rem;
        border-radius: 40px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .update-info .update-type-badge {
        background: var(--info-light);
        color: var(--info-dark);
    }

    .update-success .update-type-badge {
        background: var(--success-light);
        color: var(--success-dark);
    }

    .update-warning .update-type-badge {
        background: var(--warning-light);
        color: var(--warning-dark);
    }

    .update-critical .update-type-badge {
        background: var(--danger-light);
        color: var(--danger-dark);
    }

    .update-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .priority-indicator {
        color: var(--warning-dark);
        display: flex;
        align-items: center;
    }

    /* Toggle Switch */
    .toggle {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-track {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--neutral-300);
        transition: 0.2s;
        border-radius: 24px;
    }

    .toggle-track:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.2s;
        border-radius: 50%;
    }

    .toggle input:checked + .toggle-track {
        background-color: var(--success-dark);
    }

    .toggle input:checked + .toggle-track:before {
        transform: translateX(20px);
    }

    .update-card-body {
        flex: 1;
        padding: 1.5rem;
    }

    .update-card-body h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--neutral-800);
        margin-bottom: 0.8rem;
    }

    .update-content {
        color: var(--neutral-600);
        line-height: 1.6;
        font-size: 0.9rem;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }

    .update-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        background: var(--neutral-50);
        border-top: 1px solid var(--neutral-200);
    }

    .update-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.8rem;
        color: var(--neutral-500);
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .btn-delete {
        padding: 0.4rem;
        background: none;
        border: 1px solid var(--neutral-300);
        border-radius: 30px;
        color: var(--neutral-500);
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-delete:hover {
        background: var(--danger-light);
        border-color: var(--danger-dark);
        color: var(--danger-dark);
    }

    /* Empty State */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 24px;
        border: 1px solid var(--neutral-200);
    }

    .empty-icon {
        color: var(--neutral-400);
        margin-bottom: 1.5rem;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--neutral-700);
        margin-bottom: 0.3rem;
    }

    .empty-state p {
        color: var(--neutral-500);
        margin-bottom: 1.5rem;
    }

    /* Modal */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-card {
        background: white;
        border-radius: 24px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--neutral-200);
    }

    .modal-header h2 {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--neutral-800);
    }

    .modal-close {
        padding: 0.4rem;
        background: var(--neutral-100);
        border: none;
        border-radius: 30px;
        color: var(--neutral-500);
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close:hover {
        background: var(--neutral-200);
        color: var(--neutral-700);
    }

    .modal-body {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--neutral-600);
        text-transform: uppercase;
        letter-spacing: 0.02em;
        margin-bottom: 0.3rem;
    }

    .form-input {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid var(--neutral-300);
        border-radius: 12px;
        font-size: 0.95rem;
        transition: all 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-light);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .modal-footer {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding: 1.5rem 2rem;
        border-top: 1px solid var(--neutral-200);
        background: var(--neutral-50);
    }

    .btn-secondary {
        padding: 0.8rem 1.5rem;
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: 40px;
        color: var(--neutral-600);
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background: var(--neutral-100);
        border-color: var(--neutral-400);
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 1.5rem;
        background: var(--primary-dark);
        color: white;
        border: none;
        border-radius: 40px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        background: #1a3b5d;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(10,37,64,0.2);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .admin-updates { padding: 1rem; }
        
        .head-left h1 { font-size: 1.6rem; }
        
        .updates-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-bar {
            grid-template-columns: 1fr;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .modal-body { padding: 1.5rem; }
        .modal-footer { flex-direction: column; }
        .btn-secondary, .btn-primary { width: 100%; justify-content: center; }
    }
</style>

<script>
const basePath = '<?= Helper::url('') ?>';

function showCreateModal() {
    document.getElementById('announcementModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('announcementModal').style.display = 'none';
    document.getElementById('announcementForm').reset();
}

function submitAnnouncement(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    fetch(basePath + '/admin/announcements', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(' ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
}

function toggleAnnouncement(id, active) {
    const formData = new FormData();
    formData.append('action', 'toggle');
    formData.append('id', id);
    formData.append('active', active ? 1 : 0);
    formData.append('csrf_token', '<?= Helper::getCsrf() ?>');
    
    fetch(basePath + '/admin/announcements', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            console.log('Announcement updated');
        } else {
            alert('Error: ' + data.message);
            location.reload();
        }
    });
}

function deleteAnnouncement(id) {
    if (!confirm('Delete this announcement? This action cannot be undone.')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    formData.append('csrf_token', '<?= Helper::getCsrf() ?>');
    
    fetch(basePath + '/admin/announcements', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

// Close modal on outside click
document.getElementById('announcementModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
```