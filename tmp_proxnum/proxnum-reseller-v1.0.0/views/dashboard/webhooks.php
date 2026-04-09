<?php
use Core\Helper;
$title = 'Webhooks';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Webhooks</h2>
        <button onclick="createWebhook()" class="btn">🔗 Create Webhook</button>
    </div>
    <div class="panel-body">
        <div style="padding: 20px; background: #e7f3ff; border-radius: 6px; margin-bottom: 20px;">
            <strong>💡 About Webhooks:</strong> Webhooks allow you to receive real-time notifications when events occur (e.g., activation completed, code received).
        </div>
        
        <h3 style="margin-bottom: 15px;">Your Webhooks</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>URL</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($webhooks as $webhook): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($webhook['event']) ?></strong></td>
                    <td><code style="font-size: 12px;"><?= htmlspecialchars($webhook['url']) ?></code></td>
                    <td>
                        <label style="display: flex; align-items: center; gap: 5px;">
                            <input type="checkbox" 
                                   <?= $webhook['enabled'] ? 'checked' : '' ?>
                                   onchange="toggleWebhook(<?= $webhook['id'] ?>, this.checked)">
                            <span><?= $webhook['enabled'] ? 'Active' : 'Disabled' ?></span>
                        </label>
                    </td>
                    <td><?= Helper::timeAgo($webhook['created_at']) ?></td>
                    <td>
                        <a href="#" class="btn-link" onclick="deleteWebhook(<?= $webhook['id'] ?>); return false;" style="color: #ef4444;">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($webhooks)): ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 40px;">
                        No webhooks configured. Create one to receive real-time notifications!
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <h3 style="margin-top: 30px; margin-bottom: 15px;">Recent Webhook Logs</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Status</th>
                    <th>Response</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($logs, 0, 10) as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['event']) ?></td>
                    <td><span class="badge badge-<?= $log['status_code'] == 200 ? 'active' : 'inactive' ?>"><?= $log['status_code'] ?></span></td>
                    <td><small><?= htmlspecialchars(substr($log['response'] ?? '', 0, 50)) ?></small></td>
                    <td><?= Helper::timeAgo($log['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px; color: #999;">No webhook activity yet</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 6px;">
            <h3 style="margin-bottom: 15px;">Available Events</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 5px 0;">🎯 <strong>activation.created</strong> - New activation started</li>
                <li style="padding: 5px 0;"> <strong>activation.completed</strong> - Activation received code</li>
                <li style="padding: 5px 0;">❌ <strong>activation.cancelled</strong> - Activation cancelled</li>
                <li style="padding: 5px 0;">📞 <strong>rental.created</strong> - New rental started</li>
                <li style="padding: 5px 0;"> <strong>balance.low</strong> - Balance below threshold</li>
            </ul>
        </div>
    </div>
</div>

<script>
const basePath = '<?= $basePath ?>';

function createWebhook() {
    const event = prompt('Event name (e.g., activation.completed):');
    if (!event) return;
    
    const url = prompt('Webhook URL (must be HTTPS):');
    if (!url) return;
    
    if (!url.startsWith('https://')) {
        alert('Webhook URL must use HTTPS for security');
        return;
    }
    
    fetch(basePath + '/dashboard/webhooks', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'create', event, url})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Webhook created!\n\nWebhook Secret: ' + data.secret + '\n\n⚠️ Save this secret to verify webhook signatures');
            location.reload();
        } else {
            alert(data.message || 'Failed to create webhook');
        }
    });
}

function toggleWebhook(id, enabled) {
    fetch(basePath + '/dashboard/webhooks', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'toggle', webhook_id: id, enabled: enabled ? 1 : 0})
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Failed to update webhook');
            location.reload();
        }
    });
}

function deleteWebhook(id) {
    if (!confirm('Delete this webhook?')) return;
    
    fetch(basePath + '/dashboard/webhooks', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', webhook_id: id})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to delete webhook');
        }
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
