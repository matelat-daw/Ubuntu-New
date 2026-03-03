-- Script de migración para agregar verificación de email
-- Ejecutar este script en la base de datos 'energy'

USE energy;

-- Agregar campos de activación por email
ALTER TABLE users 
ADD COLUMN activation_token VARCHAR(64) DEFAULT NULL AFTER is_active,
ADD COLUMN activation_token_expires DATETIME DEFAULT NULL AFTER activation_token;

-- Crear índice para búsquedas rápidas
CREATE INDEX idx_activation_token ON users(activation_token);

-- Actualizar is_active por defecto a 0 (requiere activación)
ALTER TABLE users
MODIFY COLUMN is_active BOOLEAN DEFAULT 0;

-- Activar usuarios existentes que no tienen token (ya registrados antes de esta migración)
UPDATE users SET is_active = 1 WHERE activation_token IS NULL;

-- Verificar cambios
DESCRIBE users;
SELECT id, username, email, is_active, activation_token FROM users;
