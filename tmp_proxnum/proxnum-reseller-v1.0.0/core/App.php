<?php
/**
 * Core Application Class
 * Handles routing and request processing
 */

namespace Core;

class App {
    private $controller = 'Home';
    private $method = 'index';
    private $params = [];
    
    public function __construct() {
        $this->parseUrl();
    }
    
    public function run() {
        // Check if controller exists
        $controllerPath = BASE_PATH . '/controllers/' . $this->controller . 'Controller.php';
        
        if (!file_exists($controllerPath)) {
            $this->controller = 'Error';
            $this->method = 'notFound';
            $controllerPath = BASE_PATH . '/controllers/ErrorController.php';
        }
        
        require_once $controllerPath;
        
        $controllerClass = '\\Controllers\\' . $this->controller . 'Controller';
        $controllerObj = new $controllerClass();
        
        // Check if method exists
        if (!method_exists($controllerObj, $this->method)) {
            $this->method = 'index';
        }
        
        // Call controller method with parameters
        call_user_func_array([$controllerObj, $this->method], $this->params);
    }
    
    private function parseUrl() {
        if (isset($_GET['route'])) {
            $url = explode('/', filter_var(rtrim($_GET['route'], '/'), FILTER_SANITIZE_URL));
            
            if (!empty($url[0])) {
                $this->controller = ucfirst($url[0]);
            }
            
            if (isset($url[1]) && !empty($url[1])) {
                $this->method = $url[1];
            }
            
            if (isset($url[2])) {
                $this->params = array_slice($url, 2);
            }
        }
    }
}
