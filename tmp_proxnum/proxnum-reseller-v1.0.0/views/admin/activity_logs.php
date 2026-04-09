<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="activity-dashboard">
    <div class="activity-container">
        <!-- Page Header -->
        <div class="activity-header">
            <div class="header-left">
                <div class="header-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div>
                    <h1>Activity logs</h1>
                    <p>Track and monitor system events</p>
                </div>
            </div>
            <div class="header-stats">
                <div class="stat-pill">
                    <span class="stat-dot"></span>
                    <span><?= $total_logs ?? 0 ?> total events</span>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="logs-card">
            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <input type="hidden" name="route" value="admin/activityLogs">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="action">Action type</label>
                            <div class="select-wrapper">
                                <select name="action" id="action" class="filter-select">
                                    <option value="">All actions</option>
                                    <?php foreach ($actions as $action): ?>
                                    <option value="<?= htmlspecialchars($action['action']) ?>" 
                                        <?= ($filters['action'] ?? '') === $action['action'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($action['action']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <svg class="select-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn-filter">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="22 3 2 3 10 13 10 21 14 18 14 13 22 3"></polygon>
                                </svg>
                                <span>Apply filter</span>
                            </button>
                            <a href="<?= \Core\Helper::url('/admin/activityLogs') ?>" class="btn-clear">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                                <span>Clear</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Logs Table -->
            <div class="logs-table-wrapper">
                <?php if (!empty($logs)): ?>
                    <table class="logs-table">
                        <thead>
                            <tr>
                                <th>Date & time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="log-time">
                                    <div class="time-main"><?= \Core\Helper::date($log['created_at'], 'M d, Y') ?></div>
                                    <div class="time-sub"><?= \Core\Helper::date($log['created_at'], 'H:i:s') ?></div>
                                </td>
                                <td>
                                    <?php if ($log['user_name']): ?>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?= strtoupper(substr($log['user_name'], 0, 1)) ?>
                                            </div>
                                            <div class="user-details">
                                                <span class="user-name"><?= htmlspecialchars($log['user_name']) ?></span>
                                                <span class="user-email"><?= htmlspecialchars($log['user_email']) ?></span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="user-info">
                                            <div class="user-avatar system">⚙️</div>
                                            <span class="user-name system-name">System</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $actionLower = strtolower($log['action']);
                                        $badgeClass = 'badge-info';
                                        if (strpos($actionLower, 'error') !== false || strpos($actionLower, 'fail') !== false) {
                                            $badgeClass = 'badge-danger';
                                        } elseif (strpos($actionLower, 'login') !== false) {
                                            $badgeClass = 'badge-success';
                                        } elseif (strpos($actionLower, 'update') !== false || strpos($actionLower, 'edit') !== false) {
                                            $badgeClass = 'badge-warning';
                                        } elseif (strpos($actionLower, 'create') !== false || strpos($actionLower, 'add') !== false) {
                                            $badgeClass = 'badge-primary';
                                        } elseif (strpos($actionLower, 'delete') !== false || strpos($actionLower, 'remove') !== false) {
                                            $badgeClass = 'badge-danger';
                                        }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td class="log-description"><?= htmlspecialchars($log['description']) ?></td>
                                <td>
                                    <code class="ip-address"><?= htmlspecialchars($log['ip_address']) ?></code>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-logs">
                        <div class="empty-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <h3>No logs found</h3>
                        <p>Try adjusting your filters or check back later</p>
                        <a href="<?= \Core\Helper::url('/admin/activityLogs') ?>" class="btn-reset">Reset filters</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?= $current_page - 1 ?><?= ($filters['action'] ?? '') ? '&action=' . urlencode($filters['action']) : '' ?>" class="page-btn prev">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            <span>Previous</span>
                        </a>
                    <?php endif; ?>
                    
                    <div class="page-indicator">
                        <span class="current-page"><?= $current_page ?></span>
                        <span class="total-pages">of <?= $total_pages ?></span>
                    </div>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?= $current_page + 1 ?><?= ($filters['action'] ?? '') ? '&action=' . urlencode($filters['action']) : '' ?>" class="page-btn next">
                            <span>Next</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Info Note -->
        <div class="info-note">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>Logs are retained for 30 days. Use filters to narrow down specific actions or time periods.</span>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-dark: #0a2540;
        --primary-light: #4361ee;
        --accent-teal: #1e7e6c;
        --accent-gold: #c9a03d;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
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
        background: #f2f5f9;
        color: var(--neutral-800);
    }

    .activity-dashboard {
        padding: 2rem;
        min-height: 100vh;
    }

    .activity-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Header */
    .activity-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1.2rem;
    }

    .header-icon {
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

    .header-left h1 {
        font-size: 2rem;
        font-weight: 600;
        color: var(--neutral-900);
        margin: 0;
        line-height: 1.2;
    }

    .header-left p {
        color: var(--neutral-500);
        margin: 0.2rem 0 0 0;
    }

    .stat-pill {
        background: white;
        padding: 0.5rem 1.2rem;
        border-radius: 40px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: var(--neutral-700);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .stat-dot {
        width: 8px;
        height: 8px;
        background: var(--success);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.1); }
    }

    /* Main Card */
    .logs-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05);
        border: 1px solid rgba(203, 213, 225, 0.4);
        overflow: hidden;
    }

    /* Filter Section */
    .filter-section {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--neutral-200);
        background: var(--neutral-50);
    }

    .filter-row {
        display: flex;
        align-items: flex-end;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-group {
        flex: 1;
        min-width: 250px;
    }

    .filter-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--neutral-600);
        text-transform: uppercase;
        letter-spacing: 0.02em;
        margin-bottom: 0.3rem;
    }

    .select-wrapper {
        position: relative;
    }

    .filter-select {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid var(--neutral-300);
        border-radius: 40px;
        font-size: 0.95rem;
        color: var(--neutral-800);
        background: white;
        appearance: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary-light);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }

    .select-arrow {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--neutral-500);
        pointer-events: none;
    }

    .filter-actions {
        display: flex;
        gap: 0.8rem;
    }

    .btn-filter, .btn-clear {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 40px;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-filter {
        background: var(--primary-dark);
        color: white;
    }

    .btn-filter:hover {
        background: #1a3b5d;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -5px rgba(10,37,64,0.3);
    }

    .btn-clear {
        background: white;
        color: var(--neutral-600);
        border: 1px solid var(--neutral-300);
    }

    .btn-clear:hover {
        background: var(--neutral-100);
        border-color: var(--neutral-400);
    }

    /* Logs Table */
    .logs-table-wrapper {
        overflow-x: auto;
    }

    .logs-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
    }

    .logs-table th {
        text-align: left;
        padding: 1.2rem 1.5rem;
        background: var(--neutral-50);
        font-weight: 600;
        font-size: 0.8rem;
        color: var(--neutral-600);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid var(--neutral-200);
    }

    .logs-table td {
        padding: 1.2rem 1.5rem;
        border-bottom: 1px solid var(--neutral-200);
        color: var(--neutral-700);
    }

    .logs-table tr:hover td {
        background: var(--neutral-50);
    }

    .log-time {
        white-space: nowrap;
    }

    .time-main {
        font-weight: 500;
        color: var(--neutral-800);
    }

    .time-sub {
        font-size: 0.8rem;
        color: var(--neutral-500);
        margin-top: 0.2rem;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, var(--primary-light), var(--primary-dark));
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .user-avatar.system {
        background: var(--neutral-300);
        color: var(--neutral-600);
        font-size: 1rem;
    }

    .user-details {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        color: var(--neutral-800);
    }

    .user-email {
        font-size: 0.8rem;
        color: var(--neutral-500);
    }

    .system-name {
        color: var(--neutral-600);
        font-weight: 500;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 40px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .badge-info {
        background: #e6f0ff;
        color: #1e40af;
    }

    .badge-success {
        background: #e3f9ee;
        color: #0b7e55;
    }

    .badge-warning {
        background: #fff3d4;
        color: #b45b0a;
    }

    .badge-danger {
        background: #fee9e7;
        color: #b91c1c;
    }

    .badge-primary {
        background: #e6e9ff;
        color: #3730a3;
    }

    .log-description {
        max-width: 300px;
        word-break: break-word;
    }

    .ip-address {
        background: var(--neutral-100);
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        font-family: 'Monaco', 'Menlo', monospace;
        font-size: 0.8rem;
        color: var(--neutral-700);
        border: 1px solid var(--neutral-200);
    }

    /* Empty State */
    .empty-logs {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-icon {
        color: var(--neutral-400);
        margin-bottom: 1.5rem;
        opacity: 0.5;
    }

    .empty-logs h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--neutral-700);
        margin-bottom: 0.3rem;
    }

    .empty-logs p {
        color: var(--neutral-500);
        margin-bottom: 1.5rem;
    }

    .btn-reset {
        display: inline-block;
        padding: 0.7rem 1.5rem;
        background: var(--neutral-100);
        border: 1px solid var(--neutral-300);
        border-radius: 40px;
        color: var(--neutral-700);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-reset:hover {
        background: white;
        border-color: var(--neutral-400);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 1.5rem 2rem;
        border-top: 1px solid var(--neutral-200);
        background: var(--neutral-50);
    }

    .pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
    }

    .page-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.2rem;
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: 40px;
        color: var(--neutral-700);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
    }

    .page-btn:hover {
        background: var(--neutral-100);
        border-color: var(--neutral-400);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .page-indicator {
        display: flex;
        align-items: center;
        gap: 0.2rem;
        font-weight: 500;
    }

    .current-page {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary-dark);
    }

    .total-pages {
        color: var(--neutral-500);
    }

    /* Info Note */
    .info-note {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 1.2rem 1.5rem;
        background: #f0f4ff;
        border-radius: 60px;
        margin-top: 2rem;
        color: #1e40af;
        border: 1px solid #dbeafe;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .activity-dashboard { padding: 1rem; }
        .filter-row { flex-direction: column; align-items: stretch; }
        .filter-actions { justify-content: flex-end; }
    }

    @media (max-width: 768px) {
        .header-left h1 { font-size: 1.6rem; }
        .logs-table td { padding: 1rem; }
        .user-info { flex-wrap: wrap; }
        .pagination { flex-wrap: wrap; }
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>