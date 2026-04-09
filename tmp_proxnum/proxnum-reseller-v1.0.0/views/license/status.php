<?php
use Core\Helper;
$title = 'License Order Status';
$basePath = Helper::url('');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Proxnum Reseller</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .status-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #1e293b;
            margin-bottom: 2rem;
            text-align: center;
        }

        .status-icon {
            text-align: center;
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }

        .order-details {
            background: #f8f9ff;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #64748b;
        }

        .detail-value {
            font-weight: 600;
            color: #1e293b;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.875rem;
        }

        .status-pending {
            background: #fef3c7;
            color: #92 400e;
        }

        .status-verifying {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .license-key {
            background: #10b981;
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            text-align: center;
        }

        .license-key-label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .license-key-value {
            font-family: 'Courier New', monospace;
            font-size: 1.25rem;
            font-weight: 700;
            word-break: break-all;
        }

        .copy-key-btn {
            margin-top: 1rem;
            padding: 0.75rem 2rem;
            background: white;
            color: #10b981;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .copy-key-btn:hover {
            transform: scale(1.05);
        }

        .info-message {
            text-align: center;
            color: #64748b;
            line-height: 1.6;
            margin: 1.5rem 0;
        }

        .back-btn {
            display: block;
            width: 100%;
            padding: 1rem;
            background: #667eea;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            margin-top: 2rem;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #5568d3;
            transform: scale(1.02);
        }
    </style>
</head>
<body>
    <div class="status-card">
        <h1>License Order Status</h1>

        <?php
        $statusIcons = [
            'pending' => '⏳',
            'verifying' => '🔍',
            'completed' => '✅',
            'cancelled' => '❌'
        ];

        $statusMessages = [
            'pending' => 'Awaiting payment submission',
            'verifying' => 'Payment is being verified by our team',
            'completed' => 'Your license is ready!',
            'cancelled' => 'This order has been cancelled'
        ];
        ?>

        <div class="status-icon">
            <?= $statusIcons[$order['status']] ?? '📋' ?>
        </div>

        <div class="order-details">
            <div class="detail-row">
                <span class="detail-label">Order Number</span>
                <span class="detail-value"><?= htmlspecialchars($order['order_number']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Plan</span>
                <span class="detail-value"><?= ucfirst($order['plan']) ?> License</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount</span>
                <span class="detail-value">$<?= number_format($order['amount'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?= htmlspecialchars($order['email']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Domain</span>
                <span class="detail-value"><?= htmlspecialchars($order['domain']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="status-badge status-<?= $order['status'] ?>">
                    <?= ucfirst($order['status']) ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created</span>
                <span class="detail-value"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></span>
            </div>
        </div>

        <?php if ($order['status'] === 'completed' && $order['license_key']): ?>
        <div class="license-key">
            <div class="license-key-label">Your License Key</div>
            <div class="license-key-value" id="licenseKey"><?= htmlspecialchars($order['license_key']) ?></div>
            <button onclick="copyKey()" class="copy-key-btn">📋 Copy License Key</button>
        </div>
        <?php endif; ?>

        <div class="info-message">
            <?= $statusMessages[$order['status']] ?? 'Unknown status' ?>
            
            <?php if ($order['status'] === 'verifying'): ?>
                <br><br>
                We typically process orders within 24 hours. You'll receive your license key at <strong><?= htmlspecialchars($order['email']) ?></strong> once verified.
            <?php endif; ?>
            
            <?php if ($order['status'] === 'completed'): ?>
                <br><br>
                Installation instructions have been sent to <strong><?= htmlspecialchars($order['email']) ?></strong>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= $basePath ?>/dashboard" class="back-btn">Back to Dashboard</a>
        <?php else: ?>
        <a href="<?= $basePath ?>/license/purchase" class="back-btn">Purchase Another License</a>
        <?php endif; ?>
    </div>

    <script>
    function copyKey() {
        const key = document.getElementById('licenseKey').textContent;
        navigator.clipboard.writeText(key).then(() => {
            alert('✅ License key copied to clipboard!');
        });
    }
    </script>
</body>
</html>
