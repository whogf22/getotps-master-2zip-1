<?php
use Core\Helper;
$title = 'API Keys';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>API Keys</h2>
        <button onclick="generateKey()" class="btn"> Generate New Key</button>
    </div>
    <div class="panel-body">
        <div style="padding: 20px; background: #fff3cd; border-radius: 6px; margin-bottom: 20px;">
            <strong>⚠️ Security Notice:</strong> Keep your API keys secret. Never share them publicly or commit them to version control.
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>API Key</th>
                    <th>Last Used</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($api_keys as $key): ?>
                <?php
                    $fullKey = htmlspecialchars($key['api_key']);
                    $maskedKey = strlen($fullKey) > 12 ? substr($fullKey, 0, 8) . '...' . substr($fullKey, -8) : $fullKey;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($key['name']) ?></strong></td>
                    <td>
                        <code class="masked-api-key" data-full-key="<?= $fullKey ?>" style="cursor: pointer; user-select: none;" onclick="toggleKey(this)" title="Click to reveal"><?= $maskedKey ?></code>
                        <button onclick="copyKey('<?= $fullKey ?>'); event.stopPropagation();" style="margin-left: 8px; padding: 4px 8px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;" title="Copy to clipboard">📋 Copy</button>
                    </td>
                    <td><?= $key['last_used_at'] ? Helper::timeAgo($key['last_used_at']) : 'Never' ?></td>
                    <td><?= Helper::timeAgo($key['created_at']) ?></td>
                    <td>
                        <a href="#" class="btn-link" onclick="deleteKey(<?= $key['id'] ?>); return false;" style="color: #ef4444;">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($api_keys)): ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 40px;">
                        No API keys yet. Generate one to get started!
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 6px;">
            <h3 style="margin-bottom: 15px;">API Documentation</h3>
            <p style="margin-bottom: 10px;">Use your API key to integrate with our services programmatically.</p>
            <a href="<?= Helper::url('/dashboard/apiDocs') ?>" class="btn-link">View API Documentation →</a>
        </div>
    </div>
</div>

<script>
const basePath = '<?= $basePath ?>';

function generateKey() {
    const name = prompt('Enter a name for this API key (e.g., "Production Server"):');
    if (!name) return;
    
    fetch(basePath + '/dashboard/apiKeys', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'generate', name})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Create a modal-style alert
            const keyDisplay = data.api_key;
            alert(' API Key Generated Successfully!\n\nKey: ' + keyDisplay + '\n\n⚠️ Important: Copy this key now. For security, it will be masked in the table.');
            location.reload();
        } else {
            alert(data.message || 'Failed to generate key');
        }
    });
}

function deleteKey(id) {
    if (!confirm('Delete this API key? This action cannot be undone.')) return;
    
    fetch(basePath + '/dashboard/apiKeys', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', key_id: id})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to delete key');
        }
    });
}

function toggleKey(element) {
    const fullKey = element.getAttribute('data-full-key');
    const currentText = element.textContent;
    
    if (currentText.includes('...')) {
        element.textContent = fullKey;
        element.title = 'Click to hide';
    } else {
        const masked = fullKey.length > 12 ? fullKey.substring(0, 8) + '...' + fullKey.substring(fullKey.length - 8) : fullKey;
        element.textContent = masked;
        element.title = 'Click to reveal';
    }
}

function copyKey(key) {
    navigator.clipboard.writeText(key).then(() => {
        alert(' API key copied to clipboard!');
    }).catch(() => {
        prompt('Copy this API key:', key);
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
