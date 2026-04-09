<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Rental Details</h3>
                <a href="<?= \Core\Helper::url('/dashboard/rentals') ?>" class="btn btn-secondary">← Back to Rentals</a>
            </div>
        </div>
        <div class="card-body">
            <div class="rental-info">
                <div class="info-row">
                    <strong>Phone Number:</strong>
                    <span class="phone-number"><?= htmlspecialchars($rental['phone']) ?></span>
                </div>
                <div class="info-row">
                    <strong>Service:</strong>
                    <span><?= htmlspecialchars($rental['service']) ?></span>
                </div>
                <div class="info-row">
                    <strong>Country:</strong>
                    <span><?= htmlspecialchars(\Core\Helper::getCountryName($rental['country'])) ?></span>
                </div>
                <div class="info-row">
                    <strong>Status:</strong>
                    <span class="badge badge-<?= $rental['status'] === 'active' ? 'success' : 'danger' ?>">
                        <?= ucfirst($rental['status']) ?>
                    </span>
                </div>
                <div class="info-row">
                    <strong>Expires:</strong>
                    <span><?= \Core\Helper::date($rental['expires_at'], 'Y-m-d H:i:s') ?></span>
                </div>
                <div class="info-row">
                    <strong>Cost:</strong>
                    <span><?= \Core\Helper::money($rental['cost']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Messages</h3>
                <button class="btn btn-primary btn-sm" onclick="location.reload()">🔄 Refresh</button>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($messages)): ?>
            <div class="messages-list">
                <?php foreach ($messages as $message): ?>
                <div class="message-item">
                    <div class="message-header">
                        <strong>From: <?= htmlspecialchars($message['sender']) ?></strong>
                        <span class="message-time"><?= \Core\Helper::timeAgo($message['received_at']) ?></span>
                    </div>
                    <div class="message-body">
                        <?= htmlspecialchars($message['message']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div style="font-size: 48px; margin-bottom: 10px;">💬</div>
                <p>No messages received yet</p>
                <small class="text-muted">Messages will appear here when they arrive</small>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .rental-info {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 6px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .phone-number {
        font-size: 20px;
        font-weight: bold;
        color: #667eea;
    }
    .badge {
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    .badge-success {
        background: #28a745;
        color: white;
    }
    .badge-danger {
        background: #dc3545;
        color: white;
    }
    .messages-list {
        max-height: 500px;
        overflow-y: auto;
    }
    .message-item {
        background: white;
        border: 1px solid #eee;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 15px;
    }
    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .message-time {
        font-size: 12px;
        color: #666;
    }
    .message-body {
        line-height: 1.6;
        color: #333;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
    }
</style>

<script>
// Auto-refresh messages every 30 seconds if rental is active
<?php if ($rental['status'] === 'active'): ?>
setInterval(() => {
    location.reload();
}, 30000);
<?php endif; ?>
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
