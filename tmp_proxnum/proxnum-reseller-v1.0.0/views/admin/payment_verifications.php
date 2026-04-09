<?php
use Core\Helper;
$title = 'Payment Verifications';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <span class="header-badge">PAYMENTS</span>
                <h1 class="page-title">Payment Verifications</h1>
                <p class="page-description">Review and approve manual payment deposits</p>
            </div>
        </div>

        <!-- Status Filter -->
        <div class="filter-tabs">
            <a href="?status=pending" class="filter-tab <?= $status === 'pending' ? 'active' : '' ?>">
                Pending
                <?php if ($pendingCount > 0): ?>
                    <span class="badge-count"><?= $pendingCount ?></span>
                <?php endif; ?>
            </a>
            <a href="?status=approved" class="filter-tab <?= $status === 'approved' ? 'active' : '' ?>">
                Approved (<?= $approvedCount ?>)
            </a>
            <a href="?status=rejected" class="filter-tab <?= $status === 'rejected' ? 'active' : '' ?>">
                Rejected (<?= $rejectedCount ?>)
            </a>
        </div>

        <!-- Deposits List -->
        <div class="deposits-list">
            <?php if (!empty($deposits)): ?>
                <?php foreach ($deposits as $deposit): ?>
                <div class="deposit-card deposit-<?= $deposit['status'] ?>">
                    <div class="deposit-header">
                        <div class="deposit-info">
                            <div class="deposit-amount">$<?= number_format($deposit['amount'], 2) ?></div>
                            <div class="deposit-meta">
                                <span class="gateway-badge gateway-<?= $deposit['gateway'] ?>">
                                    <?php
                                    $icons = ['paypal' => '💳', 'crypto' => '₿', 'binance' => '🔶'];
                                    echo $icons[$deposit['gateway']] ?? '';
                                    ?>
                                    <?= ucfirst($deposit['gateway']) ?>
                                </span>
                                <span class="status-badge status-<?= $deposit['status'] ?>">
                                    <?= ucfirst($deposit['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="deposit-user">
                            <div class="user-name"><?= htmlspecialchars($deposit['user_name']) ?></div>
                            <div class="user-email"><?= htmlspecialchars($deposit['user_email']) ?></div>
                        </div>
                    </div>
                    
                    <div class="deposit-body">
                        <div class="deposit-details">
                            <div class="detail-row">
                                <span class="detail-label">Transaction Reference:</span>
                                <span class="detail-value"><?= htmlspecialchars($deposit['transaction_reference'] ?? 'N/A') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Submitted:</span>
                                <span class="detail-value"><?= Helper::timeAgo($deposit['created_at']) ?></span>
                            </div>
                            <?php if ($deposit['status'] !== 'pending'): ?>
                            <div class="detail-row">
                                <span class="detail-label">Processed by:</span>
                                <span class="detail-value"><?= htmlspecialchars($deposit['approved_by_name'] ?? 'N/A') ?></span>
                            </div>
                            <?php if ($deposit['admin_notes']): ?>
                            <div class="detail-row">
                                <span class="detail-label">Notes:</span>
                                <span class="detail-value"><?= htmlspecialchars($deposit['admin_notes']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($deposit['payment_proof']): ?>
                        <div class="payment-proof">
                            <div class="proof-label">Payment Proof:</div>
                            <a href="<?= Helper::url('admin/viewPaymentProof/' . urlencode($deposit['payment_proof'])) ?>" target="_blank" class="proof-image-link">
                                <?php 
                                $fileExt = strtolower(pathinfo($deposit['payment_proof'], PATHINFO_EXTENSION));
                                if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])): 
                                ?>
                                <img src="<?= Helper::url('admin/viewPaymentProof/' . urlencode($deposit['payment_proof'])) ?>" alt="Payment Proof" class="proof-image">
                                <?php else: ?>
                                <div class="pdf-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                    <p>PDF Document</p>
                                </div>
                                <?php endif; ?>
                                <div class="proof-overlay">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    View Full Size
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($deposit['status'] === 'pending'): ?>
                    <div class="deposit-actions">
                        <button onclick="showApproveModal(<?= $deposit['id'] ?>)" class="btn-approve">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Approve
                        </button>
                        <button onclick="showRejectModal(<?= $deposit['id'] ?>)" class="btn-reject">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            Reject
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    <h3>No <?= $status ?> payments</h3>
                    <p>There are no <?= $status ?> payment deposits at this time</p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <div style="margin-top: 20px; text-align: center; padding: 20px;">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="?status=<?= $status ?>&page=<?= $i ?>" class="btn" style="<?= $i === $pagination['current_page'] ? 'opacity: 0.6;' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="modal" style="display: none;">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3>Approve Payment</h3>
            <button onclick="closeModals()" class="btn-close">×</button>
        </div>
        <div class="modal-body">
            <p>Confirm approval of this payment deposit? The user's balance will be credited immediately.</p>
            <div class="form-group">
                <label>Notes (Optional)</label>
                <textarea id="approveNotes" class="form-control" rows="3" placeholder="Add any notes..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeModals()" class="btn-secondary">Cancel</button>
            <button onclick="approvePayment()" class="btn-success">Approve Payment</button>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal" style="display: none;">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3>Reject Payment</h3>
            <button onclick="closeModals()" class="btn-close">×</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to reject this payment deposit?</p>
            <div class="form-group">
                <label>Reason for Rejection</label>
                <textarea id="rejectNotes" class="form-control" rows="3" placeholder="Enter reason..." required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeModals()" class="btn-secondary">Cancel</button>
            <button onclick="rejectPayment()" class="btn-danger">Reject Payment</button>
        </div>
    </div>
