<?php
namespace App\Core;

class Router {
    protected $routes = [];

    public function get($route, $controller) {
        $this->routes['GET'][$route] = $controller;
    }

    public function post($route, $controller) {
        $this->routes['POST'][$route] = $controller;
    }

    public function run() {
        $url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
        
        // Strip .php extension if it exists so /logout.php maps to /logout
        if (substr($url, -4) === '.php') {
            $url = substr($url, 0, -4);
        }
        
        $url = '/' . $url;
        
        $method = $_SERVER['REQUEST_METHOD'];

        if (array_key_exists($url, $this->routes[$method])) {
            $controllerAction = $this->routes[$method][$url];
            $parts = explode('@', $controllerAction);
            $controllerName = "App\\Controllers\\" . $parts[0];
            $methodName = $parts[1];

            if (class_exists($controllerName)) {
                $controllerInstance = new $controllerName();
                if (method_exists($controllerInstance, $methodName)) {
                    return $controllerInstance->$methodName();
                } else {
                    $this->abort(404, "Method $methodName not found in $controllerName");
                }
            } else {
                $this->abort(404, "Controller $controllerName not found");
            }
        } else {
            // Fallback for legacy procedural files if not defined in router
            // Remove this once fully migrated to MVC
             $legacyFile = ltrim($url, '/') . '.php';
             if (!empty($url) && $url !== '/' && file_exists(dirname(__DIR__, 2) . '/' . $legacyFile)) {
                 require dirname(__DIR__, 2) . '/' . $legacyFile;
                 return;
             }

            $this->abort(404, "Route $url not found");
        }
    }

    public function abort($code = 404, $message = "Not Found") {
        http_response_code($code);
        echo "<h1>$code - $message</h1>";
        exit;
    }
}
