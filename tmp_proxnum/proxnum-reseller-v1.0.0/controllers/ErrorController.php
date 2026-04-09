<?php
/**
 * Error Controller
 * Handles error pages
 */

namespace Controllers;

use Core\Controller;

class ErrorController extends Controller {
    
    public function notFound() {
        http_response_code(404);
        $this->view('errors/404');
    }
    
    public function forbidden() {
        http_response_code(403);
        $this->view('errors/403');
    }
    
    public function serverError() {
        http_response_code(500);
        $this->view('errors/500');
    }
}
