<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>License Information</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <div class="info-section">
                        <h4>License Details</h4>
                        <table class="info-table">
                            <tr>
                                <td><strong>License Key:</strong></td>
                                <td><code><?= htmlspecialchars(substr($license_key, 0, 20)) ?>...<?= htmlspecialchars(substr($license_key, -10)) ?></code></td>
                            </tr>
                            <tr>
                                <td><strong>License Email:</strong></td>
                                <td><?= htmlspecialchars($license_email) ?></td>
                            </tr>
                            <?php if ($license_cache): ?>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge badge-success">✓ Active</span></td>
                            </tr>
                            <tr>
                                <td><strong>Last Verified:</strong></td>
                                <td><?= \Core\Helper::timeAgo($license_cache['verified_at']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Cache Expires:</strong></td>
                                <td><?= \Core\Helper::date($license_cache['expires_at']) ?></td>
                            </tr>
                            <?php 
                                $data = json_decode($license_cache['data'], true);
                                if ($data):
                            ?>
                            <tr>
                                <td><strong>License Type:</strong></td>
                                <td><?= htmlspecialchars($data['type'] ?? 'Unknown') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Licensed Domain:</strong></td>
                                <td><?= htmlspecialchars($data['domain'] ?? 'N/A') ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php else: ?>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge badge-warning">Not Verified</span></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="info-section">
                        <h4>System Information</h4>
                        <table class="info-table">
                            <tr>
                                <td><strong>PHP Version:</strong></td>
                                <td><?= $system_info['php_version'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>MySQL Version:</strong></td>
                                <td><?= $system_info['mysql_version'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Server Software:</strong></td>
                                <td><?= htmlspecialchars($system_info['server_software']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Current Domain:</strong></td>
                                <td><code><?= htmlspecialchars($system_info['current_domain']) ?></code></td>
                            </tr>
                            <tr>
                                <td><strong>Installation Path:</strong></td>
                                <td><small><?= htmlspecialchars($system_info['installation_path']) ?></small></td>
                            </tr>
                            <tr>
                                <td><strong>Disk Space Free:</strong></td>
                                <td><?= $system_info['disk_free'] ?> / <?= $system_info['disk_total'] ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="alert alert-info" style="margin-top: 30px;">
                <strong>ℹ️ License Information:</strong><br>
                Your license is automatically verified every 24 hours. A grace period of 72 hours is provided in case the verification server is temporarily unavailable. If you need to update your license, edit the configuration file located at <code>/config/app.php</code>.
            </div>
        </div>
    </div>
</div>

<style>
    .row {
        display: flex;
        gap: 20px;
    }
    .col-6 {
        flex: 0 0 48%;
    }
    .info-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 6px;
    }
    .info-section h4 {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #ddd;
    }
    .info-table {
        width: 100%;
    }
    .info-table tr {
        border-bottom: 1px solid #eee;
    }
    .info-table td {
        padding: 10px 5px;
    }
    .info-table td:first-child {
        width: 40%;
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
    .badge-warning {
        background: #ffc107;
        color: #000;
    }
    code {
        background: #f4f4f4;
        padding: 2px 8px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 13px;
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
