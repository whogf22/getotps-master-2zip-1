<?php
use Core\Helper;
$title = 'Purchase License';
$basePath = Helper::url('');
$csrf_token = Helper::getCsrf();
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
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header p {
            font-size: 1.25rem;
            opacity: 0.95;
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .plan-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            border: 3px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
        }

        .plan-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        }

        .plan-card.recommended::before {
            content: '⭐ RECOMMENDED';
            position: absolute;
            top: 20px;
            right: -35px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 0.5rem 3rem;
            font-size: 0.75rem;
            font-weight: 700;
            transform: rotate(45deg);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .plan-name {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .plan-price {
            font-size: 3rem;
            font-weight: 800;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .plan-price span {
            font-size: 1.25rem;
            color: #64748b;
            font-weight: 500;
        }

        .plan-duration {
            color: #10b981;
            font-weight: 600;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .plan-features {
            list-style: none;
            margin-bottom: 2rem;
        }

        .plan-features li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .plan-features li::before {
            content: '✓';
            color: #10b981;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .select-plan-btn {
            width: 100%;
            padding: 1rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .select-plan-btn:hover {
            background: #5568d3;
            transform: scale(1.02);
        }

        .plan-card.selected .select-plan-btn {
            background: #10b981;
        }

        .purchase-section {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            display: none;
        }

        .purchase-section.active {
            display: block;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .payment-method {
            padding: 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-method:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .payment-method.selected {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }

        .payment-method-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .payment-method-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .submit-btn {
            width: 100%;
            padding: 1.25rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.125rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        #paymentSection {
            margin-top: 2rem;
            padding: 2rem;
            background: #f8f9ff;
            border-radius: 12px;
            display: none;
        }

        #paymentSection.active {
            display: block;
        }

        .payment-info {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .payment-info h3 {
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .payment-address {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .payment-address code {
            flex: 1;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            word-break: break-all;
        }

        .copy-btn {
            padding: 0.5rem 1rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .back-link {
            display: inline-block;
            color: white;
            text-decoration: none;
            margin-bottom: 2rem;
            font-weight: 600;
            opacity: 0.9;
            transition: opacity 0.3s;
        }

        .back-link:hover {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .plans-grid {
                grid-template-columns: 1fr;
            }

            .purchase-section {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= $basePath ?>/dashboard" class="back-link">← Back to Dashboard</a>
        <?php endif; ?>

        <div class="header">
            <h1>Get Your License</h1>
            <p>Choose the perfect plan for your business</p>
        </div>

        <div id="alert"></div>

        <!-- Plans Section -->
        <div class="plans-grid" id="plansSection">
            <?php foreach ($plans as $plan): ?>
            <div class="plan-card <?= $plan['recommended'] ? 'recommended' : '' ?>" onclick="selectPlan('<?= $plan['id'] ?>', '<?= $plan['name'] ?>', <?= $plan['price'] ?>)">
                <div class="plan-name"><?= $plan['name'] ?></div>
                <div class="plan-price">
                    $<?= number_format($plan['price'], 0) ?>
                    <span>USD</span>
                </div>
                <div class="plan-duration"><?= $plan['duration'] ?></div>
                <ul class="plan-features">
                    <?php foreach ($plan['features'] as $feature): ?>
                    <li><?= $feature ?></li>
                    <?php endforeach; ?>
                </ul>
                <button class="select-plan-btn">Select Plan</button>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Purchase Form Section -->
        <div class="purchase-section" id="purchaseSection">
            <h2 style="margin-bottom: 2rem; color: #1e293b;">Complete Your Purchase</h2>
            
            <form id="purchaseForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="plan" id="selectedPlan">

                <div class="form-group">
                    <label class="form-label">Selected Plan</label>
                    <div style="padding: 1rem; background: #f8f9ff; border-radius: 12px; font-weight: 600; color: #667eea;">
                        <span id="planDisplay"></span> - $<span id="priceDisplay"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" placeholder="your@email.com" required 
                           value="<?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '' ?>">
                    <small style="color: #64748b;">License key will be sent to this email</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Domain Name</label>
                    <input type="text" name="domain" class="form-input" placeholder="yourdomain.com" required>
                    <small style="color: #64748b;">Domain where you'll install the script</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <div class="payment-methods">
                        <?php if (empty($payment_methods)): ?>
                        <div style="padding: 1rem; text-align: center; color: #64748b;">
                            No payment methods configured. Please contact administrator.
                        </div>
                        <?php else: ?>
                        <?php foreach ($payment_methods as $method): ?>
                        <div class="payment-method" onclick="selectPayment('<?= $method['name'] ?>', '<?= htmlspecialchars($method['display_name']) ?>')">
                            <div class="payment-method-icon">
                                <?php
                                $icons = ['paypal' => '💳', 'crypto' => '₿', 'binance' => '🔶'];
                                echo $icons[$method['name']] ?? '';
                                ?>
                            </div>
                            <div class="payment-method-name"><?= htmlspecialchars($method['display_name']) ?></div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="gateway" id="selectedGateway" required>
                </div>

                <?php if (!empty($payment_methods)): ?>
                <button type="submit" class="submit-btn" id="submitBtn">
                    Proceed to Payment →
                </button>
                <?php endif; ?>
            </form>

            <!-- Payment Instructions Section -->
            <div id="paymentSection">
                <h3 style="color: #1e293b; margin-bottom: 1rem;">Payment Instructions</h3>
                <div class="payment-info" id="paymentDetails"></div>

                <form id="proofForm" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="order_id" id="orderId">

                    <div class="form-group">
                        <label class="form-label">Transaction Reference / ID</label>
                        <input type="text" name="transaction_ref" class="form-input" placeholder="Enter transaction ID" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Payment Proof (Screenshot/Receipt)</label>
                        <input type="file" name="payment_proof" class="form-input" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>

                    <button type="submit" class="submit-btn">
                        Submit Payment Proof
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    const basePath = '<?= $basePath ?>';
    let selectedPlanId = null;
    let currentOrderData = null;

    function selectPlan(planId, planName, price) {
        selectedPlanId = planId;
        
        // Update UI
        document.querySelectorAll('.plan-card').forEach(card => card.classList.remove('selected'));
        event.currentTarget.classList.add('selected');
        
        // Show purchase section
        document.getElementById('plansSection').style.display = 'none';
        document.getElementById('purchaseSection').classList.add('active');
        
        // Update form
        document.getElementById('selectedPlan').value = planId;
        document.getElementById('planDisplay').textContent = planName;
        document.getElementById('priceDisplay').textContent = price.toFixed(2);
    }

    function selectPayment(gateway, gatewayName) {
        document.querySelectorAll('.payment-method').forEach(method => method.classList.remove('selected'));
        event.currentTarget.classList.add('selected');
        document.getElementById('selectedGateway').value = gateway;
    }

    document.getElementById('purchaseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = document.getElementById('submitBtn');
        
        btn.disabled = true;
        btn.textContent = 'Processing...';
        
        fetch(basePath + '/license/purchase', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                currentOrderData = data;
                showPaymentInstructions(data);
            } else {
                showAlert(data.message || 'An error occurred', 'error');
                btn.disabled = false;
                btn.textContent = 'Proceed to Payment →';
            }
        })
        .catch(err => {
            showAlert('Connection error. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = 'Proceed to Payment →';
        });
    });

    function showPaymentInstructions(data) {
        document.getElementById('orderId').value = data.order_id;
        
        let detailsHTML = `
            <p style="margin-bottom: 1rem;"><strong>Order Number:</strong> ${data.order_number}</p>
            <p style="margin-bottom: 1rem;"><strong>Amount to Pay:</strong> $${parseFloat(data.amount).toFixed(2)} USD</p>
            <p style="margin-bottom: 1rem;"><strong>Payment Method:</strong> ${data.gateway_name}</p>
            <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e2e8f0;">
        `;
        
        if (data.gateway === 'paypal' && data.config.paypal_address) {
            detailsHTML += `
                <h4>PayPal Address:</h4>
                <div class="payment-address">
                    <code>${data.config.paypal_address}</code>
                    <button onclick="copyText('${data.config.paypal_address}')" class="copy-btn">Copy</button>
                </div>
            `;
        } else if (data.gateway === 'crypto' && data.config.wallet_address) {
            detailsHTML += `
                <h4>Crypto Wallet Address (${data.config.crypto_network || 'BTC'}):</h4>
                <div class="payment-address">
                    <code>${data.config.wallet_address}</code>
                    <button onclick="copyText('${data.config.wallet_address}')" class="copy-btn">Copy</button>
                </div>
            `;
        } else if (data.gateway === 'binance') {
            detailsHTML += `<p>${data.instructions || 'Please complete payment via Binance Pay'}</p>`;
        }
        
        detailsHTML += `<p style="margin-top: 1rem; color: #64748b; font-size: 0.9rem;">${data.instructions || ''}</p>`;
        
        document.getElementById('paymentDetails').innerHTML = detailsHTML;
        document.getElementById('paymentSection').classList.add('active');
        document.getElementById('purchaseForm').style.display = 'none';
    }

    document.getElementById('proofForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(basePath + '/license/submitProof', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => {
                    if (currentOrderData && currentOrderData.order_number) {
                        window.location.href = basePath + '/license/status?order=' + currentOrderData.order_number;
                    } else {
                        window.location.href = basePath + '/dashboard';
                    }
                }, 3000);
            } else {
                showAlert(data.message || 'An error occurred', 'error');
            }
        })
        .catch(err => {
            showAlert('Connection error. Please try again.', 'error');
        });
    });

    function copyText(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('✓ Copied to clipboard!');
        });
    }

    function showAlert(message, type) {
        const alert = document.getElementById('alert');
        alert.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        alert.scrollIntoView({ behavior: 'smooth' });
    }
    </script>
</body>
</html>
