<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard-container">
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <span class="header-badge">PROFILE</span>
                <h1 class="page-title">Account settings</h1>
                <p class="page-description">Manage your profile information and security settings</p>
            </div>
        </div>

        <?php if (isset($message)): ?>
        <div class="alert alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Two Column Layout -->
        <div class="profile-grid">
            <!-- Profile Information -->
            <div class="profile-card">
                <div class="card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <h2>Profile information</h2>
                </div>
                <div class="card-body">
                    <?php if (\Core\Helper::isDemo()): ?>
                    <div class="alert alert-warning" style="margin-bottom: 1.5rem; padding: 1rem; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; display: flex; align-items: center; gap: 0.75rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <div>
                            <strong style="color: #856404;">Demo Account</strong><br>
                            <span style="color: #856404; font-size: 0.9rem;">Profile modifications are disabled in demo mode.</span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label for="name">Full name</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required <?= \Core\Helper::isDemo() ? 'disabled' : '' ?>>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required <?= \Core\Helper::isDemo() ? 'disabled' : '' ?>>
                        </div>
                        
                        <button type="submit" class="btn-primary" <?= \Core\Helper::isDemo() ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            <span>Update profile</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="profile-card">
                <div class="card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <h2>Change password</h2>
                </div>
                <div class="card-body">
                    <?php if (\Core\Helper::isDemo()): ?>
                    <div class="alert alert-warning" style="margin-bottom: 1.5rem; padding: 1rem; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; display: flex; align-items: center; gap: 0.75rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <div>
                            <strong style="color: #856404;">Demo Account</strong><br>
                            <span style="color: #856404; font-size: 0.9rem;">Password changes are disabled in demo mode.</span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="current_password">Current password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required <?= \Core\Helper::isDemo() ? 'disabled' : '' ?>>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" minlength="8" required <?= \Core\Helper::isDemo() ? 'disabled' : '' ?>>
                            <span class="hint-text">Minimum 8 characters</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm new password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" minlength="8" required <?= \Core\Helper::isDemo() ? 'disabled' : '' ?>>
                        </div>
                        
                        <button type="submit" class="btn-primary" <?= \Core\Helper::isDemo() ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <span>Change password</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Details Card -->
        <div class="profile-card">
            <div class="card-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <h2>Account details</h2>
            </div>
            <div class="card-body">
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Email address</span>
                        <span class="detail-value"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Balance</span>
                        <span class="detail-value balance"><?= \Core\Helper::money($user['balance']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Account status</span>
                        <span class="detail-value">
                            <?php
                            $status = $user['status'] ?? 'active';
                            $statusClass = $status === 'active' ? 'success' : ($status === 'suspended' ? 'warning' : 'danger');
                            ?>
                            <span class="badge badge-<?= $statusClass ?>">
                                <?= ucfirst($status) ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Member since</span>
                        <span class="detail-value">
                            <?= date('F j, Y', strtotime($user['created_at'])) ?>
                        </span>
                    </div>
                </div>
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
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        border-radius: var(--radius-md);
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        border: 1px solid transparent;
    }

    .alert-success {
        background: var(--success-light);
        color: var(--success-dark);
        border-color: rgba(11, 126, 85, 0.1);
    }

    .alert-danger {
        background: var(--danger-light);
        color: var(--danger-dark);
        border-color: rgba(185, 28, 28, 0.1);
    }

    .alert-warning {
        background: var(--warning-light);
        color: var(--warning-dark);
        border-color: rgba(180, 91, 10, 0.1);
    }

    /* Profile Grid */
    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* Profile Card */
    .profile-card {
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        transition: all 0.2s ease;
        margin-bottom: 1.5rem;
    }

    .profile-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.05);
        border-color: var(--neutral-400);
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
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Forms */
    .profile-form {
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .form-group label {
        font-weight: 500;
        font-size: 0.85rem;
        color: var(--neutral-600);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .form-control {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-sm);
        font-size: 0.95rem;
        color: var(--neutral-900);
        transition: all 0.2s;
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 3px rgba(201, 160, 61, 0.1);
    }

    .hint-text {
        font-size: 0.8rem;
        color: var(--neutral-600);
    }

    /* Buttons */
    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.8rem 1.5rem;
        background: var(--primary-deep);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 10px rgba(10, 37, 64, 0.1);
        width: 100%;
    }

    .btn-primary:hover {
        background: var(--primary-soft);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(10, 37, 64, 0.15);
    }

    .btn-warning {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.8rem 1.5rem;
        background: var(--warning-light);
        color: var(--warning-dark);
        border: 1px solid rgba(180, 91, 10, 0.2);
        border-radius: var(--radius-sm);
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
    }

    .btn-warning:hover {
        background: #ffe6b3;
        transform: translateY(-2px);
    }

    .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-sm);
        color: var(--neutral-600);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-icon:hover {
        border-color: var(--accent-teal);
        color: var(--accent-teal);
    }

    /* API Section */
    .api-card {
        margin-bottom: 1.5rem;
    }

    .api-description {
        color: var(--neutral-700);
        margin-bottom: 1.2rem;
        font-size: 0.95rem;
    }

    .link {
        color: var(--accent-teal);
        text-decoration: none;
        font-weight: 500;
    }

    .link:hover {
        text-decoration: underline;
    }

    .api-key-display {
        background: var(--neutral-100);
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-sm);
        padding: 1rem;
        margin-bottom: 1.2rem;
    }

    .api-label {
        display: block;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--neutral-600);
        margin-bottom: 0.3rem;
    }

    .api-key-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .api-key {
        flex: 1;
        padding: 0.6rem 0.8rem;
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-sm);
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        color: var(--primary-deep);
        word-break: break-all;
    }

    .api-form {
        margin-bottom: 1rem;
    }

    .security-note {
        display: flex;
        align-items: flex-start;
        gap: 0.8rem;
        padding: 1rem;
        background: var(--warning-light);
        border-radius: var(--radius-sm);
        color: var(--warning-dark);
        font-size: 0.9rem;
        border: 1px solid rgba(180, 91, 10, 0.2);
    }

    .security-note .small {
        font-size: 0.85rem;
        margin-top: 0.2rem;
        opacity: 0.9;
    }

    /* Account Details */
    .details-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--neutral-200);
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-weight: 500;
        color: var(--neutral-600);
        font-size: 0.9rem;
    }

    .detail-value {
        font-weight: 600;
        color: var(--neutral-900);
    }

    .detail-value.balance {
        color: var(--success-dark);
        font-size: 1.2rem;
    }

    .badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 60px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .badge-success {
        background: var(--success-light);
        color: var(--success-dark);
    }

    .badge-danger {
        background: var(--danger-light);
        color: var(--danger-dark);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }

        .profile-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.75rem;
        }

        .card-header {
            padding: 1rem;
        }

        .card-body {
            padding: 1rem;
        }

        .api-key-wrapper {
            flex-wrap: wrap;
        }

        .btn-icon {
            width: 100%;
        }
    }
</style>

<script>
function toggleApiKey() {
    const apiKeyEl = document.querySelector('.api-key');
    const fullKey = apiKeyEl.getAttribute('data-full-key');
    const currentText = apiKeyEl.textContent;
    
    if (currentText.includes('...')) {
        // Show full key
        apiKeyEl.textContent = fullKey;
    } else {
        // Show masked key
        const maskedKey = fullKey.length > 12 ? fullKey.substring(0, 6) + '...' + fullKey.substring(fullKey.length - 6) : fullKey;
        apiKeyEl.textContent = maskedKey;
    }
}

function copyApiKey() {
    const apiKeyEl = document.querySelector('.api-key');
    const fullKey = apiKeyEl.getAttribute('data-full-key');
    
    navigator.clipboard.writeText(fullKey).then(() => {
        // Find the copy button (second btn-icon)
        const buttons = document.querySelectorAll('.api-key-wrapper .btn-icon');
        const copyBtn = buttons[1];
        const originalHtml = copyBtn.innerHTML;
        copyBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        setTimeout(() => {
            copyBtn.innerHTML = originalHtml;
        }, 2000);
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>