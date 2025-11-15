<?php
/**
 * Remove Item from Cart Controller
 * Endpoint: DELETE /api/controllers/cart/remove.php
 * Removes a specific item from the cart
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
    
    // Get item_id from query string
    $itemId = isset($_GET['item_id']) ? (int)$_GET['item_id'] : null;
    
    if (!$itemId) {
        Response::error('El ID del item es requerido');
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Verify item belongs to user's cart
    $itemQuery = "
        SELECT ci.id, p.name
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.id
        JOIN products p ON ci.product_id = p.id
        WHERE ci.id = ? AND c.user_id = ? AND c.status = 'active'
    ";
    $itemStmt = $db->prepare($itemQuery);
    $itemStmt->execute([$itemId, $userId]);
    $item = $itemStmt->fetch();
    
    if (!$item) {
        Response::error('Item no encontrado en tu carrito', 404);
    }
    
    // Delete item
    $deleteQuery = "DELETE FROM cart_items WHERE id = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->execute([$itemId]);
    
    Response::success([
        'message' => 'Producto eliminado del carrito',
        'product_name' => $item['name']
    ]);
    
} catch (Exception $e) {
    error_log("Error in remove from cart: " . $e->getMessage());
    Response::serverError('Error al eliminar producto del carrito');
}
