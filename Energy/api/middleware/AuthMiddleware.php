<?php
// Middleware de Autenticación
class AuthMiddleware {
    /**
     * Verificar autenticación del usuario
     */
    public function handle() {
        // Obtener token de cookie o header Authorization
        $token = null;
        
        if (isset($_COOKIE['auth_token'])) {
            $token = $_COOKIE['auth_token'];
        } else {
            $headers = function_exists('getallheaders') ? getallheaders() : [];
            if (isset($headers['Authorization']) && preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $m)) {
                $token = $m[1];
            }
        }

        require_once __DIR__ . '/../config/jwt.php';
        
        $decoded = JWT::decode($token);
        
        if (!$decoded || !isset($decoded['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'No autorizado. Token inválido o ausente.'
            ]);
            exit;
        }

        // Inyectar datos del usuario autenticado en el request
        $_REQUEST['auth_user_id'] = $decoded['user_id'];
        $_REQUEST['auth_user_email'] = $decoded['email'];
        $_REQUEST['auth_user_roles'] = $decoded['roles'];
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public static function hasRole($role) {
        if (!isset($_REQUEST['auth_user_roles'])) {
            return false;
        }

        return in_array($role, $_REQUEST['auth_user_roles']);
    }

    /**
     * Verificar si el usuario es admin
     */
    public static function isAdmin() {
        return self::hasRole('admin');
    }

    /**
     * Verificar si el usuario es vendedor
     */
    public static function isSeller() {
        return self::hasRole('seller');
    }
}
?>
