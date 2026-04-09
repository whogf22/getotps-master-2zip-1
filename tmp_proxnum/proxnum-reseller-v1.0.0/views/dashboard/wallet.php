<?php
use Core\Helper;
$title = 'Wallet & Funds';
include __DIR__ . '/../layouts/header.php';
?>

<!-- Main Container with Sidebar Layout -->
<div class="wallet-layout">
    <!-- Left Sidebar - Quick Stats & Pending -->
    <div class="wallet-sidebar">
        <!-- Balance Card - Prominent -->
        <div class="balance-card">
            <div class="balance-label">Current Balance</div>
            <div class="balance-amount"><?= Helper::money($user['balance']) ?></div>
            <div class="balance-footer">
                <span class="stat-badge">
                    <span class="stat-dot"></span>
                    <?= count($transactions) ?> Transactions
                </span>
            </div>
        </div>

        <!-- Pending Deposits Section -->
        <?php if (!empty($pending_deposits)): ?>
        <div class="pending-section">
            <div class="section-header">
                <h3>Pending Approvals</h3>
                <span class="pending-count"><?= count($pending_deposits) ?></span>
            </div>
            <div class="pending-timeline">
                <?php foreach ($pending_deposits as $deposit): ?>
                <div class="timeline-item">
                    <div class="timeline-icon pending"></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <span class="timeline-amount">$<?= number_format($deposit['amount'], 2) ?></span>
                            <span class="timeline-method"><?= ucfirst($deposit['gateway']) ?></span>
                        </div>
                        <div class="timeline-time"><?= Helper::timeAgo($deposit['created_at']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-row">
                <div class="stat-item">
                    <div class="stat-value"><?= count($transactions) ?></div>
                    <div class="stat-label">Total Transactions</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">$<?= number_format(array_sum(array_column($transactions, 'amount')), 2) ?></div>
                    <div class="stat-label">Volume</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="wallet-main">
        <!-- Deposit Flow Card -->
        <div class="deposit-flow-card">
            <div class="card-header">
                <h2>Add Funds to Wallet</h2>
                <div class="step-indicator">
                    <span class="step active" id="step1Indicator">1</span>
                    <span class="step-line"></span>
                    <span class="step" id="step2Indicator">2</span>
                </div>
            </div>
            
            <?php if (Helper::isDemo()): ?>
            <div class="demo-wallet-notice">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div>
                    <strong>Demo Account</strong>
                    <p>Deposits are disabled in demo mode. <a href="<?= str_replace('/proxnum-reseller', '', Helper::url('')) ?>/license/plans">Purchase a license</a> to enable real transactions.</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Phase 1: Amount & Payment Method -->
            <div id="phase1" class="phase active">
                <form id="initiateDepositForm" class="phase-form">
                    <!-- Amount Input - Prominent -->
                    <div class="amount-section">
                        <label class="phase-label">Enter Amount</label>
                        <div class="amount-input-group">
                            <span class="currency-symbol">$</span>
                            <input type="number" id="amount" step="0.01" min="1" required
                                   class="amount-input" 
                                   placeholder="0.00"
                                   autocomplete="off">
                        </div>
                        <div class="amount-hint">Minimum deposit: $1.00</div>
                    </div>

                    <!-- Payment Methods Grid -->
                    <div class="methods-section">
                        <label class="phase-label">Select Payment Method</label>
                        <div class="methods-grid-horizontal">
                            <?php foreach ($payment_methods as $method): ?>
                            <label class="method-tile <?= $method['gateway_type'] ?>">
                                <input type="radio" name="gateway" value="<?= $method['name'] ?>" required>
                                <div class="tile-content">
                                    <span class="method-emoji">
                                        <?php
                                        $icons = ['paypal' => '💳', 'crypto' => '₿', 'binance' => '🔶'];
                                        echo $icons[$method['name']] ?? '';
                                        ?>
                                    </span>
                                    <span class="method-name"><?= htmlspecialchars($method['display_name']) ?></span>
                                    <span class="method-type"><?= $method['gateway_type'] === 'manual' ? 'Manual' : 'Instant' ?></span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (empty($payment_methods)): ?>
                            <div class="empty-methods">No payment methods available. Please contact support.</div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($payment_methods)): ?>
                    <button type="submit" class="btn-phase btn-phase-1">
                        <span>Proceed to Payment</span>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Phase 2: Payment Details & Upload -->
            <div id="phase2" class="phase">
                <div class="phase-header">
                    <button onclick="goBackToPhase1()" class="phase-back">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Change Method
                    </button>
                    <div class="phase-title" id="gatewayTitle"></div>
                </div>

                <!-- Payment Instructions Card -->
                <div class="payment-instructions-card">
                    <div class="instructions-grid">
                        <div class="instruction-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Send Exact Amount</h4>
                                <p id="paymentAmount" class="instruction-highlight"></p>
                            </div>
                        </div>
                        <div class="instruction-step">
                            <div class="step-number">2</div>
                            <div class="step-content" id="paymentDetails"></div>
                        </div>
                        <div class="instruction-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Upload Proof</h4>
                                <p>Submit screenshot/receipt for verification</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Form -->
                <form id="submitPaymentForm" class="upload-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= Helper::getCsrf() ?>">
                    <input type="hidden" name="action" value="submit_payment">
                    <input type="hidden" name="amount" id="finalAmount">
                    <input type="hidden" name="gateway" id="finalGateway">

                    <!-- Transaction Reference -->
                    <div class="input-group">
                        <label class="input-label">Transaction Reference (Optional)</label>
                        <input type="text" name="transaction_ref" 
                               class="input-field" 
                               placeholder="Enter transaction ID if available">
                        <span class="input-hint">Helps us verify faster</span>
                    </div>

                    <!-- File Upload Area -->
                    <div class="input-group">
                        <label class="input-label">Payment Proof <span class="required">*</span></label>
                        <div class="upload-container" id="fileUploadArea">
                            <input type="file" name="payment_proof" id="paymentProof" 
                                   accept="image/*,.pdf" style="display: none;">
                            
                            <div class="upload-box" onclick="document.getElementById('paymentProof').click()">
                                <div class="upload-icon">📎</div>
                                <div class="upload-text">
                                    <span class="upload-main">Click to upload</span>
                                    <span class="upload-sub">JPG, PNG, GIF, PDF (Max 5MB)</span>
                                </div>
                            </div>

                            <div class="upload-preview" id="filePreview" style="display: none;">
                                <img id="previewImage" src="" alt="Preview">
                                <div class="preview-details">
                                    <span id="fileName" class="preview-name"></span>
                                    <button type="button" onclick="removeFile()" class="remove-btn">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-phase btn-phase-2">
                        <span>Submit for Verification</span>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Recent Transactions - Moved to Main Area -->
        <div class="transactions-card">
            <div class="card-header">
                <h2>Recent Transactions</h2>
                <a href="<?= Helper::url('/dashboard/transactions') ?>" class="view-all-link">
                    View All
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 11.293a1 1 0 011.414 0L10 8.414l-3.293-3.293a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>
            <div class="transactions-list">
                <?php if (empty($transactions)): ?>
                    <div class="empty-transactions">
                        <div class="empty-icon">📭</div>
                        <h4>No Transactions Yet</h4>
                        <p>Your transaction history will appear here</p>
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($transactions, 0, 7) as $transaction): ?>
                    <div class="transaction-item">
                        <div class="transaction-icon <?= $transaction['type'] ?>">
                            <?= $transaction['type'] === 'deposit' ? '↓' : '↑' ?>
                        </div>
                        <div class="transaction-details">
                            <div class="transaction-main">
                                <span class="transaction-type"><?= ucfirst($transaction['type']) ?></span>
                                <span class="transaction-desc"><?= htmlspecialchars($transaction['description']) ?></span>
                            </div>
                            <div class="transaction-meta">
                                <span class="transaction-ref"><?= htmlspecialchars($transaction['reference'] ?? '-') ?></span>
                                <span class="transaction-date"><?= Helper::timeAgo($transaction['created_at']) ?></span>
                            </div>
                        </div>
                        <div class="transaction-amount <?= $transaction['type'] ?>">
                            <?= Helper::money($transaction['amount']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary: #2563eb;
    --primary-light: #3b82f6;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --gray-50: #f8fafc;
    --gray-100: #f1f5f9;
    --gray-200: #e2e8f0;
    --gray-300: #cbd5e1;
    --gray-400: #94a3b8;
    --gray-500: #64748b;
    --gray-600: #475569;
    --gray-700: #334155;
    --gray-800: #1e293b;
    --gray-900: #0f172a;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --radius: 0.75rem;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* Layout */
.wallet-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
    padding: 1.5rem;
}

