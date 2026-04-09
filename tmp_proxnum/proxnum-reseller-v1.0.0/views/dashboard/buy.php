<!-- Buy Number View -->
<?php
use Core\Helper;
$title = 'Buy Number';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <span class="header-badge">PURCHASE</span>
                <h1 class="page-title">Buy phone number</h1>
                <p class="page-description">Select a service and country to purchase a virtual number for SMS verification</p>
            </div>
            <a href="<?= Helper::url('/dashboard') ?>" class="btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back to dashboard</span>
            </a>
        </div>

        <!-- Main Purchase Card -->
        <div class="purchase-card">
            <div class="card-header">
                <h2>Select service & country</h2>
                <p>Choose the platform and location for your virtual number</p>
            </div>

            <div class="card-body">
                <div id="alert"></div>
                
                <?php if (Helper::isDemo()): ?>
                <div class="demo-notice">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>
                        <strong>Demo Mode</strong>
                        <p>You're using a demo account. Purchasing virtual numbers is disabled. <a href="<?= str_replace('/proxnum-reseller', '', Helper::url('')) ?>/license/plans">Get a license</a> to enable this feature.</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <form id="buyForm" class="purchase-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="form-row">
                        <!-- Service Selection -->
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect>
                                        <line x1="7" y1="2" x2="7" y2="22"></line>
                                        <line x1="17" y1="2" x2="17" y2="22"></line>
                                        <line x1="2" y1="12" x2="22" y2="12"></line>
                                        <line x1="2" y1="7" x2="7" y2="7"></line>
                                        <line x1="2" y1="17" x2="7" y2="17"></line>
                                        <line x1="17" y1="17" x2="22" y2="17"></line>
                                        <line x1="17" y1="7" x2="22" y2="7"></line>
                                    </svg>
                                    Service
                                </label>
                                <div class="search-wrapper">
                                    <input type="text" id="serviceSearch" class="search-input" placeholder="Search services..." autocomplete="off">
                                </div>
                                <div class="select-wrapper">
                                    <select name="service" id="service" required size="6">
                                        <option value="">Select a service</option>
                                        <?php foreach ($services as $svc): ?>
                                        <option value="<?= htmlspecialchars($svc['service'] ?? '') ?>">
                                            <?= htmlspecialchars($svc['name'] ?? '') ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div id="selectedService" class="selection-badge">
                                    <span class="selection-label">Selected:</span>
                                    <span class="selection-value">None</span>
                                </div>
                            </div>
                        </div>

                        <!-- Country Selection -->
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="2" y1="12" x2="22" y2="12"></line>
                                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                    </svg>
                                    Country
                                </label>
                                <div class="search-wrapper">
                                    <input type="text" id="countrySearch" class="search-input" placeholder="Search countries..." autocomplete="off">
                                </div>
                                <div class="select-wrapper">
                                    <select name="country" id="country" required size="6">
                                        <option value="">Select a country</option>
                                        <?php foreach ($countries as $country): ?>
                                        <option value="<?= htmlspecialchars($country['code']) ?>">
                                            <?= htmlspecialchars($country['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div id="selectedCountry" class="selection-badge">
                                    <span class="selection-label">Selected:</span>
                                    <span class="selection-value">None</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price & Availability Card -->
                    <div id="priceInfo" class="price-card" style="display: none;">
                        <div class="price-row">
                            <div class="price-item">
                                <span class="price-label">Price</span>
                                <span class="price-amount" id="priceAmount">—</span>
                            </div>
                            <div class="price-divider"></div>
                            <div class="price-item">
                                <span class="price-label">Available numbers</span>
                                <span class="price-count" id="availCount">—</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-purchase" id="btnBuy">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Purchase number</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Help Note -->
        <div class="help-note">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>Numbers are available immediately after purchase. SMS codes typically arrive within 30–60 seconds.</span>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-deep: #0a2540;
        --primary-soft: #1a3b5d;
        --accent-gold: #c9a03d;
        --accent-teal: #1e7e6c;
        --neutral-100: #f8fafc;
        --neutral-200: #eef2f6;
        --neutral-300: #e2e8f0;
        --neutral-400: #cbd5e1;
        --neutral-600: #475569;
        --neutral-900: #0f172a;
        --success-light: #e3f9ee;
        --success-dark: #0b7e55;
        --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        
        --radius-xl: 32px;
        --radius-lg: 24px;
        --radius-md: 16px;
        --radius-sm: 8px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: var(--font-sans);
        background: linear-gradient(135deg, var(--neutral-100) 0%, var(--neutral-200) 100%);
        color: var(--neutral-900);
        min-height: 100vh;
    }

    .dashboard-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 2rem;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-badge {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: var(--accent-gold);
        background: rgba(201, 160, 61, 0.08);
        padding: 0.4rem 1rem;
        border-radius: 30px;
        margin-bottom: 0.75rem;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 600;
        color: var(--primary-deep);
        margin-bottom: 0.25rem;
        letter-spacing: -0.01em;
    }

    .page-description {
        color: var(--neutral-600);
        font-size: 1rem;
    }

    .btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: 60px;
        color: var(--neutral-600);
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-outline:hover {
        border-color: var(--accent-teal);
        color: var(--accent-teal);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    /* Purchase Card */
    .purchase-card {
        background: white;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-xl);
        box-shadow: 0 20px 35px -15px rgba(10, 37, 64, 0.15);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .card-header {
        padding: 2rem 2.5rem 0.5rem;
    }

    .card-header h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-deep);
        margin-bottom: 0.25rem;
    }

    .card-header p {
        color: var(--neutral-600);
        font-size: 0.95rem;
    }

    .card-body {
        padding: 2rem 2.5rem 2.5rem;
    }

    /* Form Layout */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--neutral-600);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .search-wrapper {
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--neutral-300);
        border-radius: 60px;
        font-size: 0.95rem;
        transition: all 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 3px rgba(201, 160, 61, 0.1);
    }

    .select-wrapper {
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }

    .select-wrapper select {
        width: 100%;
        padding: 0.75rem;
        border: none;
        background: white;
        font-size: 0.95rem;
        color: var(--neutral-900);
        cursor: pointer;
    }

    .select-wrapper select:focus {
        outline: none;
    }

    .select-wrapper select option {
        padding: 0.5rem;
    }

    .select-wrapper select option:hover {
        background: var(--neutral-200);
    }

    /* Selection Badge */
    .selection-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, var(--primary-deep) 0%, var(--primary-soft) 100%);
        border-radius: 60px;
        margin-top: 0.5rem;
        transition: all 0.2s;
    }

    .selection-badge .selection-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.7);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .selection-badge .selection-value {
        color: white;
        font-weight: 500;
        font-size: 0.95rem;
        flex: 1;
    }

    /* Price Card */
    .price-card {
        background: var(--neutral-100);
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin: 1.5rem 0;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .price-row {
        display: flex;
        align-items: center;
        justify-content: space-around;
        gap: 2rem;
    }

    .price-item {
        text-align: center;
    }

    .price-label {
        display: block;
        font-size: 0.85rem;
        color: var(--neutral-600);
        margin-bottom: 0.25rem;
    }

    .price-amount {
        font-size: 2rem;
        font-weight: 600;
        color: var(--accent-teal);
    }

    .price-count {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-deep);
    }

    .price-divider {
        width: 1px;
        height: 40px;
        background: var(--neutral-300);
    }

    /* Purchase Button */
    .btn-purchase {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        width: 100%;
        padding: 1rem 2rem;
        background: var(--accent-teal);
        color: white;
        border: none;
        border-radius: 60px;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 10px rgba(30, 126, 108, 0.2);
    }

    .btn-purchase:hover:not(:disabled) {
        background: #166653;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(30, 126, 108, 0.3);
    }

    .btn-purchase:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Help Note */
    .help-note {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        background: var(--neutral-200);
        border-radius: 60px;
        color: var(--neutral-600);
        font-size: 0.9rem;
    }

    /* Alerts */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 60px;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
    }

    .alert-success {
        background: var(--success-light);
        color: var(--success-dark);
        border: 1px solid rgba(11, 126, 85, 0.2);
    }

    .alert-error {
        background: #fee9e7;
        color: #b91c1c;
        border: 1px solid rgba(185, 28, 28, 0.1);
    }
    
    .alert-warning {
        background: #fff3d4;
        color: #b45b0a;
        border: 1px solid rgba(180, 91, 10, 0.2);
    }
    
    /* Demo Notice */
    .demo-notice {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #fff3d4 0%, #ffe8b8 100%);
        border: 2px solid #f0ad4e;
        border-radius: var(--radius-lg);
        margin-bottom: 2rem;
    }
    
    .demo-notice svg {
        flex-shrink: 0;
        color: #b45b0a;
        margin-top: 0.1rem;
    }
    
    .demo-notice strong {
        display: block;
        color: #b45b0a;
        font-weight: 700;
        margin-bottom: 0.25rem;
        font-size: 1rem;
    }
    
    .demo-notice p {
        color: #8b4513;
        margin: 0;
        line-height: 1.5;
    }
    
    .demo-notice a {
        color: #b45b0a;
        text-decoration: underline;
        font-weight: 600;
    }
    
    .demo-notice a:hover {
        color: #8b4513;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0 1rem;
            margin: 1rem auto;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-outline {
            width: 100%;
            justify-content: center;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .card-header,
        .card-body {
            padding: 1.5rem;
        }

        .price-row {
            flex-direction: column;
            gap: 1rem;
        }

        .price-divider {
            display: none;
        }

        .help-note {
            flex-direction: column;
            text-align: center;
        }
    }

    /* Option hover effects */
    select option:checked {
        background: linear-gradient(135deg, var(--primary-deep), var(--primary-soft));
        color: white;
    }

    /* Loading state */
    .btn-purchase.loading {
        position: relative;
        color: transparent;
    }

    .btn-purchase.loading::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        border: 2px solid white;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<script>
    const basePath = '<?= Helper::url('') ?>';
    
    const serviceSelect = document.getElementById('service');
    const countrySelect = document.getElementById('country');
    const serviceSearch = document.getElementById('serviceSearch');
    const countrySearch = document.getElementById('countrySearch');
    const priceInfo = document.getElementById('priceInfo');
    const selectedServiceDisplay = document.getElementById('selectedService');
    const selectedCountryDisplay = document.getElementById('selectedCountry');
    
    // Service search functionality
    serviceSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const options = serviceSelect.options;
        
        for (let i = 1; i < options.length; i++) {
            const optionText = options[i].textContent.toLowerCase();
            options[i].style.display = optionText.includes(searchTerm) ? '' : 'none';
        }
    });
    
    // Country search functionality
    countrySearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const options = countrySelect.options;
        
        for (let i = 1; i < options.length; i++) {
            const optionText = options[i].textContent.toLowerCase();
            options[i].style.display = optionText.includes(searchTerm) ? '' : 'none';
        }
    });
    
    function checkPrice() {
        const service = serviceSelect.value;
        const country = countrySelect.value;
        
        if (service && country) {
            const priceUrl = `${basePath}/index.php?route=api/prices&service=${service}&country=${country}`;
            
            fetch(priceUrl)
                .then(r => {
                    if (!r.ok) {
                        throw new Error(`HTTP ${r.status}: ${r.statusText}`);
                    }
                    return r.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        
                        if (data.success && data.price) {
                            document.getElementById('priceAmount').textContent = '$' + parseFloat(data.price).toFixed(2);
                            priceInfo.style.display = 'block';
                        } else {
                            const serviceName = serviceSelect.options[serviceSelect.selectedIndex].textContent;
                            const countryName = countrySelect.options[countrySelect.selectedIndex].textContent;
                            const errorMsg = data.message || data.error?.message || 'Price not available';
                            
                            alert(`This service/country combination is not available.\n\nService: ${serviceName}\nCountry: ${countryName}\n\nPlease try a different combination.`);
                            priceInfo.style.display = 'none';
                        }
                    } catch (parseError) {
                        alert('Invalid response from server. Please try again or contact support.');
                    }
                })
                .catch(err => {
                    alert('Error fetching price. Please check your connection and try again.');
                });
                
            const availUrl = `${basePath}/index.php?route=api/availability&service=${service}&country=${country}`;
            
            fetch(availUrl)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('availCount').textContent = data.count || '0';
                    }
                })
                .catch(err => {
                    document.getElementById('availCount').textContent = '—';
                });
        } else {
            priceInfo.style.display = 'none';
        }
    }
    
    // Update selection displays
    serviceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const valueSpan = selectedServiceDisplay.querySelector('.selection-value');
        
        if (this.value) {
            valueSpan.textContent = selectedOption.textContent;
            selectedServiceDisplay.style.background = 'linear-gradient(135deg, var(--accent-teal) 0%, #166653 100%)';
        } else {
            valueSpan.textContent = 'None';
            selectedServiceDisplay.style.background = 'linear-gradient(135deg, var(--primary-deep) 0%, var(--primary-soft) 100%)';
        }
        
        checkPrice();
    });
    
    countrySelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const valueSpan = selectedCountryDisplay.querySelector('.selection-value');
        
        if (this.value) {
            valueSpan.textContent = selectedOption.textContent;
            selectedCountryDisplay.style.background = 'linear-gradient(135deg, var(--accent-teal) 0%, #166653 100%)';
        } else {
            valueSpan.textContent = 'None';
            selectedCountryDisplay.style.background = 'linear-gradient(135deg, var(--primary-deep) 0%, var(--primary-soft) 100%)';
        }
        
        checkPrice();
    });
    
    document.getElementById('buyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btnBuy');
        const alert = document.getElementById('alert');
        
        btn.disabled = true;
        btn.classList.add('loading');
        
        const formData = new FormData(this);
        
        fetch(window.location.pathname, {
            method: 'POST',
            body: formData
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP error ' + r.status);
            return r.json();
        })
        .then(data => {
            if (data.success) {
                const message = data.message || 'Success!';
                alert.innerHTML = '<div class="alert alert-success">' + message + '</div>';
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else {
                const errorMsg = data.error || data.message || 'An error occurred';
                const alertClass = data.demo_mode ? 'alert-warning' : 'alert-error';
                alert.innerHTML = '<div class="alert ' + alertClass + '">' + errorMsg + '</div>';
                btn.disabled = false;
                btn.classList.remove('loading');
            }
        })
        .catch(error => {
            alert.innerHTML = '<div class="alert alert-error">Connection error. Please check your internet connection and try again.</div>';
            btn.disabled = false;
            btn.classList.remove('loading');
        });
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>