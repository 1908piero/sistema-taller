<?php
namespace App;

class Router {
    // Ahora separamos las rutas por método HTTP
    private $routes = [
        'GET' => [],
        'POST' => []
    ];

    public function get($uri, $callback) {
        $this->routes['GET'][$uri] = $callback;
    }

    public function post($uri, $callback) {
        $this->routes['POST'][$uri] = $callback;
    }

    public function run() {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD']; // Detectamos si es GET o POST

        // Limpieza de parámetros GET (todo lo que va después del ?)
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        // Limpieza de la carpeta raíz (para que funcione en localhost y .test)
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        $scriptName = str_replace('\\', '/', $scriptName);

        if ($scriptName !== '/' && strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        }

        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        // Buscamos la ruta específica para el método actual
        if (isset($this->routes[$method][$uri])) {
            $callback = $this->routes[$method][$uri];
            
            if (is_array($callback)) {
                $controller = new $callback[0]();
                $methodName = $callback[1];
                $controller->$methodName();
            }
        } else {
            http_response_code(404);
            echo "<h1>Error 404</h1>";
            echo "<p>Página no encontrada.</p>";
        }
    }
}