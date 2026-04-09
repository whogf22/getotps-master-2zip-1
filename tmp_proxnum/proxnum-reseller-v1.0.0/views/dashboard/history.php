<?php
use Core\Helper;
$title = 'Usage History';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon" style="background: #667eea;"></div>
        <div class="stat-info">
            <h3><?= Helper::money($stats['total_spent']) ?></h3>
            <p>Total Spent</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: #10b981;">📱</div>
        <div class="stat-info">
            <h3><?= $stats['total_activations'] ?></h3>
            <p>Activations</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: #f59e0b;">📞</div>
        <div class="stat-info">
            <h3><?= $stats['total_rentals'] ?></h3>
            <p>Rentals</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: #8b5cf6;">⭐</div>
        <div class="stat-info">
            <h3><?= $stats['favorite_service'] ?></h3>
            <p>Favorite Service</p>
        </div>
    </div>
</div>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Usage History</h2>
        <select onchange="window.location.href='<?= $basePath ?>/dashboard/history?period='+this.value">
            <option value="7" <?= $period == 7 ? 'selected' : '' ?>>Last 7 Days</option>
            <option value="30" <?= $period == 30 ? 'selected' : '' ?>>Last 30 Days</option>
            <option value="90" <?= $period == 90 ? 'selected' : '' ?>>Last 90 Days</option>
            <option value="365" <?= $period == 365 ? 'selected' : '' ?>>Last Year</option>
        </select>
    </div>
    <div class="panel-body">
        <h3 style="margin-bottom: 15px;">Combined History</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Service/Country</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Cost</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <span class="badge" style="background: <?= $item['type'] === 'activation' ? '#667eea' : '#f59e0b' ?>; color: white;">
                            <?= $item['type'] === 'activation' ? '📱 Activation' : '📞 Rental' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($item['type'] === 'activation'): ?>
                            <?= strtoupper(htmlspecialchars($item['service'])) ?> - <?= htmlspecialchars(\Core\Helper::getCountryName($item['country'])) ?>
                        <?php else: ?>
                            <?= htmlspecialchars(\Core\Helper::getCountryName($item['country'])) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['phone']) ?></td>
                    <td><span class="badge badge-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span></td>
                    <td><?= Helper::money($item['cost']) ?></td>
                    <td><?= Helper::timeAgo($item['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #999;">No activity in this period</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <div style="margin-top: 20px; text-align: center;">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="?period=<?= $period ?>&page=<?= $i ?>" class="btn" style="<?= $i === $pagination['current_page'] ? 'opacity: 0.6;' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
