<?php
/**
 * Input Validator
 */

class Validator {
    
    private $errors = [];
    
    public function validateEmail($email, $fieldName = 'email') {
        if (empty($email)) {
            $this->errors[$fieldName] = 'El email es requerido';
            return false;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$fieldName] = 'El email no es válido';
            return false;
        }
        
        return true;
    }
    
    public function validatePassword($password, $fieldName = 'password') {
        if (empty($password)) {
            $this->errors[$fieldName] = 'La contraseña es requerida';
            return false;
        }
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $this->errors[$fieldName] = 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres';
            return false;
        }
        
        if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            $this->errors[$fieldName] = 'La contraseña debe contener al menos una mayúscula';
            return false;
        }
        
        if (PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
            $this->errors[$fieldName] = 'La contraseña debe contener al menos una minúscula';
            return false;
        }
        
        if (PASSWORD_REQUIRE_NUMBER && !preg_match('/[0-9]/', $password)) {
            $this->errors[$fieldName] = 'La contraseña debe contener al menos un número';
            return false;
        }
        
        if (PASSWORD_REQUIRE_SPECIAL && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $this->errors[$fieldName] = 'La contraseña debe contener al menos un carácter especial';
            return false;
        }
        
        return true;
    }
    
    public function validateRequired($value, $fieldName) {
        if (empty($value) && $value !== '0') {
            $this->errors[$fieldName] = 'El campo ' . $fieldName . ' es requerido';
            return false;
        }
        return true;
    }
    
    public function validatePhone($phone, $fieldName = 'phone') {
        if (!empty($phone)) {
            // Validar formato básico de teléfono (números, espacios, guiones, paréntesis)
            if (!preg_match('/^[\d\s\-\(\)\+]+$/', $phone)) {
                $this->errors[$fieldName] = 'El teléfono no tiene un formato válido';
                return false;
            }
        }
        return true;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    public function addError($fieldName, $message) {
        $this->errors[$fieldName] = $message;
    }
    
    public function clearErrors() {
        $this->errors = [];
    }
}
