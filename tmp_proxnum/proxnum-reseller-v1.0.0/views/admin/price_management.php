<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="price-manager">
    <div class="price-manager-container">
        <!-- Page Header with different styling -->
        <div class="page-head">
            <div class="head-content">
                <div class="head-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H5.78a1.65 1.65 0 0 0-1.51 1 1.65 1.65 0 0 0 .33 1.82l.87.87A10 10 0 0 0 9.3 20.4c1.2.44 2.5.6 3.8.6s2.6-.16 3.8-.6a10 10 0 0 0 3.83-2.53l.87-.87z"></path>
                    </svg>
                </div>
                <div>
                    <h1>Price management</h1>
                    <p>Configure service and country pricing</p>
                </div>
            </div>
            <?php if (isset($message)): ?>
            <div class="toast-message">
                <span>✓</span> <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Two-column layout -->
        <div class="price-grid">
            <!-- Left column: Global settings and add form -->
            <div class="price-left">
                <!-- Global Settings Card -->
                <div class="card-global">
                    <div class="card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                        <span>Global pricing rules</span>
                    </div>
                    <div class="global-rules">
                        <div class="rule-item">
                            <div class="rule-label">Global price multiplier</div>
                            <div class="rule-value"><?= htmlspecialchars(\Core\Helper::getSetting('price_multiplier', '1')) ?>×</div>
                            <div class="rule-desc">Applied to all base API prices</div>
                        </div>
                    </div>
                    <div class="formula-note">
                        <strong>Formula:</strong> final = api_price × global_multiplier × specific_multiplier
                    </div>
                </div>

                <!-- Add New Multiplier Card -->
                <div class="card-add">
                    <div class="card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Add service/country multiplier</span>
                    </div>
                    <form method="POST" class="add-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="add_multiplier">

                        <!-- Service picker -->
                        <div class="picker-group">
                            <label>Service</label>
                            <div class="search-picker">
                                <input type="text" class="search-field" id="serviceSearch" placeholder="Type to search services...">
                                <select name="service" id="serviceSelect" class="select-list" size="4">
                                    <option value="">— select —</option>
                                    <?php foreach ($services as $service): ?>
                                        <?php if (isset($service['service'], $service['name'])): ?>
                                        <option value="<?= htmlspecialchars($service['service']) ?>">
                                            <?= htmlspecialchars($service['name']) ?>
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Country picker -->
                        <div class="picker-group">
                            <label>Country</label>
                            <div class="search-picker">
                                <input type="text" class="search-field" id="countrySearch" placeholder="Type to search countries...">
                                <select name="country" id="countrySelect" class="select-list" size="4">
                                    <option value="">— select —</option>
                                    <?php foreach ($countries as $country): ?>
                                        <?php if (isset($country['code'], $country['name'])): ?>
                                        <option value="<?= htmlspecialchars($country['code']) ?>">
                                            <?= htmlspecialchars($country['name']) ?>
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Multiplier input -->
                        <div class="markup-group">
                            <label>Price multiplier</label>
                            <div class="markup-field">
                                <input type="number" name="multiplier" step="0.01" min="0.01" placeholder="1.00" required>
                                <span>×</span>
                            </div>
                            <small>Enter multiplier (e.g., 1.5 for 1.5× the price)</small>
                        </div>

                        <button type="submit" class="btn-add">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add multiplier
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right column: Existing markups -->
            <div class="price-right">
                <div class="card-list">
                    <div class="card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>Configured multipliers</span>
                        <?php if (!empty($multipliers)): ?>
                        <span class="badge-count"><?= count($multipliers) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($multipliers)): ?>
                        <?php 
                        $serviceNames = [];
                        foreach ($services as $service) {
                            if (isset($service['service'], $service['name'])) {
                                $serviceNames[$service['service']] = $service['name'];
                            }
                        }
                        $countryNames = [];
                        foreach ($countries as $country) {
                            if (isset($country['code'], $country['name'])) {
                                $countryNames[$country['code']] = $country['name'];
                            }
                        }
                        ?>

                        <div class="markup-list">
                            <?php foreach ($multipliers as $multiplier): 
                                $parts = explode('_', $multiplier['key']);
                                if (count($parts) >= 3) {
                                    $serviceCode = $parts[1];
                                    $countryCode = $parts[2];
                                    $serviceName = $serviceNames[$serviceCode] ?? $serviceCode;
                                    $countryName = $countryNames[$countryCode] ?? $countryCode;
                                } else {
                                    continue;
                                }
                            ?>
                            <div class="markup-item">
                                <div class="markup-item-info">
                                    <div class="markup-service">
                                        <strong><?= htmlspecialchars($serviceName) ?></strong>
                                        <?php if ($serviceName !== $serviceCode): ?>
                                        <span class="item-code"><?= htmlspecialchars($serviceCode) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="markup-country">
                                        <span>📍 <?= htmlspecialchars($countryName) ?></span>
                                        <?php if ($countryName !== $countryCode): ?>
                                        <span class="item-code"><?= htmlspecialchars($countryCode) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="markup-percent"><?= htmlspecialchars($multiplier['value']) ?>×</div>
                                </div>
                                <form method="POST" onsubmit="return confirm('Delete this multiplier?');" class="delete-wrap">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="delete_multiplier">
                                    <input type="hidden" name="key" value="<?= htmlspecialchars($multiplier['key']) ?>">
                                    <button type="submit" class="delete-btn" title="Remove multiplier">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-markups">
                            <div class="empty-icon">⚙️</div>
                            <p>No specific multipliers configured</p>
                            <small>Global multiplier will be applied to all services</small>
                        </div>
                    <?php endif; ?>

                    <!-- Help box -->
                    <div class="help-block">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <div>
                            <strong>How it works</strong>
                            <p>Service/country specific multipliers are applied on top of the global multiplier. They override the main multiplier for all services, set from the settings dashboard. When you set a specific multiplier, it means that service/country combination will use a different pricing multiplier. Use search to quickly find services and countries.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* fresh styling - different from previous designs */
    .price-manager {
        background: #f3f6fd;
        min-height: 100vh;
        padding: 2rem;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .price-manager-container {
        max-width: 1300px;
        margin: 0 auto;
    }

    /* Page head */
    .page-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .head-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .head-icon {
        width: 56px;
        height: 56px;
        background: #fff;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4361ee;
        box-shadow: 0 8px 16px -8px rgba(0,0,0,0.1);
    }

    .head-content h1 {
        font-size: 2rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
        line-height: 1.2;
    }

    .head-content p {
        color: #64748b;
        margin: 0.2rem 0 0 0;
    }

    .toast-message {
        background: #ecfdf5;
        border-left: 4px solid #10b981;
        padding: 0.75rem 1.5rem;
        border-radius: 40px;
        color: #065f46;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .toast-message span {
        font-size: 1.2rem;
    }

    /* Two column grid */
    .price-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 2rem;
    }

    /* Common card style */
    .card-global, .card-add, .card-list {
        background: white;
        border-radius: 24px;
        padding: 1.8rem;
        box-shadow: 0 20px 35px -10px rgba(0,0,0,0.05);
        border: 1px solid rgba(203, 213, 225, 0.4);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card-global:hover, .card-add:hover, .card-list:hover {
        box-shadow: 0 25px 45px -12px rgba(0,0,0,0.1);
    }

    .card-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        color: #334155;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 1rem;
    }

    .card-title svg {
        color: #4361ee;
    }

    /* Global rules */
    .global-rules {
        display: flex;
        gap: 2rem;
        margin-bottom: 1.2rem;
        flex-wrap: wrap;
    }

    .rule-item {
        flex: 1;
        min-width: 140px;
    }

    .rule-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .rule-value {
        font-size: 2.2rem;
        font-weight: 600;
        color: #0a2540;
        line-height: 1.1;
    }

    .rule-desc {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .formula-note {
        background: #f8fafc;
        padding: 1rem 1.2rem;
        border-radius: 14px;
        font-size: 0.9rem;
        color: #334155;
        border: 1px dashed #cbd5e1;
    }

    /* Add form */
    .add-form {
        display: flex;
        flex-direction: column;
        gap: 1.4rem;
    }

    .picker-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .picker-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
        letter-spacing: 0.01em;
    }

    .search-picker {
        background: #f8fafc;
        border-radius: 16px;
        padding: 0.3rem;
        border: 1px solid #e2e8f0;
    }

    .search-field {
        width: 100%;
        padding: 0.7rem 1rem;
        border: none;
        background: transparent;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.9rem;
        outline: none;
    }

    .search-field:focus {
        border-bottom-color: #4361ee;
    }

    .select-list {
        width: 100%;
        background: white;
        border: none;
        padding: 0.5rem;
        border-radius: 12px;
        font-size: 0.9rem;
        outline: none;
    }

    .select-list option {
        padding: 0.5rem 0.8rem;
        border-radius: 8px;
    }

    .markup-group {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .markup-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
    }

    .markup-field {
        display: flex;
        align-items: center;
        border: 1px solid #e2e8f0;
        border-radius: 40px;
        background: #f8fafc;
        overflow: hidden;
    }

    .markup-field input {
        flex: 1;
        border: none;
        padding: 0.8rem 1.2rem;
        background: transparent;
        font-size: 1rem;
        outline: none;
    }

    .markup-field span {
        padding: 0 1.2rem;
        color: #64748b;
        font-weight: 500;
        background: #f1f5f9;
        line-height: 2.5rem;
    }

    .markup-group small {
        color: #94a3b8;
        font-size: 0.75rem;
    }

    .btn-add {
        background: #0a2540;
        color: white;
        border: none;
        border-radius: 40px;
        padding: 1rem;
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 0.5rem;
    }

    .btn-add:hover {
        background: #1a3b5d;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(10,37,64,0.3);
    }

    /* Right column list */
    .badge-count {
        background: #4361ee;
        color: white;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.15rem 0.7rem;
        border-radius: 30px;
        margin-left: auto;
    }

    .markup-list {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        margin: 1.2rem 0 1.8rem;
    }

    .markup-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        padding: 1rem 1.2rem;
        border-radius: 18px;
        border: 1px solid #e9eef3;
        transition: all 0.15s;
    }

    .markup-item:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }

    .markup-item-info {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem;
    }

    .markup-service {
        min-width: 140px;
    }

    .markup-service strong {
        display: block;
        color: #0a2540;
    }

    .markup-country {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #475569;
        min-width: 120px;
    }

    .item-code {
        font-size: 0.7rem;
        color: #94a3b8;
        font-family: monospace;
        margin-left: 0.2rem;
    }

    .markup-percent {
        background: #e3f9ee;
        color: #0b7e55;
        font-weight: 700;
        padding: 0.3rem 1rem;
        border-radius: 40px;
        font-size: 0.9rem;
    }

    .delete-btn {
        background: none;
        border: 1px solid #e2e8f0;
        border-radius: 30px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #b91c1c;
        cursor: pointer;
        transition: all 0.15s;
    }

    .delete-btn:hover {
        background: #fee2e2;
        border-color: #b91c1c;
        transform: scale(1.05);
    }

    .empty-markups {
        text-align: center;
        padding: 3rem 1rem;
        background: #f8fafc;
        border-radius: 20px;
        margin: 1rem 0 1.5rem;
        border: 1px dashed #cbd5e1;
    }

    .empty-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-markups p {
        font-weight: 600;
        color: #475569;
        margin: 0;
    }

    .empty-markups small {
        color: #94a3b8;
    }

    .help-block {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        background: #f0f4ff;
        border-radius: 16px;
        padding: 1.2rem;
        color: #1e40af;
        border: 1px solid #dbeafe;
        margin-top: 1.5rem;
    }

    .help-block strong {
        display: block;
        margin-bottom: 0.2rem;
    }

    .help-block p {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.9;
    }

    /* Responsive */
    @media (max-width: 1000px) {
        .price-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .price-manager { padding: 1rem; }
        .head-content h1 { font-size: 1.6rem; }
        .global-rules { flex-direction: column; gap: 1rem; }
        .markup-item { flex-direction: column; align-items: flex-start; gap: 0.8rem; }
        .delete-btn { align-self: flex-end; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Service search
    const serviceSearch = document.getElementById('serviceSearch');
    const serviceSelect = document.getElementById('serviceSelect');
    if (serviceSearch && serviceSelect) {
        serviceSearch.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            Array.from(serviceSelect.options).forEach((opt, i) => {
                if (i === 0) return; // keep placeholder
                opt.style.display = opt.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    }

    // Country search
    const countrySearch = document.getElementById('countrySearch');
    const countrySelect = document.getElementById('countrySelect');
    if (countrySearch && countrySelect) {
        countrySearch.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            Array.from(countrySelect.options).forEach((opt, i) => {
                if (i === 0) return;
                opt.style.display = opt.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>