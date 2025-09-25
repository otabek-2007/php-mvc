<?php
namespace App\Core;

class Router {
    private array $routes = [];

    
    public function get(string $path, $action) { $this->addRoute('GET', $path, $action); }
    public function post(string $path, $action) { $this->addRoute('POST', $path, $action); }
    public function any(string $path, $action) { $this->addRoute('ANY', $path, $action); }

    private function addRoute(string $method, string $path, $action) {
        $path = '/' . trim($path, '/'); 
        if ($path === '//') $path = '/';
        $this->routes[$method][$path] = $action;
    }

    public function run() {
        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $requestPath = '/' . trim($requestPath, '/');
        if ($requestPath === '//') $requestPath = '/';

        $action = $this->routes[$httpMethod][$requestPath]
               ?? $this->routes['ANY'][$requestPath]
               ?? null;

        if (!$action) {
            foreach ($this->routes as $method => $routes) {
                if ($method !== $httpMethod && $method !== 'ANY') continue;
                foreach ($routes as $routePattern => $routeAction) {
                    $regex = $this->convertRouteToRegex($routePattern, $matches);
                    if (preg_match($regex, $requestPath, $paramValues)) {
                        array_shift($paramValues); 
                        $params = $paramValues;
                        $action = $routeAction;
                        break 2;
                    }
                }
            }
        } else {
            $params = [];
        }

        if (!$action) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        $this->dispatch($action, $params ?? []);
    }
    private function convertRouteToRegex(string $routePattern, &$matches): string {
        $regex = preg_replace('#\{[^/]+\}#', '([^/]+)', $routePattern);
        return '#^' . $regex . '$#';
    }

    private function dispatch($action, array $params) {
        if (is_callable($action)) {
            call_user_func_array($action, $params);
            return;
        }

        if (is_string($action) && strpos($action, '@') !== false) {
            [$controllerName, $controllerMethod] = explode('@', $action);
            $fullController = "App\\Controllers\\HomeController";

            if (!class_exists($fullController)) {
                http_response_code(500);
                echo "Controller $fullController not found";
                return;
            }

            $controller = new $fullController();

            if (!method_exists($controller, $controllerMethod)) {
                http_response_code(500);
                echo "Method $controllerMethod not found in $fullController";
                return;
            }

            call_user_func_array([$controller, $controllerMethod], $params);
            return;
        }

        http_response_code(500);
        echo "Invalid route action";
    }
}
