<!-- Admin Send Email View -->
<?php
use Core\Helper;
$title = 'Send Email';
include __DIR__ . '/../layouts/header.php';
?>

<style>
    .email-compose-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .recipient-options {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .recipient-option {
        margin-bottom: 15px;
    }
    
    .recipient-option label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    
    .recipient-option input[type="radio"] {
        margin-right: 8px;
    }
    
    .user-select-container {
        display: none;
        margin-top: 10px;
        padding: 15px;
        background: white;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .user-select-container.active {
        display: block;
    }
    
    .user-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
    }
    
    .user-item {
        padding: 8px;
        border-bottom: 1px solid #eee;
    }
    
    .user-item:last-child {
        border-bottom: none;
    }
    
    .user-item label {
        display: flex;
        align-items: center;
        cursor: pointer;
        font-weight: normal;
    }
    
    .user-item input[type="checkbox"] {
        margin-right: 10px;
    }
    
    .email-editor {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }
    
    .form-group textarea {
        width: 100%;
        min-height: 300px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-family: inherit;
        font-size: 14px;
        resize: vertical;
    }
    
    .btn-send {
        background: #28a745;
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-send:hover {
        background: #218838;
    }
    
    .btn-send:disabled {
        background: #6c757d;
        cursor: not-allowed;
    }
    
    .email-preview {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .select-all-controls {
        padding: 10px;
        background: #e9ecef;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    
    .help-text {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }
</style>

<div class="email-compose-container">
    <div class="dashboard-panel">
        <div class="panel-header">
            <h2>📧 Send Email</h2>
        </div>
        
        <div class="panel-body">
            <?php if (Helper::isDemo()): ?>
            <div class="alert alert-info" style="margin-bottom: 20px;">
                <strong>ℹ️ Demo Mode</strong><br>
                You're viewing this page in demo mode. You can explore all the features and interface, but email sending is disabled for demo accounts.
            </div>
            <?php endif; ?>
            
            <div id="emailResult" style="display: none;"></div>
            
            <form id="emailForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <!-- Recipient Options -->
                <div class="recipient-options">
                    <h3 style="margin-top: 0;">📬 Select Recipients</h3>
                    
                    <div class="recipient-option">
                        <label>
                            <input type="radio" name="send_type" value="all" <?= !isset($preSelectedUserId) ? 'checked' : '' ?>>
                            📢 Send to All Users
                        </label>
                        <div class="help-text">Send email to all registered users</div>
                    </div>
                    
                    <div class="recipient-option">
                        <label>
                            <input type="radio" name="send_type" value="clients_only">
                            👥 Send to All Clients Only
                        </label>
                        <div class="help-text">Send email to client users only (exclude admins)</div>
                    </div>
                    
                    <div class="recipient-option">
                        <label>
                            <input type="radio" name="send_type" value="single" <?= isset($preSelectedUserId) ? 'checked' : '' ?>>
                            👤 Send to Single User
                        </label>
                        <div id="singleUserSelect" class="user-select-container <?= isset($preSelectedUserId) ? 'active' : '' ?>">
                            <select name="user_id" id="userSelect">
                                <option value="">-- Select User --</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= $client['id'] ?>" <?= (isset($preSelectedUserId) && $preSelectedUserId == $client['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($client['name']) ?> 
                                        (<?= htmlspecialchars($client['email']) ?>) 
                                        - <?= ucfirst($client['role']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="recipient-option">
                        <label>
                            <input type="radio" name="send_type" value="multiple">
                            ✅ Send to Selected Users
                        </label>
                        <div id="multipleUserSelect" class="user-select-container">
                            <div class="select-all-controls">
                                <button type="button" class="btn" onclick="selectAllUsers()">Select All</button>
                                <button type="button" class="btn" onclick="deselectAllUsers()">Deselect All</button>
                                <span id="selectedCount" style="margin-left: 15px; font-weight: 500;">0 selected</span>
                            </div>
                            <div class="user-list">
                                <?php foreach ($clients as $client): ?>
                                    <div class="user-item">
                                        <label>
                                            <input type="checkbox" name="user_ids[]" value="<?= $client['id'] ?>" onchange="updateSelectedCount()">
                                            <span>
                                                <strong><?= htmlspecialchars($client['name']) ?></strong><br>
                                                <small><?= htmlspecialchars($client['email']) ?> • <?= ucfirst($client['role']) ?></small>
                                            </span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Email Composer -->
                <div class="email-editor">
                    <div class="form-group">
                        <label>✉️ Subject Line</label>
                        <input type="text" name="subject" id="emailSubject" placeholder="Enter email subject" required>
                    </div>
                    
                    <div class="form-group">
                        <label>📝 Message Body</label>
                        <textarea name="body" id="emailBody" placeholder="Enter your message here (HTML supported)" required></textarea>
                        <div class="help-text">
                            💡 Tip: You can use HTML tags for formatting (e.g., &lt;b&gt;bold&lt;/b&gt;, &lt;br&gt; for line breaks)
                        </div>
                    </div>
                    
                    <div style="text-align: center;">
                        <button type="submit" class="btn-send" id="sendBtn">
                            <span>📤</span>
                            <span id="btnText">Send Email</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle recipient type selection
document.querySelectorAll('input[name="send_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Hide all select containers
        document.querySelectorAll('.user-select-container').forEach(container => {
            container.classList.remove('active');
        });
        
        // Show relevant container
        if (this.value === 'single') {
            document.getElementById('singleUserSelect').classList.add('active');
        } else if (this.value === 'multiple') {
            document.getElementById('multipleUserSelect').classList.add('active');
        }
    });
});

// Select/Deselect all users
function selectAllUsers() {
    document.querySelectorAll('input[name="user_ids[]"]').forEach(cb => cb.checked = true);
    updateSelectedCount();
}

function deselectAllUsers() {
    document.querySelectorAll('input[name="user_ids[]"]').forEach(cb => cb.checked = false);
    updateSelectedCount();
}

function updateSelectedCount() {
    const count = document.querySelectorAll('input[name="user_ids[]"]:checked').length;
    document.getElementById('selectedCount').textContent = count + ' selected';
}

// Handle form submission
document.getElementById('emailForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const sendType = document.querySelector('input[name="send_type"]:checked').value;
    const subject = document.getElementById('emailSubject').value.trim();
    const body = document.getElementById('emailBody').value.trim();
    
    if (!subject || !body) {
        showAlert('Please fill in both subject and message', 'error');
        return;
    }
    
    // Validate based on send type
    if (sendType === 'single') {
        const userId = document.getElementById('userSelect').value;
        if (!userId) {
            showAlert('Please select a user', 'error');
            return;
        }
    } else if (sendType === 'multiple') {
        const selectedUsers = document.querySelectorAll('input[name="user_ids[]"]:checked');
        if (selectedUsers.length === 0) {
            showAlert('Please select at least one user', 'error');
            return;
        }
    }
    
    // Confirm before sending
    let confirmMsg = '';
    if (sendType === 'all') {
        confirmMsg = 'Send this email to ALL users?';
    } else if (sendType === 'clients_only') {
        confirmMsg = 'Send this email to all client users?';
    } else if (sendType === 'single') {
        const userName = document.getElementById('userSelect').selectedOptions[0].text;
        confirmMsg = `Send this email to ${userName}?`;
    } else if (sendType === 'multiple') {
        const count = document.querySelectorAll('input[name="user_ids[]"]:checked').length;
        confirmMsg = `Send this email to ${count} selected user(s)?`;
    }
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    // Disable button and show loading
    const sendBtn = document.getElementById('sendBtn');
    const btnText = document.getElementById('btnText');
    const originalText = btnText.textContent;
    sendBtn.disabled = true;
    btnText.textContent = 'Sending...';
    
    // Prepare form data
    const formData = new FormData(this);
    
    // Set action based on send type
    if (sendType === 'single') {
        formData.set('action', 'send_to_user');
    } else if (sendType === 'all') {
        formData.set('action', 'send_to_all');
        formData.set('role_filter', 'all');
    } else if (sendType === 'clients_only') {
        formData.set('action', 'send_to_all');
        formData.set('role_filter', 'client');
    } else if (sendType === 'multiple') {
        formData.set('action', 'send_to_multiple');
    }
    
    try {
        const response = await fetch('<?= Helper::url('/admin/sendEmail') ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            
            // Clear form after successful send
            if (sendType === 'single') {
                document.getElementById('userSelect').value = '';
            } else if (sendType === 'multiple') {
                deselectAllUsers();
            }
            document.getElementById('emailSubject').value = '';
            document.getElementById('emailBody').value = '';
            
            // Show detailed stats if available
            if (result.sent !== undefined) {
                showAlert(
                    `✅ Email sent successfully!<br>` +
                    `Sent: ${result.sent}<br>` +
                    `Failed: ${result.failed}<br>` +
                    `Total: ${result.total}`,
                    'success'
                );
            }
        } else {
            showAlert('❌ ' + result.message, 'error');
        }
    } catch (error) {
        showAlert('❌ An error occurred: ' + error.message, 'error');
    } finally {
        // Re-enable button
        sendBtn.disabled = false;
        btnText.textContent = originalText;
    }
});

function showAlert(message, type) {
    const resultDiv = document.getElementById('emailResult');
    resultDiv.className = 'alert alert-' + type;
    resultDiv.innerHTML = message;
    resultDiv.style.display = 'block';
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        resultDiv.style.display = 'none';
    }, 5000);
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
