<?php
use Core\Helper;
$title = 'Payment Gateways';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <span class="header-badge">PAYMENTS</span>
                <h1 class="page-title">Payment Gateways (<?= count($gateways) ?>)</h1>
                <p class="page-description">Configure manual payment methods: PayPal, Cryptocurrency, and Binance Pay</p>
            </div>
        </div>

        <!-- Demo Account Warning -->
        <?php if (\Core\Helper::isDemo()): ?>
        <div class="alert alert-warning" style="margin-bottom: 2rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <div>
                <strong>⚠️ Demo Account</strong>
                <p>Payment gateway modifications are disabled in demo mode. This is a read-only view.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Status Summary -->
        <div class="gateway-summary">
            <?php
            $enabledCount = count(array_filter($gateways, fn($g) => $g['enabled']));
            $totalCount = count($gateways);
            ?>
            
            <!-- Available Gateways List -->
            <div class="available-gateways">
                <strong>Available Payment Methods:</strong>
                <?php foreach ($gateways as $gw): ?>
                    <span class="gateway-pill <?= $gw['enabled'] ? 'pill-enabled' : 'pill-disabled' ?>">
                        <?php
                        $icons = ['paypal' => '💳', 'crypto' => '₿', 'binance' => '🔶'];
                        echo $icons[$gw['name']] ?? '';
                        ?>
                        <?= htmlspecialchars($gw['display_name']) ?>
                        <?= $gw['enabled'] ? '✓' : '' ?>
                    </span>
                <?php endforeach; ?>
            </div>
            
            <?php if ($enabledCount === 0): ?>
                <div class="alert alert-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <div>
                        <strong>No payment gateways enabled</strong>
                        <p>Users won't be able to add funds. Enable and configure at least one payment method below.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <strong><?= $enabledCount ?> of <?= $totalCount ?> payment methods enabled</strong>
                        <p>Users can deposit funds using the enabled payment methods.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Payment Gateways -->
        
        <?php 
        $enabledGateways = array_filter($gateways, fn($g) => $g['enabled']);
        $disabledGateways = array_filter($gateways, fn($g) => !$g['enabled']);
        ?>
        
        <?php if (!empty($enabledGateways)): ?>
        <div style="margin-bottom: 32px;">
            <h2 style="margin-bottom: 16px; color: #1f2937; font-size: 20px;"> Active Payment Methods</h2>
            <div class="gateways-grid">
                <?php foreach ($enabledGateways as $gateway): 
                    // Check if gateway is configured
                    $isConfigured = false;
                    if ($gateway['name'] === 'paypal' && !empty($gateway['config_data']['paypal_address'])) {
                        $isConfigured = true;
                    } elseif ($gateway['name'] === 'crypto' && !empty($gateway['config_data']['wallet_address'])) {
                        $isConfigured = true;
                    } elseif ($gateway['name'] === 'binance' && !empty($gateway['config_data']['binance_pay_id'])) {
                        $isConfigured = true;
                    }
                ?>
            <div class="gateway-card gateway-enabled <?= $isConfigured ? 'gateway-configured' : '' ?>" 
                 id="card_<?= $gateway['name'] ?>"
                 data-gateway="<?= $gateway['name'] ?>">
                
                <div class="gateway-header">
                    <div class="gateway-title-section">
                        <div class="gateway-icon">
                            <?php
                            $icons = ['paypal' => '💳', 'crypto' => '₿', 'binance' => '🔶'];
                            echo $icons[$gateway['name']] ?? '';
                            ?>
                        </div>
                        <div>
                            <h3><?= htmlspecialchars($gateway['display_name']) ?></h3>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <span class="gateway-status-badge">✓ Enabled</span>
                                <?php if ($isConfigured): ?>
                                <span class="configured-badge">✓ Configured</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <?php if ($isConfigured): ?>
                        <button type="button" class="btn-edit" onclick="toggleEditMode('<?= $gateway['name'] ?>')" id="editBtn_<?= $gateway['name'] ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Edit
                        </button>
                        <?php endif; ?>
                        <label class="toggle-switch" title="Click to disable">
                            <input type="checkbox" 
                                   checked 
                                   onchange="toggleGateway('<?= $gateway['name'] ?>', this.checked)"
                                   id="toggle_<?= $gateway['name'] ?>">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <!-- Read-only view for configured gateways -->
                <?php if ($isConfigured): ?>
                <div class="config-display" id="display_<?= $gateway['name'] ?>">
                    <?php if ($gateway['name'] === 'paypal'): ?>
                        <div class="config-item">
                            <span class="config-label">PayPal Email:</span>
                            <span class="config-value"><?= htmlspecialchars($gateway['config_data']['paypal_address']) ?></span>
                        </div>
                    <?php elseif ($gateway['name'] === 'crypto'): ?>
                        <div class="config-item">
                            <span class="config-label">Cryptocurrency:</span>
                            <span class="config-value"><?= htmlspecialchars($gateway['config_data']['crypto_type']) ?></span>
                        </div>
                        <div class="config-item">
                            <span class="config-label">Network:</span>
                            <span class="config-value"><?= htmlspecialchars($gateway['config_data']['network']) ?></span>
                        </div>
                        <div class="config-item">
                            <span class="config-label">Wallet Address:</span>
                            <span class="config-value" style="font-family: monospace; word-break: break-all;"><?= htmlspecialchars($gateway['config_data']['wallet_address']) ?></span>
                        </div>
                    <?php elseif ($gateway['name'] === 'binance'): ?>
                        <div class="config-item">
                            <span class="config-label">Binance Pay ID:</span>
                            <span class="config-value"><?= htmlspecialchars($gateway['config_data']['binance_pay_id']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($gateway['instructions'])): ?>
                        <div class="config-item">
                            <span class="config-label">User Instructions:</span>
                            <span class="config-value"><?= nl2br(htmlspecialchars($gateway['instructions'])) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Edit form (hidden by default for configured gateways) -->
                <form onsubmit="saveGateway(event, '<?= $gateway['name'] ?>')" class="gateway-form" id="form_<?= $gateway['name'] ?>" style="<?= $isConfigured ? 'display: none;' : '' ?>">
                    <?php if ($gateway['name'] === 'paypal'): ?>
                        <div class="form-group">
                            <label>PayPal Email Address</label>
                            <input type="email" 
                                   name="paypal_address" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($gateway['config_data']['paypal_address'] ?? '') ?>"
                                   placeholder="your-email@example.com"
                                   required>
                            <small>Users will send payments to this PayPal address</small>
                        </div>
                    <?php elseif ($gateway['name'] === 'crypto'): ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Cryptocurrency</label>
                                <select name="crypto_type" id="crypto_type_<?= $gateway['name'] ?>" class="form-control" required onchange="updateNetworkOptions('<?= $gateway['name'] ?>')">
                                    <option value="">Select Crypto</option>
                                    <option value="BTC" <?= ($gateway['config_data']['crypto_type'] ?? '') === 'BTC' ? 'selected' : '' ?>>Bitcoin (BTC)</option>
                                    <option value="ETH" <?= ($gateway['config_data']['crypto_type'] ?? '') === 'ETH' ? 'selected' : '' ?>>Ethereum (ETH)</option>
                                    <option value="USDT" <?= ($gateway['config_data']['crypto_type'] ?? '') === 'USDT' ? 'selected' : '' ?>>Tether (USDT)</option>
                                    <option value="USDC" <?= ($gateway['config_data']['crypto_type'] ?? '') === 'USDC' ? 'selected' : '' ?>>USD Coin (USDC)</option>
                                    <option value="BNB" <?= ($gateway['config_data']['crypto_type'] ?? '') === 'BNB' ? 'selected' : '' ?>>Binance Coin (BNB)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Network</label>
                                <select name="network" id="network_<?= $gateway['name'] ?>" class="form-control" required data-saved-value="<?= htmlspecialchars($gateway['config_data']['network'] ?? '') ?>">
                                    <option value="">Select Crypto First</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Wallet Address</label>
                            <input type="text" 
                                   name="wallet_address" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($gateway['config_data']['wallet_address'] ?? '') ?>"
                                   placeholder="Enter your wallet address"
                                   required>
                            <small>Users will send crypto to this wallet address</small>
                        </div>
                    <?php elseif ($gateway['name'] === 'binance'): ?>
                        <div class="form-group">
                            <label>Binance Pay ID</label>
                            <input type="text" 
                                   name="binance_pay_id" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($gateway['config_data']['binance_pay_id'] ?? '') ?>"
                                   placeholder="Enter your Binance Pay ID"
                                   required>
                            <small>Users will send payments to this Binance Pay ID</small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Instructions for Users (Optional)</label>
                        <textarea name="instructions" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Add any additional instructions for users..."><?= htmlspecialchars($gateway['instructions'] ?? '') ?></textarea>
                    </div>
                    
                    <input type="hidden" name="csrf_token" value="<?= Helper::getCsrf() ?>">
                    <input type="hidden" name="action" value="update_gateway">
                    <input type="hidden" name="gateway" value="<?= $gateway['name'] ?>">
                    <input type="hidden" name="enabled" value="<?= $gateway['enabled'] ? '1' : '0' ?>" id="enabled_<?= $gateway['name'] ?>">
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Save Configuration
                        </button>
                        <small class="save-hint">
                            <?= $gateway['enabled'] ? '✓ This gateway is enabled for users' : '○ Enable the toggle above to make this available to users' ?>
                        </small>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($disabledGateways)): ?>
        <div>
            <h2 style="margin-bottom: 16px; color: #6b7280; font-size: 20px;">⚙️ Setup Payment Methods</h2>
            <div class="gateways-grid">
                <?php foreach ($disabledGateways as $gateway): ?>
            <div class="gateway-card <?= $gateway['enabled'] ? 'gateway-enabled' : 'gateway-disabled' ?>" 
                 id="card_<?= $gateway['name'] ?>"
                 data-gateway="<?= $gateway['name'] ?>">
                
                <div class="gateway-header">
                    <div class="gateway-title-section">
                        <div class="gateway-icon">
                            <?php
                            $icons = ['paypal' => '💳', 'crypto' => '₿', 'binance' => '🔶'];
                            echo $icons[$gateway['name']] ?? '';
                            ?>
                        </div>
                        <div>
                            <h3><?= htmlspecialchars($gateway['display_name']) ?></h3>
                            <span class="gateway-status-badge">
                                <?= $gateway['enabled'] ? '✓ Enabled' : '○ Disabled' ?>
                            </span>
                        </div>
                    </div>
                    <label class="toggle-switch" title="<?= $gateway['enabled'] ? 'Click to disable' : 'Click to enable' ?>">
                        <input type="checkbox" 
                               <?= $gateway['enabled'] ? 'checked' : '' ?> 
                               onchange="toggleGateway('<?= $gateway['name'] ?>', this.checked)"
                               id="toggle_<?= $gateway['name'] ?>">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <form onsubmit="saveGateway(event, '<?= $gateway['name'] ?>')" class="gateway-form">
                    <?php if ($gateway['name'] === 'paypal'): ?>
                        <div class="form-group">
                            <label>PayPal Email Address</label>
                            <input type="email" 
                                   name="paypal_address" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($gateway['config_data']['paypal_address'] ?? '') ?>"
                                   placeholder="your-email@example.com"
                                   required>
                            <small>Users will send payments to this PayPal address</small>
                        </div>
                    <?php elseif ($gateway['name'] === 'crypto'): ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Cryptocurrency</label>
                                <select name="crypto_type" id="crypto_type_<?= $gateway['name'] ?>" class="form-control" required onchange="updateNetworkOptions('<?= $gateway['name'] ?>')">
                                    <option value="">Select Crypto</option>
                                    <option value="BTC" <?= ($gateway['config_data']['crypto_type'] ?? '') === 'BTC' ? 'selected' : '' ?>>Bitcoin (BTC)</option>
                                    <option value="ETH" <?= ($gateway['config_data']['crypto_type'] ?? '') === 'ETH' ? 'selected' : '' ?>>Ethereum (ETH)</option>
                                    <option value="USDT" <?= ($gateway['config_data']['crypto_type'] ?? '') === 'USDT' ? 'selected' : '' ?>>Tether (USDT)</option>
                                    <option value="USDC" <?= ($gateway['config_data']['crypto_type'] ?? '') === 'USDC' ? 'selected' : '' ?>>USD Coin (USDC)</option>
                                    <option value="BNB" <?= ($gateway['config_data']['crypto_type'] ?? '') === 'BNB' ? 'selected' : '' ?>>Binance Coin (BNB)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Network</label>
                                <select name="network" id="network_<?= $gateway['name'] ?>" class="form-control" required data-saved-value="<?= htmlspecialchars($gateway['config_data']['network'] ?? '') ?>">
                                    <option value="">Select Crypto First</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Wallet Address</label>
                            <input type="text" 
                                   name="wallet_address" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($gateway['config_data']['wallet_address'] ?? '') ?>"
                                   placeholder="Enter your wallet address"
                                   required>
                            <small>Users will send crypto to this wallet address</small>
                        </div>
                    <?php elseif ($gateway['name'] === 'binance'): ?>
                        <div class="form-group">
                            <label>Binance Pay ID</label>
                            <input type="text" 
                                   name="binance_pay_id" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($gateway['config_data']['binance_pay_id'] ?? '') ?>"
                                   placeholder="Enter your Binance Pay ID"
                                   required>
                            <small>Users will send payments to this Binance Pay ID</small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Instructions for Users (Optional)</label>
                        <textarea name="instructions" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Add any additional instructions for users..."><?= htmlspecialchars($gateway['instructions'] ?? '') ?></textarea>
                    </div>
                    
                    <input type="hidden" name="csrf_token" value="<?= Helper::getCsrf() ?>">
                    <input type="hidden" name="action" value="update_gateway">
                    <input type="hidden" name="gateway" value="<?= $gateway['name'] ?>">
                    <input type="hidden" name="enabled" value="<?= $gateway['enabled'] ? '1' : '0' ?>" id="enabled_<?= $gateway['name'] ?>">
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Save Configuration
                        </button>
                        <small class="save-hint">
                            <?= $gateway['enabled'] ? '✓ This gateway is enabled for users' : '○ Enable the toggle above to make this available to users' ?>
                        </small>
                    </div>
                </form>
            </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .gateway-summary {
        margin-bottom: 24px;
    }
    
    .available-gateways {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: white;
        border-radius: 8px;
        margin-bottom: 16px;
        border: 2px solid #e5e7eb;
        flex-wrap: wrap;
    }
    
    .available-gateways strong {
        color: #374151;
    }
    
    .gateway-pill {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .pill-enabled {
        background: #d1fae5;
        color: #065f46;
        border: 2px solid #10b981;
    }
    
    .pill-disabled {
        background: #f3f4f6;
        color: #6b7280;
        border: 2px solid #d1d5db;
    }
    
    .alert {
        display: flex;
        gap: 16px;
        padding: 16px 20px;
        border-radius: 8px;
        border: 1px solid;
    }
    
    .alert svg {
        flex-shrink: 0;
        margin-top: 2px;
    }
    
    .alert strong {
        display: block;
        margin-bottom: 4px;
    }
    
    .alert p {
        margin: 0;
        font-size: 14px;
    }
    
    .alert-warning {
        background: #fef3c7;
        border-color: #f59e0b;
        color: #92400e;
    }
    
    .alert-success {
        background: #d1fae5;
        border-color: #10b981;
        color: #065f46;
    }
    
    .gateways-grid {
        display: grid;
        gap: 24px;
        max-width: 1200px;
    }
    
    .gateway-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 2px solid #e5e7eb;
        transition: all 0.3s;
    }
    
    .gateway-card.gateway-enabled {
        border-color: #10b981;
        background: #f0fdf4;
    }
    
    .gateway-card.gateway-disabled {
        opacity: 0.9;
    }
    
    .gateway-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f3f4f6;
    }
    
    .gateway-title-section {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .gateway-icon {
        font-size: 40px;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        border-radius: 12px;
    }
    
    .gateway-enabled .gateway-icon {
        background: #d1fae5;
    }
    
    .gateway-header h3 {
        margin: 0 0 4px 0;
        font-size: 20px;
        color: #1f2937;
    }
    
    .gateway-status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .gateway-enabled .gateway-status-badge {
        background: #d1fae5;
        color: #065f46;
    }
    
    .gateway-disabled .gateway-status-badge {
        background: #f3f4f6;
        color: #6b7280;
    }
    
    .gateway-form .form-group {
        margin-bottom: 20px;
    }
    
    .gateway-form label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }
    
    .gateway-form .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .gateway-form .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .gateway-form small {
        display: block;
        margin-top: 4px;
        color: #6b7280;
        font-size: 12px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 52px;
        height: 28px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 28px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    
    .toggle-switch input:checked + .toggle-slider {
        background-color: #10b981;
    }
    
    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }
    
    .btn-primary {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #3b82f6;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-primary:hover {
        background: #2563eb;
    }
    
    .form-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .save-hint {
        color: #6b7280;
        font-size: 12px;
    }
    
    /* Configured gateway styles */
    .gateway-configured {
        border: 2px solid #10b981;
        background: linear-gradient(to bottom, #f0fdf4, white);
    }
    
    .configured-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        background: #059669;
        color: white;
    }
    
    .config-display {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
    }
    
    .config-item {
        display: flex;
        gap: 16px;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .config-item:last-child {
        border-bottom: none;
    }
    
    .config-label {
        font-weight: 600;
        color: #6b7280;
        min-width: 150px;
        flex-shrink: 0;
    }
    
    .config-value {
        color: #1f2937;
        font-weight: 500;
    }
    
    .btn-edit {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s;
        color: #374151;
    }
    
    .btn-edit:hover {
        background: #f9fafb;
        border-color: #3b82f6;
        color: #3b82f6;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .config-item {
            flex-direction: column;
            gap: 4px;
        }
        
        .config-label {
            min-width: auto;
        }
    }
