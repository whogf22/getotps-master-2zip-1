<!-- Admin Dashboard View -->
<?php
use Core\Helper;
$title = 'Admin Dashboard';
include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-dashboard">
    <div class="dashboard-container">
        <!-- Page Header -->
        <div class="page-head">
            <div class="head-left">
                <div class="head-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                    </svg>
                </div>
                <div>
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
                </div>
            </div>
            <div class="head-right">
                <div class="date-badge">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span><?= date('F j, Y') ?></span>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4361ee, #3a56d4);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Total clients</span>
                    <span class="stat-value"><?= $stats['total_clients'] ?></span>
                    <span class="stat-trend positive">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                        +<?= $stats['new_clients_month'] ?? 0 ?> this month
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Active clients</span>
                    <span class="stat-value"><?= $stats['active_clients'] ?></span>
                    <span class="stat-trend positive">
                        <?= round(($stats['active_clients'] / max($stats['total_clients'], 1)) * 100) ?>% of total
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Total activations</span>
                    <span class="stat-value"><?= $stats['total_activations'] ?></span>
                    <span class="stat-trend">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <?= $stats['pending_activations'] ?> pending
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H5.78a1.65 1.65 0 0 0-1.51 1 1.65 1.65 0 0 0 .33 1.82l.87.87A10 10 0 0 0 9.3 20.4c1.2.44 2.5.6 3.8.6s2.6-.16 3.8-.6a10 10 0 0 0 3.83-2.53l.87-.87z"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Total balance</span>
                    <span class="stat-value"><?= Helper::money($stats['total_balance']) ?></span>
                    <span class="stat-trend positive">Across all clients</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Today's revenue</span>
                    <span class="stat-value"><?= Helper::money($stats['today_revenue']) ?></span>
                    <span class="stat-trend positive">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                        +<?= Helper::money($stats['today_revenue'] - $stats['yesterday_revenue']) ?> vs yesterday
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #64748b, #475569);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Avg. per client</span>
                    <span class="stat-value"><?= Helper::money($stats['total_balance'] / max($stats['total_clients'], 1)) ?></span>
                    <span class="stat-trend">Average balance</span>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    <h3>Revenue (last 30 days)</h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables Grid -->
        <div class="tables-grid">
            <!-- Recent Clients -->
            <div class="table-card">
                <div class="card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <h3>Recent clients</h3>
                    <a href="<?= \Core\Helper::url('/admin/clients') ?>" class="card-link">
                        <span>View all</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_clients as $client): ?>
                                <tr>
                                    <td>
                                        <div class="client-info">
                                            <div class="client-avatar">
                                                <?= strtoupper(substr($client['name'], 0, 1)) ?>
                                            </div>
                                            <span><?= htmlspecialchars($client['name']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($client['email']) ?></td>
                                    <td>
                                        <span class="balance-badge"><?= Helper::money($client['balance']) ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $client['status'] ?>">
                                            <?= ucfirst($client['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?= Helper::date($client['created_at'], 'M d, Y') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recent_clients)): ?>
                                <tr>
                                    <td colspan="5" class="text-center empty-message">No clients yet</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="table-card">
                <div class="card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H5.78a1.65 1.65 0 0 0-1.51 1 1.65 1.65 0 0 0 .33 1.82l.87.87A10 10 0 0 0 9.3 20.4c1.2.44 2.5.6 3.8.6s2.6-.16 3.8-.6a10 10 0 0 0 3.83-2.53l.87-.87z"></path>
                    </svg>
                    <h3>Recent transactions</h3>
                    <a href="<?= \Core\Helper::url('/admin/transactions') ?>" class="card-link">
                        <span>View all</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_transactions as $trans): ?>
                                <tr>
                                    <td>
                                        <div class="client-info">
                                            <div class="client-avatar small">
                                                <?= strtoupper(substr($trans['user_name'], 0, 1)) ?>
                                            </div>
                                            <span><?= htmlspecialchars($trans['user_name']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="type-badge type-<?= $trans['type'] ?>">
                                            <?= ucfirst($trans['type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="amount <?= in_array($trans['type'], ['credit', 'refund']) ? 'positive' : 'negative' ?>">
                                            <?= in_array($trans['type'], ['credit', 'refund']) ? '+' : '-' ?><?= Helper::money(abs($trans['amount'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?= Helper::timeAgo($trans['created_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recent_transactions)): ?>
                                <tr>
                                    <td colspan="4" class="text-center empty-message">No transactions yet</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="<?= \Core\Helper::url('/admin/clients') ?>" class="action-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Manage clients</span>
            </a>
            <a href="<?= \Core\Helper::url('/admin/reports') ?>" class="action-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
                <span>View reports</span>
            </a>
            <a href="<?= \Core\Helper::url('/admin/settings') ?>" class="action-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H5.78a1.65 1.65 0 0 0-1.51 1 1.65 1.65 0 0 0 .33 1.82l.87.87A10 10 0 0 0 9.3 20.4c1.2.44 2.5.6 3.8.6s2.6-.16 3.8-.6a10 10 0 0 0 3.83-2.53l.87-.87z"></path>
                </svg>
                <span>Settings</span>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

    .admin-dashboard {
        padding: 2rem;
        min-height: 100vh;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Page Header */
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

    .date-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1.2rem;
        background: white;
        border-radius: 40px;
        color: var(--neutral-600);
        font-weight: 500;
        border: 1px solid var(--neutral-200);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.2rem;
        border: 1px solid var(--neutral-200);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        transition: all 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -8px rgba(0,0,0,0.1);
        border-color: var(--neutral-300);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 18px;
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
        font-weight: 600;
        color: var(--neutral-500);
        text-transform: uppercase;
        letter-spacing: 0.02em;
        margin-bottom: 0.2rem;
    }

    .stat-value {
        display: block;
        font-size: 2rem;
        font-weight: 700;
        color: var(--neutral-800);
        line-height: 1.2;
        margin-bottom: 0.3rem;
    }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        font-size: 0.75rem;
        color: var(--neutral-500);
        background: var(--neutral-100);
        padding: 0.2rem 0.6rem;
        border-radius: 40px;
    }

    .stat-trend.positive {
        color: var(--success-dark);
        background: var(--success-light);
    }

    /* Charts Row */
    .charts-row {
        margin-bottom: 2rem;
    }

    .chart-card {
        background: white;
        border-radius: 20px;
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

    /* Tables Grid */
    .tables-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .table-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--neutral-200);
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .card-link {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.4rem 1rem;
        background: var(--neutral-100);
        border-radius: 40px;
        color: var(--neutral-600);
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.2s;
        margin-left: auto;
    }

    .card-link:hover {
        background: var(--neutral-200);
        color: var(--neutral-800);
    }

    .table-responsive {
        overflow-x: auto;
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modern-table th {
        text-align: left;
        padding: 1rem 1.5rem;
        background: var(--neutral-50);
        font-weight: 600;
        font-size: 0.75rem;
        color: var(--neutral-500);
        text-transform: uppercase;
        letter-spacing: 0.02em;
        border-bottom: 1px solid var(--neutral-200);
    }

    .modern-table td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--neutral-200);
        color: var(--neutral-700);
        font-size: 0.9rem;
    }

    .modern-table tr:hover td {
        background: var(--neutral-50);
    }

    .client-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .client-avatar {
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
    }

    .client-avatar.small {
        width: 28px;
        height: 28px;
        font-size: 0.8rem;
    }

    .balance-badge {
        display: inline-block;
        background: var(--neutral-100);
        padding: 0.2rem 0.8rem;
        border-radius: 40px;
        font-weight: 500;
        color: var(--accent-teal);
    }

    .status-badge {
        display: inline-block;
        padding: 0.2rem 0.8rem;
        border-radius: 40px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-active {
        background: var(--success-light);
        color: var(--success-dark);
    }

    .status-inactive {
        background: var(--neutral-200);
        color: var(--neutral-600);
    }

    .type-badge {
        display: inline-block;
        padding: 0.2rem 0.8rem;
        border-radius: 40px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .type-credit, .type-refund {
        background: var(--success-light);
        color: var(--success-dark);
    }

    .type-debit, .type-purchase {
        background: var(--danger-light);
        color: var(--danger-dark);
    }

    .amount {
        font-weight: 600;
    }

    .amount.positive {
        color: var(--success-dark);
    }

    .amount.negative {
        color: var(--danger-dark);
    }

    .text-muted {
        color: var(--neutral-500);
    }

    .empty-message {
        padding: 2rem !important;
        color: var(--neutral-400);
    }

    /* Quick Actions */
    .quick-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.2rem;
        background: white;
        border: 1px solid var(--neutral-200);
        border-radius: 40px;
        color: var(--neutral-600);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .action-btn:hover {
        background: var(--neutral-100);
        border-color: var(--neutral-300);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .admin-dashboard { padding: 1rem; }
        .tables-grid { grid-template-columns: 1fr; }
        .quick-actions { justify-content: center; }
    }

    @media (max-width: 768px) {
        .head-left h1 { font-size: 1.6rem; }
        .stats-grid { grid-template-columns: 1fr; }
        .quick-actions { flex-wrap: wrap; }
        .action-btn { width: 100%; justify-content: center; }
    }
</style>

<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) ?>,
        datasets: [{
            label: 'Revenue',
            data: <?= json_encode($chart_data ?? [65, 59, 80, 81, 56, 55, 40]) ?>,
            borderColor: '#4361ee',
            backgroundColor: 'rgba(67, 97, 238, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#4361ee',
            pointBorderColor: 'white',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: { backgroundColor: 'white', titleColor: '#1e293b', bodyColor: '#475569', borderColor: '#e2e8f0', borderWidth: 1 }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: '#e2e8f0' } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>