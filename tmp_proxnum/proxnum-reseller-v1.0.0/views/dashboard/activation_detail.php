<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard-container">
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <span class="header-badge">ACTIVATION</span>
                <h1 class="page-title">Activation details</h1>
                <p class="page-description">View your phone number activation information and SMS code</p>
            </div>
            <a href="<?= \Core\Helper::url('/dashboard/activations') ?>" class="btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back to activations</span>
            </a>
        </div>

        <div id="alert-container"></div>

        <!-- Activation Details Card -->
        <div class="detail-card">
            <div class="card-header">
                <div class="header-left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                    </svg>
                    <h2>Phone Number Activation</h2>
                </div>
                <span class="status-badge status-<?= $activation['status'] ?>">
                    <?= ucfirst($activation['status']) ?>
                </span>
            </div>

            <div class="card-body">
                <!-- SMS Code Section (if received) -->
                <?php if ($activation['code']): ?>
                <div class="sms-code-section">
                    <div class="code-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <h3>SMS Code Received</h3>
                    </div>
                    <div class="code-display">
                        <code class="sms-code"><?= htmlspecialchars($activation['code']) ?></code>
                        <button class="btn-copy" onclick="copySmsCode()" title="Copy code">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            Copy
                        </button>
                    </div>
                    <?php if ($activation['completed_at']): ?>
                    <div class="code-time">
                        Received <?= \Core\Helper::timeAgo($activation['completed_at']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php elseif ($activation['status'] === 'pending'): ?>
                <div class="waiting-section">
                    <div class="waiting-animation">
                        <div class="pulse"></div>
                    </div>
                    <h3>Waiting for SMS code...</h3>
                    <p>The system is monitoring your number. The SMS code will appear here automatically when received.</p>
                    
                </div>
                <?php endif; ?>

                <!-- Activation Information -->
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            Phone Number
                        </span>
                        <span class="info-value phone-number"><?= htmlspecialchars($activation['phone']) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect>
                                <line x1="7" y1="2" x2="7" y2="22"></line>
                                <line x1="17" y1="2" x2="17" y2="22"></line>
                            </svg>
                            Service
                        </span>
                        <span class="info-value"><?= htmlspecialchars($activation['service']) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="2" y1="12" x2="22" y2="12"></line>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                            </svg>
                            Country
                        </span>
                        <span class="info-value"><?= htmlspecialchars(\Core\Helper::getCountryName($activation['country'])) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            Created
                        </span>
                        <span class="info-value"><?= date('M j, Y \a\t g:i A', strtotime($activation['created_at'])) ?></span>
                    </div>
                    <?php if ($activation['status'] === 'pending'): ?>
                        <?php
                        $createdTime = strtotime($activation['created_at']);
                        $expiryTime = $createdTime + (20 * 60); // 20 minutes
                        $remainingSeconds = $expiryTime - time();
                        ?>
                    <div class="info-item">
                        <span class="info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            Time Remaining
                        </span>
                        <span class="info-value">
                            <div class="countdown-timer-detail" 
                                 data-expiry="<?= $expiryTime ?>"
                                 data-remaining="<?= max(0, $remainingSeconds) ?>">
                                <?php if ($remainingSeconds > 0): ?>
                                    <span class="timer-display-detail"></span>
                                <?php else: ?>
                                    <span style="color: #999;">Expired</span>
                                <?php endif; ?>
                            </div>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <span class="info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                            Cost
                        </span>
                        <span class="info-value cost"><?= \Core\Helper::money($activation['cost']) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                            Activation ID
                        </span>
                        <span class="info-value"><?= htmlspecialchars($activation['activation_id']) ?></span>
                    </div>
                </div>

                <!-- Actions -->
                <?php if ($activation['status'] === 'pending'): ?>
                <div class="actions-section">
                    <button class="btn-danger" onclick="cancelActivation()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        Cancel Activation
                    </button>
                    <p class="action-note">Cancel and get a refund if you no longer need this number</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Help Box -->
        <div class="help-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <div>
                <strong>Need help?</strong>
                <p>SMS codes typically arrive within 1-5 minutes. If you don't receive a code within 20 minutes, the activation will automatically expire and you'll be refunded.</p>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-deep: #0a2540;
        --primary-soft: #1e3a5f;
        --accent-gold: #c9a03d;
        --accent-teal: #14866d;
        --neutral-900: #0f172a;
        --neutral-800: #1e293b;
        --neutral-600: #475569;
        --neutral-400: #94a3b8;
        --neutral-300: #cbd5e1;
        --neutral-200: #e2e8f0;
        --neutral-100: #f1f5f9;
        --radius-lg: 12px;
    }

    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
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

    .btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: white;
        color: var(--primary-deep);
        border: 2px solid var(--neutral-300);
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-outline:hover {
        border-color: var(--accent-gold);
        background: rgba(201, 160, 61, 0.05);
        transform: translateY(-2px);
    }

    /* Detail Card */
    .detail-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid var(--neutral-200);
        margin-bottom: 1.5rem;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--neutral-200);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header-left svg {
        color: var(--accent-gold);
    }

    .card-header h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--primary-deep);
        margin: 0;
    }

    .card-body {
        padding: 2rem;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 60px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-pending {
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
    }

    .status-completed {
        background: rgba(34, 197, 94, 0.1);
        color: #15803d;
    }

    .status-expired {
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
    }

    .status-cancelled {
        background: rgba(107, 114, 128, 0.1);
        color: #374151;
    }

    /* SMS Code Section */
    .sms-code-section {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        color: white;
        padding: 2rem;
        border-radius: var(--radius-lg);
        margin-bottom: 2rem;
        text-align: center;
    }

    .code-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .code-header h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }

    .code-display {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .sms-code {
        background: white;
        color: #16a34a;
        padding: 1rem 2rem;
        border-radius: 8px;
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: 0.1em;
    }

    .btn-copy {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-copy:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: white;
    }

    .code-time {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    /* Waiting Section */
    .waiting-section {
        background: rgba(59, 130, 246, 0.05);
        border: 2px dashed rgba(59, 130, 246, 0.3);
        border-radius: var(--radius-lg);
        padding: 3rem 2rem;
        text-align: center;
        margin-bottom: 2rem;
    }

    .waiting-animation {
        margin: 0 auto 1.5rem;
        width: 80px;
        height: 80px;
        position: relative;
    }

    .pulse {
        width: 80px;
        height: 80px;
        border: 3px solid #3b82f6;
        border-radius: 50%;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.7;
        }
    }

    .waiting-section h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-deep);
        margin-bottom: 0.75rem;
    }

    .waiting-section p {
        color: var(--neutral-600);
        margin-bottom: 1.5rem;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .info-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--neutral-600);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .info-label svg {
        color: var(--accent-gold);
    }

    .info-value {
        font-size: 1.1rem;
        font-weight: 500;
        color: var(--neutral-900);
    }

    .info-value.phone-number {
        font-size: 1.25rem;
        font-weight: 600;
        color: #3b82f6;
    }

    .info-value.cost {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--accent-teal);
    }

    /* Actions Section */
    .actions-section {
        border-top: 1px solid var(--neutral-200);
        padding-top: 2rem;
        text-align: center;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .btn-danger {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .action-note {
        margin-top: 0.75rem;
        font-size: 0.9rem;
        color: var(--neutral-600);
    }

    /* Countdown Timer */
    .countdown-timer-detail {
        font-weight: 600;
        font-size: 1rem;
    }

    .timer-display-detail {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .timer-display-detail.timer-green {
        background: rgba(34, 197, 94, 0.1);
        color: #15803d;
    }

    .timer-display-detail.timer-orange {
        background: rgba(251, 146, 60, 0.1);
        color: #c2410c;
    }

    .timer-display-detail.timer-red {
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
        animation: pulse-timer 1.5s ease-in-out infinite;
    }

    @keyframes pulse-timer {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    /* Help Box */
    .help-box {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        background: rgba(59, 130, 246, 0.05);
        border: 1px solid rgba(59, 130, 246, 0.1);
        border-radius: var(--radius-lg);
        color: #1e40af;
    }

    .help-box svg {
        flex-shrink: 0;
        color: #3b82f6;
    }

    .help-box strong {
        display: block;
        margin-bottom: 0.25rem;
        font-size: 1rem;
    }

    .help-box p {
        margin: 0;
        font-size: 0.9rem;
        line-height: 1.6;
    }

    /* Alert */
    #alert-container {
        position: relative;
        z-index: 100;
        margin-bottom: 1rem;
    }
    
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background: rgba(34, 197, 94, 0.1);
        color: #15803d;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    
    /* Spinner */
    .spinner-border {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner 0.75s linear infinite;
    }
    
    .spinner-border-sm {
        width: 0.875rem;
        height: 0.875rem;
        border-width: 2px;
    }
    
    @keyframes spinner {
        to { transform: rotate(360deg); }
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

        .btn-outline {
            width: 100%;
            justify-content: center;
        }

        .card-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .sms-code {
            font-size: 1.5rem;
        }
    }

    /* Animations */
    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<script>
const activationId = <?= $activation['id'] ?>;
const csrfToken = '<?= $csrf_token ?>';
const basePath = '<?= \Core\Helper::url('') ?>';
const currentStatus = '<?= $activation['status'] ?>';

// Auto-refresh for pending activations
let autoRefreshInterval = null;
if (currentStatus === 'pending') {
    // Check every 5 seconds for faster updates
    autoRefreshInterval = setInterval(checkActivationStatus, 5000);
    // Also check immediately after 2 seconds
    setTimeout(checkActivationStatus, 2000);
    console.log('✓ Auto-refresh enabled: Checking for SMS code every 5 seconds');
}

function copySmsCode() {
    const code = '<?= addslashes($activation['code'] ?? '') ?>';
    navigator.clipboard.writeText(code).then(() => {
        showAlert('SMS code copied to clipboard!', 'success');
    });
}

function checkActivationStatus() {
    fetch(basePath + '/dashboard/checkStatus?id=' + activationId, {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.code && data.code !== 'Waiting Sms') {
                // SMS received! Update DOM directly
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                }
                updateCodeDisplay(data.code, data.completed_at);
                showAlert('✓ SMS code received!', 'success');
            } else if (data.status !== 'pending') {
                // Status changed (expired/cancelled)
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                }
                showAlert('Status updated. Reloading...', 'success');
                setTimeout(() => location.reload(), 1500);
            }
        } else {
            console.log('Status check:', data.message || 'No update yet');
        }
    })
    .catch(err => {
        console.error('Check status error:', err);
    });
}

