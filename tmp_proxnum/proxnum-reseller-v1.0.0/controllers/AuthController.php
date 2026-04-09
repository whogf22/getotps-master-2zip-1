<?php
/**
 * Authentication Controller
 */

namespace Controllers;

use Core\Controller;
use Core\Helper;

class AuthController extends Controller {
    
    /**
     * Show login page
     */
    public function login() {
        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth/login', [
            'csrf_token' => $this->generateCsrf()
        ]);
    }
    
    /**
     * Handle login
     */
    public function handleLogin() {
        $this->validateCsrf();
        
        $email = Helper::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($email) || empty($password)) {
            $this->json(['success' => false, 'message' => 'Email and password are required']);
        }
        
        // Get user
        $user = $this->db->fetch('SELECT * FROM users WHERE email = ?', [$email]);
        
        if (!$user) {
            Helper::logActivity('login_failed', 'Failed login attempt for: ' . $email, null);
            $this->json(['success' => false, 'message' => 'Invalid credentials']);
        }
        
        // Verify password
        if (!Helper::verifyPassword($password, $user['password'])) {
            Helper::logActivity('login_failed', 'Invalid password for: ' . $email, null);
            $this->json(['success' => false, 'message' => 'Invalid credentials']);
        }
        
        // Check if account is active
        if ($user['status'] !== 'active') {
            $this->json(['success' => false, 'message' => 'Your account is ' . $user['status']]);
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Update last login
        $this->db->update('users', [
            'last_login' => date('Y-m-d H:i:s')
        ], 'id = ?', [$user['id']]);
        
        Helper::logActivity('login', 'User logged in', $user['id']);
        
        // Redirect URL (return absolute path for client-side redirect)
        $redirectUrl = $user['role'] === 'admin' ? Helper::url('/admin') : Helper::url('/dashboard');
        
        $this->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => $redirectUrl
        ]);
    }
    
    /**
     * Logout
     */
    public function logout() {
        Helper::logActivity('logout', 'User logged out');
        
        session_destroy();
        $this->redirect('/auth/login');
    }
    
    /**
     * Register (if enabled)
     */
    public function register() {
        $allowRegistration = Helper::getSetting('allow_registration', '0');
        
        if ($allowRegistration !== '1') {
            $this->redirect('/auth/login');
        }
        
        $this->view('auth/register', [
            'csrf_token' => $this->generateCsrf()
        ]);
    }
    
    /**
     * Handle registration
     */
    public function handleRegister() {
        $allowRegistration = Helper::getSetting('allow_registration', '0');
        
        if ($allowRegistration !== '1') {
            $this->json(['success' => false, 'message' => 'Registration is disabled']);
        }
        
        $this->validateCsrf();
        
        $name = Helper::sanitize($_POST['name'] ?? '');
        $email = Helper::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            $this->json(['success' => false, 'message' => 'All fields are required']);
        }
        
        if (!Helper::validateEmail($email)) {
            $this->json(['success' => false, 'message' => 'Invalid email address']);
        }
        
        if (strlen($password) < 8) {
            $this->json(['success' => false, 'message' => 'Password must be at least 8 characters']);
        }
        
        if ($password !== $confirmPassword) {
            $this->json(['success' => false, 'message' => 'Passwords do not match']);
        }
        
        // Check if email exists
        $exists = $this->db->fetch('SELECT id FROM users WHERE email = ?', [$email]);
        if ($exists) {
            $this->json(['success' => false, 'message' => 'Email already registered']);
        }
        
        // Create user
        $userId = $this->db->insert('users', [
            'name' => $name,
            'email' => $email,
            'password' => Helper::hashPassword($password),
            'role' => 'client',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        Helper::logActivity('register', 'New user registered', $userId);
        
        // Send welcome email if enabled
        $signupEmailEnabled = Helper::getSetting('mail_signup_enabled', '0');
        if ($signupEmailEnabled === '1') {
            try {
                require_once __DIR__ . '/../core/Mailer.php';
                
                $siteName = Helper::getSetting('site_name', 'Proxnum Reseller');
                $initialBalance = 0.00;
                
                \Core\Mailer::sendTemplate($email, 'welcome_email', [
                    'name' => $name,
                    'site_name' => $siteName,
                    'balance' => $initialBalance
                ]);
                
                Helper::logActivity('welcome_email_sent', 'Welcome email sent to ' . $email, $userId);
            } catch (\Exception $e) {
                // Log error but don't fail registration
                Helper::logActivity('welcome_email_failed', 'Failed to send welcome email: ' . $e->getMessage(), $userId);
            }
        }
        
        // Auto login
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'client';
        
        $this->json([
            'success' => true,
            'message' => 'Registration successful',
            'redirect' => Helper::url('/dashboard')
        ]);
    }
}
