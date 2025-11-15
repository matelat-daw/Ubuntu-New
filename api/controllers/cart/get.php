<?php
/**
 * Get Cart Controller
 * Endpoint: GET /api/controllers/cart/get.php
 * Returns the current user's active cart with all items
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
    $cartQuery = "SELECT id, creation_date, modification_date FROM carts WHERE user_id = ? AND status = 'active' LIMIT 1";
    $cartStmt = $db->prepare($cartQuery);
    $cartStmt->execute([$userId]);
    $cart = $cartStmt->fetch();
    
    if (!$cart) {
        // Return empty cart
        Response::success([
            'cart' => null,
            'items' => [],
            'summary' => [
                'total_items' => 0,
                'total_quantity' => 0,
                'subtotal' => 0,
                'total' => 0
            ]
        ]);
    }
    
    $cartId = $cart['id'];
    
    // Get cart items with product details
    $itemsQuery = "
        SELECT 
            ci.id,
            ci.product_id,
            ci.quantity,
            ci.price as cart_price,
            p.name,
            p.slug,
            p.sku,
            p.price as current_price,
            p.stock,
            p.active,
            p.seller_id,
            u.name as seller_name,
            (SELECT path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN users u ON p.seller_id = u.id
        WHERE ci.cart_id = ?
        ORDER BY ci.creation_date DESC
    ";
    $itemsStmt = $db->prepare($itemsQuery);
    $itemsStmt->execute([$cartId]);
    $items = $itemsStmt->fetchAll();
    
    $cartItems = [];
    $subtotal = 0;
    $hasUnavailableItems = false;
    
    foreach ($items as $item) {
        $itemTotal = $item['quantity'] * $item['cart_price'];
        $subtotal += $itemTotal;
        
        // Check if item is still available
        $isAvailable = $item['active'] && $item['stock'] >= $item['quantity'];
        if (!$isAvailable) {
            $hasUnavailableItems = true;
        }
        
        // Check if price changed
        $priceChanged = $item['cart_price'] != $item['current_price'];
        
        $cartItems[] = [
            'id' => (int)$item['id'],
            'product_id' => (int)$item['product_id'],
            'product_name' => $item['name'],
            'product_slug' => $item['slug'],
            'product_sku' => $item['sku'],
            'seller_id' => (int)$item['seller_id'],
            'seller_name' => $item['seller_name'],
            'quantity' => (int)$item['quantity'],
            'cart_price' => (float)$item['cart_price'],
            'current_price' => (float)$item['current_price'],
            'price_changed' => $priceChanged,
            'stock_available' => (int)$item['stock'],
            'is_available' => $isAvailable,
            'image' => $item['image'],
            'item_total' => (float)$itemTotal
        ];
    }
    
    Response::success([
        'cart' => [
            'id' => (int)$cart['id'],
            'created_at' => $cart['creation_date'],
            'updated_at' => $cart['modification_date'],
            'has_unavailable_items' => $hasUnavailableItems
        ],
        'items' => $cartItems,
        'summary' => [
            'total_items' => count($cartItems),
            'total_quantity' => array_sum(array_column($cartItems, 'quantity')),
            'subtotal' => (float)$subtotal,
            'total' => (float)$subtotal // En el futuro puedes agregar impuestos, descuentos, etc.
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in get cart: " . $e->getMessage());
    Response::serverError('Error al obtener el carrito');
}
