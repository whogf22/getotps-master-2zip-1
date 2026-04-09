<!-- Client Dashboard View -->
<?php
use Core\Helper;
$title = 'Dashboard';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <!-- <div class="stat-icon" style="background: #667eea;"></div> -->
        <div class="stat-info">
            <h3><?= Helper::money($stats['balance']) ?></h3>
            <p>Account Balance</p>
        </div>
    </div>
    
    <div class="stat-card">
        <!-- <div class="stat-icon" style="background: #10b981;"></div> -->
        <div class="stat-info">
            <h3><?= $stats['total_activations'] ?></h3>
            <p>Total Activations</p>
        </div>
    </div>
    
    <div class="stat-card">
        <!-- <div class="stat-icon" style="background: #f59e0b;">⌛</div> -->
        <div class="stat-info">
            <h3><?= $stats['pending_activations'] ?></h3>
            <p>Pending</p>
        </div>
    </div>
    
    <div class="stat-card">
        <!-- <div class="stat-icon" style="background: #ef4444;">🔁</div> -->
        <div class="stat-info">
            <h3><?= $stats['active_rentals'] ?></h3>
            <p>Active Rentals</p>
        </div>
    </div>
</div>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Quick Actions</h2>
    </div>
    <div class="panel-body">
        <div style="display: flex; gap: 15px;">
            <a href="<?= $basePath ?>/dashboard/buy" class="btn">🛒 Buy Number</a>
            <a href="<?= $basePath ?>/dashboard/activations" class="btn" style="background: #10b981;">📱 View Activations</a>
            <a href="<?= $basePath ?>/dashboard/transactions" class="btn" style="background: #8b5cf6;"> Transactions</a>
        </div>
    </div>
</div>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Recent Activations</h2>
        <a href="<?= \Core\Helper::url('/dashboard/activations') ?>" class="btn-link">View All</a>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Country</th>
                    <th>Phone</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Cost</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_activations as $activation): ?>
                <tr>
                    <td><?= strtoupper(htmlspecialchars($activation['service'])) ?></td>
                    <td><?= htmlspecialchars(\Core\Helper::getCountryName($activation['country'])) ?></td>
                    <td><?= htmlspecialchars($activation['phone']) ?></td>
                    <td>
                        <?php if ($activation['code']): ?>
                            <strong style="color: #10b981;"><?= htmlspecialchars($activation['code']) ?></strong>
                        <?php else: ?>
                            <span style="color: #999;">Waiting...</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-<?= $activation['status'] ?>"><?= ucfirst($activation['status']) ?></span></td>
                    <td><?= Helper::money($activation['cost']) ?></td>
                    <td><?= Helper::timeAgo($activation['created_at']) ?></td>
                    <td>
                        <a href="<?= \Core\Helper::url('/dashboard/activations/' . $activation['id']) ?>" class="btn-link">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent_activations)): ?>
                <tr>
                    <td colspan="8" class="text-center">
                        <p style="padding: 20px 0; color: #999;">No activations yet. <a href="<?= \Core\Helper::url('/dashboard/buy') ?>" class="btn-link">Buy your first number</a></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
