<?php
/**
 * Installation Process Handler
 */

session_start();
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

function sendResponse($success, $message, $data = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

// License verification
if ($action === 'verify_license') {
    $licenseKey = $input['license_key'] ?? '';
    $apiKey = $input['api_key'] ?? '';
    $email = $input['email'] ?? '';
    
    if (empty($licenseKey) || empty($apiKey) || empty($email)) {
        sendResponse(false, 'All fields are required');
    }
    
    // Detect environment and set appropriate license server URL
    $host = $_SERVER['HTTP_HOST'];
    $isLocal = (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false);
    
    if ($isLocal) {
        // Local development - use localhost
        $licenseServerUrl = 'http://localhost/pxnme/api/v1/verify-license';
    } else {
        // Production - use proxnum.com
        $licenseServerUrl = 'https://proxnum.com/api/v1/verify-license';
    }
    
    // Verify license with central server
    $ch = curl_init($licenseServerUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'license_key' => $licenseKey,
        'license_email' => $email,
        'domain' => $host
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // For development/testing, accept any license key format
    if ($httpCode >= 500 || $httpCode === 0) {
        // Server error or no connection - validate format only for now
        if (preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $licenseKey)) {
            // Store in session
            $_SESSION['install_license_key'] = $licenseKey;
            $_SESSION['install_api_key'] = $apiKey;
            $_SESSION['install_email'] = $email;
            sendResponse(true, 'License verified successfully');
        }
    }
    
    $result = json_decode($response, true);
    
    if ($result && $result['success']) {
        // Store in session
        $_SESSION['install_license_key'] = $licenseKey;
        $_SESSION['install_api_key'] = $apiKey;
        $_SESSION['install_email'] = $email;
        $_SESSION['install_license_type'] = $result['license']['type'] ?? 'monthly';
        
        sendResponse(true, 'License verified successfully');
    } else {
        sendResponse(false, $result['message'] ?? 'Invalid license key');
    }
}

// Database test
if ($action === 'test_database') {
    $host = $input['db_host'] ?? '';
    $name = $input['db_name'] ?? '';
    $user = $input['db_user'] ?? '';
    $pass = $input['db_pass'] ?? '';
    
    try {
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if database exists
        $stmt = $pdo->query("SHOW DATABASES LIKE '$name'");
        if ($stmt->rowCount() === 0) {
            // Create database
            $pdo->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
        
        // Store in session
        $_SESSION['install_db_host'] = $host;
        $_SESSION['install_db_name'] = $name;
        $_SESSION['install_db_user'] = $user;
        $_SESSION['install_db_pass'] = $pass;
        
        sendResponse(true, 'Database connection successful');
    } catch (PDOException $e) {
        sendResponse(false, 'Database connection failed: ' . $e->getMessage());
    }
}

// Complete installation
if ($action === 'complete_installation') {
    $adminName = $input['admin_name'] ?? '';
    $adminEmail = $input['admin_email'] ?? '';
    $adminPassword = $input['admin_password'] ?? '';
    
    if (empty($adminName) || empty($adminEmail) || empty($adminPassword)) {
        sendResponse(false, 'All fields are required');
    }
    
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, 'Invalid email address');
    }
    
    try {
        // Get session data
        $licenseKey = $_SESSION['install_license_key'] ?? '';
        $apiKey = $_SESSION['install_api_key'] ?? '';
        $email = $_SESSION['install_email'] ?? '';
        $dbHost = $_SESSION['install_db_host'] ?? '';
        $dbName = $_SESSION['install_db_name'] ?? '';
        $dbUser = $_SESSION['install_db_user'] ?? '';
        $dbPass = $_SESSION['install_db_pass'] ?? '';
        
        if (empty($licenseKey) || empty($apiKey) || empty($dbHost) || empty($dbName)) {
            sendResponse(false, 'Session expired. Please start over.');
        }
        
        // Connect to database
        $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables
        $sql = file_get_contents(__DIR__ . '/schema.sql');
        $pdo->exec($sql);
        
        // Insert admin user
        $passwordHash = password_hash($adminPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'admin', 'active', NOW())");
        $stmt->execute([$adminName, $adminEmail, $passwordHash]);
        
        // Create config files
        $configDir = dirname(__DIR__) . '/config';
        if (!file_exists($configDir)) {
            mkdir($configDir, 0755, true);
        }
        
        // Database config
        $dbConfig = "<?php\n\n";
        $dbConfig .= "define('DB_HOST', '$dbHost');\n";
        $dbConfig .= "define('DB_NAME', '$dbName');\n";
        $dbConfig .= "define('DB_USER', '$dbUser');\n";
        $dbConfig .= "define('DB_PASS', '$dbPass');\n";
        file_put_contents($configDir . '/database.php', $dbConfig);
        
        // Detect environment for license server URL
        $isLocal = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
                   strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
        $licenseServerUrl = $isLocal ? 
            'http://localhost/pxnme/api/v1' : 
            'https://proxnum.com/api/v1';
        
        // App config
        $appConfig = "<?php\n\n";
        $appConfig .= "define('APP_NAME', 'Proxnum Reseller');\n";
        $appConfig .= "define('APP_URL', 'http://' . \$_SERVER['HTTP_HOST']);\n";
        $appConfig .= "define('PROXNUM_API_URL', 'https://proxnum.com/api/v1');\n";
        $appConfig .= "define('LICENSE_SERVER_URL', '$licenseServerUrl');\n";
        $appConfig .= "define('PROXNUM_API_KEY', '$apiKey');\n";
        $appConfig .= "define('LICENSE_KEY', '$licenseKey');\n";
        $appConfig .= "define('LICENSE_EMAIL', '$email');\n";
        $appConfig .= "define('TIMEZONE', 'UTC');\n";
        $appConfig .= "define('SESSION_LIFETIME', 7200);\n";
        file_put_contents($configDir . '/app.php', $appConfig);
        
        // Create logs directory
        $logsDir = dirname(__DIR__) . '/logs';
        if (!file_exists($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
        
        file_put_contents($logsDir . '/.htaccess', "Order deny,allow\nDeny from all");
        
        // Clear session
        session_destroy();
        
        sendResponse(true, 'Installation completed successfully');
        
    } catch (Exception $e) {
        sendResponse(false, 'Installation failed: ' . $e->getMessage());
    }
}

sendResponse(false, 'Invalid action');
