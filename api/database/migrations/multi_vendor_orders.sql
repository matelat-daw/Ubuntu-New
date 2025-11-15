-- Migration: Multi-vendor order support
-- This migration adds support for multiple vendors with different payment methods

-- 1. Create table for seller payment methods
CREATE TABLE IF NOT EXISTS seller_payment_methods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id INT UNSIGNED NOT NULL,
    payment_method ENUM('stripe', 'paypal', 'mercadopago', 'transferencia', 'efectivo') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    config JSON, -- Store API keys, merchant IDs, etc.
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_seller_method (seller_id, payment_method),
    INDEX idx_seller_active (seller_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create order_groups table (groups multiple seller orders from single checkout)
CREATE TABLE IF NOT EXISTS order_groups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT UNSIGNED NOT NULL,
    group_number VARCHAR(50) NOT NULL UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'partial', 'completed', 'cancelled') DEFAULT 'pending',
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_buyer_date (buyer_id, creation_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Add seller_id and order_group_id to orders table
ALTER TABLE orders 
    ADD COLUMN seller_id INT UNSIGNED AFTER user_id,
    ADD COLUMN order_group_id INT UNSIGNED AFTER seller_id,
    ADD COLUMN seller_payment_method VARCHAR(50) AFTER payment_method,
    ADD FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE RESTRICT,
    ADD FOREIGN KEY (order_group_id) REFERENCES order_groups(id) ON DELETE RESTRICT,
    ADD INDEX idx_seller_status (seller_id, status),
    ADD INDEX idx_order_group (order_group_id);

-- 4. Add commission tracking
ALTER TABLE orders
    ADD COLUMN platform_commission_rate DECIMAL(5,2) DEFAULT 0.00 AFTER discount,
    ADD COLUMN platform_commission_amount DECIMAL(10,2) DEFAULT 0.00 AFTER platform_commission_rate,
    ADD COLUMN seller_amount DECIMAL(10,2) AFTER platform_commission_amount;

-- 5. Create payment_transactions table for tracking individual payments
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    order_group_id INT UNSIGNED,
    seller_id INT UNSIGNED NOT NULL,
    buyer_id INT UNSIGNED NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'EUR',
    status ENUM('pending', 'processing', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(255), -- External payment gateway transaction ID
    gateway_response JSON, -- Full response from payment gateway
    error_message TEXT,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (order_group_id) REFERENCES order_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_order (order_id),
    INDEX idx_group (order_group_id),
    INDEX idx_seller (seller_id),
    INDEX idx_buyer_date (buyer_id, creation_date),
    INDEX idx_status (status),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Insert default payment methods for testing (optional)
-- You can add default methods for existing sellers
