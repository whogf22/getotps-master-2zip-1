<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="transactions-dashboard">
    <div class="transactions-container">
        <!-- Page Header -->
        <div class="transactions-header">
            <div class="header-left">
                <div class="header-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H5.78a1.65 1.65 0 0 0-1.51 1 1.65 1.65 0 0 0 .33 1.82l.87.87A10 10 0 0 0 9.3 20.4c1.2.44 2.5.6 3.8.6s2.6-.16 3.8-.6a10 10 0 0 0 3.83-2.53l.87-.87z"></path>
                    </svg>
                </div>
                <div>
                    <h1>All transactions</h1>
                    <p>Complete financial history</p>
                </div>
            </div>
            <div class="header-stats">
                <div class="stat-pill">
                    <span class="stat-dot"></span>
                    <span><?= $total_transactions ?? count($transactions) ?> transactions</span>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="transactions-card">
            <!-- Summary Row -->
            <?php 
                $totalCredits = 0;
                $totalDebits = 0;
                if (!empty($transactions)) {
                    foreach ($transactions as $t) {
                        if (in_array($t['type'], ['credit', 'refund'])) {
                            $totalCredits += abs($t['amount']);
                        } else {
                            $totalDebits += abs($t['amount']);
                        }
                    }
                }
            ?>
            <?php if (!empty($transactions)): ?>
            <div class="summary-bar">
                <div class="summary-item">
                    <span class="summary-label">Total credits</span>
                    <span class="summary-value positive">+<?= \Core\Helper::money($totalCredits) ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total debits</span>
                    <span class="summary-value negative">-<?= \Core\Helper::money($totalDebits) ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Net flow</span>
                    <span class="summary-value <?= ($totalCredits - $totalDebits) >= 0 ? 'positive' : 'negative' ?>">
                        <?= ($totalCredits - $totalDebits) >= 0 ? '+' : '-' ?><?= \Core\Helper::money(abs($totalCredits - $totalDebits)) ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Transactions Table -->
            <div class="table-wrapper">
                <?php if (!empty($transactions)): ?>
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>Date & time</th>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Balance after</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td class="transaction-time">
                                    <div class="time-main"><?= \Core\Helper::date($transaction['created_at'], 'M d, Y') ?></div>
                                    <div class="time-sub"><?= \Core\Helper::date($transaction['created_at'], 'H:i:s') ?></div>
                                </td>
                                <td>
                                    <div class="client-info">
                                        <div class="client-avatar">
                                            <?= strtoupper(substr($transaction['user_name'], 0, 1)) ?>
                                        </div>
                                        <div class="client-details">
                                            <span class="client-name"><?= htmlspecialchars($transaction['user_name']) ?></span>
                                            <span class="client-email"><?= htmlspecialchars($transaction['user_email']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                        $type = strtolower($transaction['type']);
                                        $badgeClass = 'badge-info';
                                        if ($type === 'credit' || $type === 'refund') {
                                            $badgeClass = 'badge-credit';
                                        } elseif ($type === 'debit' || $type === 'purchase') {
                                            $badgeClass = 'badge-debit';
                                        }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= ucfirst($transaction['type']) ?>
                                    </span>
                                </td>
                                <td class="transaction-desc"><?= htmlspecialchars($transaction['description']) ?></td>
                                <td class="transaction-amount">
                                    <span class="amount <?= in_array($transaction['type'], ['credit', 'refund']) ? 'positive' : 'negative' ?>">
                                        <?= in_array($transaction['type'], ['credit', 'refund']) ? '+' : '-' ?>
                                        <?= \Core\Helper::money(abs($transaction['amount'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="balance"><?= \Core\Helper::money($transaction['balance_after']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-transactions">
                        <div class="empty-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                        </div>
                        <h3>No transactions found</h3>
                        <p>There are no financial records to display</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination">
                    <?php if ($pagination['has_prev']): ?>
                        <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="page-btn prev">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            <span>Previous</span>
                        </a>
                    <?php endif; ?>
                    
                    <div class="page-indicator">
                        <span class="current-page"><?= $pagination['current_page'] ?></span>
                        <span class="total-pages">of <?= $pagination['total_pages'] ?></span>
                    </div>
                    
                    <?php if ($pagination['has_next']): ?>
                        <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="page-btn next">
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
            <span>All amounts are in USD. Credits increase balance, debits decrease balance.</span>
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

    .transactions-dashboard {
        padding: 2rem;
        min-height: 100vh;
    }

    .transactions-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Header */
    .transactions-header {
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
    }

    /* Main Card */
    .transactions-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05);
        border: 1px solid rgba(203, 213, 225, 0.4);
        overflow: hidden;
    }

    /* Summary Bar */
    .summary-bar {
        display: flex;
        align-items: center;
        justify-content: space-around;
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, var(--neutral-50), white);
        border-bottom: 1px solid var(--neutral-200);
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .summary-item {
        text-align: center;
    }

    .summary-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--neutral-500);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.3rem;
    }

    .summary-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .summary-value.positive {
        color: var(--success-dark);
    }

    .summary-value.negative {
        color: var(--danger-dark);
    }

    /* Table Wrapper */
    .table-wrapper {
        overflow-x: auto;
    }

    .transactions-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
    }

    .transactions-table th {
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

    .transactions-table td {
        padding: 1.2rem 1.5rem;
        border-bottom: 1px solid var(--neutral-200);
        color: var(--neutral-700);
    }

    .transactions-table tr:hover td {
        background: var(--neutral-50);
    }

    .transaction-time {
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

    /* Client Info */
    .client-info {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .client-avatar {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, var(--primary-light), var(--primary-dark));
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .client-details {
        display: flex;
        flex-direction: column;
    }

    .client-name {
        font-weight: 600;
        color: var(--neutral-800);
    }

    .client-email {
        font-size: 0.8rem;
        color: var(--neutral-500);
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

    .badge-credit {
        background: var(--success-light);
        color: var(--success-dark);
    }

    .badge-debit {
        background: var(--danger-light);
        color: var(--danger-dark);
    }

    .badge-info {
        background: #e6e9ff;
        color: #3730a3;
    }

    .transaction-desc {
        max-width: 250px;
        word-break: break-word;
    }

    .transaction-amount {
        white-space: nowrap;
    }

    .amount {
        font-weight: 700;
        font-size: 1rem;
    }

    .amount.positive {
        color: var(--success-dark);
    }

    .amount.negative {
        color: var(--danger-dark);
    }

    .balance {
        font-weight: 600;
        color: var(--neutral-800);
        background: var(--neutral-100);
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    /* Empty State */
    .empty-transactions {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-icon {
        color: var(--neutral-400);
        margin-bottom: 1.5rem;
        opacity: 0.5;
    }

    .empty-transactions h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--neutral-700);
        margin-bottom: 0.3rem;
    }

    .empty-transactions p {
        color: var(--neutral-500);
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
        .transactions-dashboard { padding: 1rem; }
        .summary-bar { flex-direction: column; gap: 1rem; }
    }

    @media (max-width: 768px) {
        .header-left h1 { font-size: 1.6rem; }
        .transactions-table td { padding: 1rem; }
        .client-info { flex-wrap: wrap; }
        .pagination { flex-wrap: wrap; }
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>