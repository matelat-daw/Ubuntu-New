<?php
/**
 * User Model
 * Handles all user-related database operations and business logic
 */

class User {
    private $db;
    private $table = 'users';
    
    // User properties
    public $id;
    public $name;
    public $surname1;
    public $surname2;
    public $phone;
    public $email;
    public $password;
    public $email_hash;
    public $path;
    public $verification_token;
    public $reset_token;
    public $reset_token_expiry;
    public $active;
    public $email_verified;
    public $role;
    public $last_login;
    public $last_ip;
    public $creation_date;
    public $modification_date;
    
    /**
     * Constructor
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Create a new user
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
            (name, surname1, surname2, phone, email, password, email_hash, path, 
             verification_token, active, email_verified, role, last_ip) 
            VALUES 
            (:name, :surname1, :surname2, :phone, :email, :password, :email_hash, :path, 
             :verification_token, :active, :email_verified, :role, :last_ip)";
        
        $stmt = $this->db->prepare($query);
        
        // Bind values
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':surname1', $this->surname1);
        $stmt->bindParam(':surname2', $this->surname2);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':email_hash', $this->email_hash);
        $stmt->bindParam(':path', $this->path);
        $stmt->bindParam(':verification_token', $this->verification_token);
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':email_verified', $this->email_verified);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':last_ip', $this->last_ip);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Find user by ID
     */
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->hydrate($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->hydrate($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Find user by verification token
     */
    public function findByVerificationToken($token) {
        $query = "SELECT * FROM " . $this->table . " WHERE verification_token = :token LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->hydrate($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Find user by reset token
     */
    public function findByResetToken($token) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE reset_token = :token 
                  AND reset_token_expiry > NOW() 
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->hydrate($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Update user
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name,
                      surname1 = :surname1,
                      surname2 = :surname2,
                      phone = :phone,
                      email = :email,
                      email_hash = :email_hash,
                      path = :path,
                      role = :role,
                      modification_date = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':surname1', $this->surname1);
        $stmt->bindParam(':surname2', $this->surname2);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':email_hash', $this->email_hash);
        $stmt->bindParam(':path', $this->path);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Update password
     */
    public function updatePassword($newPassword) {
        $query = "UPDATE " . $this->table . " 
                  SET password = :password,
                      modification_date = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':password', $newPassword);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Verify email
     */
    public function verifyEmail() {
        $query = "UPDATE " . $this->table . " 
                  SET email_verified = 1,
                      active = 1,
                      verification_token = NULL,
                      modification_date = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Set verification token
     */
    public function setVerificationToken($token) {
        $query = "UPDATE " . $this->table . " 
                  SET verification_token = :token,
                      modification_date = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Set reset token
     */
    public function setResetToken($token, $expiry) {
        $query = "UPDATE " . $this->table . " 
                  SET reset_token = :token,
                      reset_token_expiry = :expiry,
                      modification_date = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiry', $expiry);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Clear reset token
     */
    public function clearResetToken() {
        $query = "UPDATE " . $this->table . " 
                  SET reset_token = NULL,
                      reset_token_expiry = NULL,
                      modification_date = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Update last login
     */
    public function updateLastLogin($ip) {
        $query = "UPDATE " . $this->table . " 
                  SET last_login = CURRENT_TIMESTAMP,
                      last_ip = :ip
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Activate/Deactivate user
     */
    public function setActive($active) {
        $query = "UPDATE " . $this->table . " 
                  SET active = :active,
                      modification_date = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':active', $active);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Delete user (soft delete by deactivating)
     */
    public function delete() {
        return $this->setActive(0);
    }
    
    /**
     * Hard delete user (permanent)
     */
    public function hardDelete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
    
    /**
     * Hash password with Argon2id
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,  // 64 MB
            'time_cost' => 4,
            'threads' => 1
        ]);
    }
    
    /**
     * Generate email hash
     */
    public static function generateEmailHash($email) {
        return hash('sha256', $email);
    }
    
    /**
     * Generate random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Check if verification token is expired (24 hours)
     */
    public function isVerificationExpired() {
        if (!$this->creation_date) {
            return true;
        }
        
        $creationDate = new DateTime($this->creation_date);
        $now = new DateTime();
        $diff = $now->getTimestamp() - $creationDate->getTimestamp();
        
        return $diff > 86400; // 24 hours
    }
    
    /**
     * Get user as array (without sensitive data)
     */
    public function toArray($includePassword = false) {
        require_once __DIR__ . '/../classes/RoleManager.php';
        
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'surname1' => $this->surname1,
            'surname2' => $this->surname2,
            'phone' => $this->phone,
            'email' => $this->email,
            'path' => $this->path,
            'active' => (bool)$this->active,
            'email_verified' => (bool)$this->email_verified,
            'role' => $this->role,
            'role_display' => RoleManager::getRoleDisplayName($this->role),
            'role_type' => RoleManager::getRoleType($this->role),
            'role_tier' => RoleManager::getRoleTier($this->role),
            'is_premium' => RoleManager::isPremium($this->role),
            'is_seller' => RoleManager::isSeller($this->role),
            'is_buyer' => RoleManager::isBuyer($this->role),
            'is_admin' => RoleManager::isAdmin($this->role),
            'last_login' => $this->last_login,
            'creation_date' => $this->creation_date,
            'modification_date' => $this->modification_date
        ];
        
        if ($includePassword) {
            $data['password'] = $this->password;
        }
        
        return $data;
    }
    
    /**
     * Hydrate object from array
     */
    private function hydrate($data) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->surname1 = $data['surname1'] ?? null;
        $this->surname2 = $data['surname2'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->email_hash = $data['email_hash'] ?? null;
        $this->path = $data['path'] ?? null;
        $this->verification_token = $data['verification_token'] ?? null;
        $this->reset_token = $data['reset_token'] ?? null;
        $this->reset_token_expiry = $data['reset_token_expiry'] ?? null;
        $this->active = $data['active'] ?? null;
        $this->email_verified = $data['email_verified'] ?? null;
        $this->role = $data['role'] ?? null;
        $this->last_login = $data['last_login'] ?? null;
        $this->last_ip = $data['last_ip'] ?? null;
        $this->creation_date = $data['creation_date'] ?? null;
        $this->modification_date = $data['modification_date'] ?? null;
    }
    
    /**
     * Get full name
     */
    public function getFullName() {
        $name = trim($this->name . ' ' . $this->surname1);
        if (!empty($this->surname2)) {
            $name .= ' ' . $this->surname2;
        }
        return $name;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin() {
        require_once __DIR__ . '/../classes/RoleManager.php';
        return RoleManager::isAdmin($this->role);
    }
    
    /**
     * Check if user is seller
     */
    public function isSeller() {
        require_once __DIR__ . '/../classes/RoleManager.php';
        return RoleManager::isSeller($this->role);
    }
    
    /**
     * Check if user is buyer
     */
    public function isBuyer() {
        require_once __DIR__ . '/../classes/RoleManager.php';
        return RoleManager::isBuyer($this->role);
    }
    
    /**
     * Check if user has premium role
     */
    public function isPremium() {
        require_once __DIR__ . '/../classes/RoleManager.php';
        return RoleManager::isPremium($this->role);
    }
    
    /**
     * Upgrade user to premium
     */
    public function upgradeToPremium() {
        require_once __DIR__ . '/../classes/RoleManager.php';
        $newRole = RoleManager::upgradeToPremium($this->role);
        
        if ($newRole === $this->role) {
            return false; // No upgrade available
        }
        
        $query = "UPDATE " . $this->table . " 
                  SET role = :role,
                      modification_date = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':role', $newRole);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            $this->role = $newRole;
            return true;
        }
        
        return false;
    }
    
    /**
     * Downgrade user to basic
     */
    public function downgradeToBasic() {
        require_once __DIR__ . '/../classes/RoleManager.php';
        $newRole = RoleManager::downgradeToBasic($this->role);
        
        if ($newRole === $this->role) {
            return false; // No downgrade available
        }
        
        $query = "UPDATE " . $this->table . " 
                  SET role = :role,
                      modification_date = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':role', $newRole);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            $this->role = $newRole;
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user is active and verified
     */
    public function canLogin() {
        return $this->active == 1 && $this->email_verified == 1;
    }
}
