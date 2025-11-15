<?php
/**
 * Clear Cart Controller
 * Endpoint: DELETE /api/controllers/cart/clear.php
 * Removes all items from the user's cart
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

// Only allow DELETE method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
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
        Response::success(['message' => 'El carrito ya está vacío']);
    }
    
    // Delete all items from cart
    $deleteQuery = "DELETE FROM cart_items WHERE cart_id = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->execute([$cart['id']]);
    
    $deletedCount = $deleteStmt->rowCount();
    
    Response::success([
        'message' => 'Carrito vaciado',
        'items_removed' => $deletedCount
    ]);
    
} catch (Exception $e) {
    error_log("Error in clear cart: " . $e->getMessage());
    Response::serverError('Error al vaciar el carrito');
}
