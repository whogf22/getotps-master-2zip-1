<?php
/**
 * Helper Functions
 */

namespace Core;

class Helper {
    /**
     * Get base path for URLs
     */
    public static function url($path = '') {
        static $basePath = null;
        
        if ($basePath === null) {
            $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        }
        
        // If empty path, return base path without trailing slash
        if ($path === '' || $path === null) {
            return $basePath;
        }
        
        // If path starts with /, prepend base path
        if (strpos($path, '/') === 0) {
            return $basePath . $path;
        }
        
        return $basePath . '/' . ltrim($path, '/');
    }
    
    /**
     * Sanitize input
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
        } else {
            $data = htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
    
    /**
     * Format money
     */
    public static function money($amount, $currency = '$') {
        return $currency . number_format($amount, 2);
    }
    
    /**
     * Format date
     */
    public static function date($date, $format = 'Y-m-d H:i:s') {
        if (!$date) return '-';
        return date($format, strtotime($date));
    }
    
    /**
     * Time ago
     */
    public static function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        // Handle negative values (future dates or timezone issues)
        if ($diff < 0) {
            return 'Just now';
        }
        
        if ($diff < 60) {
            return $diff . ' seconds ago';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' days ago';
        } else {
            return date('M d, Y', $timestamp);
        }
    }
    
    /**
     * Generate random string
     */
    public static function randomString($length = 16) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Get client IP
     */
    public static function getClientIp() {
        $ip = $_SERVER['REMOTE_ADDR'];
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        return $ip;
    }
    
    /**
     * Log activity
     */
    public static function logActivity($action, $description = '', $userId = null) {
        $db = Database::getInstance();
        
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        
        $db->insert('activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Generate API token
     */
    public static function generateApiToken() {
        return 'pxr_' . bin2hex(random_bytes(32));
    }
    
    /**
     * Encrypt data
     */
    public static function encrypt($data, $key = null) {
        if ($key === null) {
            $key = LICENSE_KEY;
        }
        
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * Decrypt data
     */
    public static function decrypt($data, $key = null) {
        if ($key === null) {
            $key = LICENSE_KEY;
        }
        
        list($encrypted, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    /**
     * Get setting
     */
    public static function getSetting($key, $default = null) {
        $db = Database::getInstance();
        $setting = $db->fetch('SELECT value FROM settings WHERE `key` = ?', [$key]);
        
        return $setting ? $setting['value'] : $default;
    }
    
    /**
     * Set setting
     */
    public static function setSetting($key, $value) {
        $db = Database::getInstance();
        
        $exists = $db->fetch('SELECT id FROM settings WHERE `key` = ?', [$key]);
        
        if ($exists) {
            $db->update('settings', ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')], '`key` = ?', [$key]);
        } else {
            $db->insert('settings', ['key' => $key, 'value' => $value, 'updated_at' => date('Y-m-d H:i:s')]);
        }
    }
    
    /**
     * Pagination
     */
    public static function paginate($total, $perPage, $currentPage) {
        $totalPages = ceil($total / $perPage);
        $offset = ($currentPage - 1) * $perPage;
        
        return [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'offset' => $offset,
            'has_prev' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages
        ];
    }
    
    /**
     * Format bytes to human readable
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Get country name from country code
     */
    public static function getCountryName($code) {
        static $countries = null;
        
        // Load countries from API on first call
        if ($countries === null) {
            $countries = [];
            
            // Try to get from cache first
            $cacheFile = __DIR__ . '/../storage/cache/countries.json';
            $cacheAge = file_exists($cacheFile) ? (time() - filemtime($cacheFile)) : PHP_INT_MAX;
            
            // Cache for 24 hours
            if ($cacheAge < 86400 && file_exists($cacheFile)) {
                $cached = json_decode(file_get_contents($cacheFile), true);
                if ($cached && is_array($cached)) {
                    $countries = $cached;
                }
            }
            
            // If not cached or expired, fetch from API
            if (empty($countries)) {
                try {
                    require_once __DIR__ . '/ProxnumApi.php';
                    $api = new \Core\ProxnumApi();
                    $result = $api->getCountries();
                    
                    if (isset($result['countries']) && is_array($result['countries'])) {
                        foreach ($result['countries'] as $country) {
                            if (isset($country['code']) && isset($country['name'])) {
                                $countries[$country['code']] = $country['name'];
                            }
                        }
                        
                        // Save to cache
                        if (!empty($countries)) {
                            $cacheDir = dirname($cacheFile);
                            if (!is_dir($cacheDir)) {
                                @mkdir($cacheDir, 0755, true);
                            }
                            @file_put_contents($cacheFile, json_encode($countries));
                        }
                    }
                } catch (\Exception $e) {
                    error_log("Failed to fetch countries: " . $e->getMessage());
                }
            }
        }
        
        // Return country name or code if not found
        return $countries[$code] ?? $code;
    }
    
    /**
     * Get CSRF token from session
     */
    public static function getCsrf() {
        // Generate token if it doesn't exist
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Check if current user is a demo account
     */
    public static function isDemo() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $db = Database::getInstance();
        $user = $db->fetch('SELECT is_demo FROM users WHERE id = ?', [$_SESSION['user_id']]);
        
        return $user && isset($user['is_demo']) && $user['is_demo'] == 1;
    }
    
    /**
     * Block action if demo mode
     * Returns JSON error and exits if demo user tries restricted action
     */
    public static function blockDemoAction($message = null) {
        if (self::isDemo()) {
            if ($message === null) {
                $message = 'This action is disabled in demo mode. Purchase a license to access full functionality.';
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $message,
                'demo_mode' => true
            ]);
            exit;
        }
    }
}
