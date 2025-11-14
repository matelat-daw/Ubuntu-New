<?php
/**
 * Update User Profile Controller
 * Endpoint: PUT /api/controllers/user/update.php
 * Requires authentication
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/Validator.php';
require_once __DIR__ . '/../../classes/AuditLogger.php';
require_once __DIR__ . '/../../classes/ImageUploader.php';

// Only allow PUT and POST methods
if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {
    Response::error('Método no permitido', 405);
}

try {
    // Authenticate user
    $authUser = AuthMiddleware::authenticate();
    
    // Get input data
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'multipart/form-data') !== false) {
        // FormData with possible file upload
        $input = $_POST;
    } else if (strpos($contentType, 'application/json') !== false) {
        // JSON
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido');
        }
    } else {
        // Regular POST
        $input = $_POST;
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Load current user
    $user = new User($db);
    if (!$user->findById($authUser['user_id'])) {
        Response::error('Usuario no encontrado', 404);
    }
    
    // Initialize validator
    $validator = new Validator();
    
    // Get and validate fields
    $name = isset($input['name']) ? trim($input['name']) : $user->name;
    $surname1 = isset($input['surname1']) ? trim($input['surname1']) : $user->surname1;
    $surname2 = isset($input['surname2']) ? trim($input['surname2']) : $user->surname2;
    $phone = isset($input['phone']) ? trim($input['phone']) : $user->phone;
    $email = isset($input['email']) ? trim($input['email']) : $user->email;
    
    // Validate required fields
    $validator->validateRequired($name, 'name');
    $validator->validateRequired($surname1, 'surname1');
    
    // Validate email if changed
    if ($email !== $user->email) {
        $validator->validateEmail($email, 'email');
        
        // Check if new email already exists
        if ($user->emailExists($email)) {
            $validator->addError('email', 'El email ya está registrado');
        }
    }
    
    // Validate phone if provided
    if (!empty($phone)) {
        $validator->validatePhone($phone, 'phone');
    }
    
    // Check for validation errors
    if ($validator->hasErrors()) {
        Response::error('Errores de validación', 400, $validator->getErrors());
    }
    
    // Handle profile image upload if provided
    $profileImagePath = $user->path;
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = ImageUploader::uploadProfileImage($_FILES['profile_image'], $user->id);
        if ($uploadResult['success']) {
            $profileImagePath = $uploadResult['path'];
        } else {
            Response::error($uploadResult['error'], 400);
        }
    }
    
    // Update user properties
    $user->name = $name;
    $user->surname1 = $surname1;
    $user->surname2 = $surname2;
    $user->phone = $phone;
    $user->email = $email;
    $user->email_hash = User::generateEmailHash($email);
    $user->path = $profileImagePath;
    
    // Update user
    if (!$user->update()) {
        Response::serverError('Error al actualizar el usuario');
    }
    
    // Log audit
    AuditLogger::log($db, $user->id, 'update_profile', 'user', $user->id, "Perfil actualizado");
    
    // Return success response
    Response::success([
        'user' => $user->toArray()
    ], 'Perfil actualizado exitosamente');
    
} catch (PDOException $e) {
    error_log("Database error in update profile: " . $e->getMessage());
    Response::serverError('Error en el servidor de base de datos');
} catch (Exception $e) {
    error_log("Error in update profile: " . $e->getMessage());
    Response::serverError('Error en el servidor');
}
