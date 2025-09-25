<?php
namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, string $action) {
        $this->routes['GET'][$path] = $action;
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $action = $this->routes[$method][$path] ?? null;

        if (!$action) { http_response_code(404); echo "404"; return; }

        [$controller, $method] = explode('@', $action);
        $controller = "App\\Controllers\\$controller";
        (new $controller)->$method();
    }
}
