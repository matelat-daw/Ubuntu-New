-- Schema de base de datos para plataforma de proveedores de energía

-- Tabla de roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de usuarios (vendedores y clientes)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    second_last_name VARCHAR(50) DEFAULT NULL,
    phone VARCHAR(20),
    profile_img VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT 0,
    activation_token VARCHAR(64) DEFAULT NULL,
    activation_token_expires DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activation_token (activation_token)
);

-- Tabla de relación usuario-rol
CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role (user_id, role_id)
);

-- Tabla de proveedores de energía
CREATE TABLE energy_providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    logo VARCHAR(255),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    website VARCHAR(255),
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de planes de energía
CREATE TABLE energy_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_kwh DECIMAL(10,4) NOT NULL,
    monthly_fee DECIMAL(10,2) DEFAULT 0.00,
    contract_duration_months INT DEFAULT 12,
    renewable_energy_percentage INT DEFAULT 0,
    features JSON,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES energy_providers(id) ON DELETE CASCADE
);

-- Tabla de contratos (ventas)
CREATE TABLE contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    seller_id INT,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('pending', 'active', 'cancelled', 'completed') DEFAULT 'pending',
    total_amount DECIMAL(10,2),
    commission_amount DECIMAL(10,2),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (plan_id) REFERENCES energy_plans(id) ON DELETE CASCADE
);

-- Tabla de direcciones de instalación
CREATE TABLE installation_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contract_id INT NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) DEFAULT 'España',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE
);

-- Insertar roles iniciales
INSERT INTO roles (name, description) VALUES
('admin', 'Administrador del sistema'),
('seller', 'Vendedor de proveedores de energía'),
('user', 'Cliente final');

-- Insertar usuario administrador inicial
-- Contraseña: admin123 (hash bcrypt con cost 12)
INSERT INTO users (username, email, password, first_name, last_name, is_active) VALUES
('admin', 'admin@energy.com', '$2y$12$EhxewlfrYNk8diFqxy/a/uBjfP9J895TkkW3pXCeqIDaXLxYJlPF6', 'Admin', 'Sistema', 1);

-- Asignar rol admin al usuario administrador
INSERT INTO user_roles (user_id, role_id) VALUES (1, 1);

-- Insertar proveedores de ejemplo
INSERT INTO energy_providers (name, description, contact_email, contact_phone, website) VALUES
('Iberdrola', 'Uno de los principales proveedores de energía en España', 'contacto@iberdrola.es', '+34900100100', 'https://www.iberdrola.es'),
('Endesa', 'Proveedor líder de energía eléctrica', 'info@endesa.es', '+34800760000', 'https://www.endesa.com'),
('Naturgy', 'Energía natural y sostenible', 'atencion@naturgy.es', '+34900100251', 'https://www.naturgy.es'),
('Repsol', 'Energía eléctrica y gas', 'clientes@repsol.com', '+34901100100', 'https://www.repsol.es');

-- Insertar planes de ejemplo
INSERT INTO energy_plans (provider_id, name, description, price_per_kwh, monthly_fee, contract_duration_months, renewable_energy_percentage) VALUES
(1, 'Plan Estable', 'Precio fijo durante todo el contrato', 0.1250, 5.00, 12, 30),
(1, 'Plan Verde', 'Energía 100% renovable', 0.1380, 8.00, 24, 100),
(2, 'Plan Ahorro', 'El plan más económico', 0.1180, 3.50, 12, 20),
(2, 'Plan Hogar', 'Ideal para familias', 0.1290, 6.00, 12, 50),
(3, 'Plan Eco', 'Compromiso con el medio ambiente', 0.1350, 7.00, 24, 100),
(4, 'Plan Luz', 'Electricidad sin permanencia', 0.1400, 0.00, 1, 25);

-- Crear índices para optimización
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_user_roles_user ON user_roles(user_id);
CREATE INDEX idx_contracts_client ON contracts(client_id);
CREATE INDEX idx_contracts_seller ON contracts(seller_id);
CREATE INDEX idx_contracts_status ON contracts(status);
CREATE INDEX idx_plans_provider ON energy_plans(provider_id);
