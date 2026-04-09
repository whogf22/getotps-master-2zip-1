<?php
/**
 * Base Controller Class
 */

namespace Core;

class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Load view file
     */
    protected function view($view, $data = []) {
        extract($data);
        
        $viewFile = BASE_PATH . '/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die('View not found: ' . $view);
        }
    }
    
    /**
     * Load model
     */
    protected function model($model) {
        $modelFile = BASE_PATH . '/models/' . $model . '.php';
        
        if (file_exists($modelFile)) {
            require_once $modelFile;
            $modelClass = '\\Models\\' . $model;
            return new $modelClass();
        }
        
        die('Model not found: ' . $model);
    }
    
    /**
     * JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Get base path for the application
     */
    protected function getBasePath() {
        $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
        return rtrim($scriptPath, '/');
    }
    
    /**
     * Redirect
     */
    protected function redirect($url) {
        // If URL starts with /, prepend base path
        if (strpos($url, '/') === 0) {
            $url = $this->getBasePath() . $url;
        }
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Check if user is logged in
     */
    protected function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/auth/login');
        }
    }
    
    /**
     * Check if user is admin
     */
    protected function requireAdmin() {
        $this->requireAuth();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $this->redirect('/dashboard');
        }
    }
    
    /**
     * Get current user
     */
    protected function getUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        return $this->db->fetch(
            'SELECT * FROM users WHERE id = ?',
            [$_SESSION['user_id']]
        );
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCsrf() {
        $token = $_POST['csrf_token'] ?? '';
        
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 403);
        }
    }
    
    /**
     * Generate CSRF token
     */
    protected function generateCsrf() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
