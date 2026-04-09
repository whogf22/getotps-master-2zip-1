<?php

namespace Core;

class UpdateManager {
    private $updateServerUrl;
    private $currentVersion;
    private $licenseKey;
    private $backupDir;
    private $rootDir;
    
    public function __construct() {
        $this->updateServerUrl = \Helper::getSetting('update_server_url', 'https://proxnum.com.com/updates');
        $this->currentVersion = \Helper::getSetting('app_version', '1.0.0');
        $this->licenseKey = \Helper::getSetting('license_key', '');
        $this->rootDir = dirname(__DIR__);
        $this->backupDir = $this->rootDir . '/backups';
        
        // Create backup directory if not exists
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Check if update is available
     */
    public function checkForUpdates() {
        try {
            $response = $this->callUpdateServer('', [
                'current_version' => $this->currentVersion,
                'license_key' => $this->licenseKey,
                'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost'
            ]);
            
            // Debug logging
            \Helper::logActivity('update_check_response', 'Update check response: ' . json_encode($response));
            
            if ($response && isset($response['success']) && $response['success'] && isset($response['update_available'])) {
                return [
                    'update_available' => $response['update_available'],
                    'latest_version' => $response['latest_version'] ?? null,
                    'changelog' => $response['changelog'] ?? '',
                    'download_url' => $response['download_url'] ?? '',
                    'release_date' => $response['release_date'] ?? '',
                    'size' => $response['size'] ?? 0,
                    'critical' => $response['critical'] ?? false
                ];
            }
            
            return ['update_available' => false];
        } catch (\Exception $e) {
            \Helper::logActivity('update_check_failed', 'Failed to check for updates: ' . $e->getMessage());
            return ['update_available' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Perform full backup before update
     */
    public function createBackup() {
        $backupName = 'backup_' . date('Y-m-d_His') . '_v' . $this->currentVersion;
        $backupPath = $this->backupDir . '/' . $backupName;
        
        try {
            // Create backup directory
            if (!mkdir($backupPath, 0755, true)) {
                throw new \Exception('Failed to create backup directory');
            }
            
            // Files and directories to backup
            $itemsToBackup = [
                'app',
                'controllers',
                'core',
                'models',
                'views',
                'routes',
                'config',
                'public',
                'assets',
                '.htaccess',
                'index.php'
            ];
            
            // Copy files
            foreach ($itemsToBackup as $item) {
                $source = $this->rootDir . '/' . $item;
                $dest = $backupPath . '/' . $item;
                
                if (file_exists($source)) {
                    if (is_dir($source)) {
                        $this->copyDirectory($source, $dest);
                    } else {
                        copy($source, $dest);
                    }
                }
            }
            
            // Backup database settings
            $dbConfig = [
                'version' => $this->currentVersion,
                'backup_date' => date('Y-m-d H:i:s'),
                'settings' => $this->exportSettings()
            ];
            
            file_put_contents($backupPath . '/backup_info.json', json_encode($dbConfig, JSON_PRETTY_PRINT));
            
            \Helper::logActivity('backup_created', 'Backup created: ' . $backupName);
            
            return [
                'success' => true,
                'backup_path' => $backupPath,
                'backup_name' => $backupName
            ];
        } catch (\Exception $e) {
            \Helper::logActivity('backup_failed', 'Backup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Download and install update
     */
    public function downloadAndInstall($downloadUrl, $backupPath = null) {
        try {
            // Step 1: Download update package
            \Helper::logActivity('update_download_start', 'Downloading update from: ' . $downloadUrl);
            
            $tempFile = $this->rootDir . '/storage/temp_update.zip';
            $updateContent = $this->downloadFile($downloadUrl);
            
            if (!$updateContent) {
                throw new \Exception('Failed to download update package');
            }
            
            file_put_contents($tempFile, $updateContent);
            
            // Step 2: Verify package
            if (!$this->verifyPackage($tempFile)) {
                unlink($tempFile);
                throw new \Exception('Update package verification failed');
            }
            
            // Step 3: Extract update
            \Helper::logActivity('update_extract', 'Extracting update package');
            
            $zip = new \ZipArchive();
            if ($zip->open($tempFile) !== true) {
                unlink($tempFile);
                throw new \Exception('Failed to open update package');
            }
            
            $extractPath = $this->rootDir . '/storage/temp_update';
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            
            // Step 4: Apply update
            \Helper::logActivity('update_apply', 'Applying update files');
            
            $this->applyUpdate($extractPath);
            
            // Step 5: Run migrations if exists
            $migrationFile = $extractPath . '/migrations.php';
            if (file_exists($migrationFile)) {
                \Helper::logActivity('update_migrations', 'Running database migrations');
                require_once $migrationFile;
                
                if (function_exists('runMigrations')) {
                    runMigrations();
                }
            }
            
            // Step 6: Update version
            $versionFile = $extractPath . '/version.txt';
            if (file_exists($versionFile)) {
                $newVersion = trim(file_get_contents($versionFile));
                \Helper::updateSetting('app_version', $newVersion);
                $this->currentVersion = $newVersion;
            }
            
            // Step 7: Cleanup
            unlink($tempFile);
            $this->deleteDirectory($extractPath);
            
            \Helper::logActivity('update_success', 'Update completed successfully to version ' . $this->currentVersion);
            
            return [
                'success' => true,
                'version' => $this->currentVersion,
                'message' => 'Update installed successfully'
            ];
            
        } catch (\Exception $e) {
            \Helper::logActivity('update_failed', 'Update failed: ' . $e->getMessage());
            
            // Rollback if backup exists
            if ($backupPath && is_dir($backupPath)) {
                \Helper::logActivity('update_rollback', 'Rolling back to backup');
                $this->rollback($backupPath);
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Apply update files
     */
    private function applyUpdate($extractPath) {
        $updateFiles = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($updateFiles as $file) {
            $relativePath = str_replace($extractPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $targetPath = $this->rootDir . DIRECTORY_SEPARATOR . $relativePath;
            
            // Skip special files
            if (in_array(basename($file), ['migrations.php', 'version.txt', 'update_info.json'])) {
                continue;
            }
            
            if ($file->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                // Ensure target directory exists
                $targetDir = dirname($targetPath);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                
                // Copy file
                copy($file->getPathname(), $targetPath);
            }
        }
    }
    
    /**
     * Rollback to backup
     */
    public function rollback($backupPath) {
        try {
            if (!is_dir($backupPath)) {
                throw new \Exception('Backup directory not found');
            }
            
            // Write status file to track progress
            $statusFile = $this->rootDir . '/rollback_status.json';
            file_put_contents($statusFile, json_encode([
                'status' => 'in_progress',
                'started_at' => date('Y-m-d H:i:s')
            ]));
            
            // Skip core files that are currently executing to prevent PHP crash
            $skipFiles = ['core/UpdateManager.php', 'controllers/AdminController.php', 'core/App.php'];
            
            // Restore files
            $backupFiles = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($backupPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($backupFiles as $file) {
                $relativePath = str_replace($backupPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $relativePath = str_replace('\\', '/', $relativePath); // Normalize path
                $targetPath = $this->rootDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
                
                // Skip backup_info.json and currently executing files
                if (basename($file) === 'backup_info.json' || in_array($relativePath, $skipFiles)) {
                    continue;
                }
                
                if ($file->isDir()) {
                    if (!is_dir($targetPath)) {
                        mkdir($targetPath, 0755, true);
                    }
                } else {
                    // Ensure target directory exists
                    $targetDir = dirname($targetPath);
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    copy($file->getPathname(), $targetPath);
                }
            }
            
            // Restore version
            $backupInfo = $backupPath . '/backup_info.json';
            if (file_exists($backupInfo)) {
                $info = json_decode(file_get_contents($backupInfo), true);
                if (isset($info['version'])) {
                    \Helper::updateSetting('app_version', $info['version']);
                }
            }
            
            // Update status file
            file_put_contents($statusFile, json_encode([
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ]));
            
            \Helper::logActivity('rollback_success', 'Rollback completed successfully');
            
            return ['success' => true, 'message' => 'Rollback completed'];
        } catch (\Exception $e) {
            // Write error to status file
            $statusFile = $this->rootDir . '/rollback_status.json';
            file_put_contents($statusFile, json_encode([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => date('Y-m-d H:i:s')
            ]));
            
            \Helper::logActivity('rollback_failed', 'Rollback failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * List available backups
     */
    public function listBackups() {
        $backups = [];
        
        if (!is_dir($this->backupDir)) {
            return $backups;
        }
        
        $dirs = array_diff(scandir($this->backupDir), ['.', '..']);
        
        foreach ($dirs as $dir) {
            $backupPath = $this->backupDir . '/' . $dir;
            if (is_dir($backupPath)) {
                $infoFile = $backupPath . '/backup_info.json';
                $info = [];
                
                if (file_exists($infoFile)) {
                    $info = json_decode(file_get_contents($infoFile), true);
                }
                
                $backups[] = [
                    'name' => $dir,
                    'path' => $backupPath,
                    'version' => $info['version'] ?? 'Unknown',
                    'date' => $info['backup_date'] ?? '',
                    'size' => $this->getDirectorySize($backupPath)
                ];
            }
        }
        
        // Sort by date descending
        usort($backups, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });
        
        return $backups;
    }
    
    /**
     * Delete old backups (keep last N)
     */
    public function cleanupOldBackups($keepCount = 3) {
        $backups = $this->listBackups();
        
        if (count($backups) <= $keepCount) {
            return;
        }
        
        $toDelete = array_slice($backups, $keepCount);
        
        foreach ($toDelete as $backup) {
            $this->deleteDirectory($backup['path']);
            \Helper::logActivity('backup_deleted', 'Old backup deleted: ' . $backup['name']);
        }
    }
    
    /**
     * Call update server API
     */
    private function callUpdateServer($endpoint, $params = []) {
        $url = $this->updateServerUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost testing
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Debug logging
        \Helper::logActivity('update_server_request', "URL: $url, HTTP Code: $httpCode, Response: " . substr($response, 0, 500));
        
        if ($error) {
            throw new \Exception('Update server error: ' . $error);
        }
        
        $decoded = json_decode($response, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            \Helper::logActivity('update_json_error', 'JSON decode error: ' . json_last_error_msg() . ', Response: ' . substr($response, 0, 200));
            throw new \Exception('Invalid JSON response from update server');
        }
        
        return $decoded;
    }
    
    /**
     * Download file from URL
     */
    private function downloadFile($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $content = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('Download failed: ' . $error);
        }
        
        return $content;
    }
    
    /**
     * Verify update package integrity
     */
    private function verifyPackage($filePath) {
        // Check if it's a valid ZIP file
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return false;
        }
        
        // Check for required files
        $requiredFiles = ['version.txt'];
        foreach ($requiredFiles as $file) {
            if ($zip->locateName($file) === false) {
                $zip->close();
                return false;
            }
        }
        
        $zip->close();
        return true;
    }
    
    /**
     * Export settings for backup
     */
    private function exportSettings() {
        $db = \Core\Database::getInstance();
        $settings = $db->fetchAll('SELECT * FROM settings');
        return $settings;
    }
    
    /**
     * Copy directory recursively
     */
    private function copyDirectory($source, $dest) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        
        $files = array_diff(scandir($source), ['.', '..']);
        
        foreach ($files as $file) {
            $sourcePath = $source . '/' . $file;
            $destPath = $dest . '/' . $file;
            
            if (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }
    }
    
    /**
     * Delete directory recursively
     */
    private function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
    
    /**
     * Get directory size
     */
    private function getDirectorySize($dir) {
        $size = 0;
        
        if (!is_dir($dir)) {
            return 0;
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }
    
    /**
     * Format file size
     */
    public static function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
