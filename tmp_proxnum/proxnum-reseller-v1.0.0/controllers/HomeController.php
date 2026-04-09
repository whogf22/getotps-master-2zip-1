<?php
/**
 * Home Controller
 * Handles public pages
 */

namespace Controllers;

use Core\Controller;

class HomeController extends Controller {
    
    public function index() {
        // Redirect to login if not authenticated
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/auth/login');
        }
        
        // Redirect based on role
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/admin');
        } else {
            $this->redirect('/dashboard');
        }
    }
}
