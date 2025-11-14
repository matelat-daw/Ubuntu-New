<?php
/**
 * Upgrade to Premium Controller
 * Endpoint: POST /api/controllers/user/upgrade-premium.php
 * Requires authentication
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/AuditLogger.php';
require_once __DIR__ . '/../../classes/RoleManager.php';

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método no permitido', 405);
}

try {
    // Authenticate user
    $authUser = AuthMiddleware::authenticate();
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Load user
    $user = new User($db);
    if (!$user->findById($authUser['user_id'])) {
        Response::error('Usuario no encontrado', 404);
    }
    
    // Check if user is already premium
    if ($user->isPremium()) {
        Response::error('Ya tienes una cuenta Premium', 400);
    }
    
    // Check if user is admin (admins can't be upgraded)
    if ($user->isAdmin()) {
        Response::error('Los administradores no pueden ser actualizados a Premium', 400);
    }
    
    // Validate upgrade requirements
    $canUpgrade = true;
    $upgradeRequirements = [];
    
    // Requirement 1: Email must be verified
    if (!$user->email_verified) {
        $canUpgrade = false;
        $upgradeRequirements[] = 'Debes verificar tu correo electrónico';
    }
    
    // Requirement 2: Account must be at least 7 days old
    $accountAge = time() - strtotime($user->creation_date);
    $minAge = 7 * 24 * 60 * 60; // 7 days in seconds
    if ($accountAge < $minAge) {
        $canUpgrade = false;
        $daysRemaining = ceil(($minAge - $accountAge) / (24 * 60 * 60));
        $upgradeRequirements[] = "Tu cuenta debe tener al menos 7 días (faltan {$daysRemaining} días)";
    }
    
    // Requirement 3: If seller, must have at least one product
    if ($user->isSeller()) {
        $productQuery = "SELECT COUNT(*) as product_count FROM products WHERE seller_id = ? AND active = 1";
        $stmt = $db->prepare($productQuery);
        $stmt->bind_param('i', $user->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row['product_count'] < 1) {
            $canUpgrade = false;
            $upgradeRequirements[] = 'Debes tener al menos 1 producto publicado';
        }
    }
    
    // Check if requirements are met
    if (!$canUpgrade) {
        Response::error('No cumples los requisitos para Premium', 400, [
            'requirements' => $upgradeRequirements
        ]);
    }
    
    $oldRole = $user->role;
    
    // Upgrade to premium
    if (!$user->upgradeToPremium()) {
        Response::serverError('Error al actualizar a Premium');
    }
    
    // Log audit
    AuditLogger::log(
        $db, 
        $user->id, 
        'upgrade_premium', 
        'user', 
        $user->id, 
        "Actualización a Premium: {$oldRole} → {$user->role}"
    );
    
    // Return success response
    Response::success([
        'user' => $user->toArray(),
        'old_role' => RoleManager::getRoleDisplayName($oldRole),
        'new_role' => RoleManager::getRoleDisplayName($user->role),
        'message' => '¡Felicidades! Tu cuenta ha sido actualizada a Premium'
    ], 'Actualizado a Premium exitosamente');
    
} catch (Exception $e) {
    error_log("Error in upgrade-premium: " . $e->getMessage());
    Response::serverError('Error al actualizar a Premium');
}
