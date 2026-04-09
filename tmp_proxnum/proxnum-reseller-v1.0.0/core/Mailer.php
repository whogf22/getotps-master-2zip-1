<?php
/**
 * Email/Mailer Class
 * Handles all email sending functionality using SMTP or PHP mail()
 */

namespace Core;

// Load composer autoloader if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $db;
    private $settings;
    
    /**
     * Initialize mailer
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->loadSettings();
    }
    
    /**
     * Load email settings from database
     */
    private function loadSettings() {
        try {
            $settings = $this->db->fetchAll('SELECT `key`, value FROM settings WHERE `key` LIKE "mail_%"');
            
            $this->settings = [];
            foreach ($settings as $setting) {
                $this->settings[$setting['key']] = $setting['value'];
            }
            
            // Set defaults if not configured
            if (empty($this->settings['mail_from_address'])) {
                $this->settings['mail_from_address'] = 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'example.com');
            }
            
            if (empty($this->settings['mail_from_name'])) {
                $result = $this->db->fetch('SELECT setting_value FROM settings WHERE setting_key = ?', ['site_name']);
                $this->settings['mail_from_name'] = $result['setting_value'] ?? 'SMS Reseller';
            }
        } catch (\Exception $e) {
            // Initialize with defaults if database query fails
            $this->settings = [
                'mail_smtp_enabled' => '0',
                'mail_from_address' => 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'example.com'),
                'mail_from_name' => 'SMS Reseller',
                'mail_smtp_host' => '',
                'mail_smtp_port' => '587',
                'mail_smtp_username' => '',
                'mail_smtp_password' => '',
                'mail_smtp_encryption' => 'tls',
                'mail_smtp_auth' => '1'
            ];
            error_log("Mailer: Failed to load settings from database - " . $e->getMessage());
        }
    }
    
    /**
     * Send email using configured method
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string $toName Recipient name (optional)
     * @return bool Success status
     */
    public function send($to, $subject, $body, $toName = '') {
        // Check if SMTP is enabled
        $useSmtp = !empty($this->settings['mail_smtp_enabled']) && $this->settings['mail_smtp_enabled'] == '1';
        
        if ($useSmtp && !empty($this->settings['mail_smtp_host'])) {
            return $this->sendSmtp($to, $subject, $body, $toName);
        } else {
            return $this->sendPhpMail($to, $subject, $body, $toName);
        }
    }
    
    /**
     * Send email using SMTP (PHPMailer)
     */
    private function sendSmtp($to, $subject, $body, $toName = '') {
        // Check if PHPMailer is available
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            // Fall back to PHP mail if PHPMailer not installed
            return $this->sendPhpMail($to, $subject, $body, $toName);
        }
        
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->settings['mail_smtp_host'] ?? 'localhost';
            $mail->SMTPAuth = !empty($this->settings['mail_smtp_auth']) && $this->settings['mail_smtp_auth'] == '1';
            $mail->Username = $this->settings['mail_smtp_username'] ?? '';
            $mail->Password = $this->settings['mail_smtp_password'] ?? '';
            $mail->SMTPSecure = $this->settings['mail_smtp_encryption'] ?? 'tls';
            $mail->Port = (int)($this->settings['mail_smtp_port'] ?? 587);
            
            // Timeout settings
            $mail->Timeout = 10;
            $mail->SMTPKeepAlive = false;
            
            // Recipients
            $mail->setFrom($this->settings['mail_from_address'], $this->settings['mail_from_name']);
            $mail->addAddress($to, $toName);
            $mail->addReplyTo($this->settings['mail_from_address'], $this->settings['mail_from_name']);
            
            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body = $this->wrapBody($body);
            $mail->AltBody = strip_tags($body);
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            // Log detailed error
            $errorMsg = 'SMTP Mail Error: ' . $mail->ErrorInfo . ' | Exception: ' . $e->getMessage();
            error_log($errorMsg);
            
            // Write to error file for debugging
            $logFile = __DIR__ . '/../logs/email_errors.log';
            if (!file_exists(dirname($logFile))) {
                @mkdir(dirname($logFile), 0755, true);
            }
            @file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $errorMsg . "\n", FILE_APPEND);
            
            // Don't fallback during test - return false to show real error
            return false;
        }
    }
    
    /**
     * Send email using PHP mail() function
     */
    private function sendPhpMail($to, $subject, $body, $toName = '') {
        $fromAddress = $this->settings['mail_from_address'];
        $fromName = $this->settings['mail_from_name'];
        
        // Headers
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $fromName . ' <' . $fromAddress . '>';
        $headers[] = 'Reply-To: ' . $fromAddress;
        $headers[] = 'X-Mailer: PHP/' . phpversion();
        
        $fullBody = $this->wrapBody($body);
        
        return mail($to, $subject, $fullBody, implode("\r\n", $headers));
    }
    
    /**
     * Wrap email body with HTML template
     */
    private function wrapBody($body) {
        $siteName = $this->settings['mail_from_name'];
        $year = date('Y');
        
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #667eea; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; }
        .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
        a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>{$siteName}</h1>
        </div>
        <div class='content'>
            {$body}
        </div>
        <div class='footer'>
            <p>&copy; {$year} {$siteName}. All rights reserved.</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>";
    }
    
    /**
     * Send email using template (static method)
     * 
     * @param string $to Recipient email
     * @param string $templateName Template name from database
     * @param array $variables Variables to replace in template
     * @param string $toName Recipient name
     * @return bool Success status
     */
    public static function sendTemplate($to, $templateName, $variables = [], $toName = '') {
        $mailer = new self();
        return $mailer->sendTemplateInstance($to, $templateName, $variables, $toName);
    }
    
    /**
     * Instance method for sending template email
     * 
     * @param string $to Recipient email
     * @param string $templateName Template name from database
     * @param array $variables Variables to replace in template
     * @param string $toName Recipient name
     * @return bool Success status
     */
    private function sendTemplateInstance($to, $templateName, $variables = [], $toName = '') {
        // Get template from database
        $template = $this->db->fetchOne(
            'SELECT subject, body FROM email_templates WHERE name = ?',
            [$templateName]
        );
        
        if (!$template) {
            error_log("Email template not found: {$templateName}");
            return false;
        }
        
        // Replace variables in subject and body
        $subject = $this->replaceVariables($template['subject'], $variables);
        $body = $this->replaceVariables($template['body'], $variables);
        
        return $this->send($to, $subject, $body, $toName);
    }
    
    /**
     * Replace variables in template
     */
    private function replaceVariables($text, $variables) {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        return $text;
    }
    
    /**
     * Test email configuration
     * 
     * @param string $testEmail Email to send test to
     * @return array Result with success status and message
     */
    public function testConnection($testEmail) {
        // Check if PHPMailer is available
        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            return [
                'success' => false,
                'message' => '❌ PHPMailer library not installed. Run: cd proxnum-reseller && composer install'
            ];
        }
        
        // Check if SMTP settings are configured
        if (empty($this->settings['mail_smtp_host'])) {
            return [
                'success' => false,
                'message' => '❌ SMTP host not configured. Please enter your SMTP host.'
            ];
        }
        
        if (empty($this->settings['mail_from_address'])) {
            return [
                'success' => false,
                'message' => '❌ From email address not configured. Please enter your sender email.'
            ];
        }
        
        $subject = 'Test Email from ' . $this->settings['mail_from_name'];
        $body = '<p>This is a test email to verify your SMTP configuration is working correctly.</p>
                 <p>If you received this, your email settings are properly configured!</p>
                 <p><strong>Configuration Details:</strong></p>
                 <ul>
                     <li>SMTP Host: ' . ($this->settings['mail_smtp_host'] ?? 'Not set') . '</li>
                     <li>Port: ' . ($this->settings['mail_smtp_port'] ?? 'Not set') . '</li>
                     <li>Encryption: ' . ($this->settings['mail_smtp_encryption'] ?? 'None') . '</li>
                     <li>From: ' . $this->settings['mail_from_address'] . '</li>
                 </ul>';
        
        $result = $this->send($testEmail, $subject, $body);
        
        return [
            'success' => $result,
            'message' => $result ? ' Test email sent successfully!' : '❌ Failed to send test email. Check error logs for details.'
        ];
    }
    
    /**
     * Quick send for notifications
     */
    public static function notify($to, $subject, $message, $toName = '') {
        $mailer = new self();
        return $mailer->send($to, $subject, $message, $toName);
    }
    
    /**
     * Send email to specific user
     * 
     * @param int $userId User ID to send to
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @return array Result with success status and message
     */
    public function sendToUser($userId, $subject, $body) {
        $user = $this->db->fetch('SELECT id, name, email FROM users WHERE id = ?', [$userId]);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        $result = $this->send($user['email'], $subject, $body, $user['name']);
        
        return [
            'success' => $result,
            'message' => $result ? 'Email sent successfully to ' . $user['name'] : 'Failed to send email to ' . $user['name']
        ];
    }
    
    /**
     * Send email to all users (with role filter)
     * 
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string $role User role to filter (null = all users, 'client', 'admin')
     * @return array Result with statistics
     */
    public function sendToAllUsers($subject, $body, $role = null) {
        // Build query - only send to subscribed users
        $query = 'SELECT id, name, email FROM users WHERE email_subscribed = 1';
        $params = [];
        
        if ($role) {
            $query .= ' AND role = ?';
            $params[] = $role;
        }
        
        $users = $this->db->fetchAll($query, $params);
        
        if (empty($users)) {
            return [
                'success' => false,
                'message' => 'No users found',
                'sent' => 0,
                'failed' => 0,
                'total' => 0
            ];
        }
        
        $sent = 0;
        $failed = 0;
        $failedEmails = [];
        
        foreach ($users as $user) {
            // Add unsubscribe link to bulk email
            $bodyWithUnsubscribe = $this->addUnsubscribeLink($body, $user['id']);
            
            $result = $this->send($user['email'], $subject, $bodyWithUnsubscribe, $user['name']);
            
            if ($result) {
                $sent++;
            } else {
                $failed++;
                $failedEmails[] = $user['email'];
            }
            
            // Small delay to avoid overwhelming SMTP server
            usleep(100000); // 0.1 second delay
        }
        
        $message = "Sent to {$sent} user(s)";
        if ($failed > 0) {
            $message .= ", {$failed} failed";
        }
        
        return [
            'success' => $sent > 0,
            'message' => $message,
            'sent' => $sent,
            'failed' => $failed,
            'total' => count($users),
            'failed_emails' => $failedEmails
        ];
    }
    
    /**
     * Add unsubscribe link to email body
     * 
     * @param string $body Original email body
     * @param int $userId User ID for unsubscribe token
     * @return string Body with unsubscribe link
     */
    private function addUnsubscribeLink($body, $userId) {
        // Generate unsubscribe token
        $token = $this->generateUnsubscribeToken($userId);
        
        // Build unsubscribe URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $protocol . '://' . $host;
        $unsubscribeUrl = $baseUrl . Helper::url('/unsubscribe?token=' . $token);
        
        // Add unsubscribe footer
        $footer = '
<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 12px; color: #888;">
    <p>Don\'t want to receive these emails?</p>
    <p><a href="' . $unsubscribeUrl . '" style="color: #007bff; text-decoration: underline;">Unsubscribe from bulk emails</a></p>
    <p style="font-size: 11px; margin-top: 10px;">You will still receive important account-related emails.</p>
</div>';
        
        return $body . $footer;
    }
    
    /**
     * Generate secure unsubscribe token
     * 
     * @param int $userId User ID
     * @return string Unsubscribe token
     */
    private function generateUnsubscribeToken($userId) {
        // Use a secret key for token generation
        $secretKey = defined('APP_KEY') ? APP_KEY : 'proxnum_reseller_secret_key_2026';
        return hash_hmac('sha256', $userId, $secretKey);
    }
    
    /**
     * Verify unsubscribe token
     * 
     * @param int $userId User ID
     * @param string $token Token to verify
     * @return bool Valid or not
     */
    public static function verifyUnsubscribeToken($userId, $token) {
        $secretKey = defined('APP_KEY') ? APP_KEY : 'proxnum_reseller_secret_key_2026';
        $expectedToken = hash_hmac('sha256', $userId, $secretKey);
        return hash_equals($expectedToken, $token);
    }
    
    /**
     * Send email to multiple specific users
     * 
     * @param array $userIds Array of user IDs
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @return array Result with statistics
     */
    public function sendToMultipleUsers($userIds, $subject, $body) {
        if (empty($userIds)) {
            return [
                'success' => false,
                'message' => 'No users selected',
                'sent' => 0,
                'failed' => 0,
                'total' => 0
            ];
        }
        
        // Build IN clause safely - only send to subscribed users
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        $users = $this->db->fetchAll(
            "SELECT id, name, email FROM users WHERE id IN ({$placeholders}) AND email_subscribed = 1",
            $userIds
        );
        
        if (empty($users)) {
            return [
                'success' => false,
                'message' => 'No valid users found',
                'sent' => 0,
                'failed' => 0,
                'total' => 0
            ];
        }
        
        $sent = 0;
        $failed = 0;
        $failedEmails = [];
        
        foreach ($users as $user) {
            // Add unsubscribe link to bulk email
            $bodyWithUnsubscribe = $this->addUnsubscribeLink($body, $user['id']);
            
            $result = $this->send($user['email'], $subject, $bodyWithUnsubscribe, $user['name']);
            
            if ($result) {
                $sent++;
            } else {
                $failed++;
                $failedEmails[] = $user['email'];
            }
            
            // Small delay to avoid overwhelming SMTP server
            usleep(100000); // 0.1 second delay
        }
        
        $message = "Sent to {$sent} user(s)";
        if ($failed > 0) {
            $message .= ", {$failed} failed";
        }
        
        return [
            'success' => $sent > 0,
            'message' => $message,
            'sent' => $sent,
            'failed' => $failed,
            'total' => count($users),
            'failed_emails' => $failedEmails
        ];
    }
}