</style>

<script>
const basePath = '<?= Helper::url('') ?>';

// Network options for each crypto type
const cryptoNetworks = {
    'BTC': [
        {value: 'BTC', label: 'Bitcoin Network'}
    ],
    'ETH': [
        {value: 'ERC20', label: 'ERC20 (Ethereum)'}
    ],
    'USDT': [
        {value: 'TRC20', label: 'TRC20 (Tron)'},
        {value: 'ERC20', label: 'ERC20 (Ethereum)'},
        {value: 'BEP20', label: 'BEP20 (BSC)'}
    ],
    'USDC': [
        {value: 'ERC20', label: 'ERC20 (Ethereum)'},
        {value: 'BEP20', label: 'BEP20 (BSC)'}
    ],
    'BNB': [
        {value: 'BEP20', label: 'BEP20 (BSC)'}
    ]
};

// Initialize network options on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize for all crypto gateways (both enabled and disabled)
    document.querySelectorAll('[id^="crypto_type_"]').forEach(function(select) {
        let gatewayName = select.id.replace('crypto_type_', '');
        const savedNetwork = document.getElementById('network_' + gatewayName)?.getAttribute('data-saved-value');
        
        if (select.value) {
            updateNetworkOptions(gatewayName, savedNetwork);
        }
    });
});

