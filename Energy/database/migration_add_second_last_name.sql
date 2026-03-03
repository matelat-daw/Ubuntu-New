-- Script de migración para agregar campo second_last_name a la tabla users
-- Ejecutar este script en la base de datos 'energy'

USE energy;

-- Agregar columna second_last_name después de last_name
ALTER TABLE users 
ADD COLUMN second_last_name VARCHAR(50) DEFAULT NULL 
AFTER last_name;

-- Verificar el cambio
DESCRIBE users;
