<?php
use Core\Helper;
$title = 'System Health';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon" style="background: <?= $db_status === 'healthy' ? '#10b981' : '#ef4444' ?>;">💾</div>
        <div class="stat-info">
            <h3><?= ucfirst($db_status) ?></h3>
            <p>Database Status</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: <?= $disk_usage < 80 ? '#10b981' : '#f59e0b' ?>;">💿</div>
        <div class="stat-info">
            <h3><?= number_format($disk_usage, 1) ?>%</h3>
            <p>Disk Usage</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: <?= $api_health === 'healthy' ? '#10b981' : '#f59e0b' ?>;">📡</div>
        <div class="stat-info">
            <h3><?= ucfirst($api_health) ?></h3>
            <p>API Health</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: #667eea;">🔄</div>
        <div class="stat-info">
            <h3><?= $queued_jobs ?></h3>
            <p>Queue Jobs</p>
        </div>
    </div>
</div>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>System Information</h2>
        <button onclick="location.reload()" class="btn">🔄 Refresh</button>
    </div>
    <div class="panel-body">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
            <div>
                <h3>Server</h3>
                <p><strong>PHP Version:</strong> <?= PHP_VERSION ?></p>
                <p><strong>Memory Limit:</strong> <?= $memory_limit ?></p>
                <p><strong>Memory Usage:</strong> <?= number_format($memory_usage / 1024 / 1024, 2) ?> MB</p>
            </div>
            <div>
                <h3>Queue</h3>
                <p><strong>Pending Jobs:</strong> <?= $queued_jobs ?></p>
                <p><strong>Failed Jobs:</strong> <?= $failed_jobs ?></p>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Recent Errors</h2>
    </div>
    <div class="panel-body">
        <?php if (empty($recent_errors)): ?>
            <p class="text-center" style="padding: 20px; color: #10b981;"> No recent errors</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_errors as $error): ?>
                    <tr>
                        <td><?= htmlspecialchars($error['action']) ?></td>
                        <td><?= htmlspecialchars($error['details'] ?? '') ?></td>
                        <td><?= Helper::timeAgo($error['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
