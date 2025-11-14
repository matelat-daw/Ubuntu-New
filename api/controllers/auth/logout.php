<?php
/**
 * User Logout Controller
 * Endpoint: POST /api/controllers/auth/logout.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/AuditLogger.php';

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método no permitido', 405);
}

try {
    // Authenticate user
    $user = AuthMiddleware::authenticate();
    
    // Get token from cookie or header
    $token = $_COOKIE['auth_token'] ?? null;
    
    if (!$token) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                $token = $matches[1];
            }
        }
    }
    
    if ($token) {
        // Revoke session
        AuthMiddleware::revokeSession($token);
    }
    
    // Delete cookie
    setcookie(
        'auth_token',
        '',
        [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
    
    // Log audit
    $database = new Database();
    $db = $database->getConnection();
    AuditLogger::log($db, $user['user_id'], 'logout', 'user', $user['user_id'], "Cierre de sesión");
    
    Response::success([
        'message' => 'Sesión cerrada correctamente'
    ], 'Logout exitoso');
    
} catch (Exception $e) {
    error_log("Error in logout: " . $e->getMessage());
    Response::serverError('Error al cerrar sesión');
}
