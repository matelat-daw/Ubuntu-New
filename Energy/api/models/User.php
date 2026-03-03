<?php
// Modelo de Usuario
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $second_last_name;
    public $phone;
    public $profile_img;
    public $is_active;
    public $activation_token;
    public $activation_token_expires;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Registrar nuevo usuario
     */
    public function register() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, 
                      email=:email, 
                      password=:password,
                      first_name=:first_name,
                      last_name=:last_name,
                      second_last_name=:second_last_name,
                      phone=:phone,
                      profile_img=:profile_img,
                      is_active=:is_active,
                      activation_token=:activation_token,
                      activation_token_expires=:activation_token_expires";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $first_name = $this->first_name ? htmlspecialchars(strip_tags($this->first_name)) : null;
        $last_name = $this->last_name ? htmlspecialchars(strip_tags($this->last_name)) : null;
        $second_last_name = $this->second_last_name ? htmlspecialchars(strip_tags($this->second_last_name)) : null;
        $phone = $this->phone ? htmlspecialchars(strip_tags($this->phone)) : null;
        $profile_img = $this->profile_img ? $this->profile_img : null;
        $is_active = $this->is_active !== null ? $this->is_active : 1;
        
        // Hash de contraseña con bcrypt
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":last_name", $last_name);
        $stmt->bindParam(":second_last_name", $second_last_name);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":profile_img", $profile_img);
        $stmt->bindParam(":is_active", $is_active);
        $stmt->bindParam(":activation_token", $this->activation_token);
        $stmt->bindParam(":activation_token_expires", $this->activation_token_expires);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Verificar si el email ya existe
     */
    public function emailExists() {
        $query = "SELECT id, username, email, password, first_name, last_name, second_last_name, phone, 
                         profile_img, is_active, created_at 
                  FROM " . $this->table_name . " 
                  WHERE email = :email 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        $num = $stmt->rowCount();

        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->second_last_name = $row['second_last_name'];
            $this->phone = $row['phone'];
            $this->profile_img = $row['profile_img'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            
            return true;
        }

        return false;
    }

    /**
     * Verificar si el username ya existe
     */
    public function usernameExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE username = :username 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Obtener usuario por ID
     */
    public function readOne() {
        $query = "SELECT id, username, email, first_name, last_name, second_last_name, phone, 
                         profile_img, is_active, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->second_last_name = $row['second_last_name'];
            $this->phone = $row['phone'];
            $this->profile_img = $row['profile_img'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }

        return false;
    }

    /**
     * Actualizar usuario
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name=:first_name,
                      last_name=:last_name,
                      second_last_name=:second_last_name,
                      phone=:phone,
                      profile_img=:profile_img
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $first_name = $this->first_name ? htmlspecialchars(strip_tags($this->first_name)) : null;
        $last_name = $this->last_name ? htmlspecialchars(strip_tags($this->last_name)) : null;
        $second_last_name = $this->second_last_name ? htmlspecialchars(strip_tags($this->second_last_name)) : null;
        $phone = $this->phone ? htmlspecialchars(strip_tags($this->phone)) : null;

        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":last_name", $last_name);
        $stmt->bindParam(":second_last_name", $second_last_name);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":profile_img", $this->profile_img);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Cambiar contraseña
     */
    public function updatePassword($new_password) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password=:password 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        
        $password_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Eliminar usuario
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Obtener todos los usuarios con un rol específico
     */
    public function getUsersByRole($role_name) {
        $query = "SELECT u.id, u.username, u.email, u.first_name, u.last_name, 
                         u.phone, u.profile_img, u.is_active, u.created_at
                  FROM " . $this->table_name . " u
                  INNER JOIN user_roles ur ON u.id = ur.user_id
                  INNER JOIN roles r ON ur.role_id = r.id
                  WHERE r.name = :role_name
                  ORDER BY u.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":role_name", $role_name);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Obtener roles del usuario
     */
    public function getRoles() {
        require_once __DIR__ . '/Role.php';
        $role = new Role($this->conn);
        return $role->getUserRoles($this->id);
    }

    /**
     * Activar cuenta con token
     */
    public function activateAccount($token) {
        $query = "SELECT id, username, email, activation_token_expires, is_active 
                  FROM " . $this->table_name . " 
                  WHERE activation_token = :token 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si ya está activado
            if ($row['is_active'] == 1) {
                return ['success' => false, 'message' => 'Esta cuenta ya está activada'];
            }
            
            // Verificar si el token ha expirado
            $now = new DateTime();
            $expires = new DateTime($row['activation_token_expires']);
            
            if ($now > $expires) {
                return ['success' => false, 'message' => 'El enlace de activación ha expirado'];
            }
            
            // Activar cuenta
            $updateQuery = "UPDATE " . $this->table_name . " 
                           SET is_active = 1, 
                               activation_token = NULL, 
                               activation_token_expires = NULL 
                           WHERE id = :id";
            
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":id", $row['id']);
            
            if ($updateStmt->execute()) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->is_active = 1;
                
                return ['success' => true, 'message' => 'Cuenta activada exitosamente'];
            }
        }
        
        return ['success' => false, 'message' => 'Token de activación inválido'];
    }
}
?>
