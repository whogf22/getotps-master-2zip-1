<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard-container">
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <span class="header-badge">ACTIVATIONS</span>
                <h1 class="page-title">My phone numbers</h1>
                <p class="page-description">Manage your active and past phone number activations</p>
            </div>
            <a href="<?= \Core\Helper::url('/dashboard/buy') ?>" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Buy new number</span>
            </a>
        </div>

        <!-- Activations Card -->
        <div class="activations-card">
            <?php if (!empty($activations)): ?>
                <div class="table-responsive">
                    <table class="activations-table">
                        <thead>
                            <tr>
                                <th>Phone number</th>
                                <th>Service</th>
                                <th>Country</th>
                                <th>Status</th>
                                <th>Time remaining</th>
                                <th>SMS code</th>
                                <th>Created</th>
                                <th>Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activations as $activation): ?>
                            <tr id="activation-<?= $activation['id'] ?>">
                                <td class="phone-number">
                                    <strong><?= htmlspecialchars($activation['phone']) ?></strong>
                                </td>
                                <td class="service-name"><?= htmlspecialchars($activation['service']) ?></td>
                                <td class="country-name"><?= htmlspecialchars(\Core\Helper::getCountryName($activation['country'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $activation['status'] ?>">
                                        <?php if ($activation['status'] === 'completed'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        <?php elseif ($activation['status'] === 'pending'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12 6 12 12 16 14"></polyline>
                                            </svg>
                                        <?php elseif ($activation['status'] === 'expired'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                            </svg>
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                            </svg>
                                        <?php endif; ?>
                                        <?= ucfirst($activation['status']) ?>
                                        <?php if ($activation['status'] === 'expired'): ?>
                                            <span class="refund-note">(refunded)</span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="timer-cell">
                                    <?php if ($activation['status'] === 'pending'): ?>
                                        <?php
                                        $createdTime = strtotime($activation['created_at']);
                                        $expiryTime = $createdTime + (20 * 60); // 20 minutes
                                        $remainingSeconds = $expiryTime - time();
                                        ?>
                                        <div class="countdown-timer" 
                                             data-activation-id="<?= $activation['id'] ?>"
                                             data-expiry="<?= $expiryTime ?>"
                                             data-remaining="<?= max(0, $remainingSeconds) ?>">
                                            <?php if ($remainingSeconds > 0): ?>
                                                <span class="timer-display">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <polyline points="12 6 12 12 16 14"></polyline>
                                                    </svg>
                                                    <span class="timer-text"></span>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: #999;">Expired</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="sms-code-cell">
                                    <?php if ($activation['code']): ?>
                                        <code class="sms-code"><?= htmlspecialchars($activation['code']) ?></code>
                                    <?php elseif ($activation['status'] === 'pending'): ?>
                                        <span class="waiting-indicator">
                                            <span class="dot"></span>
                                            Waiting for SMS
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="created-date">
                                    <span class="date-main"><?= date('M j, Y', strtotime($activation['created_at'])) ?></span>
                                    <span class="date-ago"><?= \Core\Helper::timeAgo($activation['created_at']) ?></span>
                                </td>
                                <td class="cost-cell">
                                    <span class="cost-amount"><?= \Core\Helper::money($activation['cost']) ?></span>
                                </td>
                                <td class="actions-cell">
                                    <a href="<?= \Core\Helper::url('/dashboard/activations/' . $activation['id']) ?>" class="action-btn view-btn" title="View details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    <?php if ($activation['status'] === 'pending'): ?>
                                        <button class="action-btn status-btn" onclick="checkStatus(<?= $activation['id'] ?>)" title="Check for SMS">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12 6 12 12 16 14"></polyline>
                                            </svg>
                                        </button>
                                        <button class="action-btn cancel-btn" onclick="cancelActivation(<?= $activation['id'] ?>)" title="Cancel activation">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Auto-refresh notice -->
                <div class="info-notice">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <div>
                        <strong>Auto‑refresh active:</strong> This page automatically checks for new SMS codes every 10 seconds for pending activations.
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📱</div>
                    <h3>No activations yet</h3>
                    <p>Start by purchasing your first virtual phone number.</p>
                    <a href="<?= \Core\Helper::url('/dashboard/buy') ?>" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Buy your first number</span>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <div class="pagination-wrapper">
                    <div class="pagination">
                        <?php if ($pagination['has_prev']): ?>
                            <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="pagination-link prev">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                                <span>Previous</span>
                            </a>
                        <?php endif; ?>
                        
                        <span class="pagination-info">
                            Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>
                        </span>
                        
                        <?php if ($pagination['has_next']): ?>
                            <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="pagination-link next">
                                <span>Next</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
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
        --neutral-900: #0f172a;
        --success-light: #e3f9ee;
        --success-dark: #0b7e55;
        --warning-light: #fff3d4;
        --warning-dark: #b45b0a;
        --danger-light: #fee9e7;
        --danger-dark: #b91c1c;
        --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: var(--font-sans);
        background: linear-gradient(135deg, var(--neutral-100) 0%, var(--neutral-200) 100%);
        color: var(--neutral-900);
        min-height: 100vh;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 2rem auto;
        padding: 0 2rem;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
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

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--primary-deep);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 60px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(10, 37, 64, 0.1);
    }

    .btn-primary:hover {
        background: var(--primary-soft);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(10, 37, 64, 0.15);
    }

    /* Activations Card */
    .activations-card {
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: 32px;
        box-shadow: 0 20px 35px -15px rgba(10, 37, 64, 0.15);
        overflow: hidden;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .activations-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
    }

    .activations-table thead tr {
        background: var(--neutral-100);
        border-bottom: 1px solid var(--neutral-300);
    }

    .activations-table th {
        text-align: left;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: var(--neutral-600);
    }

    .activations-table td {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--neutral-300);
        color: var(--neutral-900);
    }

    .activations-table tbody tr:hover {
        background: var(--neutral-100);
    }

    .phone-number {
        font-weight: 600;
        color: var(--primary-deep);
    }

    .service-name, .country-name {
        color: var(--neutral-600);
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 60px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .status-completed {
        background: var(--success-light);
        color: var(--success-dark);
    }

    .status-pending {
        background: var(--warning-light);
        color: var(--warning-dark);
    }

    .status-expired {
        background: var(--neutral-200);
        color: var(--neutral-600);
    }

    .refund-note {
        font-size: 0.75rem;
        opacity: 0.8;
        margin-left: 0.25rem;
    }

    /* SMS Code */
    .sms-code-cell {
        font-family: 'Courier New', monospace;
    }

    .sms-code {
        background: var(--neutral-200);
        padding: 0.35rem 0.75rem;
        border-radius: 60px;
        font-size: 1rem;
        font-weight: 600;
        color: var(--accent-teal);
    }

    .waiting-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--neutral-600);
        font-size: 0.9rem;
    }

    .dot {
        width: 8px;
        height: 8px;
        background: var(--warning-dark);
        border-radius: 50%;
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    /* Date column */
    .created-date {
        display: flex;
        flex-direction: column;
    }

    .date-main {
        font-weight: 500;
        color: var(--neutral-900);
    }

    .date-ago {
        font-size: 0.8rem;
        color: var(--neutral-600);
    }

    /* Cost */
    .cost-amount {
        font-weight: 600;
        color: var(--accent-teal);
    }

    /* Action buttons */
    .actions-cell {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border: 1px solid var(--neutral-300);
        border-radius: 8px;
        background: white;
        color: var(--neutral-600);
        cursor: pointer;
        transition: all 0.2s;
    }

    .action-btn:hover {
        border-color: var(--accent-teal);
        color: var(--accent-teal);
        transform: translateY(-2px);
    }

    .status-btn:hover {
        border-color: var(--accent-gold);
        color: var(--accent-gold);
    }

    .cancel-btn:hover {
        border-color: var(--danger-dark);
        color: var(--danger-dark);
    }

    .view-btn {
        text-decoration: none;
    }

    .view-btn:hover {
        border-color: var(--primary-deep);
        color: var(--primary-deep);
    }

    /* Countdown Timer Styles */
    .timer-cell {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .countdown-timer {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        background: rgba(11, 126, 85, 0.08);
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .timer-display {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Info Notice */
    .info-notice {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: #e7f3ff;
        padding: 1rem 1.5rem;
        margin: 1.5rem;
        border-radius: 60px;
        color: var(--primary-soft);
        font-size: 0.95rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        color: var(--primary-deep);
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .empty-state p {
        color: var(--neutral-600);
        margin-bottom: 2rem;
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 1.5rem;
        border-top: 1px solid var(--neutral-300);
    }

    .pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    .pagination-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border: 1px solid var(--neutral-300);
        border-radius: 60px;
        color: var(--neutral-600);
        text-decoration: none;
        transition: all 0.2s;
        font-size: 0.9rem;
    }

    .pagination-link:hover {
        border-color: var(--accent-teal);
        color: var(--accent-teal);
        transform: translateY(-2px);
    }

    .pagination-info {
        font-weight: 500;
        color: var(--neutral-900);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0 1rem;
            margin: 1rem auto;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-primary {
            width: 100%;
            justify-content: center;
        }

        .activations-table td {
            padding: 1rem;
        }

        .actions-cell {
            flex-direction: column;
        }

        .info-notice {
            flex-direction: column;
            text-align: center;
            border-radius: 24px;
        }
    }

    /* Animations */
    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes slideIn {
        from {
            transform: translateX(-20px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes highlight {
        0%, 100% {
            background: transparent;
        }
        50% {
            background: rgba(16, 185, 129, 0.1);
        }
    }

    .sms-code-display {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sms-code-display .code-value {
        font-family: 'Courier New', monospace;
        font-size: 1.1rem;
        font-weight: 700;
        color: #059669;
        background: rgba(16, 185, 129, 0.1);
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
    }

    .sms-code-display .btn-copy {
        background: transparent;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .sms-code-display .btn-copy:hover {
        background: #f3f4f6;
        color: #059669;
    }
</style>

<script>
// Countdown timer functionality
function initializeCountdownTimers() {
    const timers = document.querySelectorAll('.countdown-timer[data-remaining]');
    
    timers.forEach(timer => {
        const expiryTime = parseInt(timer.dataset.expiry);
        const activationId = timer.dataset.activationId;
        const timerText = timer.querySelector('.timer-text');
        
        if (!timerText) return;
        
        const updateTimer = () => {
            const now = Math.floor(Date.now() / 1000);
            const remaining = expiryTime - now;
            
            if (remaining <= 0) {
                timer.innerHTML = '<span style="color: #b91c1c; font-weight: 600;">⌛ Expired</span>';
                // Reload page to update status
                setTimeout(() => location.reload(), 2000);
                return false;
            }
            
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            
            // Color coding based on time remaining
            let color = '#0b7e55'; // Green (more than 10 minutes)
            if (remaining < 300) { // Less than 5 minutes
                color = '#b91c1c'; // Red
            } else if (remaining < 600) { // Less than 10 minutes
                color = '#b45b0a'; // Orange
            }
            
            timer.style.color = color;
            timerText.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            return true;
        };
        
        // Initial update
        if (updateTimer()) {
            // Update every second
            const interval = setInterval(() => {
                if (!updateTimer()) {
                    clearInterval(interval);
                }
            }, 1000);
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeCountdownTimers);

function checkStatus(activationId) {
    const btn = event.currentTarget;
    btn.disabled = true;
    btn.style.opacity = '0.6';
    
    fetch('<?= \Core\Helper::url('/dashboard/checkStatus') ?>?id=' + activationId)
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.style.opacity = '1';
            
            if (data.success) {
                if (data.code && data.code !== 'Waiting Sms') {
                    // Code received, update row dynamically
                    updateActivationRow(activationId, data);
                } else {
                    console.log('Status checked:', data.status);
                }
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.style.opacity = '1';
            console.error('Status check error:', err);
        });
}

function updateActivationRow(activationId, data) {
    const row = document.getElementById('activation-' + activationId);
    if (!row) return;

    // Update status badge
    const statusCell = row.querySelector('td:nth-child(4)');
    if (statusCell && data.code) {
        statusCell.innerHTML = '<span class="status-badge status-completed">Completed</span>';
    }

    // Update code cell
    const codeCell = row.querySelector('td:nth-child(5)');
    if (codeCell && data.code) {
        codeCell.innerHTML = `
            <div class="sms-code-display" style="animation: highlight 0.5s ease;">
                <code class="code-value">${data.code}</code>
                <button class="btn-copy" onclick="copyCode('${data.code}', event)" title="Copy code">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
        `;
    }
}

function copyCode(code, event) {
    event.preventDefault();
    event.stopPropagation();
    navigator.clipboard.writeText(code).then(() => {
        const btn = event.currentTarget;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check"></i>';
        btn.style.color = '#10b981';
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.style.color = '';
        }, 1500);
    });
}

function cancelActivation(activationId) {
    if (!confirm('Cancel this activation? This action cannot be undone.')) return;
    
    const btn = event.currentTarget;
    btn.disabled = true;
    btn.style.opacity = '0.6';
    
    fetch('<?= \Core\Helper::url('/dashboard/cancelActivation') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>',
            id: activationId
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error cancelling activation');
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
        btn.disabled = false;
        btn.style.opacity = '1';
    });
}

// Auto-refresh for pending activations every 5 seconds
<?php 
$hasPending = false;
if (!empty($activations)) {
    foreach ($activations as $a) {
        if ($a['status'] === 'pending') {
            $hasPending = true;
            break;
        }
    }
}
if ($hasPending): 
?>
let checkCount = 0;
const maxChecks = 240; // Stop after 20 minutes (240 * 5 seconds)

// Add auto-refresh indicator
const indicator = document.createElement('div');
indicator.id = 'auto-refresh-indicator';
indicator.innerHTML = `
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="refresh-icon">
        <polyline points="23 4 23 10 17 10"></polyline>
        <polyline points="1 20 1 14 7 14"></polyline>
        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
    </svg>
    <span>Auto-updating...</span>
`;
indicator.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px 16px; border-radius: 50px; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; animation: slideUp 0.3s ease;';
document.body.appendChild(indicator);

const statusChecker = setInterval(() => {
    checkCount++;
    
    if (checkCount >= maxChecks) {
        clearInterval(statusChecker);
        indicator.remove();
        location.reload();
        return;
    }
    
    // Pulse animation on indicator
    const icon = indicator.querySelector('.refresh-icon');
    icon.style.animation = 'spin 0.5s ease';
    setTimeout(() => icon.style.animation = '', 500);
    
    const rows = document.querySelectorAll('tr[id^="activation-"]');
    let hasPending = false;
    
    rows.forEach(row => {
        const badge = row.querySelector('.status-pending');
        if (badge) {
            hasPending = true;
            const activationId = row.id.split('-')[1];
            // Check status silently
            fetch('<?= \Core\Helper::url('/dashboard/checkStatus') ?>?id=' + activationId)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.code && data.code !== 'Waiting Sms') {
                        updateActivationRow(activationId, data);
                    }
                })
                .catch(err => console.error('Auto-check error:', err));
        }
    });
    
    if (!hasPending) {
        clearInterval(statusChecker);
        indicator.remove();
    }
}, 5000); // Check every 5 seconds

console.log('✓ Auto-refresh enabled: Checking for SMS codes every 5 seconds');
<?php endif; ?>
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>