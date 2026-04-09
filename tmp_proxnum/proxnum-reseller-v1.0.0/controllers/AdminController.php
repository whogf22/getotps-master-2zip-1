<?php
/**
 * Admin Controller
 * Handles admin panel functionality
 */

namespace Controllers;

use Core\Controller;
use Core\Helper;
use Core\ProxnumApi;

class AdminController extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
    }
    
    /**
     * Admin dashboard
     */
    public function index() {
        // Statistics
        $stats = [
            'total_clients' => $this->db->count('users', 'role = ?', ['client']),
            'active_clients' => $this->db->count('users', 'role = ? AND status = ?', ['client', 'active']),
            'total_activations' => $this->db->count('activations'),
            'pending_activations' => $this->db->count('activations', 'status = ?', ['pending']),
            'total_balance' => $this->db->fetch('SELECT SUM(balance) as total FROM users WHERE role = ?', ['client'])['total'] ?? 0,
            'today_revenue' => $this->db->fetch('SELECT SUM(amount) as total FROM transactions WHERE type = ? AND DATE(created_at) = CURDATE()', ['purchase'])['total'] ?? 0
        ];
        
        // Recent clients
        $recentClients = $this->db->fetchAll(
            'SELECT * FROM users WHERE role = ? ORDER BY created_at DESC LIMIT 10',
            ['client']
        );
        
        // Recent transactions
        $recentTransactions = $this->db->fetchAll(
            'SELECT t.*, u.name as user_name, u.email as user_email 
             FROM transactions t 
             JOIN users u ON t.user_id = u.id 
             ORDER BY t.created_at DESC LIMIT 10'
        );
        
        $this->view('admin/dashboard', [
            'stats' => $stats,
            'recent_clients' => $recentClients,
            'recent_transactions' => $recentTransactions
        ]);
    }
    
    /**
     * Manage clients
     */
    public function clients() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        
        $total = $this->db->count('users', 'role = ?', ['client']);
        $pagination = Helper::paginate($total, $perPage, $page);
        
        $clients = $this->db->fetchAll(
            'SELECT * FROM users WHERE role = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
            ['client', $perPage, $pagination['offset']]
        );
        
        $this->view('admin/clients', [
            'clients' => $clients,
            'pagination' => $pagination,
            'csrf_token' => $this->generateCsrf()
        ]);
    }
    
    /**
     * Add client
     */
    public function addClient() {
        $this->validateCsrf();
        
        $name = Helper::sanitize($_POST['name'] ?? '');
        $email = Helper::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $initialBalance = (float)($_POST['initial_balance'] ?? 0);
        
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            $this->json(['success' => false, 'message' => 'All fields are required']);
        }
        
        if (!Helper::validateEmail($email)) {
            $this->json(['success' => false, 'message' => 'Invalid email address']);
        }
        
        // Check if exists
        $exists = $this->db->fetch('SELECT id FROM users WHERE email = ?', [$email]);
        if ($exists) {
            $this->json(['success' => false, 'message' => 'Email already exists']);
        }
        
        // Create client
        $this->db->beginTransaction();
        
        try {
            $userId = $this->db->insert('users', [
                'name' => $name,
                'email' => $email,
                'password' => Helper::hashPassword($password),
                'role' => 'client',
                'balance' => $initialBalance,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Add initial balance transaction if > 0
            if ($initialBalance > 0) {
                $this->db->insert('transactions', [
                    'user_id' => $userId,
                    'type' => 'credit',
                    'amount' => $initialBalance,
                    'balance_before' => 0,
                    'balance_after' => $initialBalance,
                    'description' => 'Initial balance',
                    'created_by' => $_SESSION['user_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->db->commit();
            
            Helper::logActivity('client_added', 'Added new client: ' . $email);
            
            // Send welcome email if enabled
            $signupEmailEnabled = Helper::getSetting('mail_signup_enabled', '0');
            if ($signupEmailEnabled === '1') {
                try {
                    require_once __DIR__ . '/../core/Mailer.php';
                    
                    $siteName = Helper::getSetting('site_name', 'Proxnum Reseller');
                    
                    \Core\Mailer::sendTemplate($email, 'welcome_email', [
                        'name' => $name,
                        'site_name' => $siteName,
                        'balance' => $initialBalance
                    ]);
                    
                    Helper::logActivity('welcome_email_sent', 'Welcome email sent to ' . $email . ' (admin-created account)', $_SESSION['user_id']);
                } catch (\Exception $e) {
                    // Log error but don't fail client creation
                    Helper::logActivity('welcome_email_failed', 'Failed to send welcome email: ' . $e->getMessage(), $_SESSION['user_id']);
                }
            }
            
            $this->json(['success' => true, 'message' => 'Client added successfully']);
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->json(['success' => false, 'message' => 'Failed to add client']);
        }
    }
    
    /**
     * Add balance to client
     */
    public function addBalance() {
        $this->validateCsrf();
        
        $userId = (int)($_POST['user_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $description = Helper::sanitize($_POST['description'] ?? 'Balance added by admin');
        
        if ($userId <= 0 || $amount <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid data']);
        }
        
        // Get user
        $user = $this->db->fetch('SELECT * FROM users WHERE id = ? AND role = ?', [$userId, 'client']);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Client not found']);
        }
        
        $this->db->beginTransaction();
        
        try {
            $balanceBefore = $user['balance'];
            $balanceAfter = $balanceBefore + $amount;
            
            // Update balance
            $this->db->update('users', [
                'balance' => $balanceAfter,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$userId]);
            
            // Add transaction
            $this->db->insert('transactions', [
                'user_id' => $userId,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'created_by' => $_SESSION['user_id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->db->commit();
            
            Helper::logActivity('balance_added', "Added $$amount to client ID: $userId");
            
            $this->json([
                'success' => true,
                'message' => 'Balance added successfully',
                'new_balance' => $balanceAfter
            ]);
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->json(['success' => false, 'message' => 'Failed to add balance']);
        }
    }
    
    /**
     * Update client status
     */
    public function updateClientStatus() {
        $this->validateCsrf();
        
        $userId = (int)($_POST['user_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        
        if (!in_array($status, ['active', 'suspended', 'inactive'])) {
            $this->json(['success' => false, 'message' => 'Invalid status']);
        }
        
        $this->db->update('users', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ? AND role = ?', [$userId, 'client']);
        
        Helper::logActivity('client_status_updated', "Client ID $userId status changed to $status");
        
        $this->json(['success' => true, 'message' => 'Status updated']);
    }
    
    /**
     * Get client profile details
     */
    public function getClientProfile() {
        $userId = (int)($_GET['user_id'] ?? 0);
        
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Invalid user ID']);
        }
        
        // Get client info
        $client = $this->db->fetch('SELECT * FROM users WHERE id = ? AND role = ?', [$userId, 'client']);
        
        if (!$client) {
            $this->json(['success' => false, 'message' => 'Client not found']);
        }
        
        // Get total spent
        $totalSpent = $this->db->fetch(
            'SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type = ?',
            [$userId, 'debit']
        );
        
        // Get activations count (not rentals)
        $totalActivations = $this->db->count('activations', 'user_id = ?', [$userId]);
        $pendingActivations = $this->db->count('activations', 'user_id = ? AND status = ?', [$userId, 'pending']);
        $completedActivations = $this->db->count('activations', 'user_id = ? AND status = ?', [$userId, 'completed']);
        
        // Format data
        $client['balance'] = Helper::money($client['balance']);
        $client['created_at'] = Helper::date($client['created_at'], 'M d, Y');
        $client['last_login'] = $client['last_login'] ? Helper::timeAgo($client['last_login']) : 'Never';
        $client['total_spent'] = Helper::money($totalSpent['total'] ?? 0);
        $client['total_activations'] = $totalActivations;
        $client['pending_activations'] = $pendingActivations;
        $client['completed_activations'] = $completedActivations;
        
        // Remove password
        unset($client['password']);
        
        $this->json(['success' => true, 'client' => $client]);
    }
    
    /**
     * View transactions
     */
    public function transactions() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 50;
        
        $total = $this->db->count('transactions');
        $pagination = Helper::paginate($total, $perPage, $page);
        
        $transactions = $this->db->fetchAll(
            'SELECT t.*, u.name as user_name, u.email as user_email 
             FROM transactions t 
             JOIN users u ON t.user_id = u.id 
             ORDER BY t.created_at DESC 
             LIMIT ? OFFSET ?',
            [$perPage, $pagination['offset']]
        );
        
        $this->view('admin/transactions', [
            'transactions' => $transactions,
            'pagination' => $pagination
        ]);
    }
    
    /**
     * View activations history (admin)
     */
    public function activationsHistory() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 50;
        $filter = $_GET['filter'] ?? 'all';
        
        // Build query based on filter
        $where = '1=1';
        $params = [];
        
        if ($filter === 'pending') {
            $where .= ' AND a.status = ?';
            $params[] = 'pending';
        } elseif ($filter === 'completed') {
            $where .= ' AND a.status = ?';
            $params[] = 'completed';
        } elseif ($filter === 'expired') {
            $where .= ' AND a.status = ?';
            $params[] = 'expired';
        } elseif ($filter === 'cancelled') {
            $where .= ' AND a.status = ?';
            $params[] = 'cancelled';
        }
        
        $total = $this->db->count('activations a JOIN users u ON a.user_id = u.id', $where, $params);
        $pagination = Helper::paginate($total, $perPage, $page);
        
        $activations = $this->db->fetchAll(
            "SELECT a.*, u.name as user_name, u.email as user_email 
             FROM activations a 
             JOIN users u ON a.user_id = u.id 
             WHERE {$where}
             ORDER BY a.created_at DESC 
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $pagination['offset']])
        );
        
        // Get statistics
        $stats = [
            'total' => $this->db->count('activations'),
            'pending' => $this->db->count('activations', 'status = ?', ['pending']),
            'completed' => $this->db->count('activations', 'status = ?', ['completed']),
            'expired' => $this->db->count('activations', 'status = ?', ['expired']),
            'cancelled' => $this->db->count('activations', 'status = ?', ['cancelled'])
        ];
        
        $this->view('admin/activations_history', [
            'activations' => $activations,
            'pagination' => $pagination,
            'filter' => $filter,
            'stats' => $stats
        ]);
    }
    
    /**
     * Settings
     */
    public function settings() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateCsrf();
                
                $action = $_POST['action'] ?? 'system_settings';
                
                // Handle test email action separately
                if ($action === 'test_email') {
                    // Block demo accounts from sending test emails
                    if (Helper::isDemo()) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'Demo accounts cannot send test emails. This is a read-only demo.']);
                        exit;
                    }
                    
                    header('Content-Type: application/json');
                    
                    $testEmail = Helper::sanitize($_POST['test_email_address'] ?? '');
                    
                    if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
                        exit;
                    }
                    
                    // Save current settings temporarily
                    $mailSettings = [
                        'mail_smtp_enabled' => isset($_POST['mail_smtp_enabled']) ? '1' : '0',
                        'mail_smtp_host' => Helper::sanitize($_POST['mail_smtp_host'] ?? ''),
                        'mail_smtp_port' => (int)($_POST['mail_smtp_port'] ?? 587),
                        'mail_smtp_username' => Helper::sanitize($_POST['mail_smtp_username'] ?? ''),
                        'mail_smtp_password' => Helper::sanitize($_POST['mail_smtp_password'] ?? ''),
                        'mail_smtp_encryption' => Helper::sanitize($_POST['mail_smtp_encryption'] ?? 'tls'),
                        'mail_smtp_auth' => isset($_POST['mail_smtp_auth']) ? '1' : '0',
                        'mail_from_address' => Helper::sanitize($_POST['mail_from_address'] ?? ''),
                        'mail_from_name' => Helper::sanitize($_POST['mail_from_name'] ?? ''),
                    ];
                    
                    foreach ($mailSettings as $key => $value) {
                        Helper::setSetting($key, $value);
                    }
                    
                    // Load Mailer class
                    require_once __DIR__ . '/../core/Mailer.php';
                    
                    try {
                        // Check if email settings exist in database
                        $checkSettings = $this->db->fetch('SELECT COUNT(*) as count FROM settings WHERE `key` LIKE "mail_%"', []);
                        if (!$checkSettings || !isset($checkSettings['count']) || $checkSettings['count'] == 0) {
                            echo json_encode([
                                'success' => false, 
                                'message' => 'Email settings not found in database. Please import add_email_settings.sql first.'
                            ]);
                            exit;
                        }
                        
                        $mailer = new \Core\Mailer();
                        $result = $mailer->testConnection($testEmail);
                        
                        if ($result['success']) {
                            Helper::logActivity('test_email_sent', 'Test email sent to ' . $testEmail);
                            echo json_encode(['success' => true, 'message' => $result['message']]);
                        } else {
                            echo json_encode(['success' => false, 'message' => $result['message']]);
                        }
                    } catch (\Exception $e) {
                        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                    }
                    
                    exit;
                }
                
                // Handle system settings
                if ($action === 'system_settings') {
                    $settings = [
                        'site_name' => Helper::sanitize($_POST['site_name'] ?? ''),
                        'allow_registration' => isset($_POST['allow_registration']) ? '1' : '0',
                        'min_balance' => (float)($_POST['min_balance'] ?? 1),
                        'price_multiplier' => (float)($_POST['price_multiplier'] ?? 1)
                    ];
                    
                    foreach ($settings as $key => $value) {
                        Helper::setSetting($key, $value);
                    }
                    
                    Helper::logActivity('settings_updated', 'System settings updated');
                    
                    $_SESSION['settings_message'] = 'System settings saved successfully';
                }
                
                // Handle email settings
                if ($action === 'email_settings') {
                    // Block demo accounts from modifying email settings
                    if (Helper::isDemo()) {
                        $_SESSION['settings_message'] = 'Demo accounts cannot modify email settings. This is a read-only demo.';
                        header('Location: ' . Helper::url('/admin/settings'));
                        exit;
                    }
                    
                    $emailSettings = [
                        'mail_smtp_enabled' => isset($_POST['mail_smtp_enabled']) ? '1' : '0',
                        'mail_smtp_host' => Helper::sanitize($_POST['mail_smtp_host'] ?? ''),
                        'mail_smtp_port' => (int)($_POST['mail_smtp_port'] ?? 587),
                        'mail_smtp_username' => Helper::sanitize($_POST['mail_smtp_username'] ?? ''),
                        'mail_smtp_password' => Helper::sanitize($_POST['mail_smtp_password'] ?? ''),
                        'mail_smtp_encryption' => Helper::sanitize($_POST['mail_smtp_encryption'] ?? 'tls'),
                        'mail_smtp_auth' => isset($_POST['mail_smtp_auth']) ? '1' : '0',
                        'mail_from_address' => Helper::sanitize($_POST['mail_from_address'] ?? ''),
                        'mail_from_name' => Helper::sanitize($_POST['mail_from_name'] ?? ''),
                        'mail_signup_enabled' => isset($_POST['mail_signup_enabled']) ? '1' : '0',
                        'mail_low_balance_enabled' => isset($_POST['mail_low_balance_enabled']) ? '1' : '0',
                        'mail_activation_enabled' => isset($_POST['mail_activation_enabled']) ? '1' : '0',
                    ];
                    
                    foreach ($emailSettings as $key => $value) {
                        Helper::setSetting($key, $value);
                    }
                    
                    Helper::logActivity('email_settings_updated', 'Email/SMTP settings updated');
                    
                    $_SESSION['settings_message'] = 'Email settings saved successfully';
                }
                
                // Redirect to prevent form resubmission
                header('Location: ' . Helper::url('/admin/settings'));
                exit;
            } catch (\Exception $e) {
                $message = 'Error saving settings: ' . $e->getMessage();
            }
        }
        
        // Get message from session if available
        if (!isset($message)) {
            $message = $_SESSION['settings_message'] ?? null;
            unset($_SESSION['settings_message']);
        }
        
        $this->view('admin/settings', [
            'csrf_token' => $this->generateCsrf(),
            'message' => $message ?? null,
            'settings' => [
                'site_name' => Helper::getSetting('site_name', 'Proxnum Reseller'),
                'allow_registration' => Helper::getSetting('allow_registration', '0'),
                'min_balance' => Helper::getSetting('min_balance', '1.00'),
                'default_markup' => Helper::getSetting('default_markup', '0'),
                'price_multiplier' => Helper::getSetting('price_multiplier', '1'),
                // Email settings
                'mail_smtp_enabled' => Helper::getSetting('mail_smtp_enabled', '0'),
                'mail_smtp_host' => Helper::getSetting('mail_smtp_host', ''),
                'mail_smtp_port' => Helper::getSetting('mail_smtp_port', '587'),
                'mail_smtp_username' => Helper::getSetting('mail_smtp_username', ''),
                'mail_smtp_password' => Helper::getSetting('mail_smtp_password', ''),
                'mail_smtp_encryption' => Helper::getSetting('mail_smtp_encryption', 'tls'),
                'mail_smtp_auth' => Helper::getSetting('mail_smtp_auth', '1'),
                'mail_from_address' => Helper::getSetting('mail_from_address', ''),
                'mail_from_name' => Helper::getSetting('mail_from_name', ''),
                'mail_signup_enabled' => Helper::getSetting('mail_signup_enabled', '0'),
                'mail_low_balance_enabled' => Helper::getSetting('mail_low_balance_enabled', '0'),
                'mail_activation_enabled' => Helper::getSetting('mail_activation_enabled', '0'),
            ]
        ]);
    }
    
    /**
     * Reports & Analytics
     */
    public function reports() {
        // Date range filtering
        $startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        // Revenue analytics
        $revenueByDay = $this->db->fetchAll(
            "SELECT DATE(created_at) as date, SUM(amount) as revenue 
             FROM transactions 
             WHERE type = 'purchase' AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at) 
             ORDER BY date ASC",
            [$startDate, $endDate]
        );
        
        $totalRevenue = $this->db->fetch(
            "SELECT SUM(amount) as total FROM transactions 
             WHERE type = 'purchase' AND DATE(created_at) BETWEEN ? AND ?",
            [$startDate, $endDate]
        )['total'] ?? 0;
        
        // Top clients by spending
        $topClients = $this->db->fetchAll(
            "SELECT u.name, u.email, SUM(t.amount) as total_spent, COUNT(t.id) as purchases
             FROM users u
             JOIN transactions t ON u.id = t.user_id
             WHERE t.type = 'purchase' AND DATE(t.created_at) BETWEEN ? AND ?
             GROUP BY u.id
             ORDER BY total_spent DESC
             LIMIT 10",
            [$startDate, $endDate]
        );
        
        // Service popularity
        $popularServices = $this->db->fetchAll(
            "SELECT service, COUNT(*) as count, SUM(cost) as revenue
             FROM activations
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY service
             ORDER BY count DESC
             LIMIT 10",
            [$startDate, $endDate]
        );
        
        // Country popularity
        $popularCountries = $this->db->fetchAll(
            "SELECT country, COUNT(*) as count, SUM(cost) as revenue
             FROM activations
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY country
             ORDER BY count DESC
             LIMIT 10",
            [$startDate, $endDate]
        );
        
        // Monthly comparison
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));
        
        $currentMonthStats = $this->db->fetch(
            "SELECT COUNT(*) as activations, SUM(amount) as revenue
             FROM transactions
             WHERE type = 'purchase' AND DATE_FORMAT(created_at, '%Y-%m') = ?",
            [$currentMonth]
        );
        
        $lastMonthStats = $this->db->fetch(
            "SELECT COUNT(*) as activations, SUM(amount) as revenue
             FROM transactions
             WHERE type = 'purchase' AND DATE_FORMAT(created_at, '%Y-%m') = ?",
            [$lastMonth]
        );
        
        $this->view('admin/reports', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenue_by_day' => $revenueByDay,
            'total_revenue' => $totalRevenue,
            'top_clients' => $topClients,
            'popular_services' => $popularServices,
            'popular_countries' => $popularCountries,
            'current_month_stats' => $currentMonthStats,
            'last_month_stats' => $lastMonthStats
        ]);
    }
    
    /**
     * Price Management
     */
    public function priceManagement() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            $action = $_POST['action'] ?? '';
            
            if ($action === 'add_multiplier') {
                $service = Helper::sanitize($_POST['service'] ?? '');
                $country = Helper::sanitize($_POST['country'] ?? '');
                $multiplier = (float)($_POST['multiplier'] ?? 1);
                
                // Store multiplier in settings table with unique key
                $key = 'multiplier_' . $service . '_' . $country;
                Helper::setSetting($key, $multiplier);
                
                $message = 'Multiplier added successfully';
            } elseif ($action === 'delete_multiplier') {
                $key = Helper::sanitize($_POST['key'] ?? '');
                $this->db->delete('settings', '`key` = ?', [$key]);
                $message = 'Multiplier deleted successfully';
            }
        }
        
        // Get all multipliers
        $multipliers = $this->db->fetchAll(
            "SELECT * FROM settings WHERE `key` LIKE 'multiplier_%' ORDER BY `key`"
        );
        
        // Get services and countries from API
        $api = new ProxnumApi();
        $servicesData = $api->getServices();
        $countriesData = $api->getCountries();
        
        // Handle different response structures
        // Services might be returned as direct array or wrapped in 'services' key
        $services = [];
        if (isset($servicesData['services']) && is_array($servicesData['services'])) {
            $services = $servicesData['services'];
        } elseif (is_array($servicesData) && !isset($servicesData['services'])) {
            // If it's a direct array, use it
            $services = $servicesData;
        }
        
        $countries = [];
        if (isset($countriesData['countries']) && is_array($countriesData['countries'])) {
            $countries = $countriesData['countries'];
        } elseif (is_array($countriesData) && !isset($countriesData['countries'])) {
            $countries = $countriesData;
        }
        
        $this->view('admin/price_management', [
            'csrf_token' => $this->generateCsrf(),
            'message' => $message ?? null,
            'multipliers' => $multipliers,
            'services' => $services,
            'countries' => $countries
        ]);
    }
    
    /**
     * Activity Logs
     */
    public function activityLogs() {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        // Filters
        $userId = $_GET['user_id'] ?? null;
        $action = $_GET['action'] ?? null;
        
        $where = [];
        $params = [];
        
        if ($userId) {
            $where[] = 'user_id = ?';
            $params[] = $userId;
        }
        
        if ($action) {
            $where[] = 'action = ?';
            $params[] = $action;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get logs
        $logs = $this->db->fetchAll(
            "SELECT l.*, u.name as user_name, u.email as user_email
             FROM activity_logs l
             LEFT JOIN users u ON l.user_id = u.id
             $whereClause
             ORDER BY l.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $params
        );
        
        // Get total count
        $total = $this->db->count('activity_logs', $whereClause ?: '1=1', $params);
        $totalPages = ceil($total / $perPage);
        
        // Get unique actions for filter
        $actions = $this->db->fetchAll(
            "SELECT DISTINCT action FROM activity_logs ORDER BY action"
        );
        
        $this->view('admin/activity_logs', [
            'logs' => $logs,
            'actions' => $actions,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'filters' => [
                'user_id' => $userId,
                'action' => $action
            ]
        ]);
    }
    
    /**
     * License Information
     */
    public function licenseInfo() {
        // Get license from config
        $licenseKey = defined('LICENSE_KEY') ? LICENSE_KEY : 'Not Set';
        $licenseEmail = defined('LICENSE_EMAIL') ? LICENSE_EMAIL : 'Not Set';
        
        // Get cached license data
        $licenseCache = $this->db->fetch(
            "SELECT * FROM license_cache WHERE license_key = ? ORDER BY verified_at DESC LIMIT 1",
            [$licenseKey]
        );
        
        // Get system info
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'mysql_version' => $this->db->fetch("SELECT VERSION() as version")['version'] ?? 'Unknown',
            'current_domain' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
            'installation_path' => dirname(dirname(__DIR__)),
            'disk_free' => Helper::formatBytes(disk_free_space('.')),
            'disk_total' => Helper::formatBytes(disk_total_space('.'))
        ];
        
        $this->view('admin/license_info', [
            'license_key' => $licenseKey,
            'license_email' => $licenseEmail,
            'license_cache' => $licenseCache,
            'system_info' => $systemInfo
        ]);
    }
    
    /**
     * Admin Profile
     */
    public function profile() {
        $user = $this->getUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            // Block demo users from making changes
            if (Helper::isDemo()) {
                $error = 'Demo accounts cannot modify profile settings. This is a read-only demo.';
            } else {
            
            $action = $_POST['action'] ?? '';
            
            if ($action === 'update_profile') {
                $name = Helper::sanitize($_POST['name'] ?? '');
                $email = Helper::sanitize($_POST['email'] ?? '');
                
                // Check if email is already taken by another user
                $existing = $this->db->fetch(
                    'SELECT id FROM users WHERE email = ? AND id != ?',
                    [$email, $user['id']]
                );
                
                if ($existing) {
                    $error = 'Email already in use';
                } else {
                    $this->db->update('users', [
                        'name' => $name,
                        'email' => $email
                    ], 'id = ?', [$user['id']]);
                    
                    Helper::logActivity('profile_updated', 'Admin profile updated');
                    $message = 'Profile updated successfully';
                }
            } elseif ($action === 'change_password') {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                if (!password_verify($currentPassword, $user['password'])) {
                    $error = 'Current password is incorrect';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'New passwords do not match';
                } elseif (strlen($newPassword) < 8) {
                    $error = 'Password must be at least 8 characters';
                } else {
                    $this->db->update('users', [
                        'password' => password_hash($newPassword, PASSWORD_BCRYPT)
                    ], 'id = ?', [$user['id']]);
                    
                    Helper::logActivity('password_changed', 'Admin password changed');
                    $message = 'Password changed successfully';
                }
            }
            
            } // End demo check
            
            // Refresh user data
            $user = $this->db->fetch('SELECT * FROM users WHERE id = ?', [$user['id']]);
        }
        
        $this->view('admin/profile', [
            'csrf_token' => $this->generateCsrf(),
            'message' => $message ?? null,
            'error' => $error ?? null,
            'user' => $user
        ]);
    }
    
    /**
     * API Statistics
     */
    public function apiStats() {
        // Date range
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        // API usage by endpoint/method
        $apiCalls = $this->db->fetchAll(
            "SELECT service, country, COUNT(*) as calls, SUM(cost) as total_cost
             FROM activations
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY service, country
             ORDER BY calls DESC",
            [$startDate, $endDate]
        );
        
        // Success/failure rates
        $statusStats = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count
             FROM activations
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY status",
            [$startDate, $endDate]
        );
        
        // API response times (if logged)
        $avgResponseTime = 0; // Would need to implement logging for this
        
        // Error log summary
        $errors = $this->db->fetchAll(
            "SELECT action, COUNT(*) as count
             FROM activity_logs
             WHERE action LIKE '%error%' AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY action",
            [$startDate, $endDate]
        );
        
        $this->view('admin/api_stats', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'api_calls' => $apiCalls,
            'status_stats' => $statusStats,
            'errors' => $errors
        ]);
    }
    
    /**
     * Revenue Overview
     */
    public function revenue() {
        $period = $_GET['period'] ?? 'month';
        
        // Revenue statistics
        $totalRevenue = $this->db->fetch(
            "SELECT SUM(amount) as total FROM transactions WHERE type IN ('debit', 'purchase')"
        )['total'] ?? 0;
        
        $monthlyRevenue = $this->db->fetch(
            "SELECT SUM(amount) as total FROM transactions 
             WHERE type IN ('debit', 'purchase') 
             AND MONTH(created_at) = MONTH(CURRENT_DATE())
             AND YEAR(created_at) = YEAR(CURRENT_DATE())"
        )['total'] ?? 0;
        
        // Revenue by service
        $revenueByService = $this->db->fetchAll(
            "SELECT a.service, SUM(a.cost) as revenue, COUNT(*) as count
             FROM activations a
             WHERE DATE(a.created_at) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
             GROUP BY a.service
             ORDER BY revenue DESC
             LIMIT 10"
        );
        
        // Monthly trend
        $monthlyTrend = $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as revenue
             FROM transactions
             WHERE type IN ('debit', 'purchase')
             AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
             GROUP BY month
             ORDER BY month DESC"
        );
        
        $this->view('admin/revenue', [
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'revenue_by_service' => $revenueByService,
            'monthly_trend' => $monthlyTrend,
            'period' => $period
        ]);
    }
    
    /**
     * Support Tickets
     */
    public function support($ticketId = null) {
        // View single ticket
        if ($ticketId) {
            // Handle POST actions (reply, update status)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateCsrf();
                $action = $_POST['action'] ?? '';
                
                if ($action === 'reply') {
                    $message = Helper::sanitize($_POST['message'] ?? '');
                    
                    if (empty($message)) {
                        $_SESSION['ticket_error'] = 'Message is required';
                    } else {
                        $admin = $this->getUser();
                        
                        $this->db->query(
                            "INSERT INTO support_replies (ticket_id, user_id, message, is_admin, created_at) 
                             VALUES (?, ?, ?, 1, NOW())",
                            [$ticketId, $admin['id'], $message]
                        );
                        
                        // Update ticket's updated_at timestamp
                        $this->db->query(
                            "UPDATE support_tickets SET updated_at = NOW() WHERE id = ?",
                            [$ticketId]
                        );
                        
                        Helper::logActivity('ticket_replied', "Replied to ticket ID: {$ticketId}");
                        $_SESSION['ticket_success'] = 'Reply added successfully';
                    }
                    
                    $this->redirect("/admin/support/{$ticketId}");
                    return;
                } elseif ($action === 'update_status') {
                    $status = Helper::sanitize($_POST['status'] ?? '');
                    
                    if (in_array($status, ['open', 'pending', 'resolved', 'closed'])) {
                        $this->db->query(
                            "UPDATE support_tickets SET status = ?, updated_at = NOW() WHERE id = ?",
                            [$status, $ticketId]
                        );
                        
                        Helper::logActivity('ticket_status_updated', "Updated ticket ID {$ticketId} to {$status}");
                        $_SESSION['ticket_success'] = "Ticket status updated to {$status}";
                    }
                    
                    $this->redirect("/admin/support/{$ticketId}");
                    return;
                }
            }
            
            // Get ticket details
            $ticket = $this->db->fetch(
                "SELECT t.*, u.name as user_name, u.email as user_email 
                 FROM support_tickets t
                 JOIN users u ON t.user_id = u.id
                 WHERE t.id = ?",
                [$ticketId]
            );
            
            if (!$ticket) {
                $this->redirect('/admin/support');
                return;
            }
            
            // Get replies
            $replies = $this->db->fetchAll(
                "SELECT r.*, u.name as user_name, u.email as user_email 
                 FROM support_replies r 
                 LEFT JOIN users u ON r.user_id = u.id 
                 WHERE r.ticket_id = ? ORDER BY r.created_at ASC",
                [$ticketId]
            );
            
            $this->view('admin/support_detail', [
                'ticket' => $ticket,
                'replies' => $replies,
                'csrf_token' => $this->generateCsrf(),
                'success_message' => $_SESSION['ticket_success'] ?? null,
                'error_message' => $_SESSION['ticket_error'] ?? null
            ]);
            
            // Clear session messages
            unset($_SESSION['ticket_success'], $_SESSION['ticket_error']);
            return;
        }
        
        // List all tickets
        $status = $_GET['status'] ?? 'all';
        
        // Get tickets
        $sql = "SELECT t.*, u.name as user_name, u.email as user_email
                FROM support_tickets t
                JOIN users u ON t.user_id = u.id";
        
        if ($status !== 'all') {
            $sql .= " WHERE t.status = ?";
            $tickets = $this->db->fetchAll($sql . " ORDER BY t.created_at DESC", [$status]);
        } else {
            $tickets = $this->db->fetchAll($sql . " ORDER BY t.created_at DESC");
        }
        
        // Statistics
        $stats = [
            'open' => $this->db->fetch("SELECT COUNT(*) as count FROM support_tickets WHERE status = 'open'")['count'] ?? 0,
            'pending' => $this->db->fetch("SELECT COUNT(*) as count FROM support_tickets WHERE status = 'pending'")['count'] ?? 0,
            'resolved' => $this->db->fetch("SELECT COUNT(*) as count FROM support_tickets WHERE status = 'resolved'")['count'] ?? 0,
            'closed' => $this->db->fetch("SELECT COUNT(*) as count FROM support_tickets WHERE status = 'closed'")['count'] ?? 0,
        ];
        
        $this->view('admin/support', [
            'tickets' => $tickets,
            'stats' => $stats,
            'current_status' => $status
        ]);
    }
    
    /**
     * Email Templates
     */
    public function emailTemplates() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $action = $_POST['action'] ?? '';
            
            // Block demo users from updates only
            if ($action === 'update' && Helper::isDemo()) {
                $this->json(['success' => false, 'message' => 'Demo accounts cannot update templates']);
                return;
            }
            
            if ($action === 'update') {
                $this->db->query(
                    "UPDATE email_templates SET subject = ?, body = ?, updated_at = NOW() WHERE id = ?",
                    [$_POST['subject'], $_POST['body'], $_POST['template_id']]
                );
                
                $this->json(['success' => true, 'message' => 'Template updated successfully']);
                return;
            }
            
            if ($action === 'get') {
                $templateId = $_POST['template_id'] ?? 0;
                
                if (!$templateId) {
                    $this->json(['success' => false, 'message' => 'Template ID is required']);
                    return;
                }
                
                $template = $this->db->fetch(
                    "SELECT * FROM email_templates WHERE id = ?",
                    [$templateId]
                );
                
                if ($template) {
                    $this->json(['success' => true, 'template' => $template]);
                } else {
                    $this->json(['success' => false, 'message' => 'Template not found']);
                }
                return;
            }
            
            // Invalid action
            $this->json(['success' => false, 'message' => 'Invalid action']);
            return;
        }
        
        // Get all templates
        $templates = $this->db->fetchAll(
            "SELECT * FROM email_templates ORDER BY name"
        );
        
        $this->view('admin/email_templates', [
            'templates' => $templates
        ]);
    }
    
    /**
     * Send Email to Users
     */
    public function sendEmail() {
        // Block demo users from sending (but allow viewing the page)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Helper::isDemo()) {
            $this->json(['success' => false, 'message' => 'Demo accounts cannot send emails.']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            header('Content-Type: application/json');
            
            $action = $_POST['action'] ?? '';
            
            // Load Mailer
            require_once __DIR__ . '/../core/Mailer.php';
            $mailer = new \Core\Mailer();
            
            $subject = Helper::sanitize($_POST['subject'] ?? '');
            $body = $_POST['body'] ?? ''; // Don't sanitize HTML body too much
            
            if (empty($subject) || empty($body)) {
                $this->json(['success' => false, 'message' => 'Subject and message are required']);
                return;
            }
            
            try {
                if ($action === 'send_to_user') {
                    // Send to specific user
                    $userId = (int)($_POST['user_id'] ?? 0);
                    
                    if (!$userId) {
                        $this->json(['success' => false, 'message' => 'Please select a user']);
                        return;
                    }
                    
                    $result = $mailer->sendToUser($userId, $subject, $body);
                    
                    if ($result['success']) {
                        Helper::logActivity('email_sent', "Email sent to user ID: {$userId}");
                    }
                    
                    $this->json($result);
                    
                } elseif ($action === 'send_to_all') {
                    // Send to all users or filtered by role
                    $role = $_POST['role_filter'] ?? null;
                    if ($role === 'all') {
                        $role = null;
                    }
                    
                    $result = $mailer->sendToAllUsers($subject, $body, $role);
                    
                    if ($result['success']) {
                        Helper::logActivity('bulk_email_sent', "Bulk email sent to {$result['sent']} user(s)");
                    }
                    
                    $this->json($result);
                    
                } elseif ($action === 'send_to_multiple') {
                    // Send to selected users
                    $userIds = $_POST['user_ids'] ?? [];
                    
                    if (!is_array($userIds) || empty($userIds)) {
                        $this->json(['success' => false, 'message' => 'Please select at least one user']);
                        return;
                    }
                    
                    // Convert to integers
                    $userIds = array_map('intval', $userIds);
                    
                    $result = $mailer->sendToMultipleUsers($userIds, $subject, $body);
                    
                    if ($result['success']) {
                        Helper::logActivity('bulk_email_sent', "Email sent to {$result['sent']} selected user(s)");
                    }
                    
                    $this->json($result);
                    
                } else {
                    $this->json(['success' => false, 'message' => 'Invalid action']);
                }
            } catch (\Exception $e) {
                error_log('Send email error: ' . $e->getMessage());
                $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            
            return;
        }
        
        // Get all clients for the dropdown
        $clients = $this->db->fetchAll(
            'SELECT id, name, email, role FROM users ORDER BY name ASC'
        );
        
        // Check if a specific user was pre-selected
        $preSelectedUserId = isset($_GET['user']) ? (int)$_GET['user'] : null;
        
        $this->view('admin/send_email', [
            'clients' => $clients,
            'preSelectedUserId' => $preSelectedUserId,
            'csrf_token' => $this->generateCsrf()
        ]);
    }
    
    /**
     * Payment Gateways
     */
    public function paymentGateways() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            // Block demo users from modifying payment gateways
            if (Helper::isDemo()) {
                $this->json(['success' => false, 'message' => 'Demo accounts cannot modify payment gateway settings.']);
                return;
            }
            
            $action = $_POST['action'] ?? '';
            
            if ($action === 'update_gateway') {
                $gateway = Helper::sanitize($_POST['gateway'] ?? '');
                $enabled = (int)($_POST['enabled'] ?? 0);
                
                // Build config based on gateway type
                $config = [];
                
                if ($gateway === 'paypal') {
                    $config['paypal_address'] = Helper::sanitize($_POST['paypal_address'] ?? '');
                } elseif ($gateway === 'crypto') {
                    $config['crypto_type'] = Helper::sanitize($_POST['crypto_type'] ?? '');
                    $config['network'] = Helper::sanitize($_POST['network'] ?? '');
                    $config['wallet_address'] = Helper::sanitize($_POST['wallet_address'] ?? '');
                } elseif ($gateway === 'binance') {
                    $config['binance_pay_id'] = Helper::sanitize($_POST['binance_pay_id'] ?? '');
                }
                
                $instructions = Helper::sanitize($_POST['instructions'] ?? '');
                
                $this->db->update('payment_gateways', [
                    'enabled' => $enabled,
                    'config' => !empty($config) ? json_encode($config) : null,
                    'instructions' => $instructions
                ], 'name = ?', [$gateway]);
                
                Helper::logActivity('payment_gateway_updated', "Updated $gateway gateway configuration");
                
                $this->json(['success' => true, 'message' => 'Gateway updated successfully']);
                return;
            }
        }
        
        // Get manual gateways (PayPal, Crypto, Binance)
        $gateways = $this->db->fetchAll(
            "SELECT * FROM payment_gateways 
             WHERE name IN ('paypal', 'crypto', 'binance') 
             ORDER BY FIELD(name, 'paypal', 'crypto', 'binance')"
        );
        
        // Parse JSON config for each gateway
        foreach ($gateways as &$gateway) {
            if ($gateway['config']) {
                $gateway['config_data'] = json_decode($gateway['config'], true);
            } else {
                $gateway['config_data'] = [];
            }
        }
        
        $this->view('admin/payment_gateways', [
            'gateways' => $gateways
        ]);
    }
    
    /**
     * Payment Verifications
     */
    public function paymentVerifications() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            $action = $_POST['action'] ?? '';
            
            if ($action === 'approve') {
                $id = (int)($_POST['id'] ?? 0);
                
                // Get deposit details
                $deposit = $this->db->fetch(
                    "SELECT * FROM payment_deposits WHERE id = ? AND status = 'pending'",
                    [$id]
                );
                
                if (!$deposit) {
                    $this->json(['success' => false, 'message' => 'Deposit not found or already processed']);
                    return;
                }
                
                // Get user current balance
                $user = $this->db->fetch("SELECT balance FROM users WHERE id = ?", [$deposit['user_id']]);
                $newBalance = $user['balance'] + $deposit['amount'];
                
                // Start transaction
                $this->db->getConnection()->beginTransaction();
                
                try {
                    // Create transaction record
                    $this->db->query(
                        "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, description, reference, created_at) 
                         VALUES (?, 'credit', ?, ?, ?, ?, ?, NOW())",
                        [
                            $deposit['user_id'], 
                            $deposit['amount'], 
                            $user['balance'], 
                            $newBalance, 
                            'Payment approved - ' . ucfirst($deposit['gateway']),
                            'deposit:' . $deposit['id']
                        ]
                    );
                    
                    $transactionId = $this->db->getConnection()->lastInsertId();
                    
                    // Update user balance
                    $this->db->update('users', ['balance' => $newBalance], 'id = ?', [$deposit['user_id']]);
                    
                    // Update deposit status
                    $this->db->update('payment_deposits', [
                        'status' => 'approved',
                        'transaction_id' => $transactionId,
                        'approved_by' => $_SESSION['user_id'],
                        'admin_notes' => Helper::sanitize($_POST['notes'] ?? '')
                    ], 'id = ?', [$id]);
                    
                    $this->db->getConnection()->commit();
                    
                    Helper::logActivity('payment_approved', "Approved payment deposit #$id for amount: $" . $deposit['amount']);
                    
                    $this->json(['success' => true, 'message' => 'Payment approved successfully']);
                    return;
                } catch (Exception $e) {
                    $this->db->getConnection()->rollBack();
                    $this->json(['success' => false, 'message' => 'Error processing payment: ' . $e->getMessage()]);
                    return;
                }
            } elseif ($action === 'reject') {
                $id = (int)($_POST['id'] ?? 0);
                $notes = Helper::sanitize($_POST['notes'] ?? '');
                
                $this->db->update('payment_deposits', [
                    'status' => 'rejected',
                    'approved_by' => $_SESSION['user_id'],
                    'admin_notes' => $notes
                ], 'id = ? AND status = ?', [$id, 'pending']);
                
                Helper::logActivity('payment_rejected', "Rejected payment deposit #$id");
                
                $this->json(['success' => true, 'message' => 'Payment rejected']);
                return;
            }
        }
        
        // Get filter and pagination
        $status = $_GET['status'] ?? 'pending';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 30;
        
        $total = $this->db->count('payment_deposits', 'status = ?', [$status]);
        $pagination = Helper::paginate($total, $perPage, $page);
        
        // Get payment deposits
        $deposits = $this->db->fetchAll(
            "SELECT pd.*, u.name as user_name, u.email as user_email, 
                    approver.name as approved_by_name
             FROM payment_deposits pd
             LEFT JOIN users u ON pd.user_id = u.id
             LEFT JOIN users approver ON pd.approved_by = approver.id
             WHERE pd.status = ?
             ORDER BY pd.created_at DESC
             LIMIT ? OFFSET ?",
            [$status, $perPage, $pagination['offset']]
        );
        
        // Get counts for each status
        $pendingCount = $this->db->fetch("SELECT COUNT(*) as count FROM payment_deposits WHERE status = 'pending'")['count'];
        $approvedCount = $this->db->fetch("SELECT COUNT(*) as count FROM payment_deposits WHERE status = 'approved'")['count'];
        $rejectedCount = $this->db->fetch("SELECT COUNT(*) as count FROM payment_deposits WHERE status = 'rejected'")['count'];
        
        $this->view('admin/payment_verifications', [
            'deposits' => $deposits,
            'status' => $status,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'csrf_token' => $this->generateCsrf(),
            'pagination' => $pagination
        ]);
    }
    
    /**
     * View Payment Proof File
     * Serves payment proof images/PDFs with authentication
     */
    public function viewPaymentProof($filename = null) {
        // Require admin authentication
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            die('Forbidden');
        }
        
        if (!$filename) {
            http_response_code(400);
            die('Invalid request');
        }
        
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        $filePath = __DIR__ . '/../../storage/payment_proofs/' . $filename;
        
        if (!file_exists($filePath)) {
            http_response_code(404);
            die('File not found');
        }
        
        // Determine content type
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $contentTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf'
        ];
        
        $contentType = $contentTypes[$ext] ?? 'application/octet-stream';
        
        // Set headers
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=86400');
        
        // Output file
        readfile($filePath);
        exit;
    }
    
    /**
     * Backup & Restore
     */
    public function backup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'create_backup') {
                // Generate backup filename
                $filename = 'backup_' . date('Y-m-d_His') . '.sql';
                $filepath = BASE_PATH . '/storage/backups/' . $filename;
                
                // Ensure backup directory exists
                if (!is_dir(BASE_PATH . '/storage/backups')) {
                    mkdir(BASE_PATH . '/storage/backups', 0755, true);
                }
                
                // Create backup using mysqldump
                $command = sprintf(
                    'mysqldump -h%s -u%s -p%s %s > %s',
                    DB_HOST,
                    DB_USER,
                    DB_PASS,
                    DB_NAME,
                    $filepath
                );
                
                exec($command, $output, $result);
                
                if ($result === 0) {
                    // Log backup
                    $this->db->query(
                        "INSERT INTO backups (filename, size, created_by, created_at) VALUES (?, ?, ?, NOW())",
                        [$filename, filesize($filepath), $_SESSION['user_id']]
                    );
                    
                    $this->json(['success' => true, 'message' => 'Backup created successfully']);
                } else {
                    $this->json(['success' => false, 'message' => 'Backup failed']);
                }
                return;
            }
        }
        
        // Get backup history with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        
        $total = $this->db->count('backups');
        $pagination = Helper::paginate($total, $perPage, $page);
        
        $backups = $this->db->fetchAll(
            "SELECT b.*, u.name as created_by_name 
             FROM backups b 
             LEFT JOIN users u ON b.created_by = u.id 
             ORDER BY b.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $pagination['offset']]
        );
        
        $this->view('admin/backup', [
            'backups' => $backups,
            'csrf_token' => $this->generateCsrf(),
            'pagination' => $pagination
        ]);
    }
    
    /**
     * Announcements Management
     */
    public function announcements() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            $action = $_POST['action'] ?? '';
            
            if ($action === 'create') {
                $title = Helper::sanitize($_POST['title'] ?? '');
                $content = Helper::sanitize($_POST['content'] ?? '');
                $type = Helper::sanitize($_POST['type'] ?? 'info');
                $priority = (int)($_POST['priority'] ?? 0);
                
                if (empty($title) || empty($content)) {
                    $this->json(['success' => false, 'message' => 'Title and content are required']);
                    return;
                }
                
                $this->db->insert('announcements', [
                    'title' => $title,
                    'content' => $content,
                    'type' => $type,
                    'priority' => $priority,
                    'created_by' => $_SESSION['user_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                Helper::logActivity('announcement_created', 'Created announcement: ' . $title);
                $this->json(['success' => true, 'message' => 'Announcement created successfully']);
                return;
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                
                $this->db->delete('announcements', 'id = ?', [$id]);
                
                Helper::logActivity('announcement_deleted', 'Deleted announcement #' . $id);
                $this->json(['success' => true, 'message' => 'Announcement deleted successfully']);
                return;
            } elseif ($action === 'toggle') {
                $id = (int)($_POST['id'] ?? 0);
                $active = (int)($_POST['active'] ?? 0);
                
                $this->db->update('announcements', [
                    'active' => $active
                ], 'id = ?', [$id]);
                
                $this->json(['success' => true, 'message' => 'Announcement updated successfully']);
                return;
            }
        }
        
        // Get all announcements with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        
        $total = $this->db->count('announcements');
        $pagination = Helper::paginate($total, $perPage, $page);
        
        $announcements = $this->db->fetchAll(
            "SELECT a.*, u.name as created_by_name 
             FROM announcements a 
             LEFT JOIN users u ON a.created_by = u.id 
             ORDER BY a.priority DESC, a.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $pagination['offset']]
        );
        
        $this->view('admin/announcements', [
            'announcements' => $announcements,
            'csrf_token' => $this->generateCsrf(),
            'pagination' => $pagination
        ]);
    }
    
    /**
     * System Health Monitor
     */
    public function systemHealth() {
        // Database health
        $dbStatus = 'healthy';
        try {
            $this->db->query("SELECT 1");
        } catch (\Exception $e) {
            $dbStatus = 'error';
        }
        
        // Disk space
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $diskUsed = $diskTotal - $diskFree;
        $diskUsagePercent = ($diskUsed / $diskTotal) * 100;
        
        // Memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        // Queue status
        $queuedJobs = $this->db->fetch("SELECT COUNT(*) as count FROM jobs WHERE status = 'pending'")['count'] ?? 0;
        $failedJobs = $this->db->fetch("SELECT COUNT(*) as count FROM jobs WHERE status = 'failed'")['count'] ?? 0;
        
        // Recent errors
        $recentErrors = $this->db->fetchAll(
            "SELECT * FROM activity_logs WHERE action LIKE '%error%' ORDER BY created_at DESC LIMIT 10"
        );
        
        // API health check
        $apiHealth = 'healthy';
        try {
            // Check connection to main Proxnum API
            $ch = curl_init(PROXNUM_API_URL . '/status');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                $apiHealth = 'warning';
            }
        } catch (\Exception $e) {
            $apiHealth = 'error';
        }
        
        $this->view('admin/system_health', [
            'db_status' => $dbStatus,
            'disk_usage' => $diskUsagePercent,
            'memory_usage' => $memoryUsage,
            'memory_limit' => $memoryLimit,
            'queued_jobs' => $queuedJobs,
            'failed_jobs' => $failedJobs,
            'recent_errors' => $recentErrors,
            'api_health' => $apiHealth
        ]);
    }
    
    /**
     * System Updates Management
     */
    public function updates() {
        require_once BASE_PATH . '/core/UpdateManager.php';
        $updateManager = new \Core\UpdateManager();
        
        $currentVersion = Helper::getSetting('app_version', '1.0.0');
        $updateInfo = $updateManager->checkForUpdates();
        $backups = $updateManager->listBackups();
        
        $this->view('admin/updates', [
            'current_version' => $currentVersion,
            'update_info' => $updateInfo,
            'backups' => $backups,
            'csrf_token' => $this->generateCsrf()
        ]);
    }
    
    /**
     * Check for updates (AJAX)
     */
    public function checkUpdates() {
        require_once BASE_PATH . '/core/UpdateManager.php';
        $updateManager = new \Core\UpdateManager();
        
        $result = $updateManager->checkForUpdates();
        $this->json($result);
    }
    
    /**
     * Create backup (AJAX)
     */
    public function createBackup() {
        $this->validateCsrf();
        
        require_once BASE_PATH . '/core/UpdateManager.php';
        $updateManager = new \Core\UpdateManager();
        
        $result = $updateManager->createBackup();
        $this->json($result);
    }
    
    /**
     * Install update (AJAX)
     */
    public function installUpdate() {
        $this->validateCsrf();
        
        set_time_limit(600); // 10 minutes
        
        $downloadUrl = $_POST['download_url'] ?? '';
        
        if (empty($downloadUrl)) {
            $this->json([
                'success' => false,
                'error' => 'Download URL is required'
            ]);
        }
        
        require_once BASE_PATH . '/core/UpdateManager.php';
        $updateManager = new \Core\UpdateManager();
        
        // Create backup first
        Helper::logActivity('update_initiated', 'Update installation initiated');
        
        $backup = $updateManager->createBackup();
        
        if (!$backup['success']) {
            $this->json([
                'success' => false,
                'error' => 'Failed to create backup: ' . $backup['error']
            ]);
        }
        
        // Install update
        $result = $updateManager->downloadAndInstall($downloadUrl, $backup['backup_path']);
        
        // Cleanup old backups (keep last 3)
        if ($result['success']) {
            $updateManager->cleanupOldBackups(3);
        }
        
        $this->json($result);
    }
    
    /**
     * Rollback to backup (AJAX)
     */
    public function rollback() {
        $this->validateCsrf();
        
        $backupName = $_POST['backup_name'] ?? '';
        
        if (empty($backupName)) {
            $this->json([
                'success' => false,
                'error' => 'Backup name is required'
            ]);
        }
        
        $backupPath = BASE_PATH . '/backups/' . $backupName;
        
        if (!is_dir($backupPath)) {
            $this->json([
                'success' => false,
                'error' => 'Backup not found'
            ]);
        }
        
        require_once BASE_PATH . '/core/UpdateManager.php';
        $updateManager = new \Core\UpdateManager();
        
        Helper::logActivity('rollback_initiated', 'Rollback to backup: ' . $backupName);
        
        $result = $updateManager->rollback($backupPath);
        $this->json($result);
    }
    
    /**
     * Delete backup (AJAX)
     */
    public function deleteBackup() {
        $this->validateCsrf();
        
        $backupName = $_POST['backup_name'] ?? '';
        
        if (empty($backupName)) {
            $this->json([
                'success' => false,
                'error' => 'Backup name is required'
            ]);
        }
        
        $backupPath = BASE_PATH . '/backups/' . $backupName;
        
        if (!is_dir($backupPath)) {
            $this->json([
                'success' => false,
                'error' => 'Backup not found'
            ]);
        }
        
        try {
            $this->deleteDirectory($backupPath);
            Helper::logActivity('backup_deleted', 'Backup deleted: ' . $backupName);
            
            $this->json([
                'success' => true,
                'message' => 'Backup deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Helper: Delete directory recursively
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
}
