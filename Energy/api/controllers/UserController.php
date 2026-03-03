<?php
// Controlador de Usuarios
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Role.php';

class UserController {
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
     * Obtener todos los usuarios
     */
    public function index() {
        $query = "SELECT u.id, u.username, u.email, u.first_name, u.last_name, 
                         u.phone, u.profile_img, u.is_active, u.created_at
                  FROM users u
                  ORDER BY u.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Obtener roles del usuario
            $rolesStmt = $this->role->getUserRoles($row['id']);
            $roles = [];
            while ($roleRow = $rolesStmt->fetch(PDO::FETCH_ASSOC)) {
                $roles[] = $roleRow['name'];
            }
            $row['roles'] = $roles;
            $users[] = $row;
        }

        return $this->sendResponse(200, true, "Usuarios obtenidos exitosamente", $users);
    }

    /**
     * Obtener usuarios por rol
     */
    public function getUsersByRole($role_name) {
        $stmt = $this->user->getUsersByRole($role_name);
        $users = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $row;
        }

        return $this->sendResponse(200, true, "Usuarios obtenidos exitosamente", $users);
    }

    /**
     * Obtener un usuario específico
     */
    public function show($id) {
        $this->user->id = $id;

        if ($this->user->readOne()) {
            // Obtener roles del usuario
            $rolesStmt = $this->user->getRoles();
            $roles = [];
            while ($roleRow = $rolesStmt->fetch(PDO::FETCH_ASSOC)) {
                $roles[] = $roleRow['name'];
            }

            $user_data = [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'phone' => $this->user->phone,
                'profile_img' => $this->user->profile_img,
                'is_active' => $this->user->is_active,
                'roles' => $roles,
                'created_at' => $this->user->created_at,
                'updated_at' => $this->user->updated_at
            ];

            return $this->sendResponse(200, true, "Usuario encontrado", $user_data);
        }

        return $this->sendResponse(404, false, "Usuario no encontrado");
    }

    /**
     * Actualizar usuario
     */
    public function update($data, $id) {
        $this->user->id = $id;

        if (!$this->user->readOne()) {
            return $this->sendResponse(404, false, "Usuario no encontrado");
        }

        // Actualizar campos
        if (isset($data['first_name'])) {
            $this->user->first_name = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $this->user->last_name = $data['last_name'];
        }
        if (isset($data['phone'])) {
            $this->user->phone = $data['phone'];
        }
        if (isset($data['profile_img'])) {
            $this->user->profile_img = $data['profile_img'];
        }

        if ($this->user->update()) {
            return $this->sendResponse(200, true, "Usuario actualizado exitosamente");
        }

        return $this->sendResponse(500, false, "Error al actualizar usuario");
    }

    /**
     * Eliminar usuario
     */
    public function destroy($id) {
        $this->user->id = $id;

        if (!$this->user->readOne()) {
            return $this->sendResponse(404, false, "Usuario no encontrado");
        }

        if ($this->user->delete()) {
            return $this->sendResponse(200, true, "Usuario eliminado exitosamente");
        }

        return $this->sendResponse(500, false, "Error al eliminar usuario");
    }

    /**
     * Cambiar contraseña del usuario
     */
    public function updatePassword($data, $id) {
        // Validar datos requeridos
        if (empty($data['current_password']) || empty($data['new_password'])) {
            return $this->sendResponse(400, false, "Contraseña actual y nueva son requeridas");
        }

        $this->user->id = $id;

        if (!$this->user->readOne()) {
            return $this->sendResponse(404, false, "Usuario no encontrado");
        }

        // Verificar contraseña actual
        if (!password_verify($data['current_password'], $this->user->password_hash)) {
            return $this->sendResponse(401, false, "Contraseña actual incorrecta");
        }

        // Actualizar contraseña
        if ($this->user->updatePassword($data['new_password'])) {
            return $this->sendResponse(200, true, "Contraseña actualizada exitosamente");
        }

        return $this->sendResponse(500, false, "Error al actualizar contraseña");
    }

    /**
     * Asignar rol a usuario
     */
    public function assignRole($data, $id) {
        if (empty($data['role_name'])) {
            return $this->sendResponse(400, false, "Nombre del rol es requerido");
        }

        // Verificar que el usuario existe
        $this->user->id = $id;
        if (!$this->user->readOne()) {
            return $this->sendResponse(404, false, "Usuario no encontrado");
        }

        // Obtener el rol por nombre
        if (!$this->role->getRoleByName($data['role_name'])) {
            return $this->sendResponse(404, false, "Rol no encontrado");
        }

        // Asignar rol
        if ($this->role->assignRoleToUser($id, $this->role->id)) {
            return $this->sendResponse(200, true, "Rol asignado exitosamente");
        }

        return $this->sendResponse(500, false, "Error al asignar rol");
    }

    /**
     * Remover rol de usuario
     */
    public function removeRole($data, $id) {
        if (empty($data['role_name'])) {
            return $this->sendResponse(400, false, "Nombre del rol es requerido");
        }

        // Verificar que el usuario existe
        $this->user->id = $id;
        if (!$this->user->readOne()) {
            return $this->sendResponse(404, false, "Usuario no encontrado");
        }

        // Obtener el rol por nombre
        if (!$this->role->getRoleByName($data['role_name'])) {
            return $this->sendResponse(404, false, "Rol no encontrado");
        }

        // Remover rol
        if ($this->role->removeRoleFromUser($id, $this->role->id)) {
            return $this->sendResponse(200, true, "Rol removido exitosamente");
        }

        return $this->sendResponse(500, false, "Error al remover rol");
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
}
?>
