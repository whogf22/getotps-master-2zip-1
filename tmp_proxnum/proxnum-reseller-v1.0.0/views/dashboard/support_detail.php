<?php
use Core\Helper;
$title = 'Ticket #' . $ticket['ticket_number'];
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <span class="header-badge">SUPPORT TICKET</span>
                <h1 class="page-title"><?= htmlspecialchars($ticket['ticket_number']) ?></h1>
                <p class="page-description"><?= htmlspecialchars($ticket['subject']) ?></p>
            </div>
            <a href="<?= Helper::url('/dashboard/support') ?>" class="btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back to Tickets</span>
            </a>
        </div>

        <div id="alert-container"></div>

        <!-- Ticket Details Card -->
        <div class="detail-card">
            <div class="card-header">
                <div class="header-left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <h2>Ticket Information</h2>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <span class="status-badge status-<?= $ticket['status'] ?>">
                        <?= ucfirst($ticket['status']) ?>
                    </span>
                    <span class="badge badge-<?= $ticket['priority'] ?>">
                        <?= ucfirst($ticket['priority']) ?> Priority
                    </span>
                </div>
            </div>

            <div class="card-body">
                <!-- Ticket Information -->
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            Your Name
                        </span>
                        <span class="info-value"><?= htmlspecialchars($user['name']) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            Created
                        </span>
                        <span class="info-value"><?= date('M j, Y \a\t g:i A', strtotime($ticket['created_at'])) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                            Priority Level
                        </span>
                        <span class="info-value">
                            <span class="badge badge-<?= $ticket['priority'] ?>"><?= ucfirst($ticket['priority']) ?></span>
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Current Status
                        </span>
                        <span class="info-value">
                            <span class="status-badge status-<?= $ticket['status'] ?>"><?= ucfirst($ticket['status']) ?></span>
                        </span>
                    </div>

                    <?php if ($ticket['updated_at']): ?>
                    <div class="info-item">
                        <span class="info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                            </svg>
                            Last Updated
                        </span>
                        <span class="info-value"><?= Helper::timeAgo($ticket['updated_at']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Original Message -->
                <div class="message-section">
                    <h3 class="message-title">Original Message</h3>
                    <div class="message-content">
                        <?= nl2br(htmlspecialchars($ticket['message'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Replies Section -->
        <?php if (!empty($replies)): ?>
        <div class="detail-card">
            <div class="card-header">
                <div class="header-left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <h2>Replies (<?= count($replies) ?>)</h2>
                </div>
            </div>

            <div class="card-body">
                <div class="replies-list">
                    <?php foreach ($replies as $reply): ?>
                    <div class="reply-item <?= $reply['is_admin'] ? 'admin-reply' : 'user-reply' ?>">
                        <div class="reply-header">
                            <div class="reply-author">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <strong><?= htmlspecialchars($reply['user_name']) ?></strong>
                                <?php if ($reply['is_admin']): ?>
                                    <span class="admin-badge">Admin</span>
                                <?php endif; ?>
                            </div>
                            <span class="reply-time"><?= Helper::timeAgo($reply['created_at']) ?></span>
                        </div>
                        <div class="reply-content">
                            <?= nl2br(htmlspecialchars($reply['message'])) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Add Reply Section (only if ticket is open or pending) -->
        <?php if (in_array($ticket['status'], ['open', 'pending'])): ?>
        <div class="detail-card">
            <div class="card-header">
                <div class="header-left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <h2>Add Reply</h2>
                </div>
            </div>

            <div class="card-body">
                <form id="replyForm">
                    <div class="form-group">
                        <label for="replyMessage">Your Message</label>
                        <textarea id="replyMessage" rows="5" placeholder="Type your reply here..." required></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="detail-card">
            <div class="card-body text-center" style="padding: 2rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity: 0.5; margin-bottom: 1rem;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <p style="color: var(--neutral-600); margin: 0;">This ticket is <?= $ticket['status'] ?> and cannot receive new replies.</p>
            </div>
        </div>
        <?php endif; ?>
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
        color: var(--accent-teal);
        background: rgba(20, 134, 109, 0.1);
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--neutral-900);
        margin: 0;
        letter-spacing: -0.02em;
    }

    .page-description {
        color: var(--neutral-600);
        margin: 0.5rem 0 0;
        font-size: 1.1rem;
    }

    .btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: white;
        color: var(--neutral-600);
        border: 2px solid var(--neutral-300);
        border-radius: var(--radius-lg);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-outline:hover {
        border-color: var(--primary-deep);
        color: var(--primary-deep);
        transform: translateY(-2px);
    }

    .detail-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 2rem;
        border-bottom: 2px solid var(--neutral-200);
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-left h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--neutral-900);
    }

    .header-left svg {
        color: var(--accent-teal);
    }

    .card-body {
        padding: 2rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--neutral-600);
    }

    .info-label svg {
        flex-shrink: 0;
    }

    .info-value {
        font-weight: 600;
        color: var(--neutral-900);
        font-size: 1rem;
    }

    .message-section {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid var(--neutral-200);
    }

    .message-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--neutral-900);
        margin-bottom: 1rem;
    }

    .message-content {
        background: var(--neutral-100);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        color: var(--neutral-800);
        line-height: 1.7;
        border-left: 4px solid var(--accent-teal);
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: pre-wrap;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.813rem;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .badge-low {
        background: rgba(99, 102, 241, 0.1);
        color: #4338ca;
    }

    .badge-medium {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
    }

    .badge-high {
        background: rgba(249, 115, 22, 0.1);
        color: #ea580c;
    }

    .badge-urgent {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    .status-badge {
        display: inline-flex;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .status-open {
        background: rgba(34, 197, 94, 0.1);
        color: #16a34a;
    }

    .status-pending {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
    }

    .status-resolved {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
    }

    .status-closed {
        background: rgba(107, 114, 128, 0.1);
        color: #6b7280;
    }

    .replies-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .reply-item {
        background: var(--neutral-100);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        border-left: 4px solid var(--neutral-300);
    }

    .reply-item.admin-reply {
        background: rgba(20, 134, 109, 0.05);
        border-left-color: var(--accent-teal);
    }

    .reply-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .reply-author {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--neutral-900);
    }

    .admin-badge {
        background: var(--accent-teal);
        color: white;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .reply-time {
        font-size: 0.875rem;
        color: var(--neutral-600);
    }

    .reply-content {
        color: var(--neutral-800);
        line-height: 1.7;
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: pre-wrap;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: var(--neutral-900);
        margin-bottom: 0.5rem;
    }

    .form-group textarea {
        width: 100%;
        padding: 1rem;
        border: 2px solid var(--neutral-300);
        border-radius: var(--radius-lg);
        font-family: inherit;
        font-size: 1rem;
        color: var(--neutral-900);
        transition: all 0.2s ease;
        resize: vertical;
    }

    .form-group textarea:focus {
        outline: none;
        border-color: var(--accent-teal);
        box-shadow: 0 0 0 3px rgba(20, 134, 109, 0.1);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 2rem;
        background: linear-gradient(135deg, var(--accent-teal) 0%, #0f6654 100%);
        color: white;
        border: none;
        border-radius: var(--radius-lg);
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(20, 134, 109, 0.3);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .text-center {
        text-align: center;
    }

    #alert-container {
        margin-bottom: 1.5rem;
    }

    .alert {
        padding: 1rem 1.5rem;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
        animation: slideIn 0.3s ease;
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

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .card-body {
            padding: 1.5rem;
        }
    }
</style>

<script>
const basePath = '<?= Helper::url('') ?>';
const ticketId = <?= $ticket['id'] ?>;

document.getElementById('replyForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const textarea = document.getElementById('replyMessage');
    const message = textarea.value.trim();
    
    if (!message) {
        showAlert('Please enter a message', 'error');
        return;
    }
    
    const originalHtml = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span>Sending...';
    
    fetch(basePath + '/dashboard/support', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            action: 'reply',
            ticket_id: ticketId,
            message: message
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert('Reply added successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
            showAlert(data.message || 'Failed to add reply', 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
        showAlert('Network error. Please try again.', 'error');
    });
});

function showAlert(message, type) {
    const container = document.getElementById('alert-container');
    const icon = type === 'success' 
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>' 
        : '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
    
    container.innerHTML = `
        <div class="alert alert-${type}">
            ${icon}
            ${message}
        </div>
    `;
    setTimeout(() => container.innerHTML = '', 5000);
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
