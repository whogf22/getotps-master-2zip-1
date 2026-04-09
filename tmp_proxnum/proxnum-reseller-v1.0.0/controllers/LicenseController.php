<?php
/**
 * License Controller
 * Handles license purchase and management
 */

namespace Controllers;

use Core\Controller;
use Core\Helper;

class LicenseController extends Controller {
    
    /**
     * License purchase page
     */
    public function purchase() {
        // Get available payment methods
        $payment_methods = $this->db->fetchAll(
            "SELECT * FROM payment_gateways WHERE enabled = 1 ORDER BY name"
        );
        
        // License plans
        $plans = [
            [
                'id' => 'starter',
                'name' => 'Starter License',
                'price' => 99.00,
                'duration' => 'Lifetime',
                'features' => [
                    'Unlimited Users',
                    'Unlimited Transactions',
                    'All SMS Services',
                    'Priority Support',
                    'Free Updates',
                    '1 Domain License'
                ],
                'recommended' => false
            ],
            [
                'id' => 'professional',
                'name' => 'Professional License',
                'price' => 199.00,
                'duration' => 'Lifetime',
                'features' => [
                    'Everything in Starter',
                    'White Label Option',
                    'Custom Branding',
                    'API Access',
                    'Webhook Support',
                    '3 Domain Licenses'
                ],
                'recommended' => true
            ],
            [
                'id' => 'enterprise',
                'name' => 'Enterprise License',
                'price' => 499.00,
                'duration' => 'Lifetime',
                'features' => [
                    'Everything in Professional',
                    'Dedicated Support',
                    'Custom Features',
                    'Source Code Access',
                    'Priority Updates',
                    'Unlimited Domains'
                ],
                'recommended' => false
            ]
        ];
        
        // Handle POST - purchase initiation
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePurchase();
            return;
        }
        
        $this->view('license/purchase', [
            'plans' => $plans,
            'payment_methods' => $payment_methods
        ]);
    }
    
    /**
     * Handle license purchase
     */
    private function handlePurchase() {
        try {
            $this->validateCsrf();
            
            $plan = Helper::sanitize($_POST['plan'] ?? '');
            $gateway = Helper::sanitize($_POST['gateway'] ?? '');
            $email = Helper::sanitize($_POST['email'] ?? '');
            $domain = Helper::sanitize($_POST['domain'] ?? '');
            
            // Validate inputs
            if (empty($plan) || empty($gateway) || empty($email) || empty($domain)) {
                $this->json(['success' => false, 'message' => 'All fields are required']);
                return;
            }
            
            if (!Helper::validateEmail($email)) {
                $this->json(['success' => false, 'message' => 'Invalid email address']);
                return;
            }
            
            // Get plan details
            $plans = [
                'starter' => 99.00,
                'professional' => 199.00,
                'enterprise' => 499.00
            ];
            
            if (!isset($plans[$plan])) {
                $this->json(['success' => false, 'message' => 'Invalid plan selected']);
                return;
            }
            
            $amount = $plans[$plan];
            
            // Get gateway details
            $gatewayData = $this->db->fetch(
                "SELECT * FROM payment_gateways WHERE name = ? AND enabled = 1",
                [$gateway]
            );
            
            if (!$gatewayData) {
                $this->json(['success' => false, 'message' => 'Invalid payment gateway']);
                return;
            }
            
            // Create license purchase order
            $orderNumber = 'LIC-' . strtoupper(bin2hex(random_bytes(4)));
            
            $this->db->insert('license_orders', [
                'order_number' => $orderNumber,
                'plan' => $plan,
                'amount' => $amount,
                'email' => $email,
                'domain' => $domain,
                'gateway' => $gateway,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $orderId = $this->db->getConnection()->lastInsertId();
            
            // Return payment instructions
            $config = json_decode($gatewayData['config'], true) ?? [];
            
            $this->json([
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'amount' => $amount,
                'gateway' => $gateway,
                'gateway_name' => $gatewayData['display_name'],
                'config' => $config,
                'instructions' => $gatewayData['instructions']
            ]);
            
        } catch (\Exception $e) {
            error_log('License purchase error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred. Please try again.']);
        }
    }
    
    /**
     * Submit payment proof for license
     */
    public function submitProof() {
        try {
            $this->validateCsrf();
            
            $orderId = (int)($_POST['order_id'] ?? 0);
            $transactionRef = Helper::sanitize($_POST['transaction_ref'] ?? '');
            
            if (!$orderId) {
                $this->json(['success' => false, 'message' => 'Invalid order']);
                return;
            }
            
            // Get order
            $order = $this->db->fetch(
                "SELECT * FROM license_orders WHERE id = ?",
                [$orderId]
            );
            
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Order not found']);
                return;
            }
            
            // Handle file upload
            $paymentProof = null;
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
                $uploadDir = BASE_PATH . '/storage/license_proofs/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileExt = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
                
                if (!in_array(strtolower($fileExt), $allowedExts)) {
                    $this->json(['success' => false, 'message' => 'Invalid file type']);
                    return;
                }
                
                if ($_FILES['payment_proof']['size'] > 5 * 1024 * 1024) {
                    $this->json(['success' => false, 'message' => 'File too large. Maximum 5MB']);
                    return;
                }
                
                $paymentProof = $order['order_number'] . '_' . time() . '.' . $fileExt;
                move_uploaded_file($_FILES['payment_proof']['tmp_name'], $uploadDir . $paymentProof);
            }
            
            // Update order
            $this->db->update('license_orders', [
                'transaction_ref' => $transactionRef,
                'payment_proof' => $paymentProof,
                'status' => 'verifying',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$orderId]);
            
            $this->json([
                'success' => true,
                'message' => 'Payment submitted! We will verify and send your license key to ' . $order['email'] . ' within 24 hours.'
            ]);
            
        } catch (\Exception $e) {
            error_log('License proof submission error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred. Please try again.']);
        }
    }
    
    /**
     * Check license order status
     */
    public function status() {
        $orderNumber = Helper::sanitize($_GET['order'] ?? '');
        
        if (empty($orderNumber)) {
            echo 'Invalid order number';
            return;
        }
        
        $order = $this->db->fetch(
            "SELECT * FROM license_orders WHERE order_number = ?",
            [$orderNumber]
        );
        
        if (!$order) {
            echo 'Order not found';
            return;
        }
        
        $this->view('license/status', [
            'order' => $order
        ]);
    }
}
