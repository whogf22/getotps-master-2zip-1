<?php
/**
 * Unsubscribe Controller
 * Handles email unsubscribe functionality
 */

namespace Controllers;

use Core\Controller;
use Core\Helper;
use Core\Database;
use Core\Mailer;

class UnsubscribeController extends Controller {
    
    /**
     * Unsubscribe page and handler
     */
    public function index() {
        $token = $_GET['token'] ?? '';
        $db = Database::getInstance();
        
        // Try to find user by token
        $users = $db->fetchAll('SELECT id, name, email, email_subscribed FROM users');
        $matchedUser = null;
        
        foreach ($users as $user) {
            if (Mailer::verifyUnsubscribeToken($user['id'], $token)) {
                $matchedUser = $user;
                break;
            }
        }
        
        if (!$matchedUser) {
            $this->view('unsubscribe/error', [
                'message' => 'Invalid or expired unsubscribe link.'
            ]);
            return;
        }
        
        // Handle unsubscribe action
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'unsubscribe') {
                // Update user's email subscription status
                $db->update('users', [
                    'email_subscribed' => 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$matchedUser['id']]);
                
                // Log activity
                Helper::logActivity('email_unsubscribed', 'User unsubscribed from bulk emails', $matchedUser['id']);
                
                $this->view('unsubscribe/success', [
                    'user' => $matchedUser
                ]);
                return;
            }
            
            if ($action === 'resubscribe') {
                // Re-subscribe user
                $db->update('users', [
                    'email_subscribed' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$matchedUser['id']]);
                
                // Log activity
                Helper::logActivity('email_resubscribed', 'User re-subscribed to bulk emails', $matchedUser['id']);
                
                $this->view('unsubscribe/resubscribe_success', [
                    'user' => $matchedUser
                ]);
                return;
            }
        }
        
        // Show unsubscribe confirmation page
        $this->view('unsubscribe/confirm', [
            'user' => $matchedUser,
            'token' => $token
        ]);
    }
}
