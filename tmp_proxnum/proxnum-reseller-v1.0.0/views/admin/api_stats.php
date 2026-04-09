<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="api-stats-dashboard">
    <div class="api-stats-container">
        <!-- Page Header -->
        <div class="page-head">
            <div class="head-left">
                <div class="head-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                        <line x1="12" y1="2" x2="12" y2="22"></line>
                    </svg>
                </div>
                <div>
                    <h1>API statistics</h1>
                    <p>Monitor API usage and performance</p>
                </div>
            </div>
            <div class="head-right">
                <div class="period-indicator">
                    <span class="dot"></span>
                    <span><?= date('M d, Y', strtotime($start_date)) ?> - <?= date('M d, Y', strtotime($end_date)) ?></span>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
            <form method="GET" class="filter-form">
                <input type="hidden" name="route" value="admin/apiStats">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="start_date">Start date</label>
                        <div class="date-wrapper">
                            <svg class="date-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="date-input">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="end_date">End date</label>
                        <div class="date-wrapper">
                            <svg class="date-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="date-input">
                        </div>
                    </div>
                    <div class="filter-group filter-action">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn-filter">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 13 10 21 14 18 14 13 22 3"></polygon>
                            </svg>
                            <span>Apply filter</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Two Column Layout -->
        <div class="stats-grid">
            <!-- Status Distribution -->
            <div class="stats-card">
                <div class="card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <h3>Activation status</h3>
                </div>
                <div class="card-body">
                    <?php 
                        $total = array_sum(array_column($status_stats, 'count'));
                    ?>
                    <?php if (!empty($status_stats)): ?>
                        <div class="status-list">
                            <?php foreach ($status_stats as $stat): 
                                $percentage = $total > 0 ? ($stat['count'] / $total) * 100 : 0;
                                $statusClass = '';
                                if ($stat['status'] === 'completed') $statusClass = 'success';
                                elseif ($stat['status'] === 'pending') $statusClass = 'warning';
                                else $statusClass = 'danger';
                            ?>
                            <div class="status-item">
                                <div class="status-left">
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= ucfirst($stat['status']) ?>
                                    </span>
                                </div>
                                <div class="status-right">
                                    <span class="status-count"><?= $stat['count'] ?></span>
                                    <div class="progress-wrapper">
                                        <div class="progress">
                                            <div class="progress-bar <?= $statusClass ?>" style="width: <?= $percentage ?>%"></div>
                                        </div>
                                        <span class="progress-label"><?= number_format($percentage, 1) ?>%</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state small">
                            <p>No status data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- API Errors -->
            <div class="stats-card">
                <div class="card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <h3>API errors</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="errors-list">
                            <?php foreach ($errors as $error): ?>
                            <div class="error-item">
                                <span class="error-type"><?= htmlspecialchars($error['action']) ?></span>
                                <span class="error-count"><?= $error['count'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">✓</div>
                            <p>No errors recorded</p>
                            <small>Selected period is error-free</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- API Calls by Service/Country -->
        <div class="calls-card">
            <div class="card-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <h3>API calls by service & country</h3>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($api_calls)): ?>
                    <div class="table-wrapper">
                        <table class="calls-table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Country</th>
                                    <th>Total calls</th>
                                    <th>Total cost</th>
                                    <th>Avg cost/call</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($api_calls as $call): 
                                    $avgCost = $call['calls'] > 0 ? $call['total_cost'] / $call['calls'] : 0;
                                ?>
                                <tr>
                                    <td>
                                        <span class="service-name"><?= htmlspecialchars($call['service']) ?></span>
                                    </td>
                                    <td>
                                        <span class="country-name"><?= htmlspecialchars($call['country']) ?></span>
                                    </td>
                                    <td>
                                        <span class="calls-count"><?= $call['calls'] ?></span>
                                    </td>
                                    <td>
                                        <span class="cost-amount"><?= \Core\Helper::money($call['total_cost']) ?></span>
                                    </td>
                                    <td>
                                        <span class="avg-cost"><?= \Core\Helper::money($avgCost) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon"></div>
                        <p>No API calls in selected period</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Note -->
        <div class="info-note">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <div>
                <strong>About API statistics</strong>
                <p>These statistics show your system's usage of the Proxnum API. Monitoring helps identify popular services and optimize pricing. High error rates may indicate configuration issues.</p>
            </div>
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
        --success-light: #e3f9ee;
        --success-dark: #0b7e55;
        --warning: #f59e0b;
        --warning-light: #fff3d4;
        --warning-dark: #b45b0a;
        --danger: #ef4444;
        --danger-light: #fee9e7;
        --danger-dark: #b91c1c;
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

    .api-stats-dashboard {
        padding: 2rem;
        min-height: 100vh;
    }

    .api-stats-container {
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

    .period-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        padding: 0.5rem 1.2rem;
        border-radius: 40px;
        font-weight: 500;
        color: var(--neutral-700);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .dot {
        width: 8px;
        height: 8px;
        background: var(--primary-light);
        border-radius: 50%;
    }

    /* Filter Card */
    .filter-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid var(--neutral-200);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
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
        font-weight: 600;
        color: var(--neutral-600);
        text-transform: uppercase;
        letter-spacing: 0.02em;
        margin-bottom: 0.3rem;
    }

    .date-wrapper {
        position: relative;
    }

    .date-icon {
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
        border-radius: 40px;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .date-input:focus {
        outline: none;
        border-color: var(--primary-light);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }

    .btn-filter {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 2rem;
        background: var(--primary-dark);
        color: white;
        border: none;
        border-radius: 40px;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
        justify-content: center;
    }

    .btn-filter:hover {
        background: #1a3b5d;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -5px rgba(10,37,64,0.3);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .stats-card {
        background: white;
        border-radius: 24px;
        border: 1px solid var(--neutral-200);
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .card-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1.2rem 1.5rem;
        background: var(--neutral-50);
        border-bottom: 1px solid var(--neutral-200);
    }

    .card-header svg {
        color: var(--primary-light);
    }

    .card-header h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--neutral-800);
        margin: 0;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Status List */
    .status-list {
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
    }

    .status-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .status-left {
        min-width: 100px;
    }

    .status-badge {
        display: inline-block;
        padding: 0.3rem 1rem;
        border-radius: 40px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-badge.success {
        background: var(--success-light);
        color: var(--success-dark);
    }

    .status-badge.warning {
        background: var(--warning-light);
        color: var(--warning-dark);
    }

    .status-badge.danger {
        background: var(--danger-light);
        color: var(--danger-dark);
    }

    .status-right {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 200px;
    }

    .status-count {
        font-weight: 600;
        color: var(--neutral-800);
        min-width: 40px;
    }

    .progress-wrapper {
        flex: 1;
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
        transition: width 0.3s;
    }

    .progress-bar.success {
        background: var(--success-dark);
    }

    .progress-bar.warning {
        background: var(--warning-dark);
    }

    .progress-bar.danger {
        background: var(--danger-dark);
    }

    .progress-label {
        font-size: 0.8rem;
        color: var(--neutral-600);
        min-width: 45px;
    }

    /* Errors List */
    .errors-list {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }

    .error-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.8rem 1rem;
        background: var(--danger-light);
        border-radius: 40px;
        border: 1px solid rgba(185, 28, 28, 0.1);
    }

    .error-type {
        font-weight: 500;
        color: var(--danger-dark);
    }

    .error-count {
        background: white;
        padding: 0.2rem 0.8rem;
        border-radius: 40px;
        font-weight: 600;
        color: var(--danger-dark);
    }

    /* Calls Card */
    .calls-card {
        background: white;
        border-radius: 24px;
        border: 1px solid var(--neutral-200);
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .calls-table {
        width: 100%;
        border-collapse: collapse;
    }

    .calls-table th {
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

    .calls-table td {
        padding: 1.2rem 1.5rem;
        border-bottom: 1px solid var(--neutral-200);
        color: var(--neutral-700);
    }

    .calls-table tr:hover td {
        background: var(--neutral-50);
    }

    .service-name {
        font-weight: 600;
        color: var(--neutral-800);
    }

    .country-name {
        color: var(--neutral-600);
    }

    .calls-count {
        font-weight: 600;
        color: var(--primary-dark);
    }

    .cost-amount {
        font-weight: 600;
        color: var(--accent-teal);
    }

    .avg-cost {
        font-weight: 500;
        color: var(--neutral-600);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2rem 1rem;
        color: var(--neutral-500);
    }

    .empty-state.small {
        padding: 1rem;
    }

    .empty-icon {
        font-size: 2.5rem;
        color: var(--neutral-400);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        margin-bottom: 0.2rem;
    }

    .empty-state small {
        font-size: 0.8rem;
        opacity: 0.8;
    }

    /* Info Note */
    .info-note {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.2rem 1.5rem;
        background: #f0f4ff;
        border-radius: 20px;
        color: #1e40af;
        border: 1px solid #dbeafe;
    }

    .info-note strong {
        display: block;
        margin-bottom: 0.2rem;
    }

    .info-note p {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.9;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .api-stats-dashboard { padding: 1rem; }
        .stats-grid { grid-template-columns: 1fr; }
        .filter-grid { flex-direction: column; }
        .filter-group { width: 100%; }
    }

    @media (max-width: 768px) {
        .head-left h1 { font-size: 1.6rem; }
        .status-item { flex-direction: column; align-items: flex-start; }
        .status-right { width: 100%; }
        .calls-table td { padding: 1rem; }
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>