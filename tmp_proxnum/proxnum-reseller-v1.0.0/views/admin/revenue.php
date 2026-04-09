<?php
use Core\Helper;
$title = 'Revenue Overview';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Revenue Overview</h2>
        <div>
            <select id="periodFilter" onchange="window.location.href='<?= $basePath ?>/admin/revenue?period='+this.value">
                <option value="week" <?= $period === 'week' ? 'selected' : '' ?>>This Week</option>
                <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>This Month</option>
                <option value="year" <?= $period === 'year' ? 'selected' : '' ?>>This Year</option>
            </select>
        </div>
    </div>
    <div class="panel-body">
        <div class="dashboard-stats">
            <div class="stat-card">
                <!-- <div class="stat-icon" style="background: #10b981;"></div> -->
                <div class="stat-info">
                    <h3><?= Helper::money($total_revenue) ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            
            <div class="stat-card">
                <!-- <div class="stat-icon" style="background: #667eea;"></div> -->
                <div class="stat-info">
                    <h3><?= Helper::money($monthly_revenue) ?></h3>
                    <p>This Month</p>
                </div>
            </div>
        </div>
        
        <h3 style="margin-top: 30px; margin-bottom: 15px;">Revenue by Service</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Orders</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($revenue_by_service as $service): ?>
                <tr>
                    <td><strong><?= strtoupper(htmlspecialchars($service['service'])) ?></strong></td>
                    <td><?= number_format($service['count']) ?></td>
                    <td><?= Helper::money($service['revenue']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h3 style="margin-top: 30px; margin-bottom: 15px;">Monthly Trend</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthly_trend as $trend): ?>
                <tr>
                    <td><?= htmlspecialchars($trend['month']) ?></td>
                    <td><strong><?= Helper::money($trend['revenue']) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
