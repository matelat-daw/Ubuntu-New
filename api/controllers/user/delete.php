<?php
/**
 * Delete User Profile Controller
 * Endpoint: DELETE /api/controllers/user/delete.php
 * Requires authentication
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/AuditLogger.php';

// Only allow DELETE and POST methods
if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'])) {
    Response::error('Método no permitido', 405);
}

try {
    // Authenticate user
    $authUser = AuthMiddleware::authenticate();
    
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check if it's a soft delete (deactivate) or hard delete (permanent)
    $hardDelete = isset($input['permanent']) && $input['permanent'] === true;
    
    // Optional: require password confirmation
    $password = $input['password'] ?? null;
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Load user
    $user = new User($db);
    if (!$user->findById($authUser['user_id'])) {
        Response::error('Usuario no encontrado', 404);
    }
    
    // Verify password if provided
    if ($password) {
        if (!$user->verifyPassword($password)) {
            Response::error('Contraseña incorrecta', 401);
        }
    }
    
    $userId = $user->id;
    $userEmail = $user->email;
    
    if ($hardDelete) {
        // HARD DELETE - Permanent deletion
        
        // Delete related data first (foreign key constraints)
        
        // 1. Delete sessions
        $db->prepare("DELETE FROM sessions WHERE user_id = :user_id")->execute([':user_id' => $userId]);
        
        // 2. Delete cart and cart items
        $db->prepare("DELETE ci FROM cart_items ci 
                      INNER JOIN carts c ON ci.cart_id = c.id 
                      WHERE c.user_id = :user_id")->execute([':user_id' => $userId]);
        $db->prepare("DELETE FROM carts WHERE user_id = :user_id")->execute([':user_id' => $userId]);
        
        // 3. Delete reviews
        $db->prepare("DELETE FROM reviews WHERE user_id = :user_id")->execute([':user_id' => $userId]);
        
        // 4. Delete addresses
        $db->prepare("DELETE FROM addresses WHERE user_id = :user_id")->execute([':user_id' => $userId]);
        
        // 5. Update orders (keep orders but anonymize user)
        $db->prepare("UPDATE orders SET user_id = NULL WHERE user_id = :user_id")->execute([':user_id' => $userId]);
        
        // 6. Delete profile image directory
        $userDir = PROFILE_UPLOAD_PATH . $userId;
        if (is_dir($userDir)) {
            deleteDirectory($userDir);
        }
        
        // 7. Delete user from database
        if (!$user->hardDelete()) {
            Response::serverError('Error al eliminar el usuario');
        }
        
        // Revoke all sessions (logout)
        AuthMiddleware::revokeAllUserSessions($userId);
        
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
        
        Response::success([
            'message' => 'Tu cuenta ha sido eliminada permanentemente'
        ], 'Cuenta eliminada');
        
    } else {
        // SOFT DELETE - Deactivate account (uses model method)
        
        if (!$user->delete()) {
            Response::serverError('Error al desactivar la cuenta');
        }
        
        // Log audit
        AuditLogger::log($db, $userId, 'deactivate_account', 'user', $userId, "Cuenta desactivada: " . $userEmail);
        
        // Revoke all sessions (logout)
        AuthMiddleware::revokeAllUserSessions($userId);
        
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
        
        Response::success([
            'message' => 'Tu cuenta ha sido desactivada. Puedes contactar al administrador para reactivarla'
        ], 'Cuenta desactivada');
    }
    
} catch (PDOException $e) {
    error_log("Database error in delete profile: " . $e->getMessage());
    Response::serverError('Error en el servidor de base de datos');
} catch (Exception $e) {
    error_log("Error in delete profile: " . $e->getMessage());
    Response::serverError('Error en el servidor');
}

/**
 * Recursively delete a directory
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    
    rmdir($dir);
}
