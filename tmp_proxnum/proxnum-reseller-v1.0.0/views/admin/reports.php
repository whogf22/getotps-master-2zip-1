<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard-container">
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <span class="header-badge">ANALYTICS</span>
                <h1 class="page-title">Reports & analytics</h1>
                <p class="page-description">Track revenue, monitor growth, and analyze performance metrics</p>
            </div>
        </div>

        <!-- Date Range Filter Card -->
        <div class="filter-card">
            <form method="GET" class="filter-form">
                <input type="hidden" name="route" value="admin/reports">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="start_date">Start date</label>
                        <div class="date-input-wrapper">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>" class="date-input">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="end_date">End date</label>
                        <div class="date-input-wrapper">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>" class="date-input">
                        </div>
                    </div>
                    <div class="filter-group filter-action">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn-filter">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 13 10 21 14 18 14 13 22 3"></polygon>
                            </svg>
                            <span>Apply filter</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(10, 37, 64, 0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary-deep)" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Total revenue</span>
                    <span class="stat-value"><?= \Core\Helper::money($total_revenue) ?></span>
                    <span class="stat-period">Selected period</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(30, 126, 108, 0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-teal)" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Current month</span>
                    <span class="stat-value"><?= \Core\Helper::money($current_month_stats['revenue'] ?? 0) ?></span>
                    <span class="stat-period"><?= $current_month_stats['activations'] ?? 0 ?> activations</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(201, 160, 61, 0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-gold)" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Last month</span>
                    <span class="stat-value"><?= \Core\Helper::money($last_month_stats['revenue'] ?? 0) ?></span>
                    <span class="stat-period"><?= $last_month_stats['activations'] ?? 0 ?> activations</span>
                </div>
            </div>

            <div class="stat-card">
                <?php 
                    $lastRevenue = $last_month_stats['revenue'] ?? 1;
                    $currentRevenue = $current_month_stats['revenue'] ?? 0;
                    $growth = $lastRevenue > 0 ? (($currentRevenue - $lastRevenue) / $lastRevenue) * 100 : 0;
                    $growthClass = $growth >= 0 ? 'growth-positive' : 'growth-negative';
                ?>
                <div class="stat-icon" style="background: <?= $growth >= 0 ? 'rgba(30, 126, 108, 0.1)' : 'rgba(185, 28, 28, 0.1)' ?>;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?= $growth >= 0 ? 'var(--accent-teal)' : 'var(--danger-dark)' ?>" stroke-width="2">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Growth</span>
                    <span class="stat-value <?= $growthClass ?>">
                        <?= ($growth >= 0 ? '+' : '') . number_format($growth, 1) ?>%
                    </span>
                    <span class="stat-period">Month over month</span>
                </div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="chart-card">
            <div class="card-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
                <h3>Revenue by day</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($revenue_by_day)): ?>
                    <div class="chart-container">
                        <?php 
                            $maxRevenue = max(array_column($revenue_by_day, 'revenue'));
                            foreach ($revenue_by_day as $data): 
                                $percentage = $maxRevenue > 0 ? ($data['revenue'] / $maxRevenue) * 100 : 0;
                        ?>
                        <div class="chart-bar">
                            <div class="bar-tooltip"><?= \Core\Helper::money($data['revenue']) ?></div>
                            <div class="bar" style="height: <?= $percentage ?>%; background: linear-gradient(180deg, var(--primary-deep) 0%, var(--primary-soft) 100%);"></div>
                            <div class="bar-label"><?= \Core\Helper::date($data['date'], 'M d') ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon"></div>
                        <p class="text-muted">No revenue data for selected period</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="analytics-grid">
            <!-- Top Clients -->
            <div class="analytics-card">
                <div class="card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <h3>Top clients</h3>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($top_clients)): ?>
                        <div class="list-table">
                            <div class="list-header">
                                <div class="list-cell">Client</div>
                                <div class="list-cell text-center">Purchases</div>
                                <div class="list-cell text-right">Total spent</div>
                            </div>
                            <div class="list-body">
                                <?php foreach ($top_clients as $client): ?>
                                <div class="list-row">
                                    <div class="list-cell">
                                        <div class="client-info">
                                            <strong><?= htmlspecialchars($client['name']) ?></strong>
                                            <small><?= htmlspecialchars($client['email']) ?></small>
                                        </div>
                                    </div>
                                    <div class="list-cell text-center"><?= $client['purchases'] ?></div>
                                    <div class="list-cell text-right">
                                        <span class="amount"><?= \Core\Helper::money($client['total_spent']) ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p class="text-muted">No client data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Popular Services -->
            <div class="analytics-card">
                <div class="card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    <h3>Popular services</h3>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($popular_services)): ?>
                        <div class="list-table">
                            <div class="list-header">
                                <div class="list-cell">Service</div>
                                <div class="list-cell text-center">Count</div>
                                <div class="list-cell text-right">Revenue</div>
                            </div>
                            <div class="list-body">
                                <?php foreach ($popular_services as $service): ?>
                                <div class="list-row">
                                    <div class="list-cell"><?= htmlspecialchars($service['service']) ?></div>
                                    <div class="list-cell text-center"><?= $service['count'] ?></div>
                                    <div class="list-cell text-right">
                                        <span class="amount"><?= \Core\Helper::money($service['revenue']) ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p class="text-muted">No service data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Popular Countries -->
        <div class="analytics-card full-width">
            <div class="card-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
                <h3>Popular countries</h3>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($popular_countries)): 
                    $totalCount = array_sum(array_column($popular_countries, 'count'));
                ?>
                    <div class="list-table">
                        <div class="list-header">
                            <div class="list-cell">Country</div>
                            <div class="list-cell text-center">Activations</div>
                            <div class="list-cell text-right">Revenue</div>
                            <div class="list-cell text-right" style="width: 200px;">Share</div>
                        </div>
                        <div class="list-body">
                            <?php foreach ($popular_countries as $country): 
                                $percentage = $totalCount > 0 ? ($country['count'] / $totalCount) * 100 : 0;
                            ?>
                            <div class="list-row">
                                <div class="list-cell"><?= htmlspecialchars($country['country']) ?></div>
                                <div class="list-cell text-center"><?= $country['count'] ?></div>
                                <div class="list-cell text-right">
                                    <span class="amount"><?= \Core\Helper::money($country['revenue']) ?></span>
                                </div>
                                <div class="list-cell text-right" style="width: 200px;">
                                    <div class="progress-wrapper">
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?= $percentage ?>%"></div>
                                        </div>
                                        <span class="progress-label"><?= number_format($percentage, 1) ?>%</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p class="text-muted">No country data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-deep: #0a2540;
        --primary-soft: #1a3b5d;
        --accent-gold: #c9a03d;
        --accent-teal: #1e7e6c;
        --neutral-100: #f8fafc;
        --neutral-200: #eef2f6;
        --neutral-300: #e2e8f0;
        --neutral-400: #cbd5e1;
        --neutral-600: #475569;
        --neutral-700: #334155;
        --neutral-800: #1e293b;
        --neutral-900: #0f172a;
        --success-light: #e3f9ee;
        --success-dark: #0b7e55;
        --warning-light: #fff3d4;
        --warning-dark: #b45b0a;
        --danger-light: #fee9e7;
        --danger-dark: #b91c1c;
        --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        
        --radius-xl: 32px;
        --radius-lg: 24px;
        --radius-md: 16px;
        --radius-sm: 8px;
        --transition: all 0.2s ease;
    }

    body {
        font-family: var(--font-sans);
        background: linear-gradient(135deg, var(--neutral-100) 0%, var(--neutral-200) 100%);
        color: var(--neutral-900);
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 2rem;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 2rem;
    }

    .header-badge {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: var(--accent-gold);
        background: rgba(201, 160, 61, 0.08);
        padding: 0.4rem 1rem;
        border-radius: 30px;
        margin-bottom: 0.75rem;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 600;
        color: var(--primary-deep);
        margin-bottom: 0.25rem;
        letter-spacing: -0.01em;
    }

    .page-description {
        color: var(--neutral-600);
        font-size: 1rem;
    }

    /* Filter Card */
    .filter-card {
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-xl);
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
    }

    .filter-grid {
        display: flex;
        gap: 1rem;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--neutral-600);
        text-transform: uppercase;
        letter-spacing: 0.02em;
        margin-bottom: 0.3rem;
    }

    .date-input-wrapper {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--neutral-400);
    }

    .date-input {
        width: 100%;
        padding: 0.8rem 1rem 0.8rem 2.5rem;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-sm);
        font-size: 0.9rem;
        color: var(--neutral-900);
        transition: var(--transition);
    }

    .date-input:focus {
        outline: none;
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 3px rgba(201, 160, 61, 0.1);
    }

    .btn-filter {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 1.5rem;
        background: var(--primary-deep);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: var(--transition);
        width: 100%;
        justify-content: center;
    }

    .btn-filter:hover {
        background: var(--primary-soft);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(10, 37, 64, 0.15);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-xl);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.05);
        border-color: var(--neutral-400);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        display: block;
        font-size: 0.8rem;
        color: var(--neutral-600);
        text-transform: uppercase;
        letter-spacing: 0.02em;
        margin-bottom: 0.2rem;
    }

    .stat-value {
        display: block;
        font-size: 1.8rem;
        font-weight: 600;
        color: var(--neutral-900);
        line-height: 1.2;
        margin-bottom: 0.2rem;
    }

    .stat-period {
        font-size: 0.8rem;
        color: var(--neutral-600);
    }

    .growth-positive {
        color: var(--success-dark);
    }

    .growth-negative {
        color: var(--danger-dark);
    }

    /* Chart Card */
    .chart-card {
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-xl);
        margin-bottom: 2rem;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
    }

    .card-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1.25rem 1.5rem;
        background: var(--neutral-100);
        border-bottom: 1px solid var(--neutral-300);
    }

    .card-header svg {
        color: var(--primary-soft);
    }

    .card-header h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--primary-deep);
        margin: 0;
    }

    .card-body {
        padding: 1.5rem;
    }

    .chart-container {
        display: flex;
        align-items: flex-end;
        height: 300px;
        gap: 2px;
        padding: 1rem 0.5rem;
        background: var(--neutral-100);
        border-radius: var(--radius-lg);
        position: relative;
    }

    .chart-bar {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        height: 100%;
        justify-content: flex-end;
    }

    .bar {
        width: 100%;
        border-radius: var(--radius-sm) var(--radius-sm) 0 0;
        transition: var(--transition);
        min-height: 4px;
    }

    .bar-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: var(--neutral-900);
        color: white;
        padding: 0.3rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.7rem;
        white-space: nowrap;
        opacity: 0;
        transition: var(--transition);
        pointer-events: none;
        margin-bottom: 0.5rem;
    }

    .chart-bar:hover .bar-tooltip {
        opacity: 1;
    }

    .bar-label {
        font-size: 0.7rem;
        color: var(--neutral-600);
        margin-top: 0.3rem;
    }

    /* Analytics Grid */
    .analytics-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .analytics-card {
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
    }

    .analytics-card.full-width {
        grid-column: 1 / -1;
    }

    /* List Table */
    .list-table {
        width: 100%;
    }

    .list-header {
        display: flex;
        padding: 1rem 1.5rem;
        background: var(--neutral-100);
        border-bottom: 1px solid var(--neutral-300);
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--neutral-600);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .list-row {
        display: flex;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--neutral-200);
        transition: var(--transition);
    }

    .list-row:hover {
        background: var(--neutral-100);
    }

    .list-cell {
        flex: 1;
        padding: 0 0.5rem;
    }

    .list-cell:first-child {
        padding-left: 0;
    }

    .list-cell:last-child {
        padding-right: 0;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .client-info {
        display: flex;
        flex-direction: column;
    }

    .client-info strong {
        color: var(--neutral-900);
        font-weight: 600;
    }

    .client-info small {
        color: var(--neutral-600);
        font-size: 0.8rem;
    }

    .amount {
        font-weight: 600;
        color: var(--accent-teal);
    }

    /* Progress Bar */
    .progress-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .progress {
        flex: 1;
        height: 8px;
        background: var(--neutral-200);
        border-radius: 20px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-deep), var(--primary-soft));
        border-radius: 20px;
        transition: width 0.3s;
    }

    .progress-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--neutral-600);
        min-width: 45px;
        text-align: right;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-icon {
        font-size: 2.5rem;
        color: var(--neutral-400);
        margin-bottom: 0.5rem;
    }

    .text-muted {
        color: var(--neutral-600);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .dashboard-container {
            padding: 1rem;
        }

        .page-title {
            font-size: 1.75rem;
        }

        .filter-grid {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-group {
            min-width: 100%;
        }

        .filter-action label {
            display: none;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .analytics-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .stat-card {
            padding: 1rem;
        }

        .stat-value {
            font-size: 1.5rem;
        }

        .list-header, .list-row {
            padding: 0.75rem 1rem;
        }

        .progress-wrapper {
            flex-direction: column;
            align-items: flex-start;
        }

        .progress-label {
            text-align: left;
        }
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>