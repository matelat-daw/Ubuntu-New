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
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Verify item belongs to user's cart and get product info
        $itemQuery = "
            SELECT ci.id, ci.product_id, ci.quantity, p.name
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            JOIN products p ON ci.product_id = p.id
            WHERE ci.id = ? AND c.user_id = ? AND c.status = 'active'
        ";
        $itemStmt = $db->prepare($itemQuery);
        $itemStmt->execute([$itemId, $userId]);
        $item = $itemStmt->fetch();
        
        if (!$item) {
            throw new Exception('Item no encontrado en tu carrito');
        }
        
        // Release reserved stock
        $releaseStockQuery = "UPDATE products SET reserved_stock = GREATEST(0, reserved_stock - ?) WHERE id = ?";
        $releaseStockStmt = $db->prepare($releaseStockQuery);
        $releaseStockStmt->execute([$item['quantity'], $item['product_id']]);
        
        // Delete item
        $deleteQuery = "DELETE FROM cart_items WHERE id = ?";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->execute([$itemId]);
        
        $db->commit();
        
        Response::success([
            'message' => 'Producto eliminado del carrito',
            'product_name' => $item['name']
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error in remove from cart: " . $e->getMessage());
    Response::serverError('Error al eliminar producto del carrito');
}
