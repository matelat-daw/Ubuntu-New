<?php
/**
 * Audit Logger Helper Class
 * Centralized audit logging for all controllers
 */

class AuditLogger {
    
    /**
     * Log an audit event
     * 
     * @param PDO $db Database connection
     * @param int $userId User ID performing the action
     * @param string $action Action type (login, register, update_profile, etc.)
     * @param string $entityType Entity type (user, product, order, etc.)
     * @param int $entityId Entity ID
     * @param string $description Human-readable description
     * @return bool Success status
     */
    public static function log($db, $userId, $action, $entityType, $entityId, $description) {
        try {
            $query = "INSERT INTO audit_log (user_id, action, entity_type, entity_id, description, ip_address, user_agent) 
                      VALUES (:user_id, :action, :entity_type, :entity_id, :description, :ip, :user_agent)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':entity_type', $entityType);
            $stmt->bindParam(':entity_id', $entityId);
            $stmt->bindParam(':description', $description);
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $stmt->bindParam(':ip', $ip);
            
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $stmt->bindParam(':user_agent', $userAgent);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Audit log error: " . $e->getMessage());
            return false;
        }
    }
}
