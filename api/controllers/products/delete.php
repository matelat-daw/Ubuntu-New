<?php
/**
 * Delete Product Controller
 * Endpoint: DELETE /api/controllers/products/delete.php
 * Requires seller authentication (can only delete own products)
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/AuditLogger.php';

// Only allow DELETE method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    Response::error('Método no permitido', 405);
}

try {
    // Require seller privileges
    $user = AuthMiddleware::requireSeller();
    
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        Response::error('JSON inválido');
    }
    
    // Validate product ID
    $productId = $input['id'] ?? null;
    if (!$productId) {
        Response::error('ID de producto requerido', 400);
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Get product
    $product = Product::findById($db, $productId);
    
    if (!$product) {
        Response::error('Producto no encontrado', 404);
    }
    
    // Check ownership (unless admin)
    if ($product->seller_id !== $user['user_id'] && !in_array($user['role'], ['admin', 'manager'])) {
        Response::error('No tienes permiso para eliminar este producto', 403);
    }
    
    $productName = $product->name;
    
    // Soft delete by default
    $product->active = 0;
    if (!$product->update()) {
        Response::serverError('Error al desactivar el producto');
    }
    
    // Log audit
    AuditLogger::log(
        $db,
        $user['user_id'],
        'delete_product',
        'product',
        $product->id,
        "Producto desactivado: {$productName}"
    );
    
    Response::success(null, 'Producto desactivado exitosamente');
    
} catch (Exception $e) {
    error_log("Error in delete product: " . $e->getMessage());
    Response::serverError('Error al eliminar el producto');
}
