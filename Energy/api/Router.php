<?php
// Router simple para manejar rutas de la API
class Router {
    private $routes = [];
    private $middlewareGroups = [];
    private $currentGroup = null;

    /**
     * Agregar una ruta
     */
    public function add($method, $pattern, $callback) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'callback' => $callback,
            'middleware' => $this->currentGroup ? $this->middlewareGroups[$this->currentGroup] : null
        ];
    }

    /**
     * Ruta GET
     */
    public function get($pattern, $callback) {
        $this->add('GET', $pattern, $callback);
    }

    /**
     * Ruta POST
     */
    public function post($pattern, $callback) {
        $this->add('POST', $pattern, $callback);
    }

    /**
     * Ruta PUT
     */
    public function put($pattern, $callback) {
        $this->add('PUT', $pattern, $callback);
    }

    /**
     * Ruta DELETE
     */
    public function delete($pattern, $callback) {
        $this->add('DELETE', $pattern, $callback);
    }

    /**
     * Agrupar rutas con middleware
     */
    public function group($options, $callback) {
        if (isset($options['middleware'])) {
            $groupId = uniqid('group_', true);
            $this->middlewareGroups[$groupId] = $options['middleware'];
            $this->currentGroup = $groupId;
            $callback();
            $this->currentGroup = null;
        } else {
            $callback();
        }
    }

    /**
     * Ejecutar el router
     */
    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            // Convertir patrón de ruta a regex
            $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $route['pattern']);
            $pattern = '#^' . $pattern . '$#';

            if ($method === $route['method'] && preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Quitar la coincidencia completa

                // Ejecutar middleware si existe
                if ($route['middleware']) {
                    $middleware = $route['middleware'];
                    if (is_string($middleware) && class_exists($middleware)) {
                        $instance = new $middleware();
                        if (method_exists($instance, 'handle')) {
                            $instance->handle();
                        }
                    }
                }

                $callback = $route['callback'];

                // Leer datos para POST y PUT
                if (in_array($method, ['POST', 'PUT'])) {
                    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                    
                    if (stripos($contentType, 'multipart/form-data') !== false) {
                        // Formulario con archivos
                        $input = $_POST;
                        if (!empty($_FILES)) {
                            $input['_files'] = $_FILES;
                        }
                    } else {
                        // JSON
                        $input = json_decode(file_get_contents('php://input'), true);
                    }
                    
                    array_unshift($matches, $input);
                }

                // Ejecutar callback
                if (is_array($callback) && count($callback) === 2) {
                    $controller = new $callback[0]();
                    $methodName = $callback[1];
                    call_user_func_array([$controller, $methodName], $matches);
                } elseif (is_callable($callback)) {
                    call_user_func_array($callback, $matches);
                }
                
                return;
            }
        }

        // No encontrado
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ruta no encontrada']);
    }
}
?>
