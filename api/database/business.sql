-- =====================================================
-- Base de datos Business - E-commerce Platform
-- =====================================================

DROP DATABASE IF EXISTS business;
CREATE DATABASE business CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE business;

-- =====================================================
-- Tabla: users
-- Gestión de usuarios del sistema
-- =====================================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Información personal
    name VARCHAR(100) NOT NULL,
    surname1 VARCHAR(100) NOT NULL,
    surname2 VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    
    -- Seguridad
    password VARCHAR(255) NOT NULL COMMENT 'Hash con Argon2id (compatible con ASP.NET)',
    email_hash VARCHAR(64) NOT NULL COMMENT 'SHA-256 hash del email para verificación',
    
    -- Perfil y estado
    path VARCHAR(500) DEFAULT NULL COMMENT 'Ruta a imagen de perfil',
    active TINYINT(1) DEFAULT 1 COMMENT '1=activo, 0=inactivo',
    email_verified TINYINT(1) DEFAULT 0 COMMENT '1=verificado, 0=no verificado',
    role ENUM('seller_basic', 'seller_premium', 'buyer_basic', 'buyer_premium', 'admin', 'manager') DEFAULT 'buyer_basic' COMMENT 'Roles: seller/buyer (basic/premium), admin, manager',
    
    -- Tokens y verificación
    verification_token VARCHAR(100) DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    
    -- Información adicional
    birth_date DATE DEFAULT NULL,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say') DEFAULT NULL,
    
    -- Auditoría
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL,
    last_ip VARCHAR(45) DEFAULT NULL COMMENT 'Soporta IPv4 e IPv6',
    login_attempts INT DEFAULT 0 COMMENT 'Contador de intentos fallidos',
    locked_until TIMESTAMP NULL DEFAULT NULL COMMENT 'Bloqueo temporal por intentos fallidos',
    
    -- Índices
    INDEX idx_email (email),
    INDEX idx_active (active),
    INDEX idx_role (role),
    INDEX idx_creation_date (creation_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: addresses
-- Direcciones de envío y facturación
-- =====================================================
CREATE TABLE addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    
    -- Tipo de dirección
    type ENUM('shipping', 'billing', 'both') DEFAULT 'shipping',
    is_default TINYINT(1) DEFAULT 0,
    
    -- Información de dirección
    alias VARCHAR(50) DEFAULT NULL COMMENT 'Casa, Oficina, etc.',
    full_name VARCHAR(200) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'España',
    
    -- Notas
    notes TEXT DEFAULT NULL,
    
    -- Auditoría
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: sessions
-- Sesiones activas y tokens JWT
-- =====================================================
CREATE TABLE sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    
    -- Token y seguridad
    token_hash VARCHAR(64) NOT NULL UNIQUE COMMENT 'SHA-256 del JWT token',
    refresh_token_hash VARCHAR(64) DEFAULT NULL COMMENT 'Para renovar tokens',
    
    -- Información de sesión
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    device_type VARCHAR(50) DEFAULT NULL COMMENT 'mobile, desktop, tablet',
    
    -- Validez
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Estado
    revoked TINYINT(1) DEFAULT 0,
    revoked_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires_at (expires_at),
    INDEX idx_revoked (revoked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: categories
-- Categorías de productos
-- =====================================================
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED DEFAULT NULL,
    
    -- Información
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    
    -- Orden y estado
    sort_order INT DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    
    -- Auditoría
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_parent_id (parent_id),
    INDEX idx_slug (slug),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: products
-- Productos del e-commerce
-- =====================================================
CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED DEFAULT NULL,
    
    -- Información básica
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    sku VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    short_description VARCHAR(500) DEFAULT NULL,
    
    -- Precio e inventario
    price DECIMAL(10, 2) NOT NULL,
    compare_price DECIMAL(10, 2) DEFAULT NULL COMMENT 'Precio antes de descuento',
    cost_price DECIMAL(10, 2) DEFAULT NULL COMMENT 'Costo de adquisición',
    stock INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 5,
    
    -- Características físicas
    weight DECIMAL(10, 3) DEFAULT NULL COMMENT 'Peso en kg',
    length DECIMAL(10, 2) DEFAULT NULL COMMENT 'Largo en cm',
    width DECIMAL(10, 2) DEFAULT NULL COMMENT 'Ancho en cm',
    height DECIMAL(10, 2) DEFAULT NULL COMMENT 'Alto en cm',
    
    -- SEO
    meta_title VARCHAR(200) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    meta_keywords VARCHAR(500) DEFAULT NULL,
    
    -- Estado
    active TINYINT(1) DEFAULT 1,
    featured TINYINT(1) DEFAULT 0,
    
    -- Auditoría
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category_id (category_id),
    INDEX idx_slug (slug),
    INDEX idx_sku (sku),
    INDEX idx_active (active),
    INDEX idx_featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: product_images
-- Imágenes de productos
-- =====================================================
CREATE TABLE product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    
    -- Información de imagen
    path VARCHAR(500) NOT NULL,
    alt_text VARCHAR(200) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_primary TINYINT(1) DEFAULT 0,
    
    -- Auditoría
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_is_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: carts
-- Carritos de compra
-- =====================================================
CREATE TABLE carts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    session_id VARCHAR(100) DEFAULT NULL COMMENT 'Para usuarios no autenticados',
    
    -- Estado
    status ENUM('active', 'abandoned', 'converted') DEFAULT 'active',
    
    -- Auditoría
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: cart_items
-- Items en el carrito
-- =====================================================
CREATE TABLE cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    
    -- Cantidad y precio
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL COMMENT 'Precio al momento de agregar',
    
    -- Auditoría
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_cart_id (cart_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: orders
-- Órdenes de compra
-- =====================================================
CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    
    -- Número de orden
    order_number VARCHAR(50) NOT NULL UNIQUE,
    
    -- Direcciones
    shipping_address_id INT UNSIGNED DEFAULT NULL,
    billing_address_id INT UNSIGNED DEFAULT NULL,
    
    -- Totales
    subtotal DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(10, 2) DEFAULT 0,
    shipping_cost DECIMAL(10, 2) DEFAULT 0,
    discount DECIMAL(10, 2) DEFAULT 0,
    total DECIMAL(10, 2) NOT NULL,
    
    -- Estado
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT NULL,
    
    -- Notas
    customer_notes TEXT DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    
    -- Auditoría
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL DEFAULT NULL,
    shipped_at TIMESTAMP NULL DEFAULT NULL,
    delivered_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (shipping_address_id) REFERENCES addresses(id) ON DELETE SET NULL,
    FOREIGN KEY (billing_address_id) REFERENCES addresses(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_creation_date (creation_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: order_items
-- Items de las órdenes
-- =====================================================
CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    
    -- Información del producto al momento de compra
    product_name VARCHAR(200) NOT NULL,
    product_sku VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    
    -- Auditoría
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: reviews
-- Reseñas de productos
-- =====================================================
CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    
    -- Calificación y contenido
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(200) DEFAULT NULL,
    content TEXT DEFAULT NULL,
    
    -- Estado
    approved TINYINT(1) DEFAULT 0,
    
    -- Auditoría
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_product_id (product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_approved (approved),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: audit_log
-- Registro de auditoría de acciones importantes
-- =====================================================
CREATE TABLE audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    
    -- Información de la acción
    action VARCHAR(100) NOT NULL COMMENT 'login, logout, register, update_profile, etc.',
    entity_type VARCHAR(50) DEFAULT NULL COMMENT 'user, product, order, etc.',
    entity_id INT UNSIGNED DEFAULT NULL,
    
    -- Detalles
    description TEXT DEFAULT NULL,
    old_values JSON DEFAULT NULL,
    new_values JSON DEFAULT NULL,
    
    -- Contexto
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    
    -- Auditoría
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_creation_date (creation_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: settings
-- Configuración del sistema
-- =====================================================
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value TEXT DEFAULT NULL,
    description VARCHAR(500) DEFAULT NULL,
    type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    
    -- Auditoría
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Datos iniciales
-- =====================================================

-- Configuración del sistema
INSERT INTO settings (`key`, value, description, type) VALUES
('site_name', 'Business E-commerce', 'Nombre del sitio', 'string'),
('site_email', 'admin@business.local', 'Email principal', 'string'),
('jwt_secret', '', 'Secret key para JWT (generar con openssl)', 'string'),
('jwt_expiration', '3600', 'Tiempo de expiración del JWT en segundos', 'number'),
('max_login_attempts', '5', 'Máximo de intentos de login', 'number'),
('lockout_duration', '900', 'Duración del bloqueo en segundos', 'number'),
('password_min_length', '8', 'Longitud mínima de contraseña', 'number'),
('require_email_verification', '1', 'Requiere verificación de email', 'boolean'),
('tax_rate', '0.21', 'Tasa de impuesto (IVA)', 'number'),
('currency', 'EUR', 'Moneda del sistema', 'string'),
('currency_symbol', '€', 'Símbolo de moneda', 'string');

-- Usuario administrador por defecto (password: Admin@123)
-- Hash Argon2id generado con PASSWORD_ARGON2ID en PHP
INSERT INTO users (name, surname1, surname2, email, password, email_hash, active, email_verified, role)
VALUES (
    'Admin',
    'Sistema',
    NULL,
    'admin@business.local',
    '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQxMjM0NTY3OA$J8qPvLVVT4wEVTfKGHhCp2IbQnqTx5KmN+rQw5LnHvE',
    SHA2('admin@business.local', 256),
    1,
    1,
    'admin'
);

-- Categoría de ejemplo
INSERT INTO categories (name, slug, description) VALUES
('Electrónica', 'electronica', 'Productos electrónicos y tecnología');

-- =====================================================
-- Vista útil: user_summary
-- =====================================================
CREATE VIEW user_summary AS
SELECT 
    u.id,
    u.name,
    u.surname1,
    u.surname2,
    u.email,
    u.phone,
    u.role,
    u.active,
    u.email_verified,
    u.creation_date,
    u.last_login,
    COUNT(DISTINCT o.id) as total_orders,
    COALESCE(SUM(o.total), 0) as total_spent
FROM users u
LEFT JOIN orders o ON u.id = o.user_id
GROUP BY u.id;

-- =====================================================
-- Procedimientos almacenados útiles
-- =====================================================

DELIMITER //

-- Procedimiento para limpiar sesiones expiradas
CREATE PROCEDURE clean_expired_sessions()
BEGIN
    DELETE FROM sessions 
    WHERE expires_at < NOW() 
    OR revoked = 1;
END //

-- Procedimiento para limpiar carritos abandonados (más de 30 días)
CREATE PROCEDURE clean_abandoned_carts()
BEGIN
    DELETE FROM carts 
    WHERE status = 'abandoned' 
    AND modification_date < DATE_SUB(NOW(), INTERVAL 30 DAY);
END //

DELIMITER ;

-- =====================================================
-- Triggers para auditoría
-- =====================================================

DELIMITER //

-- Trigger para auditar cambios en usuarios
CREATE TRIGGER users_after_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (user_id, action, entity_type, entity_id, description, old_values, new_values, ip_address)
    VALUES (
        NEW.id,
        'update_user',
        'user',
        NEW.id,
        CONCAT('Usuario actualizado: ', NEW.email),
        JSON_OBJECT('name', OLD.name, 'email', OLD.email, 'active', OLD.active),
        JSON_OBJECT('name', NEW.name, 'email', NEW.email, 'active', NEW.active),
        '0.0.0.0'
    );
END //

DELIMITER ;

-- =====================================================
-- Eventos programados (requiere event_scheduler = ON)
-- =====================================================

-- Limpiar sesiones expiradas cada hora
CREATE EVENT IF NOT EXISTS cleanup_sessions
ON SCHEDULE EVERY 1 HOUR
DO CALL clean_expired_sessions();

-- Limpiar carritos abandonados cada día
CREATE EVENT IF NOT EXISTS cleanup_carts
ON SCHEDULE EVERY 1 DAY
DO CALL clean_abandoned_carts();

-- =====================================================
-- Permisos recomendados
-- =====================================================

-- Crear usuario para la API (ajustar password)
-- CREATE USER 'business_api'@'localhost' IDENTIFIED BY 'SecurePassword123!';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON business.* TO 'business_api'@'localhost';
-- FLUSH PRIVILEGES;

-- =====================================================
-- Notas importantes:
-- =====================================================
-- 1. El password del admin por defecto debe cambiarse
-- 2. Generar un JWT secret seguro y actualizar en settings
-- 3. Configurar el usuario de la API con permisos limitados
-- 4. Habilitar event_scheduler: SET GLOBAL event_scheduler = ON;
-- 5. Las rutas de imágenes seguirán el patrón:
--    - Usuarios: assets/profiles/{user_id}/profile.webp
--    - Productos: assets/products/{product_id}/{image_id}.webp
--    - Categorías: assets/categories/{category_id}.webp
-- =====================================================
