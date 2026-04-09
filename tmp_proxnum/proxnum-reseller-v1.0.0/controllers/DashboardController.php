<?php
/**
 * Dashboard Controller
 * Handles client dashboard functionality
 */

namespace Controllers;

use Core\Controller;
use Core\Helper;
use Core\ProxnumApi;

class DashboardController extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    /**
     * Dashboard home
     */
    public function index() {
        // Redirect admins to admin dashboard
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
            return;
        }
        
        $user = $this->getUser();
        
        // Sync activations from Proxnum API
        $this->syncActivationsFromApi($user['id']);
        
        // Get statistics
        $stats = [
            'balance' => $user['balance'],
            'total_activations' => $this->db->count('activations', 'user_id = ?', [$user['id']]),
            'pending_activations' => $this->db->count('activations', 'user_id = ? AND status = ?', [$user['id'], 'pending']),
            'active_rentals' => $this->db->count('rentals', 'user_id = ? AND status = ? AND expires_at > NOW()', [$user['id'], 'active'])
        ];
        
        // Recent activations
        $recentActivations = $this->db->fetchAll(
            'SELECT * FROM activations WHERE user_id = ? ORDER BY created_at DESC LIMIT 10',
            [$user['id']]
        );
        
        $this->view('dashboard/index', [
            'user' => $user,
            'stats' => $stats,
            'recent_activations' => $recentActivations
        ]);
    }
    
    /**
     * Purchase virtual number
     */
    public function buy() {
        // Prevent admins from purchasing numbers
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handleBuy();
        }
        
        $api = new ProxnumApi();
        
        // Get countries and services
        $countries = $api->getCountries();
        $services = $api->getServices();
        
        $this->view('dashboard/buy', [
            'countries' => $countries['countries'] ?? [],
            'services' => $services['services'] ?? [],
            'csrf_token' => $this->generateCsrf()
        ]);
    }
    
    /**
     * Auto-expire pending activations older than 20 minutes
     */
    private function autoExpireActivations($userId = null) {
        $where = "status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL 20 MINUTE)";
        $params = [];
        
        if ($userId) {
            $where .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        // Get expired activations
        $expired = $this->db->fetchAll(
            "SELECT * FROM activations WHERE {$where}",
            $params
        );
        
        if (!empty($expired)) {
            foreach ($expired as $activation) {
                // Refund the cost
                $user = $this->db->fetch('SELECT balance FROM users WHERE id = ?', [$activation['user_id']]);
                $newBalance = $user['balance'] + $activation['cost'];
                
                $this->db->beginTransaction();
                
                try {
                    // Update activation status
                    $this->db->update('activations', [
                        'status' => 'expired'
                    ], 'id = ?', [$activation['id']]);
                    
                    // Refund user
                    $this->db->update('users', [
                        'balance' => $newBalance
                    ], 'id = ?', [$activation['user_id']]);
                    
                    // Add refund transaction
                    $this->db->query(
                        "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, description, reference, created_at) 
                         VALUES (?, 'credit', ?, ?, ?, ?, ?, NOW())",
                        [$activation['user_id'], $activation['cost'], $user['balance'], $newBalance, 
                         'Refund for expired activation ' . $activation['activation_id'], 'refund:' . $activation['activation_id']]
                    );
                    
                    $this->db->commit();
                } catch (\Exception $e) {
                    $this->db->rollback();
                }
            }
        }
    }
    
    /**
     * Handle purchase
     */
    private function handleBuy() {
        try {
            $this->validateCsrf();
            
            // Block demo users from purchasing
            Helper::blockDemoAction('Demo accounts cannot purchase virtual numbers. Purchase a license to enable this feature.');
            
            $service = Helper::sanitize($_POST['service'] ?? '');
            $country = Helper::sanitize($_POST['country'] ?? '');
            
            if (empty($service) || empty($country)) {
                $this->json(['success' => false, 'message' => 'Service and country are required']);
                return;
            }
            
            $user = $this->getUser();
            $api = new ProxnumApi();
            
            // Get price
            $priceData = $api->getPrices($country, $service);
            
            if (!isset($priceData['success']) || !$priceData['success']) {
                $errorMsg = $priceData['error']['message'] ?? 'Failed to get price';
                $this->json([
                    'success' => false,
                    'message' => $errorMsg
                ]);
                return;
            }
        
            // Extract price from nested structure
            $apiPrice = 0;
            if (isset($priceData['prices'][$country][$service]['sell_price'])) {
                $apiPrice = $priceData['prices'][$country][$service]['sell_price'];
            } elseif (isset($priceData['prices'][$country][$service]['base_price'])) {
                $apiPrice = $priceData['prices'][$country][$service]['base_price'];
            } elseif (isset($priceData['price'])) {
                // Fallback for flat structure
                $apiPrice = $priceData['price'];
            }
            
            if ($apiPrice <= 0) {
                $this->json([
                    'success' => false,
                    'message' => 'Unable to determine price'
                ]);
                return;
            }
            
            // Apply global price multiplier
            $globalMultiplier = (float)Helper::getSetting('price_multiplier', '1');
            $price = $apiPrice * $globalMultiplier;
            
            // Apply service/country specific multiplier if exists
            $specificMultiplier = (float)Helper::getSetting('multiplier_' . $service . '_' . $country, '1');
            $finalPrice = $price * $specificMultiplier;
            
            // Check balance
            if ($user['balance'] < $finalPrice) {
                $this->json([
                    'success' => false,
                    'message' => 'Insufficient balance. You need $' . number_format($price, 2)
                ]);
                return;
            }
            
            // Purchase from Proxnum
            $result = $api->buyVirtual($service, $country);
            
            if (!isset($result['success']) || !$result['success']) {
                $errorMsg = $result['error']['message'] ?? 'Purchase failed';
                $this->json([
                    'success' => false,
                    'message' => $errorMsg
                ]);
                return;
            }
            
            $activation = $result['activation'] ?? [];
            
            $this->db->beginTransaction();
            
            // Deduct balance
            $balanceBefore = $user['balance'];
            $balanceAfter = $balanceBefore - $finalPrice;
            
            $this->db->update('users', [
                'balance' => $balanceAfter,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$user['id']]);
            
            // Add transaction
            $this->db->query(
                "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, description, reference, created_at) 
                 VALUES (?, 'purchase', ?, ?, ?, ?, ?, NOW())",
                [$user['id'], $finalPrice, $balanceBefore, $balanceAfter, 
                 "Purchased $service number for $country", $activation['activation_id'] ?? '']
            );
            
            // Save activation
            $this->db->query(
                "INSERT INTO activations (user_id, activation_id, service, country, phone, status, cost, proxnum_response, created_at) 
                 VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, NOW())",
                [$user['id'], $activation['activation_id'] ?? '', $service, $country, 
                 $activation['phone'] ?? '', $finalPrice, json_encode($result)]
            );
            $activationId = $this->db->getConnection()->lastInsertId();
            
            $this->db->commit();
            
            Helper::logActivity('virtual_purchased', "Purchased $service for $country");
            
            // Check for low balance and send email if enabled
            $minBalance = (float)Helper::getSetting('min_balance', '1.00');
            $lowBalanceEmailEnabled = Helper::getSetting('mail_low_balance_enabled', '0');
            
            if ($balanceAfter <= $minBalance && $lowBalanceEmailEnabled === '1') {
                try {
                    require_once __DIR__ . '/../core/Mailer.php';
                    
                    \Core\Mailer::sendTemplate($user['email'], 'balance_low', [
                        'name' => $user['name'],
                        'balance' => number_format($balanceAfter, 2)
                    ]);
                    
                    Helper::logActivity('low_balance_email_sent', 'Low balance email sent (balance: $' . number_format($balanceAfter, 2) . ')', $user['id']);
                } catch (\Exception $e) {
                    // Log error but don't fail
                    Helper::logActivity('low_balance_email_failed', 'Failed to send low balance email: ' . $e->getMessage(), $user['id']);
                }
            }
            
            $this->json([
                'success' => true,
                'message' => 'Number purchased successfully',
                'activation' => $activation,
                'new_balance' => $balanceAfter,
                'redirect' => Helper::url('/dashboard/activations/' . $activationId)
            ]);
            
        } catch (\Exception $e) {
            if (isset($this->db) && $this->db->inTransaction()) {
                $this->db->rollback();
            }
            $this->json(['success' => false, 'message' => 'Request failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * View activations
     */
    public function activations($id = null) {
        // Prevent admins from accessing client activations
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
            return;
        }
        
        $user = $this->getUser();
        
        // Auto-expire pending activations older than 20 minutes
        $this->autoExpireActivations($user['id']);
        
        // Auto-sync activations from Proxnum API on page load
        $this->syncActivationsFromApi($user['id']);
        
        // Single activation view
        if ($id) {
            $activation = $this->db->fetch(
                'SELECT * FROM activations WHERE id = ? AND user_id = ?',
                [$id, $user['id']]
            );
            
            if (!$activation) {
                $this->redirect('/dashboard');
            }
            
            // Check status from API if pending
            if ($activation['status'] === 'pending') {
                $api = new ProxnumApi();
                $status = $api->getVirtualStatus($activation['activation_id']);
                
                if ($status['success'] && isset($status['activation'])) {
                    $apiActivation = $status['activation'];
                    
                    // Update if code received
                    if (!empty($apiActivation['code']) && empty($activation['code'])) {
                        $this->db->update('activations', [
                            'code' => $apiActivation['code'],
                            'status' => 'completed',
                            'completed_at' => date('Y-m-d H:i:s')
                        ], 'id = ?', [$id]);
                        
                        $activation['code'] = $apiActivation['code'];
                        $activation['status'] = 'completed';
                        
                        // Send activation completed email if enabled
                        $activationEmailEnabled = Helper::getSetting('mail_activation_enabled', '0');
                        if ($activationEmailEnabled === '1') {
                            try {
                                require_once __DIR__ . '/../core/Mailer.php';
                                
                                \Core\Mailer::sendTemplate($user['email'], 'activation_completed', [
                                    'name' => $user['name'],
                                    'service' => $activation['service'],
                                    'code' => $apiActivation['code'],
                                    'phone' => $activation['phone']
                                ]);
                                
                                Helper::logActivity('activation_email_sent', 'Activation completed email sent for activation #' . $id, $user['id']);
                            } catch (\Exception $e) {
                                // Log error but don't fail
                                Helper::logActivity('activation_email_failed', 'Failed to send activation email: ' . $e->getMessage(), $user['id']);
                            }
                        }
                    }
                }
            }
            
            $this->view('dashboard/activation_detail', [
                'activation' => $activation,
                'csrf_token' => $this->generateCsrf()
            ]);
            
            return;
        }
        
        // List all activations
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        
        $total = $this->db->count('activations', 'user_id = ?', [$user['id']]);
        $pagination = Helper::paginate($total, $perPage, $page);
        
        $activations = $this->db->fetchAll(
            'SELECT * FROM activations WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$user['id'], $perPage, $pagination['offset']]
        );
        
        $this->view('dashboard/activations', [
            'activations' => $activations,
            'pagination' => $pagination
        ]);
    }
    
    /**
     * Sync activations from Proxnum API to local database
     */
    private function syncActivationsFromApi($userId) {
        try {
            $api = new ProxnumApi();
            
            // Get activations from Proxnum API (reseller endpoint)
            $result = $api->listActivations(100, 0); // Get last 100 activations
            
            // Log the API response for debugging
            error_log('Activation sync API response: ' . json_encode($result));
            
            // Check for API response - new format returns {total, page, per_page, data}
            if (!isset($result['data']) || !is_array($result['data'])) {
                // Silent fail - API might be down or response format unexpected
                error_log('Activation sync: No data in API response. Full response: ' . json_encode($result));
                return;
            }
            
            $apiActivations = $result['data'];
            error_log('Activation sync: Found ' . count($apiActivations) . ' activations from API');
            
            foreach ($apiActivations as $apiActivation) {
                $activationId = $apiActivation['activation_id'] ?? null;
                
                if (!$activationId) {
                    error_log('Activation sync: Skipping activation with no ID');
                    continue;
                }
                
                // Check if activation already exists in local database
                $existing = $this->db->fetch(
                    'SELECT id, code, status FROM activations WHERE activation_id = ? AND user_id = ?',
                    [$activationId, $userId]
                );
                
                if ($existing) {
                    // Update existing activation with latest info
                    $updateData = [];
                    
                    // Code from API (field is 'msg')
                    $apiMsg = $apiActivation['msg'] ?? null;
                    
                    // Only treat it as a valid code if it's numeric (not "Waiting Sms")
                    $isValidCode = !empty($apiMsg) && $apiMsg !== 'Waiting Sms' && is_numeric($apiMsg);
                    
                    if ($isValidCode && empty($existing['code'])) {
                        // Store the actual numeric code
                        $updateData['code'] = $apiMsg;
                        $updateData['status'] = 'completed';
                        $updateData['completed_at'] = date('Y-m-d H:i:s');
                        error_log("Activation sync: Updated activation {$activationId} with code {$apiMsg}, status: completed");
                    } elseif ($isValidCode && strtolower($existing['status']) !== 'completed') {
                        // Already has code but status needs update
                        $updateData['status'] = 'completed';
                        if (empty($existing['completed_at'])) {
                            $updateData['completed_at'] = date('Y-m-d H:i:s');
                        }
                        error_log("Activation sync: Updated activation {$activationId} status to completed");
                    }
                    
                    if (!empty($updateData)) {
                        $this->db->update('activations', $updateData, 'id = ?', [$existing['id']]);
                        error_log("Activation sync: Database updated for activation {$activationId}");
                    }
                } else {
                    // New activation from API - add to local database
                    $phone = $apiActivation['phone'] ?? '';
                    $apiMsg = $apiActivation['msg'] ?? null;
                    
                    // Only store code if it's a valid numeric code (not "Waiting Sms")
                    $isNumericCode = !empty($apiMsg) && $apiMsg !== 'Waiting Sms' && is_numeric($apiMsg);
                    $code = $isNumericCode ? $apiMsg : null; // Store null instead of "Waiting Sms"
                    $status = $isNumericCode ? 'completed' : 'pending';
                    
                    $cost = $apiActivation['amount_paid'] ?? 0;
                    $createdAt = $apiActivation['date_created'] ?? date('Y-m-d H:i:s');
                    $completedAt = $isNumericCode ? date('Y-m-d H:i:s') : null;
                    
                    // Extract service and country from order if available
                    $service = 'Unknown';
                    $country = 'Unknown';
                    
                    if (isset($apiActivation['order'])) {
                        // Try to extract from order relationships or transaction type
                        $order = $apiActivation['order'];
                        // Service and country might be in order details
                    }
                    
                    // If we have transaction details, try to extract service/country from type
                    if (isset($apiActivation['transaction']['type'])) {
                        $type = $apiActivation['transaction']['type'];
                        // Transaction type might be like "WhatsApp purchase for Kenya"
                        if (preg_match('/(.+?)\s+purchase\s+for\s+(.+)/i', $type, $matches)) {
                            $service = trim($matches[1]);
                            $country = trim($matches[2]);
                        }
                    }
                    
                    error_log("Activation sync: Importing new activation {$activationId} - Phone: {$phone}, Status: {$status}");
                    
                    $this->db->query(
                        "INSERT INTO activations (user_id, activation_id, service, country, phone, status, code, cost, proxnum_response, created_at, completed_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [
                            $userId, 
                            $activationId, 
                            $service, 
                            $country, 
                            $phone, 
                            $status, 
                            $code,
                            $cost,
                            json_encode($apiActivation),
                            $createdAt,
                            $completedAt
                        ]
                    );
                    
                    error_log("Activation sync: Successfully imported activation {$activationId}");
                }
            }
        } catch (\Exception $e) {
            // Silent fail - log error but don't disrupt user experience
            error_log('Activation sync error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        }
    }
    
    /**
     * Cancel activation
     */
    public function cancelActivation() {
        // Prevent admins from cancelling client activations
        if ($_SESSION['user_role'] === 'admin') {
            $this->json(['success' => false, 'message' => 'Admins cannot cancel client activations']);
            return;
        }
        
        // Handle JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        // Validate CSRF
        if (!isset($input['csrf_token']) || $input['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }
        
        $id = (int)($input['id'] ?? 0);
        $user = $this->getUser();
        
        $activation = $this->db->fetch(
            'SELECT * FROM activations WHERE id = ? AND user_id = ?',
            [$id, $user['id']]
        );
        
        if (!$activation) {
            $this->json(['success' => false, 'message' => 'Activation not found']);
            return;
        }
        
        if ($activation['status'] !== 'pending') {
            $this->json(['success' => false, 'message' => 'Only pending activations can be cancelled']);
            return;
        }
        
        // Check if 2 minutes have passed since purchase
        $createdTime = strtotime($activation['created_at']);
        $currentTime = time();
        $timeDiff = $currentTime - $createdTime;
        
        if ($timeDiff < 120) { // 120 seconds = 2 minutes
            $remainingSeconds = 120 - $timeDiff;
            $remainingMinutes = ceil($remainingSeconds / 60);
            $this->json([
                'success' => false, 
                'message' => "You must wait {$remainingMinutes} minute(s) before cancelling this activation"
            ]);
            return;
        }
        
        // Cancel on Proxnum
        $api = new ProxnumApi();
        $result = $api->cancelVirtual($activation['activation_id']);
        
        if (!isset($result['success']) || !$result['success']) {
            $errorMsg = $result['error']['message'] ?? 'Cancellation failed';
            $errorKey = $result['error']['key'] ?? '';
            
            // If resource not found, it might already be cancelled on their end
            if (strpos($errorMsg, 'not found') !== false || $errorKey === 'not_found') {
                // Mark as cancelled locally and refund
                $this->db->update('activations', [
                    'status' => 'cancelled'
                ], 'id = ?', [$id]);
                
                // Refund the cost to user's balance
                $this->db->query(
                    'UPDATE users SET balance = balance + ? WHERE id = ?',
                    [$activation['cost'], $user['id']]
                );
                
                // Record transaction
                $this->db->insert('transactions', [
                    'user_id' => $user['id'],
                    'type' => 'refund',
                    'amount' => $activation['cost'],
                    'description' => 'Refund for cancelled activation #' . $activation['activation_id'],
                    'status' => 'completed',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                Helper::logActivity('activation_cancelled', "Force cancelled activation ID: $id (not found on API)");
                
                $this->json([
                    'success' => true, 
                    'message' => 'Activation cancelled and refunded successfully'
                ]);
                return;
            }
            
            $this->json([
                'success' => false,
                'message' => $errorMsg
            ]);
            return;
        }
        
        // Update status
        $this->db->update('activations', [
            'status' => 'cancelled'
        ], 'id = ?', [$id]);
        
        // Refund the cost to user's balance
        $this->db->query(
            'UPDATE users SET balance = balance + ? WHERE id = ?',
            [$activation['cost'], $user['id']]
        );
        
        // Record transaction
        $this->db->insert('transactions', [
            'user_id' => $user['id'],
            'type' => 'refund',
            'amount' => $activation['cost'],
            'description' => 'Refund for cancelled activation #' . $activation['activation_id'],
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        Helper::logActivity('activation_cancelled', "Cancelled activation ID: $id");
        
        $this->json(['success' => true, 'message' => 'Activation cancelled and refunded successfully']);
    }
    
    /**
     * Check activation status
     */
    public function checkStatus() {
        // Prevent admins from checking client activation status
        if ($_SESSION['user_role'] === 'admin') {
            $this->json(['success' => false, 'message' => 'Admins cannot access client activations']);
            return;
        }
        
        $activationId = $_GET['id'] ?? null;
        $user = $this->getUser();
        
        if (!$activationId) {
            $this->json(['success' => false, 'message' => 'Activation ID required']);
            return;
        }
        
        // Get activation from database
        $activation = $this->db->fetch(
            'SELECT * FROM activations WHERE id = ? AND user_id = ?',
            [$activationId, $user['id']]
        );
        
        if (!$activation) {
            $this->json(['success' => false, 'message' => 'Activation not found']);
            return;
        }
        
        // If already completed or cancelled, return current status
        if ($activation['status'] !== 'pending') {
            $this->json([
                'success' => true,
                'status' => $activation['status'],
                'code' => $activation['code']
            ]);
            return;
        }
        
        // Check status from Proxnum API
        $api = new ProxnumApi();
        $result = $api->getVirtualStatus($activation['activation_id']);
        
        if (!$result['success']) {
            $this->json([
                'success' => false,
                'message' => 'Failed to check status'
            ]);
            return;
        }
        
        $apiActivation = $result['activation'] ?? [];
        $code = $apiActivation['code'] ?? null;
        $apiStatus = $apiActivation['status'] ?? 'pending';
        
        // Update database if code received
        if ($code && empty($activation['code'])) {
            $this->db->update('activations', [
                'code' => $code,
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$activationId]);
            
            $this->json([
                'success' => true,
                'status' => 'completed',
                'code' => $code,
                'message' => 'Code received!'
            ]);
            return;
        }
        
        $this->json([
            'success' => true,
            'status' => $apiStatus,
            'code' => $code
        ]);
    }
    
    /**
     * View transactions
     */
    public function transactions() {
        // Prevent admins from viewing client transactions
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin/transactions');
            return;
        }
        
        $user = $this->getUser();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 50;
        
        $total = $this->db->count('transactions', 'user_id = ?', [$user['id']]);
        $pagination = Helper::paginate($total, $perPage, $page);
        
        $transactions = $this->db->fetchAll(
            'SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$user['id'], $perPage, $pagination['offset']]
        );
        
        $this->view('dashboard/transactions', [
            'transactions' => $transactions,
            'pagination' => $pagination
        ]);
    }
    
    /**
     * Profile/Settings
     */
    public function profile() {
        // Prevent admins from accessing client profile
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
            return;
        }
        
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
                
                // Check if email is already taken
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
                    
                    Helper::logActivity('profile_updated', 'Profile updated');
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
                    
                    Helper::logActivity('password_changed', 'Password changed');
                    $message = 'Password changed successfully';
                }
            }
            
            } // End demo check
            
            // Refresh user data
            $user = $this->db->fetch('SELECT * FROM users WHERE id = ?', [$user['id']]);
        }
        
        $this->view('dashboard/profile', [
            'csrf_token' => $this->generateCsrf(),
            'message' => $message ?? null,
            'error' => $error ?? null,
            'user' => $user
        ]);
    }
    
    /**
     * Rental Numbers
     */
    public function rentals($id = null) {
        // Prevent admins from accessing client rentals
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
            return;
        }
        
        $user = $this->getUser();
        
        // View single rental with messages
        if ($id) {
            $rental = $this->db->fetch(
                'SELECT * FROM rentals WHERE id = ? AND user_id = ?',
                [$id, $user['id']]
            );
            
            if (!$rental) {
                $this->redirect('/error/notFound');
                return;
            }
            
            // Get messages for this rental
            $messages = $this->db->fetchAll(
                'SELECT * FROM rental_messages WHERE rental_id = ? ORDER BY received_at DESC',
                [$rental['rental_id']]
            );
            
            // Check for new messages from API
            $api = new ProxnumApi();
            $apiMessages = $api->getRentalMessages($rental['rental_id']);
            
            if ($apiMessages && isset($apiMessages['messages'])) {
                // Store new messages
                foreach ($apiMessages['messages'] as $msg) {
                    // Check if message already exists
                    $exists = $this->db->fetch(
                        'SELECT id FROM rental_messages WHERE rental_id = ? AND sender = ? AND received_at = ?',
                        [$rental['rental_id'], $msg['sender'], $msg['received_at']]
                    );
                    
                    if (!$exists) {
                        $this->db->insert('rental_messages', [
                            'rental_id' => $rental['rental_id'],
                            'sender' => $msg['sender'],
                            'message' => $msg['message'],
                            'received_at' => $msg['received_at']
                        ]);
                    }
                }
                
                // Refresh messages
                $messages = $this->db->fetchAll(
                    'SELECT * FROM rental_messages WHERE rental_id = ? ORDER BY received_at DESC',
                    [$rental['rental_id']]
                );
            }
            
            $this->view('dashboard/rental_detail', [
                'rental' => $rental,
                'messages' => $messages
            ]);
            
        } else {
            // List all rentals with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 20;
            
            $total = $this->db->count('rentals', 'user_id = ?', [$user['id']]);
            $pagination = Helper::paginate($total, $perPage, $page);
            
            $rentals = $this->db->fetchAll(
                'SELECT * FROM rentals WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
                [$user['id'], $perPage, $pagination['offset']]
            );
            
            $this->view('dashboard/rentals', [
                'rentals' => $rentals,
                'pagination' => $pagination
            ]);
        }
    }
    
    /**
     * Buy rental number
     */
    public function buyRental() {
        // Prevent admins from purchasing numbers
        if ($_SESSION['user_role'] === 'admin') {
            $this->json(['success' => false, 'message' => 'Admins cannot purchase numbers']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard/rentals');
            return;
        }
        
        $this->validateCsrf();
        $user = $this->getUser();
        
        $service = Helper::sanitize($_POST['service'] ?? '');
        $country = Helper::sanitize($_POST['country'] ?? '');
        $days = (int)($_POST['days'] ?? 7);
        
        // Get price from API
        $api = new ProxnumApi();
        $prices = $api->getRentalPrices($service, $country);
        
        // Extract price from response (handle both nested and flat structures)
        $pricePerDay = 0;
        if (isset($prices['prices'][$country][$service]['sell_price'])) {
            $pricePerDay = $prices['prices'][$country][$service]['sell_price'];
        } elseif (isset($prices['prices'][$country][$service]['base_price'])) {
            $pricePerDay = $prices['prices'][$country][$service]['base_price'];
        } elseif (isset($prices['price'])) {
            $pricePerDay = $prices['price'];
        }
        
        if ($pricePerDay <= 0) {
            $this->json(['success' => false, 'message' => 'Unable to get price']);
            return;
        }
        
        $apiPrice = $pricePerDay * $days;
        
        // Apply global price multiplier
        $globalMultiplier = (float)Helper::getSetting('price_multiplier', '1');
        $price = $apiPrice * $globalMultiplier;
        
        // Apply service/country specific multiplier if exists
        $specificMultiplier = (float)Helper::getSetting('multiplier_' . $service . '_' . $country, '1');
        $finalPrice = $price * $specificMultiplier;
        
        // Check balance
        if ($user['balance'] < $finalPrice) {
            $this->json(['success' => false, 'message' => 'Insufficient balance']);
            return;
        }
        
        // Purchase from API
        $result = $api->buyRental($service, $country, $days);
        
        if (!$result || !isset($result['rental_id'])) {
            $this->json(['success' => false, 'message' => $result['message'] ?? 'Purchase failed']);
            return;
        }
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Deduct balance
            $newBalance = $user['balance'] - $finalPrice;
            $this->db->update('users', ['balance' => $newBalance], 'id = ?', [$user['id']]);
            
            // Log transaction
            $this->db->insert('transactions', [
                'user_id' => $user['id'],
                'type' => 'purchase',
                'amount' => $finalPrice,
                'balance_before' => $user['balance'],
                'balance_after' => $newBalance,
                'description' => "Rental: {$service} - {$country} ({$days} days)"
            ]);
            
            // Store rental
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));
            
            $this->db->insert('rentals', [
                'user_id' => $user['id'],
                'rental_id' => $result['rental_id'],
                'service' => $service,
                'country' => $country,
                'phone' => $result['phone'] ?? '',
                'days' => $days,
                'status' => 'active',
                'cost' => $finalPrice,
                'expires_at' => $expiresAt
            ]);
            
            $this->db->commit();
            
            Helper::logActivity('rental_purchased', "Rental purchased: {$service} - {$country}");
            
            $this->json([
                'success' => true,
                'message' => 'Rental purchased successfully',
                'rental' => [
                    'id' => $result['rental_id'],
                    'phone' => $result['phone'] ?? '',
                    'expires_at' => $expiresAt
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->json(['success' => false, 'message' => 'Purchase failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Cancel rental
     */
    public function cancelRental() {
        // Prevent admins from cancelling client rentals
        if ($_SESSION['user_role'] === 'admin') {
            $this->json(['success' => false, 'message' => 'Admins cannot cancel client rentals']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $this->validateCsrf();
        $user = $this->getUser();
        
        $rentalId = (int)($_POST['rental_id'] ?? 0);
        
        $rental = $this->db->fetch(
            'SELECT * FROM rentals WHERE id = ? AND user_id = ?',
            [$rentalId, $user['id']]
        );
        
        if (!$rental) {
            $this->json(['success' => false, 'message' => 'Rental not found']);
            return;
        }
        
        if ($rental['status'] !== 'active') {
            $this->json(['success' => false, 'message' => 'Rental is not active']);
            return;
        }
        
        // Cancel via API
        $api = new ProxnumApi();
        $result = $api->cancelRental($rental['rental_id']);
        
        if (!$result || !isset($result['success']) || !$result['success']) {
            $this->json(['success' => false, 'message' => 'Failed to cancel rental']);
            return;
        }
        
        // Update status
        $this->db->update('rentals', [
            'status' => 'cancelled'
        ], 'id = ?', [$rentalId]);
        
        Helper::logActivity('rental_cancelled', "Rental cancelled: {$rental['rental_id']}");
        
        $this->json(['success' => true, 'message' => 'Rental cancelled successfully']);
    }
    
    /**
     * Notifications
     */
    public function notifications() {
        // Prevent admins from accessing client notifications
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
            return;
        }
        
        $user = $this->getUser();
        
        // Handle clear all notifications
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            // Validate CSRF
            if (!isset($input['csrf_token']) || $input['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                $this->json(['success' => false, 'message' => 'Invalid CSRF token']);
                return;
            }
            
            $action = $input['action'] ?? '';
            
            if ($action === 'clear_all') {
                // Delete all activity logs for this user
                $deleted = $this->db->query(
                    'DELETE FROM activity_logs WHERE user_id = ?',
                    [$user['id']]
                );
                
                Helper::logActivity('notifications_cleared', 'Cleared all notifications');
                
                $this->json([
                    'success' => true,
                    'message' => 'All notifications cleared successfully'
                ]);
                return;
            }
        }
        
        // Pagination for activities
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 30;
        
        $total = $this->db->count('activity_logs', 'user_id = ?', [$user['id']]);
        $pagination = Helper::paginate($total, $perPage, $page);
        
        // Get recent activities
        $activities = $this->db->fetchAll(
            'SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$user['id'], $perPage, $pagination['offset']]
        );
        
        // Get expiring rentals
        $expiringRentals = $this->db->fetchAll(
            'SELECT * FROM rentals WHERE user_id = ? AND status = ? AND expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)',
            [$user['id'], 'active']
        );
        
        // Low balance warning
        $minBalance = Helper::getSetting('min_balance', 1);
        $lowBalance = $user['balance'] < $minBalance;
        
        $this->view('dashboard/notifications', [
            'user' => $user,
            'activities' => $activities,
            'expiring_rentals' => $expiringRentals,
            'low_balance' => $lowBalance,
            'csrf_token' => $this->generateCsrf(),
            'pagination' => $pagination
        ]);
    }
    
    /**
     * Announcements
     */
    public function announcements() {
        // Prevent admins from accessing client announcements
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
            return;
        }
        
        $user = $this->getUser();
        
        // Get active announcements only
        $announcements = $this->db->fetchAll(
            "SELECT a.*, u.name as created_by_name 
             FROM announcements a 
             LEFT JOIN users u ON a.created_by = u.id 
             WHERE a.active = 1
             ORDER BY a.priority DESC, a.created_at DESC"
        );
        
        $this->view('dashboard/announcements', [
            'user' => $user,
            'announcements' => $announcements
        ]);
    }
    
    /**
     * Favorites
     */
    public function favorites() {
        // Prevent admins from accessing client favorites
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
            return;
        }
        
        $user = $this->getUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $action = $input['action'] ?? '';
            
            if ($action === 'add') {
                // Check if already exists
                $exists = $this->db->fetch(
                    "SELECT id FROM user_favorites WHERE user_id = ? AND service = ? AND country = ?",
                    [$user['id'], $input['service'], $input['country']]
                );
                
                if (!$exists) {
                    $this->db->query(
                        "INSERT INTO user_favorites (user_id, service, country, created_at) VALUES (?, ?, ?, NOW())",
                        [$user['id'], $input['service'], $input['country']]
                    );
                }
                
                $this->json(['success' => true, 'message' => 'Added to favorites']);
                return;
            } elseif ($action === 'remove') {
                $this->db->query(
                    "DELETE FROM user_favorites WHERE user_id = ? AND id = ?",
                    [$user['id'], $input['favorite_id']]
                );
                
                $this->json(['success' => true, 'message' => 'Removed from favorites']);
                return;
            }
        }
        
        // Get favorites
        $favorites = $this->db->fetchAll(
            "SELECT * FROM user_favorites WHERE user_id = ? ORDER BY created_at DESC",
            [$user['id']]
        );
        
        // Get frequently used services (purchased more than 2 times)
        $frequentServices = $this->db->fetchAll(
            "SELECT service, country, COUNT(*) as usage_count 
             FROM activations 
             WHERE user_id = ? 
             GROUP BY service, country 
             HAVING COUNT(*) >= 2 
             ORDER BY usage_count DESC 
             LIMIT 10",
            [$user['id']]
        );
        
        $this->view('dashboard/favorites', [
            'user' => $user,
            'favorites' => $favorites,
            'frequent_services' => $frequentServices
        ]);
    }
    
    /**
     * Usage History
     */
    public function history() {
        // Prevent admins from accessing client history
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
            return;
        }
        
        $user = $this->getUser();
        $period = $_GET['period'] ?? '30';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 30;
        
        // Count total items
        $totalActivations = $this->db->count(
            'activations',
            'user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)',
            [$user['id'], $period]
        );
        $totalRentals = $this->db->count(
            'rentals',
            'user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)',
            [$user['id'], $period]
        );
        
        $total = $totalActivations + $totalRentals;
        $pagination = Helper::paginate($total, $perPage, $page);
        
        // Get combined history with pagination
        $activations = $this->db->fetchAll(
            "SELECT *, 'activation' as type FROM activations 
             WHERE user_id = ? 
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$user['id'], $period]
        );
        
        $rentals = $this->db->fetchAll(
            "SELECT *, 'rental' as type FROM rentals 
             WHERE user_id = ? 
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$user['id'], $period]
        );
        
        // Combine and sort
        $allItems = array_merge($activations, $rentals);
        usort($allItems, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Apply pagination
        $allItems = array_slice($allItems, $pagination['offset'], $perPage);
        
        // Statistics
        $stats = [
            'total_spent' => $this->db->fetch(
                "SELECT SUM(amount) as total FROM transactions 
                 WHERE user_id = ? AND type = 'debit' 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$user['id'], $period]
            )['total'] ?? 0,
            'total_activations' => count($activations),
            'total_rentals' => count($rentals),
            'favorite_service' => $this->db->fetch(
                "SELECT service, COUNT(*) as count FROM activations 
                 WHERE user_id = ? 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                 GROUP BY service ORDER BY count DESC LIMIT 1",
                [$user['id'], $period]
            )['service'] ?? 'N/A'
        ];
        
        $this->view('dashboard/history', [
            'user' => $user,
            'items' => $allItems,
            'stats' => $stats,
            'period' => $period,
            'pagination' => $pagination
        ]);
    }
    
    /**
     * Wallet & Funds
     */
    public function wallet() {
        // Prevent admins from accessing client wallet
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
            return;
        }
        
        $user = $this->getUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'initiate_deposit') {
                $amount = floatval($_POST['amount'] ?? 0);
                $gateway = Helper::sanitize($_POST['gateway'] ?? '');
                
                if ($amount < 1) {
                    $this->json(['success' => false, 'message' => 'Minimum deposit is $1']);
                    return;
                }
                
                // Get gateway details
                $gatewayData = $this->db->fetch(
                    "SELECT * FROM payment_gateways WHERE name = ? AND enabled = 1",
                    [$gateway]
                );
                
                if (!$gatewayData) {
                    $this->json(['success' => false, 'message' => 'Invalid payment gateway']);
                    return;
                }
                
                $config = json_decode($gatewayData['config'], true) ?? [];
                
                $this->json([
                    'success' => true,
                    'gateway' => $gateway,
                    'gateway_name' => $gatewayData['display_name'],
                    'amount' => $amount,
                    'config' => $config,
                    'instructions' => $gatewayData['instructions']
                ]);
                return;
            } elseif ($action === 'submit_payment') {
                $this->validateCsrf();
                
                // Block demo users from making deposits
                Helper::blockDemoAction('Demo accounts cannot make deposits. Purchase a license to access full functionality.');
                
                $amount = floatval($_POST['amount'] ?? 0);
                $gateway = Helper::sanitize($_POST['gateway'] ?? '');
                $transactionRef = Helper::sanitize($_POST['transaction_ref'] ?? '');
                
                if ($amount < 1) {
                    $this->json(['success' => false, 'message' => 'Invalid amount']);
                    return;
                }
                
                // Handle file upload
                $paymentProof = null;
                if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
                    $uploadDir = __DIR__ . '/../../storage/payment_proofs/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileExt = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
                    
                    if (!in_array(strtolower($fileExt), $allowedExts)) {
                        $this->json(['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, PDF']);
                        return;
                    }
                    
                    if ($_FILES['payment_proof']['size'] > 5 * 1024 * 1024) {
                        $this->json(['success' => false, 'message' => 'File too large. Maximum 5MB']);
                        return;
                    }
                    
                    $paymentProof = uniqid() . '_' . time() . '.' . $fileExt;
                    move_uploaded_file($_FILES['payment_proof']['tmp_name'], $uploadDir . $paymentProof);
                }
                
                // Create deposit request
                $this->db->insert('payment_deposits', [
                    'user_id' => $user['id'],
                    'gateway' => $gateway,
                    'amount' => $amount,
                    'payment_proof' => $paymentProof,
                    'transaction_reference' => $transactionRef,
                    'status' => 'pending'
                ]);
                
                Helper::logActivity('payment_deposit_submitted', "Submitted payment deposit of $$amount via $gateway");
                
                $this->json([
                    'success' => true, 
                    'message' => 'Payment submitted for verification. Your balance will be updated once approved.'
                ]);
                return;
            }
        }
        
        // Get recent transactions
        $transactions = $this->db->fetchAll(
            "SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20",
            [$user['id']]
        );
        
        // Get payment methods
        $paymentMethods = $this->db->fetchAll(
            "SELECT * FROM payment_gateways WHERE enabled = 1 AND name IN ('paypal', 'crypto', 'binance')"
        );
        
        // Get pending deposits
        $pendingDeposits = $this->db->fetchAll(
            "SELECT * FROM payment_deposits WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC",
            [$user['id']]
        );
        
        $this->view('dashboard/wallet', [
            'user' => $user,
            'transactions' => $transactions,
            'payment_methods' => $paymentMethods,
            'pending_deposits' => $pendingDeposits,
            'basePath' => Helper::url('')
        ]);
    }
    
    /**
     * Invoices
     */
    public function invoices() {
        // Prevent admins from accessing client invoices
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
            return;
        }
        
        $user = $this->getUser();
        
        // If invoice ID is provided, generate PDF
        if (isset($_GET['download'])) {
            $invoiceId = $_GET['download'];
            $invoice = $this->db->fetch(
                "SELECT * FROM transactions WHERE id = ? AND user_id = ?",
                [$invoiceId, $user['id']]
            );
            
            if ($invoice) {
                $this->generateInvoicePDF($invoice, $user);
                exit;
            }
        }
        
        // Get all debit transactions (invoices)
        $invoices = $this->db->fetchAll(
            "SELECT * FROM transactions 
             WHERE user_id = ? AND type IN ('debit', 'purchase')
             ORDER BY created_at DESC",
            [$user['id']]
        );
        
        $this->view('dashboard/invoices', [
            'user' => $user,
            'invoices' => $invoices
        ]);
    }
    
    /**
     * Generate PDF invoice
     */
    private function generateInvoicePDF($invoice, $user) {
        // Simple HTML invoice that can be printed as PDF
        $html = '<html><head><style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
            .invoice-details { margin: 30px 0; }
            table { width: 100%; border-collapse: collapse; margin-top: 30px; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f5f5f5; font-weight: bold; }
            .total { font-size: 18px; font-weight: bold; text-align: right; margin-top: 20px; }
            .footer { margin-top: 50px; text-align: center; color: #666; font-size: 12px; }
        </style></head><body>';
        
        $html .= '<div class="header">';
        $html .= '<h1>' . APP_NAME . '</h1>';
        $html .= '<p>Invoice #INV-' . str_pad($invoice['id'], 6, '0', STR_PAD_LEFT) . '</p>';
        $html .= '</div>';
        
        $html .= '<div class="invoice-details">';
        $html .= '<p><strong>Bill To:</strong><br>' . htmlspecialchars($user['name']) . '<br>' . htmlspecialchars($user['email']) . '</p>';
        $html .= '<p><strong>Date:</strong> ' . date('F j, Y', strtotime($invoice['created_at'])) . '</p>';
        $html .= '<p><strong>Reference:</strong> ' . htmlspecialchars($invoice['reference']) . '</p>';
        $html .= '</div>';
        
        $html .= '<table>';
        $html .= '<tr><th>Description</th><th>Amount</th></tr>';
        $html .= '<tr><td>' . htmlspecialchars($invoice['description']) . '</td><td>$' . number_format($invoice['amount'], 2) . '</td></tr>';
        $html .= '</table>';
        
        $html .= '<div class="total">Total: $' . number_format($invoice['amount'], 2) . '</div>';
        
        $html .= '<div class="footer">';
        $html .= '<p>Thank you for your business!</p>';
        $html .= '<p>' . APP_NAME . ' | ' . APP_URL . '</p>';
        $html .= '</div>';
        
        $html .= '</body></html>';
        
        // Output as HTML (browser can print to PDF)
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
    
    /**
     * Referral Program
     */
    public function referral() {
        // Prevent admins from accessing client referral program
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
            return;
        }
        
        $user = $this->getUser();
        
        // Get or create referral code
        $referralCode = $this->db->fetch(
            "SELECT referral_code FROM users WHERE id = ?",
            [$user['id']]
        )['referral_code'];
        
        if (!$referralCode) {
            $referralCode = strtoupper(bin2hex(random_bytes(4)));
            $this->db->query(
                "UPDATE users SET referral_code = ? WHERE id = ?",
                [$referralCode, $user['id']]
            );
        }
        
        // Get referral statistics
        $referrals = $this->db->fetchAll(
            "SELECT * FROM users WHERE referred_by = ? ORDER BY created_at DESC",
            [$user['id']]
        );
        
        $earnings = $this->db->fetch(
            "SELECT SUM(amount) as total FROM referral_earnings WHERE user_id = ?",
            [$user['id']]
        )['total'] ?? 0;
        
        $this->view('dashboard/referral', [
            'user' => $user,
            'referral_code' => $referralCode,
            'referrals' => $referrals,
            'earnings' => $earnings,
            'referral_url' => APP_URL . '/register?ref=' . $referralCode
        ]);
    }
    
    /**
     * Support Tickets
     */
    public function support($ticketId = null) {
        // Prevent admins from accessing client support tickets
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin/support');
            return;
        }
        
        $user = $this->getUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $action = $input['action'] ?? '';
            
            if ($action === 'create_ticket') {
                $subject = Helper::sanitize($input['subject'] ?? '');
                $message = Helper::sanitize($input['message'] ?? '');
                $priority = Helper::sanitize($input['priority'] ?? 'medium');
                
                if (empty($subject) || empty($message)) {
                    $this->json(['success' => false, 'message' => 'Subject and message are required']);
                    return;
                }
                
                $ticketNumber = 'TKT-' . strtoupper(bin2hex(random_bytes(4)));
                
                $this->db->query(
                    "INSERT INTO support_tickets (user_id, ticket_number, subject, message, priority, status, created_at) 
                     VALUES (?, ?, ?, ?, ?, 'open', NOW())",
                    [$user['id'], $ticketNumber, $subject, $message, $priority]
                );
                
                Helper::logActivity('ticket_created', "Created support ticket: {$ticketNumber}");
                
                $this->json(['success' => true, 'message' => 'Ticket created successfully', 'ticket_number' => $ticketNumber]);
                return;
            } elseif ($action === 'reply') {
                $ticketId = (int)($input['ticket_id'] ?? 0);
                $message = Helper::sanitize($input['message'] ?? '');
                
                if (empty($message)) {
                    $this->json(['success' => false, 'message' => 'Message is required']);
                    return;
                }
                
                $this->db->query(
                    "INSERT INTO support_replies (ticket_id, user_id, message, created_at) 
                     VALUES (?, ?, ?, NOW())",
                    [$ticketId, $user['id'], $message]
                );
                
                // Update ticket's updated_at timestamp
                $this->db->query(
                    "UPDATE support_tickets SET updated_at = NOW() WHERE id = ?",
                    [$ticketId]
                );
                
                $this->json(['success' => true, 'message' => 'Reply added']);
                return;
            }
        }
        
        // View single ticket
        if ($ticketId) {
            $ticket = $this->db->fetch(
                "SELECT * FROM support_tickets WHERE id = ? AND user_id = ?",
                [$ticketId, $user['id']]
            );
            
            if (!$ticket) {
                $this->redirect('/dashboard/support');
                return;
            }
            
            $replies = $this->db->fetchAll(
                "SELECT r.*, u.name as user_name FROM support_replies r 
                 LEFT JOIN users u ON r.user_id = u.id 
                 WHERE r.ticket_id = ? ORDER BY r.created_at ASC",
                [$ticketId]
            );
            
            $this->view('dashboard/support_detail', [
                'user' => $user,
                'ticket' => $ticket,
                'replies' => $replies
            ]);
            return;
        }
        
        // List all tickets
        $tickets = $this->db->fetchAll(
            "SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC",
            [$user['id']]
        );
        
        $this->view('dashboard/support', [
            'user' => $user,
            'tickets' => $tickets
        ]);
    }
}
