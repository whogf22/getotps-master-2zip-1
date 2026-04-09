<?php
use Core\Helper;
$title = 'Ticket #' . htmlspecialchars($ticket['ticket_number']);
include __DIR__ . '/../layouts/header.php';
?>

<style>
    .ticket-detail-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #4a5568;
        text-decoration: none;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .back-link:hover {
        color: #2d3748;
    }
    
    .ticket-header {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 20px;
    }
    
    .ticket-title {
        font-size: 24px;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 16px;
    }
    
    .ticket-meta {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    
    .meta-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .meta-label {
        font-size: 12px;
        color: #718096;
        text-transform: uppercase;
        font-weight: 600;
    }
    
    .meta-value {
        font-size: 14px;
        color: #2d3748;
    }
    
    .ticket-status-form {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
    }
    
    .status-update {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .status-select {
        padding: 8px 12px;
        border: 1px solid #cbd5e0;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .btn-update {
        padding: 8px 16px;
        background: #4299e1;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
    }
    
    .btn-update:hover {
        background: #3182ce;
    }
    
    .messages-section {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 20px;
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 20px;
    }
    
    .message-item {
        padding: 16px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 16px;
    }
    
    .message-item.admin-reply {
        background: #ebf8ff;
        border-color: #90cdf4;
    }
    
    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    
    .message-author {
        font-weight: 600;
        color: #2d3748;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .admin-badge {
        background: #4299e1;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .message-time {
        font-size: 13px;
        color: #718096;
    }
    
    .message-body {
        color: #4a5568;
        line-height: 1.6;
        white-space: pre-wrap;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    
    .reply-form {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 24px;
    }
    
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 8px;
    }
    
    .form-textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #cbd5e0;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        resize: vertical;
        min-height: 120px;
    }
    
    .form-textarea:focus {
        outline: none;
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }
    
    .btn-reply {
        padding: 10px 24px;
        background: #48bb78;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }
    
    .btn-reply:hover {
        background: #38a169;
    }
    
    .alert {
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .alert-success {
        background: #c6f6d5;
        color: #22543d;
        border: 1px solid #9ae6b4;
    }
    
    .alert-error {
        background: #fed7d7;
        color: #742a2a;
        border: 1px solid #fc8181;
    }
    
    .badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-open { background: #c6f6d5; color: #22543d; }
    .badge-pending { background: #feebc8; color: #7c2d12; }
    .badge-resolved { background: #bee3f8; color: #2c5282; }
    .badge-closed { background: #e2e8f0; color: #2d3748; }
    .badge-low { background: #e6fffa; color: #234e52; }
    .badge-medium { background: #feebc8; color: #7c2d12; }
    .badge-high { background: #fed7d7; color: #742a2a; }
    .badge-urgent { background: #feb2b2; color: #742a2a; }
</style>

<div class="ticket-detail-container">
    <a href="<?= Helper::url('/admin/support') ?>" class="back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Tickets
    </a>
    
    <?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    
    <div class="ticket-header">
        <h1 class="ticket-title">
            Ticket #<?= htmlspecialchars($ticket['ticket_number']) ?>: <?= htmlspecialchars($ticket['subject']) ?>
        </h1>
        
        <div class="ticket-meta">
            <div class="meta-item">
                <span class="meta-label">Client</span>
                <span class="meta-value">
                    <?= htmlspecialchars($ticket['user_name']) ?>
                    <br>
                    <small><?= htmlspecialchars($ticket['user_email']) ?></small>
                </span>
            </div>
            
            <div class="meta-item">
                <span class="meta-label">Status</span>
                <span class="meta-value">
                    <span class="badge badge-<?= $ticket['status'] ?>"><?= ucfirst($ticket['status']) ?></span>
                </span>
            </div>
            
            <div class="meta-item">
                <span class="meta-label">Priority</span>
                <span class="meta-value">
                    <span class="badge badge-<?= $ticket['priority'] ?>"><?= ucfirst($ticket['priority']) ?></span>
                </span>
            </div>
            
            <div class="meta-item">
                <span class="meta-label">Created</span>
                <span class="meta-value"><?= date('M d, Y h:i A', strtotime($ticket['created_at'])) ?></span>
            </div>
            
            <?php if ($ticket['updated_at']): ?>
            <div class="meta-item">
                <span class="meta-label">Last Updated</span>
                <span class="meta-value"><?= Helper::timeAgo($ticket['updated_at']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="ticket-status-form">
            <form method="POST" class="status-update">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="update_status">
                <label class="meta-label">Update Status:</label>
                <select name="status" class="status-select">
                    <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="pending" <?= $ticket['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
                <button type="submit" class="btn-update">Update</button>
            </form>
        </div>
    </div>
    
    <div class="messages-section">
        <h2 class="section-title">Conversation</h2>
        
        <div class="message-item">
            <div class="message-header">
                <span class="message-author">
                    <?= htmlspecialchars($ticket['user_name']) ?>
                </span>
                <span class="message-time"><?= date('M d, Y h:i A', strtotime($ticket['created_at'])) ?></span>
            </div>
            <div class="message-body"><?= nl2br(htmlspecialchars($ticket['message'])) ?></div>
        </div>
        
        <?php foreach ($replies as $reply): ?>
        <div class="message-item <?= $reply['is_admin'] ? 'admin-reply' : '' ?>">
            <div class="message-header">
                <span class="message-author">
                    <?= htmlspecialchars($reply['user_name']) ?>
                    <?php if ($reply['is_admin']): ?>
                    <span class="admin-badge">ADMIN</span>
                    <?php endif; ?>
                </span>
                <span class="message-time"><?= date('M d, Y h:i A', strtotime($reply['created_at'])) ?></span>
            </div>
            <div class="message-body"><?= nl2br(htmlspecialchars($reply['message'])) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="reply-form">
        <h2 class="section-title">Add Reply</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="action" value="reply">
            
            <div class="form-group">
                <label class="form-label">Your Reply</label>
                <textarea name="message" class="form-textarea" required placeholder="Type your reply here..."></textarea>
            </div>
            
            <button type="submit" class="btn-reply">Send Reply</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
