<?php
/**
 * User Login Controller
 * Endpoint: POST /api/controllers/auth/login.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/Validator.php';
require_once __DIR__ . '/../../classes/AuditLogger.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método no permitido', 405);
}

try {
    // Check if request has JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido');
        }
    } else {
        $input = $_POST;
    }
    
    // Validate required fields
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    $validator = new Validator();
    $validator->validateEmail($email, 'email');
    $validator->validateRequired($password, 'password');
    
    if ($validator->hasErrors()) {
        Response::error('Errores de validación', 400, $validator->getErrors());
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize User model
    $user = new User($db);
    
    // Find user by email
    if (!$user->findByEmail($email)) {
        Response::error('Credenciales inválidas', 401);
    }
    
    // Verify password
    if (!$user->verifyPassword($password)) {
        Response::error('Credenciales inválidas', 401);
    }
    
    // Check if email is verified
    if ($user->email_verified != 1) {
        Response::error('Debes verificar tu email antes de iniciar sesión. Revisa tu bandeja de entrada', 403);
    }
    
    // Check if account is active
    if ($user->active != 1) {
        Response::error('Tu cuenta ha sido desactivada. Contacta al administrador', 403);
    }
    
    // Update last login
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user->updateLastLogin($ip);
    
    // Generate JWT token
    $issuedAt = time();
    $expirationTime = $issuedAt + JWT_EXPIRATION;
    
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'iss' => $_SERVER['HTTP_HOST'] ?? 'localhost',
        'data' => [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'name' => $user->getFullName()
        ]
    ];
    
    $jwt = JWT::encode($payload, JWT_SECRET, JWT_ALGORITHM);
    
    // Create hash of the token for storage
    $tokenHash = hash('sha256', $jwt);
    
    // Create session in database
    $sessionQuery = "INSERT INTO sessions (user_id, token_hash, ip_address, user_agent, expires_at) 
                     VALUES (:user_id, :token_hash, :ip, :user_agent, FROM_UNIXTIME(:expires_at))";
    $sessionStmt = $db->prepare($sessionQuery);
    $sessionStmt->bindParam(':user_id', $user->id);
    $sessionStmt->bindParam(':token_hash', $tokenHash);
    $sessionStmt->bindParam(':ip', $ip);
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $sessionStmt->bindParam(':user_agent', $userAgent);
    $sessionStmt->bindParam(':expires_at', $expirationTime);
    $sessionStmt->execute();
    
    $sessionId = $db->lastInsertId();
    
    // Set JWT in HTTP-only cookie
    setcookie(
        'auth_token',           // name
        $jwt,                   // value
        [
            'expires' => $expirationTime,
            'path' => '/',
            'domain' => '',
            'secure' => false,  // Set to true in production with HTTPS
            'httponly' => true, // JavaScript cannot access
            'samesite' => 'Lax' // CSRF protection
        ]
    );
    
    // Log audit
    AuditLogger::log($db, $user->id, 'login', 'user', $user->id, "Inicio de sesión exitoso");
    
    // Return success response with user data and token
    Response::success([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'surname1' => $user->surname1,
            'surname2' => $user->surname2,
            'email' => $user->email,
            'phone' => $user->phone,
            'profile_image' => $user->path,
            'role' => $user->role,
            'last_login' => $user->last_login
        ],
        'token' => $jwt,
        'session_id' => $sessionId,
        'expires_at' => date('Y-m-d H:i:s', $expirationTime),
        'message' => 'Cookie de autenticación establecida'
    ], 'Inicio de sesión exitoso');
    
} catch (PDOException $e) {
    error_log("Database error in login: " . $e->getMessage());
    Response::serverError('Error en el servidor de base de datos');
} catch (Exception $e) {
    error_log("Error in login: " . $e->getMessage());
    Response::serverError('Error en el servidor');
}