function updateCodeDisplay(code, completedAt) {
    // Replace waiting section with code display
    const waitingSection = document.querySelector('.waiting-section');
    if (!waitingSection) return;

    const timeAgo = completedAt ? 'Just now' : '';
    
    const codeHtml = `
        <div class="sms-code-section" style="animation: slideIn 0.3s ease-out;">
            <div class="code-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <h3>SMS Code Received</h3>
            </div>
            <div class="code-display">
                <code class="sms-code">${code}</code>
                <button class="btn-copy" onclick="copyNewCode('${code}')" title="Copy code">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    Copy
                </button>
            </div>
            <div class="code-time">
                Received ${timeAgo}
            </div>
        </div>
    `;
    
    waitingSection.outerHTML = codeHtml;
}

function copyNewCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        showAlert('SMS code copied to clipboard!', 'success');
    });
}

function cancelActivation() {
    if (!confirm('Are you sure you want to cancel this activation? Your balance will be refunded.')) {
        return;
    }

    const btn = event.target.closest('button');
    if (!btn) {
        console.error('Cancel button not found');
        showAlert('Error: Button not found', 'error');
        return;
    }
    
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Cancelling...';

    console.log('Cancelling activation:', activationId);
    console.log('Request URL:', basePath + '/dashboard/cancelActivation');

    fetch(basePath + '/dashboard/cancelActivation', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            id: activationId,
            csrf_token: csrfToken
        })
    })
    .then(async (r) => {
        console.log('Response status:', r.status);
        const text = await r.text();
        console.log('Response text:', text);
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            throw new Error('Invalid response from server: ' + text.substring(0, 100));
        }
    })
    .then(data => {
        console.log('Parsed response:', data);
        if (data.success) {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
            showAlert(data.message || 'Activation cancelled and refunded!', 'success');
            setTimeout(() => window.location.href = basePath + '/dashboard/activations', 1500);
        } else {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            showAlert(data.message || 'Failed to cancel activation', 'error');
        }
    })
    .catch(err => {
        console.error('Cancel error:', err);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        showAlert(err.message || 'Network error. Please try again.', 'error');
    });
}

