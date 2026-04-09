<?php
/**
 * License Verification Class
 * Handles secure license validation
 */

namespace Core;

class License {
    private $licenseKey;
    private $email;
    private $domain;
    private $cacheFile;
    
    public function __construct() {
        $this->licenseKey = defined('LICENSE_KEY') ? LICENSE_KEY : '';
        $this->email = defined('LICENSE_EMAIL') ? LICENSE_EMAIL : '';
        $this->domain = $_SERVER['HTTP_HOST'];
        $this->cacheFile = BASE_PATH . '/cache/license.cache';
    }
    
    /**
     * Verify license validity
     */
    public function verify() {
        // Check cache first
        if ($this->checkCache()) {
            return true;
        }
        
        // Verify with central server
        if ($this->verifyOnline()) {
            $this->updateCache();
            return true;
        }
        
        return false;
    }
    
    /**
     * Check cached license
     */
    private function checkCache() {
        if (!file_exists($this->cacheFile)) {
            return false;
        }
        
        $cache = json_decode(file_get_contents($this->cacheFile), true);
        
        if (!$cache || !isset($cache['expires_at'])) {
            return false;
        }
        
        // Check if cache expired (24 hours)
        if (time() > $cache['expires_at']) {
            return false;
        }
        
        // Verify signature
        $signature = $this->generateSignature($cache['data']);
        if ($signature !== $cache['signature']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Verify license online
     */
    private function verifyOnline() {
        // Use configured license server URL, or auto-detect environment
        if (defined('LICENSE_SERVER_URL')) {
            $baseUrl = LICENSE_SERVER_URL;
        } else {
            // Auto-detect: localhost = local server, otherwise production
            $isLocal = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
                       strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
            $baseUrl = $isLocal ? 'http://localhost/pxnme/api/v1' : 'https://proxnum.com/api/v1';
        }
        
        $url = $baseUrl . '/verify-license';
        
        $data = [
            'license_key' => $this->licenseKey,
            'license_email' => $this->email,
            'domain' => $this->domain,
            'version' => VERSION
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: ProxnumReseller/' . VERSION
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // If server unreachable, allow 72 hours grace period
        if ($httpCode === 0 || $httpCode >= 500) {
            return $this->checkGracePeriod();
        }
        
        if ($httpCode !== 200) {
            return false;
        }
        
        $result = json_decode($response, true);
        
        return $result && isset($result['success']) && $result['success'] === true;
    }
    
    /**
     * Update license cache
     */
    private function updateCache() {
        $cacheDir = dirname($this->cacheFile);
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $data = [
            'license_key' => $this->licenseKey,
            'domain' => $this->domain,
            'verified_at' => time()
        ];
        
        $cache = [
            'data' => $data,
            'expires_at' => time() + 86400, // 24 hours
            'signature' => $this->generateSignature($data)
        ];
        
        file_put_contents($this->cacheFile, json_encode($cache));
    }
    
    /**
     * Generate security signature
     */
    private function generateSignature($data) {
        $string = json_encode($data) . $this->licenseKey . $this->email;
        return hash_hmac('sha256', $string, 'proxnum_reseller_secret_key');
    }
    
    /**
     * Check grace period (72 hours)
     */
    private function checkGracePeriod() {
        if (!file_exists($this->cacheFile)) {
            return false;
        }
        
        $cache = json_decode(file_get_contents($this->cacheFile), true);
        
        if (!$cache || !isset($cache['data']['verified_at'])) {
            return false;
        }
        
        // Allow 72 hours grace period
        $gracePeriod = 72 * 3600;
        return (time() - $cache['data']['verified_at']) < $gracePeriod;
    }
    
    /**
     * Get license info
     */
    public function getInfo() {
        if (!file_exists($this->cacheFile)) {
            return null;
        }
        
        $cache = json_decode(file_get_contents($this->cacheFile), true);
        return $cache['data'] ?? null;
    }
    
    /**
     * Force license revalidation
     */
    public function revalidate() {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
        return $this->verify();
    }
}
