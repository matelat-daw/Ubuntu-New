<?php
/**
 * Get User Profile Controller
 * Endpoint: GET /api/controllers/user/profile.php
 * Requires authentication
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Response.php';

// Only allow GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

try {
    // Authenticate user
    $authUser = AuthMiddleware::authenticate();
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Get full user data
    $user = new User($db);
    
    if (!$user->findById($authUser['user_id'])) {
        Response::error('Usuario no encontrado', 404);
    }
    
    // Return user profile
    Response::success([
        'user' => $user->toArray()
    ], 'Perfil de usuario');
    
} catch (Exception $e) {
    error_log("Error in profile: " . $e->getMessage());
    Response::serverError('Error al obtener el perfil');
}
