<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard-container">
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <span class="header-badge">NOTIFICATIONS</span>
                <h1 class="page-title">Notifications & alerts</h1>
                <p class="page-description">Stay updated with important account alerts and recent activity</p>
            </div>
            <?php if (!empty($activities)): ?>
            <button class="btn-danger" onclick="clearAllNotifications()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                <span>Clear all</span>
            </button>
            <?php endif; ?>
        </div>

        <!-- Balance Warning Alert -->
        <?php if ($low_balance): ?>
        <div class="alert alert-warning balance-alert">
            <div class="alert-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="alert-content">
                <strong>Low balance warning</strong>
                <p>Your balance is running low. Please add funds to continue using services without interruption.</p>
            </div>
            <a href="<?= \Core\Helper::url('/dashboard/wallet') ?>" class="btn-primary btn-small">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Add funds</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- Expiring Rentals -->
        <?php if (!empty($expiring_rentals)): ?>
        <div class="alert alert-info rentals-alert">
            <div class="alert-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="alert-content">
                <strong>Expiring rentals</strong>
                <p>You have <?= count($expiring_rentals) ?> rental(s) expiring within 24 hours:</p>
                <div class="rental-list">
                    <?php foreach ($expiring_rentals as $rental): ?>
                    <div class="rental-item">
                        <span class="rental-phone"><?= htmlspecialchars($rental['phone']) ?></span>
                        <span class="rental-service"><?= htmlspecialchars($rental['service']) ?></span>
                        <span class="rental-expiry">Expires <?= \Core\Helper::timeAgo($rental['expires_at']) ?></span>
                        <a href="<?= \Core\Helper::url('/dashboard/rentals/' . $rental['id']) ?>" class="rental-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Activity Card -->
        <div class="activity-card">
            <div class="card-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <h2>Recent activity</h2>
                <span class="activity-count"><?= count($activities) ?> <?= count($activities) === 1 ? 'notification' : 'notifications' ?></span>
            </div>
            <div class="card-body">
                <?php if (!empty($activities)): ?>
                <div class="activity-list">
                    <?php foreach ($activities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <?php
                                $icon = '•';
                                if (strpos($activity['action'], 'login') !== false) $icon = '';
                                elseif (strpos($activity['action'], 'purchase') !== false) $icon = '🛒';
                                elseif (strpos($activity['action'], 'activation') !== false) $icon = '📱';
                                elseif (strpos($activity['action'], 'rental') !== false) $icon = '📞';
                                elseif (strpos($activity['action'], 'profile') !== false) $icon = '👤';
                                elseif (strpos($activity['action'], 'password') !== false) $icon = '🔑';
                                elseif (strpos($activity['action'], 'deposit') !== false) $icon = '';
                                elseif (strpos($activity['action'], 'withdraw') !== false) $icon = '💸';
                                else $icon = '📋';
                            ?>
                            <span class="activity-emoji"><?= $icon ?></span>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title"><?= htmlspecialchars($activity['description']) ?></div>
                            <div class="activity-meta">
                                <span class="meta-time">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <?= \Core\Helper::timeAgo($activity['created_at']) ?>
                                </span>
                                <span class="meta-ip">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="2" y1="12" x2="22" y2="12"></line>
                                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                    </svg>
                                    IP: <?= htmlspecialchars($activity['ip_address']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <h3>No recent activity</h3>
                    <p>Your recent actions will appear here</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <div style="margin-top: 20px; text-align: center; padding: 20px;">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="?page=<?= $i ?>" class="btn" style="<?= $i === $pagination['current_page'] ? 'opacity: 0.6;' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <!-- Info Box -->
        <div class="info-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <div>
                <strong>About notifications</strong>
                <p class="small">This page shows important alerts and your recent account activity. Check regularly to stay updated on your account status and service usage.</p>
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
        --info-light: #dbeafe;
        --info-dark: #1e40af;
        --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        
        --radius-lg: 24px;
        --radius-md: 16px;
        --radius-sm: 8px;
    }

    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 2rem;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2rem;
    }
    
    .header-content {
        flex: 1;
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

    /* Alerts */
    .alert {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 1.5rem;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }

    .alert-warning {
        background: var(--warning-light);
        color: var(--warning-dark);
        border-color: rgba(180, 91, 10, 0.15);
    }

    .alert-info {
        background: var(--info-light);
        color: var(--info-dark);
        border-color: rgba(30, 64, 175, 0.15);
    }

    .alert-icon {
        flex-shrink: 0;
    }

    .alert-content {
        flex: 1;
    }

    .alert-content strong {
        display: block;
        font-size: 1rem;
        margin-bottom: 0.3rem;
    }

    .alert-content p {
        margin: 0;
        font-size: 0.95rem;
        opacity: 0.9;
    }

    .btn-small {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.5rem 1rem;
        background: white;
        color: var(--warning-dark);
        border: 1px solid rgba(180, 91, 10, 0.2);
        border-radius: 60px;
        font-weight: 500;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .btn-small:hover {
        background: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-color: var(--warning-dark);
    }

    /* Clear All Button */
    .btn-danger {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.5rem;
        background: linear-gradient(135deg, #44a2ef 0%, #26dcbe 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:active {
        transform: translateY(0);
    }

    .btn-danger svg {
        flex-shrink: 0;
    }

    /* Rental List */
    .rental-list {
        margin-top: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .rental-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        background: rgba(255,255,255,0.5);
        border-radius: var(--radius-sm);
        flex-wrap: wrap;
    }

    .rental-phone {
        font-weight: 600;
        font-family: 'Courier New', monospace;
        background: white;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        border: 1px solid rgba(30, 64, 175, 0.1);
    }

    .rental-service {
        color: var(--info-dark);
        font-size: 0.9rem;
    }

    .rental-expiry {
        font-size: 0.85rem;
        color: var(--neutral-600);
        margin-left: auto;
    }

    .rental-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 4px;
        color: var(--info-dark);
        text-decoration: none;
        transition: all 0.2s;
    }

    .rental-link:hover {
        background: white;
        transform: translateX(2px);
    }

    /* Activity Card */
    .activity-card {
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        margin-bottom: 1.5rem;
    }

    .card-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1.25rem 1.5rem;
        background: var(--neutral-100);
        border-bottom: 1px solid var(--neutral-300);
    }

    .card-header svg {
        color: var(--primary-soft);
    }

    .card-header h2 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary-deep);
        margin: 0;
        flex: 1;
    }
    
    .activity-count {
        font-size: 0.875rem;
        color: var(--neutral-600);
        background: var(--neutral-200);
        padding: 0.375rem 0.875rem;
        border-radius: 30px;
        font-weight: 600;
    }

    .card-body {
        padding: 0;
    }

    /* Activity List */
    .activity-list {
        max-height: 600px;
        overflow-y: auto;
    }

    .activity-item {
        display: flex;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--neutral-200);
        transition: background 0.2s;
    }

    .activity-item:hover {
        background: var(--neutral-100);
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        flex-shrink: 0;
    }

    .activity-emoji {
        font-size: 1.5rem;
        line-height: 1;
    }

    .activity-content {
        flex: 1;
    }

    .activity-title {
        font-weight: 500;
        color: var(--neutral-800);
        margin-bottom: 0.3rem;
    }

    .activity-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: 0.8rem;
        color: var(--neutral-600);
    }

    .meta-time, .meta-ip {
        display: flex;
        align-items: center;
        gap: 0.2rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: var(--neutral-600);
    }

    .empty-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 1.1rem;
        font-weight: 500;
        color: var(--neutral-800);
        margin-bottom: 0.3rem;
    }

    .empty-state p {
        font-size: 0.9rem;
        color: var(--neutral-600);
    }

    /* Info Box */
    .info-box {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: var(--info-light);
        border: 1px solid rgba(30, 64, 175, 0.1);
        border-radius: var(--radius-lg);
        color: var(--info-dark);
        margin-top: 1.5rem;
    }

    .info-box strong {
        display: block;
        margin-bottom: 0.2rem;
    }

    .info-box .small {
        font-size: 0.9rem;
        opacity: 0.9;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            gap: 1rem;
        }

        .btn-danger {
            width: 100%;
            justify-content: center;
        }

        .page-title {
            font-size: 1.75rem;
        }

        .alert {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-small {
            width: 100%;
            justify-content: center;
            margin-top: 0.5rem;
        }

        .rental-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .rental-expiry {
            margin-left: 0;
        }

        .activity-item {
            padding: 1rem;
        }

        .activity-meta {
            flex-direction: column;
            gap: 0.3rem;
        }

        .info-box {
            flex-direction: column;
        }
    }
</style>

<script>
function clearAllNotifications() {
    if (!confirm('Are you sure you want to clear all notifications? This action cannot be undone.')) {
        return;
    }
    
    const csrfToken = '<?= $csrf_token ?? '' ?>';
    
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'clear_all',
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to show empty notifications
            window.location.reload();
        } else {
            alert(data.message || 'Failed to clear notifications');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while clearing notifications');
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>