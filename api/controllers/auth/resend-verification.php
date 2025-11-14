<?php
/**
 * Resend Verification Email Controller
 * Endpoint: POST /api/auth/resend-verification.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/Validator.php';
require_once __DIR__ . '/../../classes/EmailHelper.php';
require_once __DIR__ . '/../../classes/AuditLogger.php';

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método no permitido', 405);
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        Response::error('JSON inválido');
    }
    
    // Validate email
    $email = trim($input['email'] ?? '');
    $validator = new Validator();
    $validator->validateEmail($email, 'email');
    
    if ($validator->hasErrors()) {
        Response::error('Email inválido', 400, $validator->getErrors());
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize User model
    $user = new User($db);
    
    // Find user by email
    if (!$user->findByEmail($email)) {
        // Don't reveal if email exists or not (security)
        Response::success([
            'message' => 'Si el email existe, recibirás un nuevo correo de verificación'
        ], 'Solicitud procesada');
    }
    
    // Check if already verified
    if ($user->email_verified == 1) {
        Response::error('Este email ya ha sido verificado', 400);
    }
    
    // Generate and set new verification token
    $verificationToken = User::generateToken();
    if (!$user->setVerificationToken($verificationToken)) {
        Response::serverError('Error al generar nuevo token');
    }
    
    // Send verification email
    $emailSent = EmailHelper::sendVerificationEmail(
        $user->email, 
        $user->getFullName(), 
        $verificationToken
    );
    
    if (!$emailSent) {
        error_log("Failed to resend verification email to: " . $user->email);
        Response::serverError('Error al enviar el email');
    }
    
    // Log audit
    AuditLogger::log($db, $user->id, 'resend_verification', 'user', $user->id, "Reenvío de email de verificación");
    
    // Return success response
    Response::success([
        'email' => $user->email,
        'message' => 'Se ha enviado un nuevo email de verificación. Por favor revisa tu bandeja de entrada'
    ], 'Email de verificación enviado');
    
} catch (PDOException $e) {
    error_log("Database error in resend-verification: " . $e->getMessage());
    Response::serverError('Error en el servidor');
} catch (Exception $e) {
    error_log("Error in resend-verification: " . $e->getMessage());
    Response::serverError($e->getMessage());
}
