<?php
/**
 * Proxnum Reseller System
 * Main Entry Point
 * 
 * @version 1.0.0
 * @license Commercial
 */

// Start session
session_start();

// Set timezone to UTC to prevent timestamp inconsistencies
date_default_timezone_set('UTC');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

// Define constants
define('BASE_PATH', __DIR__);
define('VERSION', '1.0.0');

// Check if installed
$isInstalled = file_exists(BASE_PATH . '/config/database.php');
$route = $_GET['route'] ?? '';
$isInstallerRoute = (strpos($route, 'install') !== false);

if (!$isInstalled && !$isInstallerRoute) {
    // Not installed and not accessing installer, redirect to installer
    $installPath = dirname($_SERVER['PHP_SELF']) . '/install/index.php';
    header('Location: ' . $installPath);
    exit;
}

// Load configuration if installed
if (file_exists(BASE_PATH . '/config/database.php')) {
    require_once BASE_PATH . '/config/database.php';
    require_once BASE_PATH . '/config/app.php';
    
    // License verification
    require_once BASE_PATH . '/core/License.php';
    $license = new \Core\License();
    if (!$license->verify()) {
        die('<h1>License Error</h1><p>Your license is invalid or expired. Please contact support.</p>');
    }
}

// Autoloader
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    $file = BASE_PATH . '/' . strtolower($class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load core classes
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/App.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Helper.php';

// Create class alias for Helper so views can use it without namespace
class_alias('Core\Helper', 'Helper');

// Initialize application
$app = new \Core\App();
$app->run();
