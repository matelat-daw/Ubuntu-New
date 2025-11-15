<?php
/**
 * Cart Count Controller
 * Endpoint: GET /api/controllers/cart/count.php
 * Returns the total number of items in the cart (for badge display)
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
    
    // Get cart item count
    $countQuery = "
        SELECT 
            COUNT(*) as item_count,
            COALESCE(SUM(ci.quantity), 0) as total_quantity
        FROM carts c
        LEFT JOIN cart_items ci ON c.id = ci.cart_id
        WHERE c.user_id = ? AND c.status = 'active'
        GROUP BY c.id
    ";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute([$userId]);
    $result = $countStmt->fetch();
    
    Response::success([
        'item_count' => $result ? (int)$result['item_count'] : 0,
        'total_quantity' => $result ? (int)$result['total_quantity'] : 0
    ]);
    
} catch (Exception $e) {
    error_log("Error in cart count: " . $e->getMessage());
    Response::serverError('Error al obtener contador del carrito');
}
