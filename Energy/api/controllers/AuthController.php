<?php
// Controlador de Autenticación
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../models/Provider.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/../models/Contract.php';
require_once __DIR__ . '/../services/EmailService.php';

class AuthController {
    private $db;
    private $user;
    private $role;

    public function __construct() {
        global $conn;
        $this->db = $conn;
        $this->user = new User($this->db);
        $this->role = new Role($this->db);
    }

    /**
     * Registrar nuevo usuario
     */
    public function register($data) {
        // Validar datos requeridos
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return $this->sendResponse(400, false, "Username, email y contraseña son requeridos");
        }

        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->sendResponse(400, false, "Email inválido");
        }

        // Validar longitud de contraseña
        if (strlen($data['password']) < 6) {
            return $this->sendResponse(400, false, "La contraseña debe tener al menos 6 caracteres");
        }

        // Verificar si el email ya existe
        $this->user->email = $data['email'];
        if ($this->user->emailExists()) {
            return $this->sendResponse(409, false, "El email ya está registrado");
        }

        // Verificar si el username ya existe
        $this->user->username = $data['username'];
        if ($this->user->usernameExists()) {
            return $this->sendResponse(409, false, "El username ya está en uso");
        }

        // Determinar el rol del usuario (por defecto 'user')
        $role_name = isset($data['role']) ? $data['role'] : 'user';
        
        // Validar que el rol sea válido
        if (!in_array($role_name, ['user', 'seller'])) {
            $role_name = 'user';
        }

        // Asignar datos del usuario
        $this->user->username = $data['username'];
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        $this->user->first_name = isset($data['first_name']) ? $data['first_name'] : null;
        $this->user->last_name = isset($data['last_name']) ? $data['last_name'] : null;
        $this->user->second_last_name = isset($data['second_last_name']) && !empty($data['second_last_name']) ? $data['second_last_name'] : null;
        $this->user->phone = isset($data['phone']) ? $data['phone'] : null;
        $this->user->profile_img = null;
        
        // Configurar activación por email
        $this->user->is_active = 0; // No activo hasta confirmar email
        $this->user->activation_token = EmailService::generateActivationToken();
        // Token válido por 24 horas
        $this->user->activation_token_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        if ($this->user->register()) {
            $userId = $this->user->id;
            
            // Asignar rol al usuario
            if ($this->role->getRoleByName($role_name)) {
                $this->role->assignRoleToUser($userId, $this->role->id);
            }
            
            // Obtener roles del usuario
            $rolesStmt = $this->user->getRoles();
            $roles = [];
            while ($roleRow = $rolesStmt->fetch(PDO::FETCH_ASSOC)) {
                $roles[] = $roleRow['name'];
            }
            
            // Crear contrato si se proporcionó información de proveedor
            if (!empty($data['contract_number']) || !empty($data['current_company'])) {
                $this->createUserContract($userId, $data);
            }
            
            // Enviar email de activación
            $emailService = new EmailService();
            $emailSent = $emailService->sendActivationEmail(
                $this->user->email,
                $this->user->username,
                $this->user->activation_token
            );
            
            if (!$emailSent) {
                error_log("❌ Error al enviar email de activación a: " . $this->user->email);
            }
            
            // NO generar JWT ni loguear al usuario automáticamente
            // El usuario debe activar su cuenta primero
            
            $fullName = trim($this->user->first_name . ' ' . $this->user->last_name . ($this->user->second_last_name ? ' ' . $this->user->second_last_name : ''));
            
            $userData = [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'name' => $fullName,
                'requiresActivation' => true,
                'roles' => $roles
            ];
            
            return $this->sendResponse(201, true, "Usuario registrado. Por favor revisa tu email para activar tu cuenta.", $userData);
        }
        
        return $this->sendResponse(500, false, "Error al registrar usuario");
    }

    /**
     * Crear contrato para un usuario registrado
     */
    private function createUserContract($userId, $data) {
        try {
            // Determinar el proveedor
            $providerName = 'Naturgy'; // Proveedor por defecto
            
            // Si se especificó un proveedor, usarlo
            if (!empty($data['provider'])) {
                $providerName = $data['provider'];
            }
            
            // Buscar o crear proveedor
            $provider = new Provider($this->db);
            $providerId = $provider->findOrCreate($providerName);
            
            if (!$providerId) {
                error_log("❌ Error al crear/buscar proveedor para el usuario {$userId}");
                return false;
            }
            
            // Obtener o crear plan genérico para este proveedor
            $plan = new Plan($this->db);
            $sellerId = isset($data['seller_id']) ? $data['seller_id'] : null;
            $planId = $plan->getOrCreateDefaultPlan($providerId, $sellerId);
            
            if (!$planId) {
                error_log("❌ Error al crear/buscar plan para el usuario {$userId}");
                return false;
            }
            
            // Crear el contrato
            $contract = new Contract($this->db);
            $contract->client_id = $userId;
            $contract->seller_id = $sellerId;
            $contract->plan_id = $planId;
            $contract->start_date = date('Y-m-d');
            $contract->end_date = null;
            $contract->status = 'active';
            $contract->total_amount = 0.00;
            $contract->commission_amount = 0.00;
            
            // Construir notas con la información del contrato anterior
            $notes = [];
            if (!empty($data['contract_number'])) {
                $notes[] = "Número de contrato anterior: " . $data['contract_number'];
            }
            if (!empty($data['current_company'])) {
                $notes[] = "Compañía anterior: " . $data['current_company'];
            }
            $contract->notes = !empty($notes) ? implode("\n", $notes) : null;
            
            if ($contract->create()) {
                error_log("✅ Contrato creado exitosamente para usuario {$userId} con ID {$contract->id}");
                return true;
            } else {
                error_log("❌ Error al crear contrato para usuario {$userId}");
                return false;
            }
        } catch (Exception $e) {
            error_log("❌ Excepción al crear contrato: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Login de usuario
     */
    public function login($data) {
        // Validar datos requeridos
        if (empty($data['email']) || empty($data['password'])) {
            return $this->sendResponse(400, false, "Email y contraseña son requeridos");
        }

        // Verificar si el usuario existe
        $this->user->email = $data['email'];
        if (!$this->user->emailExists()) {
            return $this->sendResponse(401, false, "Credenciales incorrectas");
        }

        // Verificar contraseña primero (antes de revisar activación)
        if (!password_verify($data['password'], $this->user->password)) {
            return $this->sendResponse(401, false, "Credenciales incorrectas");
        }

        // Ahora verificar si la cuenta está activa
        if ($this->user->is_active == 0) {
            return $this->sendResponse(403, false, "Debes activar tu cuenta antes de iniciar sesión. Revisa tu correo electrónico.");
        }

        // Obtener roles del usuario
        $rolesStmt = $this->user->getRoles();
        $roles = [];
        while ($roleRow = $rolesStmt->fetch(PDO::FETCH_ASSOC)) {
            $roles[] = $roleRow['name'];
        }

        // Generar JWT
        $payload = [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'username' => $this->user->username,
            'roles' => $roles,
            'exp' => time() + (7 * 24 * 60 * 60) // 7 días
        ];

        $jwt = JWT::encode($payload);

        // Enviar token en cookie HTTP-only segura
        $this->setAuthCookie($jwt);

        $fullName = trim($this->user->first_name . ' ' . $this->user->last_name . ($this->user->second_last_name ? ' ' . $this->user->second_last_name : ''));
        
        $userData = [
            'id' => $this->user->id,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'name' => $fullName,
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'second_last_name' => $this->user->second_last_name,
            'phone' => $this->user->phone,
            'profile_img' => $this->user->profile_img,
            'roles' => $roles
        ];

        return $this->sendResponse(200, true, "Login exitoso", $userData);
    }

    /**
     * Validar token
     */
    public function validateToken() {
        $token = $this->getTokenFromRequest();
        
        if (!$token) {
            return $this->sendResponse(401, false, "Token no proporcionado");
        }

        $decoded = JWT::decode($token);
        
        if (!$decoded) {
            return $this->sendResponse(401, false, "Token inválido o expirado");
        }

        // Verificar que el usuario aún existe y está activo
        $this->user->id = $decoded['user_id'];
        if (!$this->user->readOne()) {
            return $this->sendResponse(401, false, "Usuario no encontrado");
        }

        if ($this->user->is_active == 0) {
            return $this->sendResponse(403, false, "Cuenta inactiva");
        }

        $fullName = trim($this->user->first_name . ' ' . $this->user->last_name . ($this->user->second_last_name ? ' ' . $this->user->second_last_name : ''));
        
        $userData = [
            'id' => $this->user->id,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'name' => $fullName,
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'second_last_name' => $this->user->second_last_name,
            'phone' => $this->user->phone,
            'profile_img' => $this->user->profile_img,
            'roles' => $decoded['roles']
        ];

        return $this->sendResponse(200, true, "Token válido", $userData);
    }

    /**
     * Logout
     */
    public function logout() {
        // Eliminar cookie
        setcookie('auth_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => false,
            'samesite' => 'Lax'
        ]);

        return $this->sendResponse(200, true, "Logout exitoso");
    }

    /**
     * Actualizar perfil
     */
    public function updateProfile($data) {
        $token = $this->getTokenFromRequest();
        
        if (!$token) {
            return $this->sendResponse(401, false, "No autorizado");
        }

        $decoded = JWT::decode($token);
        
        if (!$decoded) {
            return $this->sendResponse(401, false, "Token inválido");
        }

        $this->user->id = $decoded['user_id'];
        
        if (!$this->user->readOne()) {
            return $this->sendResponse(404, false, "Usuario no encontrado");
        }

        // Actualizar campos
        if (isset($data['name'])) {
            // Si viene 'name', dividirlo en first_name, last_name y second_last_name
            $nameParts = explode(' ', $data['name'], 3);
            $this->user->first_name = $nameParts[0];
            $this->user->last_name = isset($nameParts[1]) ? $nameParts[1] : '';
            $this->user->second_last_name = isset($nameParts[2]) ? $nameParts[2] : null;
        }
        if (isset($data['first_name'])) {
            $this->user->first_name = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $this->user->last_name = $data['last_name'];
        }
        if (isset($data['second_last_name'])) {
            $this->user->second_last_name = !empty($data['second_last_name']) ? $data['second_last_name'] : null;
        }
        if (isset($data['username'])) {
            $this->user->username = $data['username'];
        }
        if (isset($data['email'])) {
            $this->user->email = $data['email'];
        }
        if (isset($data['phone'])) {
            $this->user->phone = $data['phone'];
        }

        if ($this->user->update()) {
            // Obtener roles del usuario
            $rolesStmt = $this->user->getRoles();
            $roles = [];
            while ($roleRow = $rolesStmt->fetch(PDO::FETCH_ASSOC)) {
                $roles[] = $roleRow['name'];
            }

            $fullName = trim($this->user->first_name . ' ' . $this->user->last_name . ($this->user->second_last_name ? ' ' . $this->user->second_last_name : ''));
            
            $userData = [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'name' => $fullName,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'second_last_name' => $this->user->second_last_name,
                'phone' => $this->user->phone,
                'profile_img' => $this->user->profile_img,
                'roles' => $roles
            ];

            return $this->sendResponse(200, true, "Perfil actualizado exitosamente", $userData);
        }

        return $this->sendResponse(500, false, "Error al actualizar perfil");
    }

    /**
     * Obtener token del request
     */
    private function getTokenFromRequest() {
        // Intentar obtener de cookie primero
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }

        // Intentar obtener de header Authorization (fallback)
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Establecer cookie de autenticación
     */
    private function setAuthCookie($token) {
        $expires = time() + (7 * 24 * 60 * 60); // 7 días

        setcookie('auth_token', $token, [
            'expires'  => $expires,
            'path'     => '/',
            'secure'   => false, // Cambiar a true en producción con HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    /**
     * Enviar respuesta JSON
     */
    private function sendResponse($code, $success, $message, $data = null) {
        http_response_code($code);
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
    }

    /**
     * Activar cuenta de usuario
     */
    public function activateAccount($data) {
        if (empty($data['token'])) {
            return $this->sendResponse(400, false, "Token de activación requerido");
        }

        $result = $this->user->activateAccount($data['token']);

        if ($result['success']) {
            return $this->sendResponse(200, true, $result['message']);
        } else {
            return $this->sendResponse(400, false, $result['message']);
        }
    }
    
    /**
     * Crear vendedor (solo admin)
     */
    public function createSeller($data) {
        $token = $this->getTokenFromRequest();
        
        if (!$token) {
            return $this->sendResponse(401, false, "No autorizado");
        }

        $decoded = JWT::decode($token);
        
        if (!$decoded) {
            return $this->sendResponse(401, false, "Token inválido");
        }

        // Verificar que el usuario sea admin
        if (!isset($decoded['roles']) || !in_array('admin', $decoded['roles'])) {
            return $this->sendResponse(403, false, "No tienes permisos de administrador");
        }

        // Validar datos requeridos
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return $this->sendResponse(400, false, "Username, email y contraseña son requeridos");
        }

        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->sendResponse(400, false, "Email inválido");
        }

        // Validar longitud de contraseña
        if (strlen($data['password']) < 6) {
            return $this->sendResponse(400, false, "La contraseña debe tener al menos 6 caracteres");
        }

        // Verificar si el email ya existe
        $this->user->email = $data['email'];
        if ($this->user->emailExists()) {
            return $this->sendResponse(409, false, "El email ya está registrado");
        }

        // Verificar si el username ya existe
        $this->user->username = $data['username'];
        if ($this->user->usernameExists()) {
            return $this->sendResponse(409, false, "El username ya está en uso");
        }

        // Asignar datos del usuario
        $this->user->username = $data['username'];
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        $this->user->first_name = isset($data['first_name']) ? $data['first_name'] : null;
        $this->user->last_name = isset($data['last_name']) ? $data['last_name'] : null;
        $this->user->second_last_name = isset($data['second_last_name']) && !empty($data['second_last_name']) ? $data['second_last_name'] : null;
        $this->user->phone = isset($data['phone']) ? $data['phone'] : null;
        $this->user->profile_img = null;
        
        // Vendedor activo inmediatamente (sin necesidad de activación por email)
        $this->user->is_active = 1;
        $this->user->activation_token = null;
        $this->user->activation_token_expires = null;

        if ($this->user->register()) {
            $userId = $this->user->id;
            
            // Asignar rol de vendedor
            if ($this->role->getRoleByName('seller')) {
                $this->role->assignRoleToUser($userId, $this->role->id);
            }
            
            $fullName = trim($this->user->first_name . ' ' . $this->user->last_name . ($this->user->second_last_name ? ' ' . $this->user->second_last_name : ''));
            
            $userData = [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'name' => $fullName,
                'role' => 'seller'
            ];
            
            return $this->sendResponse(201, true, "Vendedor creado exitosamente", $userData);
        }
        
        return $this->sendResponse(500, false, "Error al crear vendedor");
    }
}