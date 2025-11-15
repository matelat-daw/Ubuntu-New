<?php
/**
 * Checkout Controller
 * Endpoint: POST /api/controllers/cart/checkout.php
 * Creates multiple orders (one per seller) from cart and initiates payment
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método no permitido', 405);
}

try {
    // Verify authentication
    $userData = AuthMiddleware::verifyToken();
    $userId = $userData['user_id'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($input['shipping_address_id'])) {
        Response::error('La dirección de envío es requerida');
    }
    
    $shippingAddressId = (int)$input['shipping_address_id'];
    $billingAddressId = isset($input['billing_address_id']) ? (int)$input['billing_address_id'] : $shippingAddressId;
    $customerNotes = isset($input['notes']) ? trim($input['notes']) : null;
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Get active cart
        $cartQuery = "SELECT id FROM carts WHERE user_id = ? AND status = 'active' LIMIT 1 FOR UPDATE";
        $cartStmt = $db->prepare($cartQuery);
        $cartStmt->execute([$userId]);
        $cart = $cartStmt->fetch();
        
        if (!$cart) {
            throw new Exception('Carrito vacío');
        }
        
        $cartId = $cart['id'];
        
        // Get cart items grouped by seller
        $itemsQuery = "
            SELECT 
                ci.id as cart_item_id,
                ci.product_id,
                ci.quantity,
                ci.price,
                p.name as product_name,
                p.sku,
                p.stock,
                p.active,
                p.seller_id
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.cart_id = ?
            ORDER BY p.seller_id
            FOR UPDATE
        ";
        $itemsStmt = $db->prepare($itemsQuery);
        $itemsStmt->execute([$cartId]);
        $items = $itemsStmt->fetchAll();
        
        if (empty($items)) {
            throw new Exception('Carrito vacío');
        }
        
        // Validate all items
        foreach ($items as $item) {
            if (!$item['active']) {
                throw new Exception("El producto '{$item['product_name']}' no está disponible");
            }
            if ($item['stock'] < $item['quantity']) {
                throw new Exception("Stock insuficiente para '{$item['product_name']}'. Disponible: {$item['stock']}");
            }
        }
        
        // Get seller payment methods
        $sellerIds = array_unique(array_column($items, 'seller_id'));
        $placeholders = str_repeat('?,', count($sellerIds) - 1) . '?';
        
        $paymentMethodsQuery = "
            SELECT seller_id, payment_method
            FROM seller_payment_methods
            WHERE seller_id IN ($placeholders) AND is_active = 1
        ";
        $paymentStmt = $db->prepare($paymentMethodsQuery);
        $paymentStmt->execute($sellerIds);
        $paymentMethods = $paymentStmt->fetchAll();
        
        // Group by seller
        $sellerPayments = [];
        foreach ($paymentMethods as $pm) {
            if (!isset($sellerPayments[$pm['seller_id']])) {
                $sellerPayments[$pm['seller_id']] = [];
            }
            $sellerPayments[$pm['seller_id']][] = $pm['payment_method'];
        }
        
        // Validate all sellers have payment methods
        foreach ($sellerIds as $sellerId) {
            if (empty($sellerPayments[$sellerId])) {
                throw new Exception("El vendedor ID $sellerId no tiene métodos de pago configurados");
            }
        }
        
        // Create order group
        $groupNumber = 'GRP-' . strtoupper(uniqid());
        $grandTotal = array_sum(array_map(fn($i) => $i['quantity'] * $i['price'], $items));
        
        $groupQuery = "INSERT INTO order_groups (buyer_id, group_number, total_amount, status) VALUES (?, ?, ?, 'pending')";
        $groupStmt = $db->prepare($groupQuery);
        $groupStmt->execute([$userId, $groupNumber, $grandTotal]);
        $orderGroupId = $db->lastInsertId();
        
        // Group items by seller
        $sellerItems = [];
        foreach ($items as $item) {
            $sellerId = $item['seller_id'];
            if (!isset($sellerItems[$sellerId])) {
                $sellerItems[$sellerId] = [];
            }
            $sellerItems[$sellerId][] = $item;
        }
        
        // Create one order per seller
        $createdOrders = [];
        
        foreach ($sellerItems as $sellerId => $sellerProducts) {
            // Calculate seller subtotal
            $subtotal = array_sum(array_map(fn($i) => $i['quantity'] * $i['price'], $sellerProducts));
            
            // Get first available payment method for this seller
            $paymentMethod = $sellerPayments[$sellerId][0];
            
            // Generate unique order number
            $orderNumber = 'ORD-' . strtoupper(uniqid());
            
            // Create order
            $orderQuery = "
                INSERT INTO orders (
                    user_id, seller_id, order_group_id, order_number,
                    shipping_address_id, billing_address_id,
                    subtotal, tax, shipping_cost, discount, total,
                    status, payment_status, payment_method, seller_payment_method,
                    customer_notes, platform_commission_rate, platform_commission_amount, seller_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, 0, ?, 'pending', 'pending', 'multi_vendor', ?, ?, 5.00, ?, ?)
            ";
            
            $platformCommission = $subtotal * 0.05; // 5% commission
            $sellerAmount = $subtotal - $platformCommission;
            
            $orderStmt = $db->prepare($orderQuery);
            $orderStmt->execute([
                $userId, $sellerId, $orderGroupId, $orderNumber,
                $shippingAddressId, $billingAddressId,
                $subtotal, $subtotal,
                $paymentMethod, $customerNotes,
                $platformCommission, $sellerAmount
            ]);
            
            $orderId = $db->lastInsertId();
            
            // Add order items and update stock
            foreach ($sellerProducts as $item) {
                // Insert order item
                $itemQuery = "
                    INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, price, total)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ";
                $itemStmt = $db->prepare($itemQuery);
                $itemTotal = $item['quantity'] * $item['price'];
                $itemStmt->execute([
                    $orderId, $item['product_id'], $item['product_name'], $item['sku'],
                    $item['quantity'], $item['price'], $itemTotal
                ]);
                
                // Update product stock
                $updateStockQuery = "UPDATE products SET stock = stock - ? WHERE id = ?";
                $updateStockStmt = $db->prepare($updateStockQuery);
                $updateStockStmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Create payment transaction
            $transactionQuery = "
                INSERT INTO payment_transactions (
                    order_id, order_group_id, seller_id, buyer_id,
                    payment_method, amount, status
                ) VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ";
            $transactionStmt = $db->prepare($transactionQuery);
            $transactionStmt->execute([
                $orderId, $orderGroupId, $sellerId, $userId,
                $paymentMethod, $subtotal
            ]);
            
            $createdOrders[] = [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'seller_id' => $sellerId,
                'amount' => (float)$subtotal,
                'payment_method' => $paymentMethod,
                'items_count' => count($sellerProducts)
            ];
        }
        
        // Clear cart
        $deleteCartQuery = "DELETE FROM cart_items WHERE cart_id = ?";
        $deleteCartStmt = $db->prepare($deleteCartQuery);
        $deleteCartStmt->execute([$cartId]);
        
        // Update cart status
        $updateCartQuery = "UPDATE carts SET status = 'converted', modification_date = CURRENT_TIMESTAMP WHERE id = ?";
        $updateCartStmt = $db->prepare($updateCartQuery);
        $updateCartStmt->execute([$cartId]);
        
        // Commit transaction
        $db->commit();
        
        Response::success([
            'message' => 'Órdenes creadas exitosamente',
            'order_group_id' => $orderGroupId,
            'group_number' => $groupNumber,
            'total_amount' => (float)$grandTotal,
            'orders' => $createdOrders,
            'total_orders' => count($createdOrders),
            'next_step' => 'payment' // Frontend should redirect to payment page
        ], 201);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error in checkout: " . $e->getMessage());
    Response::error($e->getMessage());
}
