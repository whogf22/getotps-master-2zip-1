<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="transactions-dashboard">
    <div class="transactions-container">
        <!-- Page Header -->
        <div class="page-head">
            <div class="head-left">
                <div class="head-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H5.78a1.65 1.65 0 0 0-1.51 1 1.65 1.65 0 0 0 .33 1.82l.87.87A10 10 0 0 0 9.3 20.4c1.2.44 2.5.6 3.8.6s2.6-.16 3.8-.6a10 10 0 0 0 3.83-2.53l.87-.87z"></path>
                    </svg>
                </div>
                <div>
                    <h1>My transactions</h1>
                    <p>Track your financial activity</p>
                </div>
            </div>
            <div class="head-right">
                <div class="balance-pill">
                    <span class="balance-label">Current balance</span>
                    <span class="balance-value"><?= \Core\Helper::money($_SESSION['user']['balance'] ?? 0) ?></span>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <?php
            $totalCredit = 0;
            $totalDebit = 0;
            $totalPurchases = 0;
            
            if (!empty($transactions)) {
                foreach ($transactions as $t) {
                    if ($t['type'] === 'credit') $totalCredit += $t['amount'];
                    if ($t['type'] === 'debit') $totalDebit += abs($t['amount']);
                    if ($t['type'] === 'purchase') $totalPurchases += abs($t['amount']);
                }
            }
        ?>
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-icon credit">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </div>
                <div class="summary-content">
                    <span class="summary-label">Total credits</span>
                    <span class="summary-value positive"><?= \Core\Helper::money($totalCredit) ?></span>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon purchase">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <div class="summary-content">
                    <span class="summary-label">Total purchases</span>
                    <span class="summary-value negative"><?= \Core\Helper::money($totalPurchases) ?></span>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon balance">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                </div>
                <div class="summary-content">
                    <span class="summary-label">Net activity</span>
                    <span class="summary-value <?= ($totalCredit - $totalDebit) >= 0 ? 'positive' : 'negative' ?>">
                        <?= ($totalCredit - $totalDebit) >= 0 ? '+' : '-' ?><?= \Core\Helper::money(abs($totalCredit - $totalDebit)) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Transactions Card -->
        <div class="transactions-card">
            <div class="card-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <h3>Transaction history</h3>
                <?php if (!empty($transactions)): ?>
                <span class="count-badge"><?= count($transactions) ?> transactions</span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($transactions)): ?>
                    <div class="table-wrapper">
                        <table class="transactions-table">
                            <thead>
                                <tr>
                                    <th>Date & time</th>
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
                    </div>
                <?php else: ?>
                    <div class="empty-transactions">
                        <div class="empty-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                        </div>
                        <h3>No transactions yet</h3>
                        <p>Your financial activity will appear here</p>
                        <a href="<?= \Core\Helper::url('/dashboard') ?>" class="btn-home">Go to dashboard</a>
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
            <span>Credits increase your balance, debits and purchases decrease it. All amounts are in USD.</span>
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
        max-width: 1200px;
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

    .balance-pill {
        background: white;
        padding: 0.75rem 1.5rem;
        border-radius: 60px;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        border: 1px solid var(--neutral-200);
    }

    .balance-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--neutral-500);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .balance-value {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--primary-dark);
    }

    /* Summary Grid */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .summary-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border: 1px solid var(--neutral-200);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -8px rgba(0,0,0,0.1);
        border-color: var(--neutral-300);
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .summary-icon.credit {
        background: var(--success-light);
        color: var(--success-dark);
    }

    .summary-icon.purchase {
        background: var(--danger-light);
        color: var(--danger-dark);
    }

    .summary-icon.balance {
        background: #e6e9ff;
        color: #3730a3;
    }

    .summary-content {
        flex: 1;
    }

    .summary-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--neutral-500);
        text-transform: uppercase;
        letter-spacing: 0.02em;
        margin-bottom: 0.2rem;
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

    /* Transactions Card */
    .transactions-card {
        background: white;
        border-radius: 24px;
        border: 1px solid var(--neutral-200);
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        margin-bottom: 2rem;
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

    .count-badge {
        margin-left: auto;
        background: var(--neutral-200);
        color: var(--neutral-600);
        padding: 0.2rem 0.8rem;
        border-radius: 40px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .card-body {
        padding: 0;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .transactions-table {
        width: 100%;
        border-collapse: collapse;
    }

    .transactions-table th {
        text-align: left;
        padding: 1.2rem 1.5rem;
        background: white;
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
        max-width: 300px;
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
        display: inline-block;
        font-weight: 600;
        color: var(--neutral-800);
        background: var(--neutral-100);
        padding: 0.2rem 0.8rem;
        border-radius: 40px;
        font-size: 0.9rem;
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
        margin-bottom: 1.5rem;
    }

    .btn-home {
        display: inline-block;
        padding: 0.7rem 1.5rem;
        background: var(--primary-dark);
        color: white;
        text-decoration: none;
        border-radius: 40px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-home:hover {
        background: #1a3b5d;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -5px rgba(10,37,64,0.3);
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
        padding: 1rem 1.5rem;
        background: #f0f4ff;
        border-radius: 60px;
        color: #1e40af;
        border: 1px solid #dbeafe;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .transactions-dashboard { padding: 1rem; }
        .summary-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .head-left h1 { font-size: 1.6rem; }
        .summary-grid { grid-template-columns: 1fr; }
        .transactions-table td { padding: 1rem; }
        .pagination { flex-wrap: wrap; }
        .info-note { flex-direction: column; text-align: center; }
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>