function showAlert(message, type) {
    const container = document.getElementById('alert-container');
    const icon = type === 'success' 
        ? '<polyline points="20 6 9 17 4 12"></polyline>' 
        : '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>';
    
    container.innerHTML = `
        <div class="alert alert-${type}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                ${icon}
            </svg>
            ${message}
        </div>
    `;
    setTimeout(() => container.innerHTML = '', 5000);
}

// Countdown timer
function initializeCountdownTimer() {
    const timerElement = document.querySelector('.countdown-timer-detail');
    if (!timerElement) return;

    const displayElement = timerElement.querySelector('.timer-display-detail');
    if (!displayElement) return;

    const expiryTime = parseInt(timerElement.dataset.expiry);
    let remainingSeconds = parseInt(timerElement.dataset.remaining);

    function updateTimer() {
        if (remainingSeconds <= 0) {
            displayElement.textContent = 'Expired';
            displayElement.style.color = '#999';
            clearInterval(timerInterval);
            // Auto-reload to update status
            setTimeout(() => location.reload(), 2000);
            return;
        }

        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        displayElement.textContent = `${minutes}m ${seconds}s`;

        // Color coding based on time remaining
        displayElement.className = 'timer-display-detail';
        if (remainingSeconds > 600) { // > 10 minutes
            displayElement.classList.add('timer-green');
        } else if (remainingSeconds > 300) { // > 5 minutes
            displayElement.classList.add('timer-orange');
        } else { // < 5 minutes
            displayElement.classList.add('timer-red');
        }

        remainingSeconds--;
    }

    // Initial update
    updateTimer();

    // Update every second
    const timerInterval = setInterval(updateTimer, 1000);
}

// Initialize countdown timer when page loads
if (currentStatus === 'pending') {
    initializeCountdownTimer();
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
