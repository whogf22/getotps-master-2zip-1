<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <?php if (isset($message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3>System Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="system_settings">
                
                <div class="form-group">
                    <label>Site Name:</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name']) ?>" class="form-control" required>
                    <small class="form-text">Displayed in the header and page title</small>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="allow_registration" <?= $settings['allow_registration'] == '1' ? 'checked' : '' ?>>
                        Allow Client Registration
                    </label>
                    <small class="form-text">If disabled, only administrators can create client accounts</small>
                </div>
                
                <div class="form-group">
                    <label>Minimum Balance Required:</label>
                    <input type="number" name="min_balance" value="<?= htmlspecialchars($settings['min_balance']) ?>" step="0.01" class="form-control" required>
                    <small class="form-text">Minimum balance clients should maintain (for warnings)</small>
                </div>
                
                <div class="form-group">
                    <label>Price Multiplier:</label>
                    <input type="number" name="price_multiplier" value="<?= htmlspecialchars($settings['price_multiplier']) ?>" step="0.01" min="0.01" class="form-control" required>
                    <small class="form-text">Multiply all API prices by this constant (e.g., 2 means 2× the API price). All purchase costs will be charged at this rate.</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>

    <!-- Email/SMTP Configuration -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3>Email Configuration (SMTP)</h3>
            <?php if (!\Core\Helper::isDemo()): ?>
            <button type="button" class="btn btn-sm btn-info" onclick="showTestEmailModal()">📧 Test Email</button>
            <?php else: ?>
            <button type="button" class="btn btn-sm btn-secondary" disabled title="Demo accounts cannot send test emails">📧 Test Email (Disabled)</button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php 
            // Check if email settings appear to be unconfigured (all empty)
            $notConfigured = empty($settings['mail_smtp_host']) && empty($settings['mail_from_address']);
            if ($notConfigured): 
            ?>
            <div class="alert alert-warning">
                <strong>⚠️ Email Settings Not Configured</strong><br>
                The email configuration settings haven't been set up yet. If you haven't imported the database settings, please run:
                <pre>mysql -u root proxnum_reseller &lt; database/add_email_settings.sql</pre>
                Or import via phpMyAdmin: <code>database/add_email_settings.sql</code>
            </div>
            <?php endif; ?>
            
            <?php if (\Core\Helper::isDemo()): ?>
            <div class="alert alert-warning">
                <strong>⚠️ Demo Account</strong><br>
                Email settings are view-only in demo mode. You cannot modify SMTP configuration or send test emails.
            </div>
            <?php endif; ?>
            
            <form method="POST" id="emailSettingsForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="email_settings">
                
                <div class="alert alert-info">
                    <strong>ℹ️ Email Configuration:</strong> Configure SMTP settings to send emails from your own domain. 
                    If SMTP is disabled, the system will use PHP mail() function.
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="mail_smtp_enabled" <?= !empty($settings['mail_smtp_enabled']) && $settings['mail_smtp_enabled'] == '1' ? 'checked' : '' ?>>
                        Enable SMTP Email
                    </label>
                    <small class="form-text">Use SMTP server for sending emails</small>
                </div>

                <div class="form-group">
                    <label>From Email Address:</label>
                    <input type="email" name="mail_from_address" value="<?= htmlspecialchars($settings['mail_from_address'] ?? '') ?>" class="form-control" required>
                    <small class="form-text">Email address that emails will be sent from (e.g., noreply@yourdomain.com)</small>
                </div>

                <div class="form-group">
                    <label>From Name:</label>
                    <input type="text" name="mail_from_name" value="<?= htmlspecialchars($settings['mail_from_name'] ?? '') ?>" class="form-control" required>
                    <small class="form-text">Name that will appear as sender</small>
                </div>
                
                <hr style="margin: 30px 0;">
                <h4 style="margin-bottom: 20px;">SMTP Server Settings</h4>
                
                <div class="form-group">
                    <label>SMTP Host:</label>
                    <input type="text" name="mail_smtp_host" value="<?= htmlspecialchars($settings['mail_smtp_host'] ?? '') ?>" class="form-control" placeholder="smtp.gmail.com">
                    <small class="form-text">SMTP server hostname (e.g., smtp.gmail.com, smtp.mailgun.org)</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="display: inline-block; width: 48%; margin-right: 2%;">
                        <label>SMTP Port:</label>
                        <input type="number" name="mail_smtp_port" value="<?= htmlspecialchars($settings['mail_smtp_port'] ?? '587') ?>" class="form-control">
                        <small class="form-text">Common: 587 (TLS) or 465 (SSL)</small>
                    </div>
                    
                    <div class="form-group" style="display: inline-block; width: 48%;">
                        <label>Encryption:</label>
                        <select name="mail_smtp_encryption" class="form-control">
                            <option value="tls" <?= (isset($settings['mail_smtp_encryption']) && $settings['mail_smtp_encryption'] == 'tls') ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= (isset($settings['mail_smtp_encryption']) && $settings['mail_smtp_encryption'] == 'ssl') ? 'selected' : '' ?>>SSL</option>
                            <option value="" <?= empty($settings['mail_smtp_encryption']) ? 'selected' : '' ?>>None</option>
                        </select>
                        <small class="form-text">TLS recommended for port 587</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="mail_smtp_auth" <?= empty($settings['mail_smtp_auth']) || $settings['mail_smtp_auth'] == '1' ? 'checked' : '' ?>>
                        SMTP Authentication Required
                    </label>
                    <small class="form-text">Leave checked if your SMTP server requires username/password</small>
                </div>
                
                <div class="form-group">
                    <label>SMTP Username:</label>
                    <input type="text" name="mail_smtp_username" value="<?= htmlspecialchars($settings['mail_smtp_username'] ?? '') ?>" class="form-control" autocomplete="off">
                    <small class="form-text">Usually your email address</small>
                </div>
                
                <div class="form-group">
                    <label>SMTP Password:</label>
                    <input type="password" name="mail_smtp_password" value="<?= htmlspecialchars($settings['mail_smtp_password'] ?? '') ?>" class="form-control" autocomplete="new-password" placeholder="<?= !empty($settings['mail_smtp_password']) ? '••••••••' : '' ?>">
                    <small class="form-text">Your email password or app-specific password</small>
                </div>

                <hr style="margin: 30px 0;">
                <h4 style="margin-bottom: 20px;">Email Notifications</h4>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="mail_signup_enabled" <?= !empty($settings['mail_signup_enabled']) && $settings['mail_signup_enabled'] == '1' ? 'checked' : '' ?>>
                        Send Welcome Email on Signup
                    </label>
                    <small class="form-text">Automatically send welcome email to new users when they register</small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="mail_low_balance_enabled" <?= !empty($settings['mail_low_balance_enabled']) && $settings['mail_low_balance_enabled'] == '1' ? 'checked' : '' ?>>
                        Send Low Balance Alerts
                    </label>
                    <small class="form-text">Send email notification when client balance drops below minimum threshold</small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="mail_activation_enabled" <?= !empty($settings['mail_activation_enabled']) && $settings['mail_activation_enabled'] == '1' ? 'checked' : '' ?>>
                        Send Activation Completed Notifications
                    </label>
                    <small class="form-text">Send email when SMS activation code is received</small>
                </div>
                
                <div class="alert alert-info" style="margin-top: 20px;">
                    <strong>ℹ️ Email Notifications:</strong><br>
                    All email notifications are disabled by default. Enable the ones you want to send to your clients. Make sure SMTP is configured above before enabling notifications.
                </div>
                
                <?php if (!\Core\Helper::isDemo()): ?>
                <button type="submit" class="btn btn-primary">Save Email Settings</button>
                <?php else: ?>
                <button type="button" class="btn btn-secondary" disabled>Save Email Settings (Demo Mode)</button>
                <?php endif; ?>
            </form>

            <div class="alert alert-info" style="margin-top: 30px;">
                <strong>💡 Popular SMTP Providers:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    <li><strong>Gmail:</strong> smtp.gmail.com, Port 587 (TLS). Use App Password if 2FA enabled.</li>
                    <li><strong>Mailgun:</strong> smtp.mailgun.org, Port 587 (TLS)</li>
                    <li><strong>SendGrid:</strong> smtp.sendgrid.net, Port 587 (TLS), Username: apikey</li>
                    <li><strong>Outlook:</strong> smtp-mail.outlook.com, Port 587 (TLS)</li>
                    <li><strong>Yahoo:</strong> smtp.mail.yahoo.com, Port 587 (TLS)</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Configuration Information</h3>
        </div>
        <div class="card-body">
            <table class="info-table">
                <tr>
                    <td><strong>Proxnum API URL:</strong></td>
                    <td><code><?= defined('PROXNUM_API_URL') ? PROXNUM_API_URL : 'Not configured' ?></code></td>
                </tr>
                <tr>
                    <td><strong>API Key Status:</strong></td>
                    <td>
                        <?php if (defined('PROXNUM_API_KEY') && !empty(PROXNUM_API_KEY)): ?>
                            <span class="badge badge-success">✓ Configured</span>
                        <?php else: ?>
                            <span class="badge badge-danger">✗ Not configured</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>License Key Status:</strong></td>
                    <td>
                        <?php if (defined('LICENSE_KEY') && !empty(LICENSE_KEY)): ?>
                            <span class="badge badge-success">✓ Configured</span>
                        <?php else: ?>
                            <span class="badge badge-danger">✗ Not configured</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Configuration File:</strong></td>
                    <td><code>/config/app.php</code></td>
                </tr>
            </table>

            <div class="alert alert-info" style="margin-top: 20px;">
                <strong>ℹ️ To modify API and license settings:</strong><br>
                Edit the configuration file at <code>/config/app.php</code> and update the following constants:
                <ul style="margin: 10px 0 0 20px;">
                    <li><code>PROXNUM_API_URL</code> - Proxnum API endpoint</li>
                    <li><code>PROXNUM_API_KEY</code> - Your Proxnum API key</li>
                    <li><code>LICENSE_KEY</code> - Your reseller license key</li>
                    <li><code>LICENSE_EMAIL</code> - Email associated with your license</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .alert {
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
    }
    .alert-success {
        background: #d4edda;
        border-left: 4px solid #28a745;
        color: #155724;
    }
    .alert-info {
        background: #d1ecf1;
        border-left: 4px solid #17a2b8;
        color: #0c5460;
    }
    .alert-warning {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        color: #856404;
    }
    .alert-danger {
        background: #f8d7da;
        border-left: 4px solid #dc3545;
        color: #721c24;
    }
    .alert pre {
        background: #fff !important;
        padding: 10px;
        margin-top: 10px;
        border-radius: 4px;
        overflow-x: auto;
        font-size: 13px;
        border: 1px solid #ddd;
    }
    .alert ul {
        list-style: none;
    }
    .form-group {
        margin-bottom: 25px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    .form-control {
        width: 100%;
        max-width: 500px;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    .form-text {
        display: block;
        margin-top: 5px;
        color: #666;
        font-size: 13px;
    }
    .info-table {
        width: 100%;
    }
    .info-table tr {
        border-bottom: 1px solid #eee;
    }
    .info-table td {
        padding: 12px 10px;
    }
    .info-table td:first-child {
        width: 250px;
        font-weight: 500;
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
    code {
        background: #f4f4f4;
        padding: 2px 8px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 13px;
    }
    
    /* Test Email Modal Styles */
    .test-email-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.2s;
    }
    .test-email-modal.show {
        display: flex;
    }
    .test-email-modal-content {
        background: white;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideDown 0.3s;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }
    .test-email-modal-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .test-email-modal-header h3 {
        margin: 0;
        font-size: 18px;
    }
    .test-email-modal-close {
        background: transparent;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #999;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .test-email-modal-close:hover {
        color: #333;
    }
    .test-email-modal-body {
        padding: 20px;
    }
    .test-result {
        margin-top: 15px;
        padding: 15px;
        border-radius: 6px;
        display: none;
    }
    .test-result.success {
        background: #d4edda;
        border-left: 4px solid #28a745;
        color: #155724;
        display: block;
    }
    .test-result.error {
        background: #f8d7da;
        border-left: 4px solid #dc3545;
        color: #721c24;
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<!-- Test Email Modal -->
<div id="testEmailModal" class="test-email-modal">
    <div class="test-email-modal-content">
        <div class="test-email-modal-header">
            <h3>📧 Test Email Configuration</h3>
            <button class="test-email-modal-close" onclick="closeTestEmailModal()">&times;</button>
        </div>
        <div class="test-email-modal-body">
            <p>Send a test email to verify your SMTP configuration.</p>
            
            <div class="form-group">
                <label>Test Email Address:</label>
                <input type="email" id="testEmailAddress" class="form-control" placeholder="your-email@example.com" required>
                <small class="form-text">Enter the email address where you want to receive the test email</small>
            </div>
            
            <button type="button" class="btn btn-primary" onclick="sendTestEmail()">Send Test Email</button>
            
            <div id="testResult" class="test-result"></div>
        </div>
    </div>
</div>

<script>
function showTestEmailModal() {
    document.getElementById('testEmailModal').classList.add('show');
}

function closeTestEmailModal() {
    document.getElementById('testEmailModal').classList.remove('show');
    document.getElementById('testResult').className = 'test-result';
    document.getElementById('testResult').innerHTML = '';
}

// Close modal on background click
document.getElementById('testEmailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTestEmailModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTestEmailModal();
    }
});

function sendTestEmail() {
    const emailAddress = document.getElementById('testEmailAddress').value.trim();
    const resultDiv = document.getElementById('testResult');
    
    if (!emailAddress) {
        resultDiv.className = 'test-result error';
        resultDiv.innerHTML = '⚠️ Please enter an email address.';
        return;
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailAddress)) {
        resultDiv.className = 'test-result error';
        resultDiv.innerHTML = '⚠️ Please enter a valid email address.';
        return;
    }
    
    // Show loading
    resultDiv.className = 'test-result';
    resultDiv.style.display = 'block';
    resultDiv.style.background = '#f0f0f0';
    resultDiv.style.color = '#333';
    resultDiv.innerHTML = '⏳ Sending test email...';
    
    // Get form data from the email settings form
    const form = document.getElementById('emailSettingsForm');
    const formData = new FormData(form);
    formData.append('action', 'test_email');
    formData.append('test_email_address', emailAddress);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.className = 'test-result success';
            resultDiv.innerHTML = '✅ ' + data.message;
        } else {
            resultDiv.className = 'test-result error';
            resultDiv.innerHTML = '❌ ' + data.message;
        }
    })
    .catch(error => {
        resultDiv.className = 'test-result error';
        resultDiv.innerHTML = '❌ Failed to send test email: ' + error.message;
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