/* Sidebar */
.wallet-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.balance-card {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    padding: 1.5rem;
    border-radius: var(--radius);
    color: white;
}

.balance-label {
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    opacity: 0.8;
    margin-bottom: 0.5rem;
}

.balance-amount {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 1rem;
}

.balance-footer {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stat-badge {
    background: rgba(255, 255, 255, 0.1);
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.stat-dot {
    width: 6px;
    height: 6px;
    background: var(--success);
    border-radius: 50%;
}

/* Pending Timeline */
.pending-section {
    background: white;
    border-radius: var(--radius);
    padding: 1.25rem;
    box-shadow: var(--shadow);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.section-header h3 {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gray-500);
}

.pending-count {
    background: var(--warning);
    color: white;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.pending-timeline {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.timeline-item {
    display: flex;
    gap: 0.75rem;
}

.timeline-icon {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-top: 0.375rem;
}

.timeline-icon.pending {
    background: var(--warning);
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
}

.timeline-content {
    flex: 1;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.25rem;
}

.timeline-amount {
    font-weight: 600;
    color: var(--gray-900);
}

.timeline-method {
    font-size: 0.75rem;
    color: var(--gray-500);
    background: var(--gray-100);
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
}

.timeline-time {
    font-size: 0.75rem;
    color: var(--gray-400);
}

/* Quick Stats */
.quick-stats {
    background: white;
    border-radius: var(--radius);
    padding: 1rem;
    box-shadow: var(--shadow);
}

.stat-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
}

.stat-label {
    font-size: 0.75rem;
    color: var(--gray-500);
    margin-top: 0.25rem;
}

/* Main Content */
.wallet-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Deposit Flow Card */
.deposit-flow-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-800);
}

/* Demo Wallet Notice */
.demo-wallet-notice {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #fff3d4 0%, #ffe8b8 100%);
    border: 2px solid #f0ad4e;
    border-radius: 0.75rem;
    margin: 0 1.5rem 1rem;
}

.demo-wallet-notice svg {
    flex-shrink: 0;
    color: #b45b0a;
    margin-top: 0.1rem;
}

.demo-wallet-notice strong {
    display: block;
    color: #b45b0a;
    font-weight: 700;
    margin-bottom: 0.25rem;
    font-size: 0.95rem;
}

.demo-wallet-notice p {
    color: #8b4513;
    margin: 0;
    line-height: 1.5;
    font-size: 0.9rem;
}

.demo-wallet-notice a {
    color: #b45b0a;
    text-decoration: underline;
    font-weight: 600;
}

.demo-wallet-notice a:hover {
    color: #8b4513;
}

/* Step Indicator */
.step-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.step {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--gray-200);
    color: var(--gray-500);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 600;
}

