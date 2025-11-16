-- Migration: Cart expiration and stock management
-- Adds fields and tables for cart expiration and stock alerts

-- 1. Add reserved_stock to products table
ALTER TABLE products
    ADD COLUMN reserved_stock INT DEFAULT 0 AFTER stock,
    ADD COLUMN low_stock_alert_sent BOOLEAN DEFAULT FALSE AFTER low_stock_threshold,
    ADD COLUMN last_stock_alert_date TIMESTAMP NULL AFTER low_stock_alert_sent;

-- 2. Create cart_expiration_emails table to track sent notifications
CREATE TABLE IF NOT EXISTS cart_expiration_emails (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    email_type ENUM('warning_7days', 'final_3days') NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_cart (cart_id),
    INDEX idx_user_type (user_id, email_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create stock_alert_log table to avoid duplicate alerts
CREATE TABLE IF NOT EXISTS stock_alert_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    seller_id INT UNSIGNED NOT NULL,
    stock_level INT NOT NULL,
    alert_sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_seller_date (seller_id, alert_sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Add expires_at to carts table
ALTER TABLE carts
    ADD COLUMN expires_at TIMESTAMP NULL AFTER modification_date,
    ADD INDEX idx_expires (expires_at, status);

-- 5. Update existing active carts to expire in 10 days
UPDATE carts 
SET expires_at = DATE_ADD(creation_date, INTERVAL 10 DAY)
WHERE status = 'active' AND expires_at IS NULL;
