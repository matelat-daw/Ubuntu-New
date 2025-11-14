<?php
/**
 * Email Verification Controller
 * Endpoint: GET /api/auth/verify-email.php?token=xxx
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/AuditLogger.php';

// Only allow GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

try {
    // Get token from query string
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        Response::error('Token de verificación requerido', 400);
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize User model
    $user = new User($db);
    
    // Find user by verification token
    if (!$user->findByVerificationToken($token)) {
        Response::error('Token de verificación inválido o expirado', 400);
    }
    
    // Check if already verified
    if ($user->email_verified == 1) {
        Response::success([
            'email' => $user->email,
            'message' => 'Tu email ya ha sido verificado anteriormente'
        ], 'Email ya verificado');
    }
    
    // Check if token is expired (24 hours)
    if ($user->isVerificationExpired()) {
        Response::error('El token de verificación ha expirado. Por favor solicita un nuevo email de verificación', 400);
    }
    
    // Verify email and activate account
    if (!$user->verifyEmail()) {
        Response::serverError('Error al verificar el email');
    }
    
    // Log audit
    AuditLogger::log($db, $user->id, 'email_verified', 'user', $user->id, "Email verificado: " . $user->email);
    
    // Return success response
    Response::success([
        'email' => $user->email,
        'name' => $user->getFullName(),
        'verified' => true,
        'message' => '¡Tu cuenta ha sido verificada exitosamente! Ya puedes iniciar sesión'
    ], 'Email verificado exitosamente');
    
} catch (PDOException $e) {
    error_log("Database error in verify-email: " . $e->getMessage());
    Response::serverError('Error en el servidor');
} catch (Exception $e) {
    error_log("Error in verify-email: " . $e->getMessage());
    Response::serverError($e->getMessage());
}
