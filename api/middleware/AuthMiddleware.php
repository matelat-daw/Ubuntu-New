<?php
/**
 * JWT Authentication Middleware
 * Validates JWT token from cookie or Authorization header
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Response.php';
require_once __DIR__ . '/../classes/RoleManager.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class AuthMiddleware {
    
    /**
     * Verify JWT token and return user data
     */
    public static function authenticate() {
        $token = self::getTokenFromRequest();
        
        if (!$token) {
            Response::unauthorized('Token de autenticación no proporcionado');
        }
        
        try {
            // Decode JWT
            $decoded = JWT::decode($token, new Key(JWT_SECRET, JWT_ALGORITHM));
            
            // Create hash of the token to search in database
            $tokenHash = hash('sha256', $token);
            
            // Verify token exists in sessions table and is not revoked
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT id, user_id, revoked, expires_at, NOW() as server_time 
                      FROM sessions 
                      WHERE token_hash = :token_hash 
                      LIMIT 1";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':token_hash', $tokenHash);
            $stmt->execute();
            
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$session) {
                Response::unauthorized('Sesión inválida');
            }
            
            if ($session['revoked'] == 1) {
                Response::unauthorized('La sesión ha sido revocada');
            }
            
            // Check if session has expired (compare database times)
            if ($session['expires_at'] < $session['server_time']) {
                Response::unauthorized('La sesión ha expirado');
            }
            
            // Return decoded token data
            return [
                'user_id' => $decoded->data->user_id,
                'email' => $decoded->data->email,
                'role' => $decoded->data->role,
                'name' => $decoded->data->name,
                'session_id' => $session['id']
            ];
            
        } catch (ExpiredException $e) {
            Response::unauthorized('Token expirado');
        } catch (Exception $e) {
            error_log("JWT validation error: " . $e->getMessage());
            Response::unauthorized('Token inválido');
        }
    }
    
    /**
     * Verify user is admin
     */
    public static function requireAdmin() {
        $user = self::authenticate();
        
        if (!RoleManager::isAdmin($user['role'])) {
            Response::forbidden('Acceso denegado. Se requieren permisos de administrador');
        }
        
        return $user;
    }
    
    /**
     * Verify user is seller
     */
    public static function requireSeller() {
        $user = self::authenticate();
        
        if (!RoleManager::isSeller($user['role'])) {
            Response::forbidden('Acceso denegado. Se requiere ser vendedor');
        }
        
        return $user;
    }
    
    /**
     * Verify user is premium
     */
    public static function requirePremium() {
        $user = self::authenticate();
        
        if (!RoleManager::isPremium($user['role'])) {
            Response::forbidden('Acceso denegado. Se requiere cuenta Premium');
        }
        
        return $user;
    }
    
    /**
     * Verify user is seller with premium
     */
    public static function requireSellerPremium() {
        $user = self::authenticate();
        
        if (!RoleManager::isSeller($user['role']) || !RoleManager::isPremium($user['role'])) {
            Response::forbidden('Acceso denegado. Se requiere ser vendedor Premium');
        }
        
        return $user;
    }
    
    /**
     * Get token from cookie or Authorization header
     */
    private static function getTokenFromRequest() {
        // First, try to get from cookie
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }
        
        // Second, try Authorization header (for API calls)
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            
            // Check if it's Bearer token
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Revoke current session (logout)
     */
    public static function revokeSession($token) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $tokenHash = hash('sha256', $token);
            
            $query = "UPDATE sessions SET revoked = 1, revoked_at = NOW() WHERE token_hash = :token_hash";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':token_hash', $tokenHash);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error revoking session: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Revoke all user sessions
     */
    public static function revokeAllUserSessions($userId) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "UPDATE sessions SET revoked = 1 WHERE user_id = :user_id AND revoked = 0";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error revoking all sessions: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean expired sessions (can be called by cron)
     */
    public static function cleanExpiredSessions() {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "DELETE FROM sessions WHERE expires_at < NOW()";
            $stmt = $db->prepare($query);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error cleaning expired sessions: " . $e->getMessage());
            return false;
        }
    }
}
