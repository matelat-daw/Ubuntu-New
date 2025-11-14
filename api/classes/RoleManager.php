<?php
/**
 * Role Manager Class
 * Handles user roles and permissions
 */

class RoleManager {
    
    // Role constants
    const SELLER_BASIC = 'seller_basic';
    const SELLER_PREMIUM = 'seller_premium';
    const BUYER_BASIC = 'buyer_basic';
    const BUYER_PREMIUM = 'buyer_premium';
    const ADMIN = 'admin';
    const MANAGER = 'manager';
    
    // Role groups
    const SELLERS = [self::SELLER_BASIC, self::SELLER_PREMIUM];
    const BUYERS = [self::BUYER_BASIC, self::BUYER_PREMIUM];
    const ADMINS = [self::ADMIN, self::MANAGER];
    const BASIC_ROLES = [self::SELLER_BASIC, self::BUYER_BASIC];
    const PREMIUM_ROLES = [self::SELLER_PREMIUM, self::BUYER_PREMIUM];
    
    /**
     * Get all valid roles
     */
    public static function getAllRoles() {
        return [
            self::SELLER_BASIC,
            self::SELLER_PREMIUM,
            self::BUYER_BASIC,
            self::BUYER_PREMIUM,
            self::ADMIN,
            self::MANAGER
        ];
    }
    
    /**
     * Get roles available for registration (only basic seller/buyer)
     */
    public static function getRegistrationRoles() {
        return [
            self::SELLER_BASIC,
            self::BUYER_BASIC
        ];
    }
    
    /**
     * Validate if role is valid
     */
    public static function isValidRole($role) {
        return in_array($role, self::getAllRoles());
    }
    
    /**
     * Validate if role is allowed for registration
     */
    public static function isRegistrationRole($role) {
        return in_array($role, self::getRegistrationRoles());
    }
    
    /**
     * Check if user is a seller
     */
    public static function isSeller($role) {
        return in_array($role, self::SELLERS);
    }
    
    /**
     * Check if user is a buyer
     */
    public static function isBuyer($role) {
        return in_array($role, self::BUYERS);
    }
    
    /**
     * Check if user is an admin
     */
    public static function isAdmin($role) {
        return in_array($role, self::ADMINS);
    }
    
    /**
     * Check if user has premium role
     */
    public static function isPremium($role) {
        return in_array($role, self::PREMIUM_ROLES);
    }
    
    /**
     * Check if user has basic role
     */
    public static function isBasic($role) {
        return in_array($role, self::BASIC_ROLES);
    }
    
    /**
     * Upgrade user to premium
     */
    public static function upgradeToPremium($currentRole) {
        switch ($currentRole) {
            case self::SELLER_BASIC:
                return self::SELLER_PREMIUM;
            case self::BUYER_BASIC:
                return self::BUYER_PREMIUM;
            default:
                return $currentRole; // No upgrade needed or not applicable
        }
    }
    
    /**
     * Downgrade user to basic
     */
    public static function downgradeToBasic($currentRole) {
        switch ($currentRole) {
            case self::SELLER_PREMIUM:
                return self::SELLER_BASIC;
            case self::BUYER_PREMIUM:
                return self::BUYER_BASIC;
            default:
                return $currentRole; // No downgrade needed or not applicable
        }
    }
    
    /**
     * Get role display name
     */
    public static function getRoleDisplayName($role) {
        $names = [
            self::SELLER_BASIC => 'Vendedor Básico',
            self::SELLER_PREMIUM => 'Vendedor Premium',
            self::BUYER_BASIC => 'Comprador Básico',
            self::BUYER_PREMIUM => 'Comprador Premium',
            self::ADMIN => 'Administrador',
            self::MANAGER => 'Gestor'
        ];
        
        return $names[$role] ?? 'Desconocido';
    }
    
    /**
     * Get role type (seller, buyer, admin)
     */
    public static function getRoleType($role) {
        if (self::isSeller($role)) {
            return 'seller';
        } elseif (self::isBuyer($role)) {
            return 'buyer';
        } elseif (self::isAdmin($role)) {
            return 'admin';
        }
        return 'unknown';
    }
    
    /**
     * Get role tier (basic, premium, admin)
     */
    public static function getRoleTier($role) {
        if (self::isPremium($role)) {
            return 'premium';
        } elseif (self::isBasic($role)) {
            return 'basic';
        } elseif (self::isAdmin($role)) {
            return 'admin';
        }
        return 'unknown';
    }
}
