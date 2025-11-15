<?php
/**
 * Cart Preview Controller
 * Endpoint: GET /api/controllers/cart/preview.php
 * Shows cart grouped by seller with totals and available payment methods
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

// Only allow GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

try {
    // Verify authentication
    $userData = AuthMiddleware::verifyToken();
    $userId = $userData['user_id'];
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Get active cart
    $cartQuery = "SELECT id FROM carts WHERE user_id = ? AND status = 'active' LIMIT 1";
    $cartStmt = $db->prepare($cartQuery);
    $cartStmt->execute([$userId]);
    $cart = $cartStmt->fetch();
    
    if (!$cart) {
        Response::error('Carrito vacío', 404);
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
            p.seller_id,
            u.name as seller_name,
            u.email as seller_email,
            (SELECT path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        JOIN users u ON p.seller_id = u.id
        WHERE ci.cart_id = ?
        ORDER BY p.seller_id, ci.creation_date
    ";
    $itemsStmt = $db->prepare($itemsQuery);
    $itemsStmt->execute([$cartId]);
    $items = $itemsStmt->fetchAll();
    
    if (empty($items)) {
        Response::error('Carrito vacío', 404);
    }
    
    // Get payment methods for all sellers
    $sellerIds = array_unique(array_column($items, 'seller_id'));
    $placeholders = str_repeat('?,', count($sellerIds) - 1) . '?';
    
    $paymentMethodsQuery = "
        SELECT 
            seller_id,
            payment_method,
            is_active
        FROM seller_payment_methods
        WHERE seller_id IN ($placeholders) AND is_active = 1
        ORDER BY seller_id, payment_method
    ";
    $paymentStmt = $db->prepare($paymentMethodsQuery);
    $paymentStmt->execute($sellerIds);
    $paymentMethods = $paymentStmt->fetchAll();
    
    // Group payment methods by seller
    $sellerPaymentMethods = [];
    foreach ($paymentMethods as $pm) {
        $sellerPaymentMethods[$pm['seller_id']][] = $pm['payment_method'];
    }
    
    // Group items by seller
    $sellerOrders = [];
    $grandTotal = 0;
    $hasUnavailableItems = false;
    
    foreach ($items as $item) {
        $sellerId = $item['seller_id'];
        
        if (!isset($sellerOrders[$sellerId])) {
            $sellerOrders[$sellerId] = [
                'seller_id' => (int)$sellerId,
                'seller_name' => $item['seller_name'],
                'seller_email' => $item['seller_email'],
                'payment_methods' => $sellerPaymentMethods[$sellerId] ?? [],
                'items' => [],
                'subtotal' => 0,
                'item_count' => 0
            ];
        }
        
        $itemTotal = $item['quantity'] * $item['price'];
        $isAvailable = $item['active'] && $item['stock'] >= $item['quantity'];
        
        if (!$isAvailable) {
            $hasUnavailableItems = true;
        }
        
        $sellerOrders[$sellerId]['items'][] = [
            'cart_item_id' => (int)$item['cart_item_id'],
            'product_id' => (int)$item['product_id'],
            'product_name' => $item['product_name'],
            'sku' => $item['sku'],
            'quantity' => (int)$item['quantity'],
            'price' => (float)$item['price'],
            'stock_available' => (int)$item['stock'],
            'is_available' => $isAvailable,
            'image' => $item['image'],
            'item_total' => (float)$itemTotal
        ];
        
        $sellerOrders[$sellerId]['subtotal'] += $itemTotal;
        $sellerOrders[$sellerId]['item_count']++;
        $grandTotal += $itemTotal;
    }
    
    // Convert to indexed array
    $sellerOrders = array_values($sellerOrders);
    
    // Check for sellers without payment methods
    $sellersWithoutPayment = [];
    foreach ($sellerOrders as $order) {
        if (empty($order['payment_methods'])) {
            $sellersWithoutPayment[] = $order['seller_name'];
        }
    }
    
    Response::success([
        'can_checkout' => !$hasUnavailableItems && empty($sellersWithoutPayment),
        'has_unavailable_items' => $hasUnavailableItems,
        'sellers_without_payment' => $sellersWithoutPayment,
        'seller_orders' => $sellerOrders,
        'summary' => [
            'total_sellers' => count($sellerOrders),
            'total_items' => array_sum(array_column($sellerOrders, 'item_count')),
            'grand_total' => (float)$grandTotal
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in cart preview: " . $e->getMessage());
    Response::serverError('Error al obtener vista previa del carrito');
}
