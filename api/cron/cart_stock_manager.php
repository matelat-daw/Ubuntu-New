#!/usr/bin/env php
<?php
/**
 * Cart Expiration and Stock Management Cron Job
 * 
 * This script should run every 48 hours to:
 * 1. Send 7-day warning emails for carts about to expire in 3 days
 * 2. Delete expired carts (10 days old) and release stock
 * 3. Check low stock and notify sellers
 * 
 * Add to crontab (every 48 hours at 2 AM):
 * 0 2 asterisk-slash-2 asterisk asterisk /usr/bin/php /var/www/html/api/cron/cart_stock_manager.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/EmailHelper.php';

$logFile = __DIR__ . '/../logs/cron.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

try {
    logMessage("=== Starting Cart & Stock Management Cron ===");
    
    $database = new Database();
    $db = $database->getConnection();
    
    // ==========================================
    // STEP 1: Send 7-day warning emails
    // ==========================================
    logMessage("Checking carts that will expire in 3 days...");
    
    $warning7DaysQuery = "
        SELECT 
            c.id as cart_id,
            c.user_id,
            c.expires_at,
            u.name,
            u.email,
            COUNT(ci.id) as item_count,
            SUM(ci.quantity * ci.price) as total_value
        FROM carts c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN cart_items ci ON c.id = ci.cart_id
        WHERE c.status = 'active'
          AND c.expires_at BETWEEN NOW() + INTERVAL 3 DAY AND NOW() + INTERVAL 3 DAY + INTERVAL 1 HOUR
          AND NOT EXISTS (
              SELECT 1 FROM cart_expiration_emails 
              WHERE cart_id = c.id AND email_type = 'warning_7days'
          )
        GROUP BY c.id
        HAVING item_count > 0
    ";
    
    $warning7Stmt = $db->prepare($warning7DaysQuery);
    $warning7Stmt->execute();
    $warningCarts = $warning7Stmt->fetchAll();
    
    logMessage("Found " . count($warningCarts) . " carts to send 7-day warning");
    
    foreach ($warningCarts as $cart) {
        try {
            // Get cart items
            $itemsQuery = "
                SELECT p.name, ci.quantity, ci.price
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                WHERE ci.cart_id = ?
            ";
            $itemsStmt = $db->prepare($itemsQuery);
            $itemsStmt->execute([$cart['cart_id']]);
            $items = $itemsStmt->fetchAll();
            
            // Build email content
            $itemsList = "";
            foreach ($items as $item) {
                $itemsList .= "- {$item['name']} (x{$item['quantity']}) - €" . number_format($item['price'] * $item['quantity'], 2) . "\n";
            }
            
            $emailBody = "
Hola {$cart['name']},

Tienes {$cart['item_count']} producto(s) en tu carrito de compras que expirará en 3 días.

🛒 Productos en tu carrito:
{$itemsList}

💰 Total: €" . number_format($cart['total_value'], 2) . "

⏰ Tu carrito será eliminado automáticamente el " . date('d/m/Y H:i', strtotime($cart['expires_at'])) . "

👉 Completa tu compra ahora: " . FRONTEND_URL . "/checkout

Si no deseas estos productos, simplemente ignora este mensaje.

Saludos,
El equipo de " . SITE_NAME;
            
            // Send email
            EmailHelper::sendPlainEmail(
                $cart['email'],
                '⏰ Tu carrito expirará en 3 días',
                $emailBody
            );
            
            // Log sent email
            $logEmailQuery = "INSERT INTO cart_expiration_emails (cart_id, user_id, email_type) VALUES (?, ?, 'warning_7days')";
            $logEmailStmt = $db->prepare($logEmailQuery);
            $logEmailStmt->execute([$cart['cart_id'], $cart['user_id']]);
            
            logMessage("Sent 7-day warning to user #{$cart['user_id']} ({$cart['email']})");
            
        } catch (Exception $e) {
            logMessage("ERROR sending email to user #{$cart['user_id']}: " . $e->getMessage());
        }
    }
    
    // ==========================================
    // STEP 2: Delete expired carts (10 days)
    // ==========================================
    logMessage("Checking expired carts to delete...");
    
    $expiredCartsQuery = "
        SELECT 
            c.id as cart_id,
            c.user_id,
            u.name,
            u.email
        FROM carts c
        JOIN users u ON c.user_id = u.id
        WHERE c.status = 'active'
          AND c.expires_at < NOW()
    ";
    
    $expiredStmt = $db->prepare($expiredCartsQuery);
    $expiredStmt->execute();
    $expiredCarts = $expiredStmt->fetchAll();
    
    logMessage("Found " . count($expiredCarts) . " expired carts to delete");
    
    foreach ($expiredCarts as $cart) {
        try {
            $db->beginTransaction();
            
            // Get items to release stock
            $itemsQuery = "SELECT product_id, quantity FROM cart_items WHERE cart_id = ?";
            $itemsStmt = $db->prepare($itemsQuery);
            $itemsStmt->execute([$cart['cart_id']]);
            $items = $itemsStmt->fetchAll();
            
            $itemCount = count($items);
            
            // Release reserved stock
            foreach ($items as $item) {
                $releaseStockQuery = "UPDATE products SET reserved_stock = GREATEST(0, reserved_stock - ?) WHERE id = ?";
                $releaseStockStmt = $db->prepare($releaseStockQuery);
                $releaseStockStmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Delete cart items
            $deleteItemsQuery = "DELETE FROM cart_items WHERE cart_id = ?";
            $deleteItemsStmt = $db->prepare($deleteItemsQuery);
            $deleteItemsStmt->execute([$cart['cart_id']]);
            
            // Mark cart as abandoned
            $updateCartQuery = "UPDATE carts SET status = 'abandoned', modification_date = CURRENT_TIMESTAMP WHERE id = ?";
            $updateCartStmt = $db->prepare($updateCartQuery);
            $updateCartStmt->execute([$cart['cart_id']]);
            
            $db->commit();
            
            logMessage("Deleted cart #{$cart['cart_id']} for user #{$cart['user_id']}, released {$itemCount} items");
            
            // Send notification email
            try {
                $emailBody = "
Hola {$cart['name']},

Tu carrito de compras ha expirado y los productos han sido eliminados.

Si aún estás interesado en estos productos, puedes agregarlos nuevamente a tu carrito.

👉 Ver productos: " . FRONTEND_URL . "/products

Saludos,
El equipo de " . SITE_NAME;
                
                EmailHelper::sendPlainEmail(
                    $cart['email'],
                    '🛒 Tu carrito ha expirado',
                    $emailBody
                );
                
                logMessage("Sent expiration notification to user #{$cart['user_id']}");
                
            } catch (Exception $e) {
                logMessage("ERROR sending expiration email: " . $e->getMessage());
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            logMessage("ERROR deleting cart #{$cart['cart_id']}: " . $e->getMessage());
        }
    }
    
    // ==========================================
    // STEP 3: Check low stock and notify sellers
    // ==========================================
    logMessage("Checking low stock products...");
    
    $lowStockQuery = "
        SELECT 
            p.id as product_id,
            p.seller_id,
            p.name as product_name,
            p.sku,
            p.stock,
            p.reserved_stock,
            p.low_stock_threshold,
            u.name as seller_name,
            u.email as seller_email
        FROM products p
        JOIN users u ON p.seller_id = u.id
        WHERE p.active = 1
          AND (p.stock - p.reserved_stock) < p.low_stock_threshold
          AND (
              p.low_stock_alert_sent = FALSE 
              OR p.last_stock_alert_date IS NULL 
              OR p.last_stock_alert_date < NOW() - INTERVAL 7 DAY
          )
        ORDER BY p.seller_id, (p.stock - p.reserved_stock)
    ";
    
    $lowStockStmt = $db->prepare($lowStockQuery);
    $lowStockStmt->execute();
    $lowStockProducts = $lowStockStmt->fetchAll();
    
    logMessage("Found " . count($lowStockProducts) . " products with low stock");
    
    // Group by seller
    $sellerProducts = [];
    foreach ($lowStockProducts as $product) {
        $sellerId = $product['seller_id'];
        if (!isset($sellerProducts[$sellerId])) {
            $sellerProducts[$sellerId] = [
                'seller_name' => $product['seller_name'],
                'seller_email' => $product['seller_email'],
                'products' => []
            ];
        }
        $sellerProducts[$sellerId]['products'][] = $product;
    }
    
    // Send email to each seller
    foreach ($sellerProducts as $sellerId => $sellerData) {
        try {
            $productsList = "";
            $productIds = [];
            
            foreach ($sellerData['products'] as $product) {
                $availableStock = $product['stock'] - $product['reserved_stock'];
                $productsList .= "- {$product['product_name']} (SKU: {$product['sku']})\n";
                $productsList .= "  Stock disponible: {$availableStock} (Reservado: {$product['reserved_stock']}, Total: {$product['stock']})\n";
                $productsList .= "  Umbral bajo: {$product['low_stock_threshold']}\n\n";
                
                $productIds[] = $product['product_id'];
            }
            
            $emailBody = "
Hola {$sellerData['seller_name']},

⚠️ Tienes " . count($sellerData['products']) . " producto(s) con stock bajo:

{$productsList}

Por favor, repón el stock lo antes posible para evitar perder ventas.

👉 Gestionar productos: " . FRONTEND_URL . "/seller/products

Saludos,
El equipo de " . SITE_NAME;
            
            EmailHelper::sendPlainEmail(
                $sellerData['seller_email'],
                '⚠️ Alerta de Stock Bajo',
                $emailBody
            );
            
            // Update alert status for all products
            $updateAlertQuery = "
                UPDATE products 
                SET low_stock_alert_sent = TRUE, 
                    last_stock_alert_date = CURRENT_TIMESTAMP 
                WHERE id IN (" . implode(',', array_map('intval', $productIds)) . ")
            ";
            $db->exec($updateAlertQuery);
            
            // Log alert
            foreach ($productIds as $productId) {
                $logAlertQuery = "INSERT INTO stock_alert_log (product_id, seller_id, stock_level) 
                                  SELECT id, seller_id, (stock - reserved_stock) FROM products WHERE id = ?";
                $logAlertStmt = $db->prepare($logAlertQuery);
                $logAlertStmt->execute([$productId]);
            }
            
            logMessage("Sent low stock alert to seller #{$sellerId} ({$sellerData['seller_email']}) for " . count($productIds) . " products");
            
        } catch (Exception $e) {
            logMessage("ERROR sending stock alert to seller #{$sellerId}: " . $e->getMessage());
        }
    }
    
    logMessage("=== Cron Job Completed Successfully ===");
    
} catch (Exception $e) {
    logMessage("FATAL ERROR: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    exit(1);
}