.step.active {
    background: var(--primary);
    color: white;
}

.step-line {
    width: 40px;
    height: 2px;
    background: var(--gray-200);
}

/* Phases */
.phase {
    padding: 1.5rem;
    display: none;
}

.phase.active {
    display: block;
}

.phase-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-200);
}

.phase-back {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid var(--gray-300);
    border-radius: 0.5rem;
    color: var(--gray-700);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.phase-back:hover {
    background: var(--gray-50);
}

.phase-title {
    font-weight: 600;
    color: var(--gray-800);
}

/* Amount Section */
.amount-section {
    margin-bottom: 2rem;
}

.phase-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.amount-input-group {
    position: relative;
    margin-bottom: 0.5rem;
}

.currency-symbol {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--gray-400);
}

.amount-input {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    border: 2px solid var(--gray-200);
    border-radius: 1rem;
    font-size: 2rem;
    font-weight: 600;
    transition: all 0.2s;
}

.amount-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.amount-hint {
    font-size: 0.875rem;
    color: var(--gray-500);
}

/* Methods Grid */
.methods-grid-horizontal {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.method-tile {
    position: relative;
    cursor: pointer;
}

.method-tile input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.tile-content {
    padding: 1rem;
    border: 2px solid var(--gray-200);
    border-radius: 0.75rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.method-tile input[type="radio"]:checked + .tile-content {
    border-color: var(--primary);
    background: #eff6ff;
}

.method-emoji {
    font-size: 2rem;
}

.method-name {
    font-weight: 600;
    color: var(--gray-800);
    text-align: center;
}

.method-type {
    font-size: 0.75rem;
    color: var(--gray-500);
}

/* Buttons */
.btn-phase {
    width: 100%;
    padding: 1rem;
    border: none;
    border-radius: 0.75rem;
    font-weight: 600;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-phase-1 {
    background: var(--primary);
    color: white;
}

.btn-phase-1:hover {
    background: var(--primary-light);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.btn-phase-2 {
    background: var(--success);
    color: white;
}

.btn-phase-2:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

/* Payment Instructions */
.payment-instructions-card {
    background: var(--gray-50);
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.instructions-grid {
    display: grid;
    gap: 1rem;
}

.instruction-step {
    display: flex;
    gap: 1rem;
}

.step-number {
    width: 28px;
    height: 28px;
    background: white;
    border: 2px solid var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--primary);
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.step-content h4 {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.25rem;
}

.step-content p {
    font-size: 0.875rem;
    color: var(--gray-600);
}

.instruction-highlight {
    font-size: 1.125rem !important;
    font-weight: 600;
    color: var(--primary) !important;
}

/* Upload Form */
.upload-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.input-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-700);
}

.input-field {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--gray-300);
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.input-field:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.input-hint {
    font-size: 0.75rem;
    color: var(--gray-500);
}

.required {
    color: var(--danger);
}

/* Upload Container */
.upload-container {
    border: 2px dashed var(--gray-300);
    border-radius: 0.75rem;
    background: var(--gray-50);
}

.upload-box {
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}

.upload-box:hover {
    background: white;
}

.upload-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.upload-text {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.upload-main {
    font-weight: 600;
    color: var(--gray-700);
}

.upload-sub {
    font-size: 0.75rem;
    color: var(--gray-500);
}

.upload-preview {
    padding: 1.5rem;
    text-align: center;
}

.upload-preview img {
    max-width: 100%;
    max-height: 150px;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.preview-details {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.preview-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray-700);
}

.remove-btn {
    padding: 0.25rem 0.75rem;
    background: var(--danger);
    color: white;
    border: none;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    cursor: pointer;
}

/* Transactions Card */
.transactions-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.view-all-link {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: var(--primary);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
}

.transactions-list {
    padding: 0.5rem;
}

.transaction-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--gray-100);
    transition: background 0.2s;
}

.transaction-item:hover {
    background: var(--gray-50);
}

.transaction-item:last-child {
    border-bottom: none;
}

.transaction-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.transaction-icon.deposit {
    background: #d1fae5;
    color: #065f46;
}

.transaction-icon.withdrawal {
    background: #fee2e2;
    color: #991b1b;
}

.transaction-details {
    flex: 1;
    min-width: 0;
}

.transaction-main {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
    flex-wrap: wrap;
}

.transaction-type {
    font-weight: 600;
    color: var(--gray-800);
    font-size: 0.875rem;
}

.transaction-desc {
    color: var(--gray-600);
    font-size: 0.875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.transaction-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.75rem;
}

.transaction-ref {
    color: var(--gray-400);
    font-family: monospace;
}

.transaction-date {
    color: var(--gray-400);
}

.transaction-amount {
    font-weight: 600;
    font-size: 1rem;
    white-space: nowrap;
}

.transaction-amount.deposit {
    color: var(--success);
}

.transaction-amount.withdrawal {
    color: var(--danger);
}

/* Empty State */
.empty-transactions {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-transactions h4 {
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.empty-transactions p {
    color: var(--gray-500);
    font-size: 0.875rem;
}

.empty-methods {
    text-align: center;
    padding: 2rem;
    color: var(--gray-500);
    background: var(--gray-50);
    border-radius: 0.5rem;
}

/* Responsive */
@media (max-width: 1024px) {
    .wallet-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .wallet-layout {
        padding: 1rem;
    }
    
    .methods-grid-horizontal {
        grid-template-columns: 1fr;
    }
    
    .transaction-item {
        flex-wrap: wrap;
    }
    
    .transaction-amount {
        width: 100%;
        margin-left: 3rem;
    }
}
</style>

<script>
const basePath = '<?= $basePath ?>';
let currentDepositData = null;

// Phase 1: Initiate deposit
document.getElementById('initiateDepositForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const amount = document.getElementById('amount').value;
    const gateway = document.querySelector('input[name="gateway"]:checked')?.value;
    
    if (!gateway) {
        alert('Please select a payment method');
        return;
    }
    
    if (amount < 1) {
        alert('Minimum deposit is $1');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'initiate_deposit');
    formData.append('amount', amount);
    formData.append('gateway', gateway);
    
    fetch(basePath + '/dashboard/wallet', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            currentDepositData = data;
            showPhase2(data);
        } else {
            const errorMsg = data.error || data.message || 'An error occurred';
            const icon = data.demo_mode ? '⚠️' : '❌';
            alert(icon + ' ' + errorMsg);
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
});

function showPhase2(data) {
    // Update phases
    document.getElementById('phase1').classList.remove('active');
    document.getElementById('phase2').classList.add('active');
    
    // Update step indicators
    document.getElementById('step1Indicator').classList.remove('active');
    document.getElementById('step2Indicator').classList.add('active');
    
    // Update UI with data
    document.getElementById('gatewayTitle').textContent = data.gateway_name;
    document.getElementById('paymentAmount').textContent = '$' + parseFloat(data.amount).toFixed(2);
    document.getElementById('finalAmount').value = data.amount;
    document.getElementById('finalGateway').value = data.gateway;
    
    let detailsHTML = '';
    if (data.gateway === 'paypal' && data.config.paypal_address) {
        detailsHTML = `
            <h4>Send to PayPal</h4>
            <div class="payment-address">
                <code>${data.config.paypal_address}</code>
                <button class="copy-btn" onclick="copyText('${data.config.paypal_address}')">Copy</button>
            </div>
        `;
    } else if (data.gateway === 'crypto' && data.config.wallet_address) {
        detailsHTML = `
            <h4>Send Crypto</h4>
            <p>Currency: ${data.config.crypto_type}</p>
            <p>Network: ${data.config.network}</p>
            <div class="payment-address">
                <code>${data.config.wallet_address}</code>
                <button class="copy-btn" onclick="copyText('${data.config.wallet_address}')">Copy</button>
            </div>
        `;
    } else if (data.gateway === 'binance' && data.config.binance_pay_id) {
        detailsHTML = `
            <h4>Binance Pay ID</h4>
            <div class="payment-address">
                <code>${data.config.binance_pay_id}</code>
                <button class="copy-btn" onclick="copyText('${data.config.binance_pay_id}')">Copy</button>
            </div>
        `;
    }
    
    document.getElementById('paymentDetails').innerHTML = detailsHTML;
}

function goBackToPhase1() {
    document.getElementById('phase2').classList.remove('active');
    document.getElementById('phase1').classList.add('active');
    document.getElementById('step2Indicator').classList.remove('active');
    document.getElementById('step1Indicator').classList.add('active');
    currentDepositData = null;
}

function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert(' Copied to clipboard!');
    });
}

// File upload preview
document.getElementById('paymentProof').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    if (file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5MB');
        this.value = '';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        if (file.type.startsWith('image/')) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('previewImage').style.display = 'block';
        } else {
            document.getElementById('previewImage').style.display = 'none';
        }
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('filePreview').style.display = 'block';
        document.querySelector('.upload-box').style.display = 'none';
    };
    reader.readAsDataURL(file);
});

function removeFile() {
    document.getElementById('paymentProof').value = '';
    document.getElementById('filePreview').style.display = 'none';
    document.querySelector('.upload-box').style.display = 'block';
}

// Submit payment
document.getElementById('submitPaymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    if (!formData.get('payment_proof') || !document.getElementById('paymentProof').files[0]) {
        alert('Please upload payment proof');
        return;
    }
    
    fetch(basePath + '/dashboard/wallet', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const message = data.message || 'Success!';
            alert('✅ ' + message);
            location.reload();
        } else {
            const errorMsg = data.error || data.message || 'An error occurred';
            const icon = data.demo_mode ? '⚠️' : '❌';
            alert(icon + ' ' + errorMsg);
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>