// Toggle between read-only display and edit mode for configured gateways
function toggleEditMode(gatewayName) {
    const display = document.getElementById('display_' + gatewayName);
    const form = document.getElementById('form_' + gatewayName);
    const editBtn = document.getElementById('editBtn_' + gatewayName);
    
    if (display.style.display === 'none') {
        // Switch back to display mode
        display.style.display = 'block';
        form.style.display = 'none';
        editBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg> Edit';
    } else {
        // Switch to edit mode
        display.style.display = 'none';
        form.style.display = 'block';
        editBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> Cancel';
    }
}

function updateNetworkOptions(gatewayName, savedNetwork) {
    const cryptoSelect = document.getElementById('crypto_type_' + gatewayName);
    const networkSelect = document.getElementById('network_' + gatewayName);
    const selectedCrypto = cryptoSelect.value;
    const currentNetwork = savedNetwork || networkSelect.value;
    
    // Clear existing options
    networkSelect.innerHTML = '<option value="">Select Network</option>';
    
    if (selectedCrypto && cryptoNetworks[selectedCrypto]) {
        const networks = cryptoNetworks[selectedCrypto];
        networks.forEach(function(network) {
            const option = document.createElement('option');
            option.value = network.value;
            option.textContent = network.label;
            if (network.value === currentNetwork) {
                option.selected = true;
            }
            networkSelect.appendChild(option);
        });
        networkSelect.disabled = false;
    } else {
        networkSelect.disabled = true;
    }
}

