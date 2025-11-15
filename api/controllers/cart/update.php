<?php
/**
 * Update Cart Item Controller
 * Endpoint: PUT /api/controllers/cart/update.php
 * Updates the quantity of a cart item
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

// Only allow PUT method
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    Response::error('Método no permitido', 405);
}

try {
    // Verify authentication
    $userData = AuthMiddleware::verifyToken();
    $userId = $userData['user_id'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($input['item_id']) || !isset($input['quantity'])) {
        Response::error('El ID del item y la cantidad son requeridos');
    }
    
    $itemId = (int)$input['item_id'];
    $quantity = (int)$input['quantity'];
    
    if ($quantity < 1) {
        Response::error('La cantidad debe ser mayor a 0');
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Verify item belongs to user's cart
    $itemQuery = "
        SELECT ci.id, ci.product_id, p.name, p.stock, p.active
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
    
    if (!$item['active']) {
        Response::error('Este producto ya no está disponible');
    }
    
    // Check stock
    if ($item['stock'] < $quantity) {
        Response::error('Stock insuficiente. Disponible: ' . $item['stock']);
    }
    
    // Update quantity
    $updateQuery = "UPDATE cart_items SET quantity = ?, modification_date = CURRENT_TIMESTAMP WHERE id = ?";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([$quantity, $itemId]);
    
    Response::success([
        'message' => 'Cantidad actualizada',
        'item_id' => $itemId,
        'product_name' => $item['name'],
        'new_quantity' => $quantity
    ]);
    
} catch (Exception $e) {
    error_log("Error in update cart item: " . $e->getMessage());
    Response::serverError('Error al actualizar el carrito');
}
