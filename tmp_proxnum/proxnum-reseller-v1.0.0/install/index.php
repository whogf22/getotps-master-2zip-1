<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proxnum Reseller Installation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 40px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .step::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 1;
        }
        
        .step:first-child::before {
            left: 50%;
        }
        
        .step:last-child::before {
            right: 50%;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 2;
            margin-bottom: 5px;
        }
        
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        
        .step.completed .step-number {
            background: #10b981;
            color: white;
        }
        
        .step-label {
            font-size: 12px;
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .input-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .alert-info {
            background: #eef;
            color: #33c;
            border: 1px solid #ccf;
        }
        
        .requirements-list {
            list-style: none;
        }
        
        .requirements-list li {
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 6px;
            background: #f5f5f5;
        }
        
        .requirements-list li.passed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .requirements-list li.failed {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .requirements-list li::before {
            margin-right: 10px;
            font-weight: bold;
        }
        
        .requirements-list li.passed::before {
            content: '✓';
            color: #10b981;
        }
        
        .requirements-list li.failed::before {
            content: '✗';
            color: #ef4444;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
        }
        
        .btn-group button {
            flex: 1;
        }
        
        .hidden {
            display: none;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Proxnum Reseller</h1>
            <p>Professional SMS Reseller Platform - Installation Wizard</p>
        </div>
        
        <div class="content">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active" id="step-ind-1">
                    <div class="step-number">1</div>
                    <div class="step-label">Requirements</div>
                </div>
                <div class="step" id="step-ind-2">
                    <div class="step-number">2</div>
                    <div class="step-label">License</div>
                </div>
                <div class="step" id="step-ind-3">
                    <div class="step-number">3</div>
                    <div class="step-label">Database</div>
                </div>
                <div class="step" id="step-ind-4">
                    <div class="step-number">4</div>
                    <div class="step-label">Admin</div>
                </div>
                <div class="step" id="step-ind-5">
                    <div class="step-number">5</div>
                    <div class="step-label">Complete</div>
                </div>
            </div>
            
            <!-- Step 1: Requirements Check -->
            <div id="step-1" class="step-content">
                <h2 style="margin-bottom: 20px;">System Requirements</h2>
                <p id="checking-message" style="margin-bottom: 20px; color: #666;">Checking your server...</p>
                <ul class="requirements-list" id="requirements-list">
                    <li><div class="spinner"></div></li>
                </ul>
                <button class="btn" onclick="nextStep(2)" id="btn-next-1" disabled>Continue</button>
            </div>
            
            <!-- Step 2: License Verification -->
            <div id="step-2" class="step-content hidden">
                <h2 style="margin-bottom: 20px;">License & API Configuration</h2>
                <div id="license-alert"></div>
                
                <div class="form-group">
                    <label for="license-key">License Key *</label>
                    <input type="text" id="license-key" placeholder="XXXX-XXXX-XXXX-XXXX" required>
                    <div class="input-hint">Enter your Proxnum reseller license key</div>
                </div>
                
                <div class="form-group">
                    <label for="api-key">Proxnum API Key *</label>
                    <input type="text" id="api-key" placeholder="Your Proxnum API key" required>
                    <div class="input-hint">Get this from your Proxnum profile</div>
                </div>
                
                <div class="form-group">
                    <label for="license-email">License Email *</label>
                    <input type="email" id="license-email" placeholder="your@email.com" required>
                    <div class="input-hint">Email associated with your license</div>
                </div>
                
                <div class="btn-group">
                    <button class="btn btn-secondary" onclick="prevStep(1)">Back</button>
                    <button class="btn" onclick="verifyLicense()" id="btn-verify">Verify & Continue</button>
                </div>
            </div>
            
            <!-- Step 3: Database Configuration -->
            <div id="step-3" class="step-content hidden">
                <h2 style="margin-bottom: 20px;">Database Configuration</h2>
                <div id="db-alert"></div>
                
                <div class="form-group">
                    <label for="db-host">Database Host *</label>
                    <input type="text" id="db-host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="db-name">Database Name *</label>
                    <input type="text" id="db-name" placeholder="proxnum_reseller" required>
                </div>
                
                <div class="form-group">
                    <label for="db-user">Database Username *</label>
                    <input type="text" id="db-user" placeholder="root" required>
                </div>
                
                <div class="form-group">
                    <label for="db-pass">Database Password</label>
                    <input type="password" id="db-pass" placeholder="Leave empty if none">
                </div>
                
                <div class="btn-group">
                    <button class="btn btn-secondary" onclick="prevStep(2)">Back</button>
                    <button class="btn" onclick="testDatabase()" id="btn-test-db">Test & Continue</button>
                </div>
            </div>
            
            <!-- Step 4: Admin Account -->
            <div id="step-4" class="step-content hidden">
                <h2 style="margin-bottom: 20px;">Create Admin Account</h2>
                <div id="admin-alert"></div>
                
                <div class="form-group">
                    <label for="admin-name">Full Name *</label>
                    <input type="text" id="admin-name" placeholder="John Doe" required>
                </div>
                
                <div class="form-group">
                    <label for="admin-email">Email Address *</label>
                    <input type="email" id="admin-email" placeholder="admin@example.com" required>
                </div>
                
                <div class="form-group">
                    <label for="admin-password">Password *</label>
                    <input type="password" id="admin-password" placeholder="Minimum 8 characters" required>
                </div>
                
                <div class="form-group">
                    <label for="admin-password-confirm">Confirm Password *</label>
                    <input type="password" id="admin-password-confirm" placeholder="Re-enter password" required>
                </div>
                
                <div class="btn-group">
                    <button class="btn btn-secondary" onclick="prevStep(3)">Back</button>
                    <button class="btn" onclick="completeInstallation()" id="btn-install">Complete Installation</button>
                </div>
            </div>
            
            <!-- Step 5: Complete -->
            <div id="step-5" class="step-content hidden">
                <div style="text-align: center;">
                    <div style="font-size: 60px; margin-bottom: 20px;">🎉</div>
                    <h2 style="margin-bottom: 20px;">Installation Complete!</h2>
                    <div class="alert alert-success">
                        Your Proxnum Reseller system has been successfully installed.
                    </div>
                    <p style="margin-bottom: 30px; color: #666;">
                        For security reasons, please delete the <strong>/install</strong> folder from your server.
                    </p>
                    <a href="#" id="admin-login-link" class="btn" style="display: inline-block; text-decoration: none;">
                        Go to Admin Panel
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Helper function to get the base path (parent of install directory)
        function getBasePath() {
            const pathname = window.location.pathname;
            // Remove trailing slash if present
            const cleanPath = pathname.replace(/\/$/, '');
            // Get the directory containing the install folder
            if (cleanPath.includes('/install')) {
                return cleanPath.substring(0, cleanPath.lastIndexOf('/install'));
            }
            // If we're at the root of the reseller system
            return cleanPath;
        }
        
        // Helper function to get correct path for install directory files
        function getInstallPath(filename) {
            const scriptPath = window.location.pathname;
            // If we're not in the install directory, add it to the path
            if (!scriptPath.includes('/install/')) {
                return 'install/' + filename;
            }
            return filename;
        }
        
        // Check requirements on load
        window.onload = function() {
            checkRequirements();
            
            // Set correct admin login link
            const adminLoginLink = document.getElementById('admin-login-link');
            if (adminLoginLink) {
                adminLoginLink.href = getBasePath() + '/auth/login';
            }
        };
        
        function checkRequirements() {
            const checkUrl = getInstallPath('check_requirements.php');
            
            console.log('Fetching requirements from:', checkUrl);
            
            fetch(checkUrl)
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response content-type:', response.headers.get('content-type'));
                    
                    if (!response.ok) {
                        throw new Error('HTTP error ' + response.status);
                    }
                    
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Expected JSON, got ' + contentType);
                    }
                    
                    return response.json();
                })
                .then(data => {
                    // Hide checking message
                    document.getElementById('checking-message').style.display = 'none';
                    
                    const list = document.getElementById('requirements-list');
                    list.innerHTML = '';
                    
                    let allPassed = true;
                    
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.className = item.passed ? 'passed' : 'failed';
                        li.textContent = item.name;
                        list.appendChild(li);
                        
                        if (!item.passed) allPassed = false;
                    });
                    
                    document.getElementById('btn-next-1').disabled = !allPassed;
                })
                .catch(error => {
                    console.error('Error checking requirements:', error);
                    document.getElementById('checking-message').textContent = 'Error loading requirements check';
                    document.getElementById('checking-message').style.color = '#dc3545';
                    
                    const list = document.getElementById('requirements-list');
                    list.innerHTML = '<li class="failed">Failed to load requirements: ' + error.message + '</li>';
                    list.innerHTML += '<li class="failed">Please refresh the page or check browser console</li>';
                    list.innerHTML += '<li class="failed">Attempted URL: ' + checkUrl + '</li>';
                    document.getElementById('btn-next-1').disabled = true;
                });
        }
        
        function nextStep(step) {
            // Hide all steps
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.step').forEach(el => el.classList.remove('active'));
            
            // Show current step
            document.getElementById('step-' + step).classList.remove('hidden');
            document.getElementById('step-ind-' + step).classList.add('active');
            
            // Mark previous steps as completed
            for (let i = 1; i < step; i++) {
                document.getElementById('step-ind-' + i).classList.add('completed');
            }
        }
        
        function prevStep(step) {
            nextStep(step);
        }
        
        function verifyLicense() {
            const btn = document.getElementById('btn-verify');
            const alert = document.getElementById('license-alert');
            
            const licenseKey = document.getElementById('license-key').value;
            const apiKey = document.getElementById('api-key').value;
            const email = document.getElementById('license-email').value;
            
            if (!licenseKey || !apiKey || !email) {
                alert.innerHTML = '<div class="alert alert-error">Please fill in all fields</div>';
                return;
            }
            
            btn.disabled = true;
            btn.textContent = 'Verifying...';
            
            fetch(getInstallPath('process.php'), {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'verify_license',
                    license_key: licenseKey,
                    api_key: apiKey,
                    email: email
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    nextStep(3);
                } else {
                    alert.innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
                }
            })
            .catch(error => {
                alert.innerHTML = '<div class="alert alert-error">Connection error. Please try again.</div>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Verify & Continue';
            });
        }
        
        function testDatabase() {
            const btn = document.getElementById('btn-test-db');
            const alert = document.getElementById('db-alert');
            
            const host = document.getElementById('db-host').value;
            const name = document.getElementById('db-name').value;
            const user = document.getElementById('db-user').value;
            const pass = document.getElementById('db-pass').value;
            
            if (!host || !name || !user) {
                alert.innerHTML = '<div class="alert alert-error">Please fill in required fields</div>';
                return;
            }
            
            btn.disabled = true;
            btn.textContent = 'Testing...';
            
            fetch(getInstallPath('process.php'), {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'test_database',
                    db_host: host,
                    db_name: name,
                    db_user: user,
                    db_pass: pass
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    nextStep(4);
                } else {
                    alert.innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
                }
            })
            .catch(error => {
                alert.innerHTML = '<div class="alert alert-error">Connection error. Please try again.</div>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Test & Continue';
            });
        }
        
        function completeInstallation() {
            const btn = document.getElementById('btn-install');
            const alert = document.getElementById('admin-alert');
            
            const name = document.getElementById('admin-name').value;
            const email = document.getElementById('admin-email').value;
            const password = document.getElementById('admin-password').value;
            const confirmPassword = document.getElementById('admin-password-confirm').value;
            
            if (!name || !email || !password || !confirmPassword) {
                alert.innerHTML = '<div class="alert alert-error">Please fill in all fields</div>';
                return;
            }
            
            if (password.length < 8) {
                alert.innerHTML = '<div class="alert alert-error">Password must be at least 8 characters</div>';
                return;
            }
            
            if (password !== confirmPassword) {
                alert.innerHTML = '<div class="alert alert-error">Passwords do not match</div>';
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = 'Installing... <div class="spinner" style="display: inline-block; width: 20px; height: 20px; margin-left: 10px;"></div>';
            
            fetch(getInstallPath('process.php'), {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'complete_installation',
                    admin_name: name,
                    admin_email: email,
                    admin_password: password
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    nextStep(5);
                } else {
                    alert.innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
                    btn.disabled = false;
                    btn.textContent = 'Complete Installation';
                }
            })
            .catch(error => {
                alert.innerHTML = '<div class="alert alert-error">Installation failed. Please try again.</div>';
                btn.disabled = false;
                btn.textContent = 'Complete Installation';
            });
        }
    </script>
</body>
</html>