function toggleGateway(name, enabled) {
    document.getElementById('enabled_' + name).value = enabled ? '1' : '0';
    
    // Update card styling
    const card = document.getElementById('card_' + name);
    if (enabled) {
        card.classList.remove('gateway-disabled');
        card.classList.add('gateway-enabled');
        card.querySelector('.gateway-status-badge').textContent = '✓ Enabled';
    } else {
        card.classList.remove('gateway-enabled');
        card.classList.add('gateway-disabled');
        card.querySelector('.gateway-status-badge').textContent = '○ Disabled';
    }
}

function saveGateway(e, gatewayName) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const enabled = document.getElementById('toggle_' + gatewayName).checked;
    
    // Check if configuration is complete before allowing enable
    if (enabled) {
        let isValid = true;
        let missingFields = [];
        
        if (gatewayName === 'paypal') {
            const paypal = formData.get('paypal_address');
            if (!paypal) {
                missingFields.push('PayPal Email Address');
                isValid = false;
            }
        } else if (gatewayName === 'crypto') {
            if (!formData.get('crypto_type')) missingFields.push('Cryptocurrency');
            if (!formData.get('network')) missingFields.push('Network');
            if (!formData.get('wallet_address')) missingFields.push('Wallet Address');
            if (missingFields.length > 0) isValid = false;
        } else if (gatewayName === 'binance') {
            if (!formData.get('binance_pay_id')) {
                missingFields.push('Binance Pay ID');
                isValid = false;
            }
        }
        
        if (!isValid) {
            alert('❌ Cannot enable gateway. Please fill in required fields:\n- ' + missingFields.join('\n- '));
            document.getElementById('toggle_' + gatewayName).checked = false;
            document.getElementById('enabled_' + gatewayName).value = '0';
            return;
        }
    }
    
    fetch(basePath + '/admin/paymentGateways', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(' ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
