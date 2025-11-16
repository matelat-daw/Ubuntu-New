<?php
/**
 * User Registration Controller
 * Endpoint: POST /api/auth/register.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/Validator.php';
require_once __DIR__ . '/../../classes/EmailHelper.php';
require_once __DIR__ . '/../../classes/AuditLogger.php';
require_once __DIR__ . '/../../classes/ImageUploader.php';
require_once __DIR__ . '/../../classes/RoleManager.php';

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método no permitido', 405);
}

try {
    // Check if request has files (FormData) or JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'multipart/form-data') !== false) {
        // FormData from HTML form
        $input = $_POST;
    } else {
        // JSON API request
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido');
        }
    }
    
    // Initialize validator
    $validator = new Validator();
    
    // Extract and validate required fields
    $name = trim($input['name'] ?? '');
    $surname1 = trim($input['surname1'] ?? '');
    $surname2 = trim($input['surname2'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $phone = trim($input['phone'] ?? '');
    $role = trim($input['role'] ?? RoleManager::BUYER_BASIC); // Default to buyer_basic
    
    // Validate required fields
    $validator->validateRequired($name, 'name');
    $validator->validateRequired($surname1, 'surname1');
    $validator->validateEmail($email, 'email');
    $validator->validatePassword($password, 'password');
    
    // Validate role - only allow seller_basic or buyer_basic for registration
    if (!RoleManager::isRegistrationRole($role)) {
        $validator->addError('role', 'Rol inválido. Solo puedes registrarte como vendedor o comprador');
    }
    
    // Validate optional phone
    if (!empty($phone)) {
        $validator->validatePhone($phone, 'phone');
    }
    
    // Check for validation errors
    if ($validator->hasErrors()) {
        Response::error('Errores de validación', 400, $validator->getErrors());
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize User model
    $user = new User($db);
    
    // Check if email already exists
    if ($user->emailExists($email)) {
        Response::error('El email ya está registrado', 409);
    }
    
    // Handle profile image upload if provided
    $profileImagePath = DEFAULT_PROFILE_IMAGE;
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = ImageUploader::uploadProfileImage($_FILES['profile_image']);
        if ($uploadResult['success']) {
            $profileImagePath = $uploadResult['path'];
        }
    }
    
    // Set user properties
    $user->name = $name;
    $user->surname1 = $surname1;
    $user->surname2 = $surname2;
    $user->phone = $phone;
    $user->email = $email;
    $user->password = User::hashPassword($password);
    $user->email_hash = User::generateEmailHash($email);
    $user->path = $profileImagePath;
    $user->verification_token = User::generateToken();
    $user->active = 0;
    $user->email_verified = 0;
    $user->role = $role;
    $user->last_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Create user
    if (!$user->create()) {
        Response::serverError('Error al crear el usuario');
    }
    
    // If profile image was uploaded, move it to user's directory
    if ($profileImagePath !== DEFAULT_PROFILE_IMAGE && isset($uploadResult['temp_path'])) {
        $userDir = PROFILE_UPLOAD_PATH . $user->id;
        if (!is_dir($userDir)) {
            mkdir($userDir, 0755, true);
        }
        
        // Move temp WebP file to user directory
        $finalPath = $userDir . '/profile.webp';
        rename($uploadResult['temp_path'], $finalPath);
        
        // Update user with final path
        $user->path = 'assets/profiles/' . $user->id . '/profile.webp';
        $user->update();
        
        $profileImagePath = $user->path;
    }
    
    // Send verification email
    $emailSent = EmailHelper::sendVerificationEmail(
        $user->email, 
        $user->getFullName(), 
        $user->verification_token
    );
    
    if (!$emailSent) {
        error_log("Failed to send verification email to: " . $user->email);
    }
    
    // Log audit
    AuditLogger::log($db, $user->id, 'register', 'user', $user->id, "Usuario registrado: " . $user->email);
    
    // Return success response
    Response::success([
        'user_id' => $user->id,
        'email' => $user->email,
        'name' => $user->getFullName(),
        'profile_image' => $profileImagePath,
        'verification_sent' => $emailSent,
        'message' => 'Por favor revisa tu email para verificar tu cuenta'
    ], 'Usuario registrado exitosamente', 201);
    
} catch (PDOException $e) {
    error_log("Database error in register: " . $e->getMessage());
    
    // Check for duplicate entry error
    if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
        Response::error('El email ya está registrado', 409);
    }
    
    Response::serverError('Error en el servidor de base de datos');
} catch (Exception $e) {
    error_log("Error in register: " . $e->getMessage());
    Response::serverError('Error en el servidor');
}