</div>

<style>
    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .filter-tab {
        padding: 12px 24px;
        text-decoration: none;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .filter-tab:hover {
        color: #3b82f6;
    }
    
    .filter-tab.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }
    
    .badge-count {
        background: #ef4444;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
    }
    
    .deposits-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    
    .deposit-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border-left: 4px solid #3b82f6;
    }
    
    .deposit-card.deposit-approved {
        border-left-color: #10b981;
    }
    
    .deposit-card.deposit-rejected {
        border-left-color: #ef4444;
    }
    
    .deposit-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 16px;
    }
    
    .deposit-amount {
        font-size: 32px;
        font-weight: 700;
        color: #1f2937;
    }
    
    .deposit-meta {
        display: flex;
        gap: 8px;
        margin-top: 8px;
    }
    
    .gateway-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
    }
    
    .gateway-paypal {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .gateway-crypto {
        background: #fef3c7;
        color: #92400e;
    }
    
    .gateway-binance {
        background: #fef08a;
        color: #713f12;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .deposit-user {
        text-align: right;
    }
    
    .user-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 16px;
    }
    
    .user-email {
        color: #6b7280;
        font-size: 14px;
        margin-top: 4px;
    }
    
    .deposit-body {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 24px;
        padding: 20px 0;
    }
    
    .deposit-details {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .detail-row {
        display: flex;
        gap: 12px;
    }
    
    .detail-label {
        font-weight: 600;
        color: #6b7280;
        min-width: 150px;
    }
    
    .detail-value {
        color: #1f2937;
    }
    
    .payment-proof {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .proof-label {
        padding: 8px 12px;
        background: #f3f4f6;
        font-weight: 600;
        font-size: 13px;
        color: #6b7280;
    }
    
    .proof-image-link {
        display: block;
        position: relative;
        cursor: pointer;
    }
    
    .proof-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    
    .pdf-placeholder {
        width: 100%;
        height: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f9fafb;
        color: #6b7280;
    }
    
    .pdf-placeholder svg {
        margin-bottom: 12px;
    }
    
    .pdf-placeholder p {
        margin: 0;
        font-weight: 600;
    }
    
    .proof-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s;
        color: white;
        gap: 8px;
    }
    
    .proof-image-link:hover .proof-overlay {
        opacity: 1;
    }
    
    .deposit-actions {
        display: flex;
        gap: 12px;
        padding-top: 20px;
        border-top: 2px solid #f3f4f6;
    }
    
    .btn-approve, .btn-reject {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    
    .btn-approve {
        background: #10b981;
        color: white;
    }
    
    .btn-approve:hover {
        background: #059669;
    }
    
    .btn-reject {
        background: #ef4444;
        color: white;
    }
    
    .btn-reject:hover {
        background: #dc2626;
    }
    
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
    }
    
    .modal-sm {
        max-width: 500px;
    }
    
    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h3 {
        margin: 0;
        font-size: 18px;
    }
    
    .btn-close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #6b7280;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
    
    .btn-close:hover {
        background: #f3f4f6;
    }
    
    .modal-body {
        padding: 24px;
    }
    
    .modal-body p {
        margin: 0 0 16px 0;
        color: #6b7280;
    }
    
    .form-group {
        margin-bottom: 0;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #374151;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    
    .btn-secondary, .btn-success, .btn-danger {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }
    
    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
    }
    
    .empty-state svg {
        color: #9ca3af;
        margin-bottom: 16px;
    }
    
    .empty-state h3 {
        color: #374151;
        margin: 0 0 8px 0;
    }
    
    .empty-state p {
        color: #6b7280;
        margin: 0;
    }
    
    @media (max-width: 768px) {
        .deposit-body {
            grid-template-columns: 1fr;
        }
        
        .deposit-header {
            flex-direction: column;
        }
        
        .deposit-user {
            text-align: left;
        }
    }
</style>

<script>
const basePath = '<?= Helper::url('') ?>';
let currentDepositId = null;

function showApproveModal(id) {
    currentDepositId = id;
    document.getElementById('approveModal').style.display = 'flex';
}

function showRejectModal(id) {
    currentDepositId = id;
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeModals() {
    document.getElementById('approveModal').style.display = 'none';
    document.getElementById('rejectModal').style.display = 'none';
    currentDepositId = null;
}

function approvePayment() {
    if (!currentDepositId) return;
    
    const notes = document.getElementById('approveNotes').value;
    const formData = new FormData();
    formData.append('action', 'approve');
    formData.append('id', currentDepositId);
    formData.append('notes', notes);
    formData.append('csrf_token', '<?= Helper::getCsrf() ?>');
    
    fetch(basePath + '/admin/paymentVerifications', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(' ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
}

function rejectPayment() {
    if (!currentDepositId) return;
    
    const notes = document.getElementById('rejectNotes').value;
    if (!notes.trim()) {
        alert('Please enter a reason for rejection');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'reject');
    formData.append('id', currentDepositId);
    formData.append('notes', notes);
    formData.append('csrf_token', '<?= Helper::getCsrf() ?>');
    
    fetch(basePath + '/admin/paymentVerifications', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(' ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
}

// Close on outside click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModals();
        }
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
