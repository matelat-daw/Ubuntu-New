<?php
/**
 * Update Product Controller
 * Endpoint: PUT /api/controllers/products/update.php
 * Requires seller authentication (can only update own products)
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/Validator.php';
require_once __DIR__ . '/../../classes/AuditLogger.php';

// Only allow PUT method
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
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
        Response::error('No tienes permiso para editar este producto', 403);
    }
    
    // Initialize validator
    $validator = new Validator();
    
    // Update fields if provided
    if (isset($input['name'])) {
        $name = trim($input['name']);
        $validator->validateRequired($name, 'name');
        $product->name = $name;
        
        // Regenerate slug if name changed
        $product->slug = Product::generateSlug($name, $db, $productId);
    }
    
    if (isset($input['description'])) {
        $product->description = trim($input['description']);
    }
    
    if (isset($input['short_description'])) {
        $product->short_description = trim($input['short_description']);
    }
    
    if (isset($input['price'])) {
        if (!is_numeric($input['price']) || $input['price'] <= 0) {
            $validator->addError('price', 'El precio debe ser mayor a 0');
        } else {
            $product->price = $input['price'];
        }
    }
    
    if (isset($input['compare_price'])) {
        $product->compare_price = $input['compare_price'] ? (float)$input['compare_price'] : null;
    }
    
    if (isset($input['cost_price'])) {
        $product->cost_price = $input['cost_price'] ? (float)$input['cost_price'] : null;
    }
    
    if (isset($input['stock'])) {
        if (!is_numeric($input['stock']) || $input['stock'] < 0) {
            $validator->addError('stock', 'El stock no puede ser negativo');
        } else {
            $product->stock = $input['stock'];
        }
    }
    
    if (isset($input['low_stock_threshold'])) {
        $product->low_stock_threshold = max(0, (int)$input['low_stock_threshold']);
    }
    
    if (isset($input['category_id'])) {
        $product->category_id = $input['category_id'] ? (int)$input['category_id'] : null;
    }
    
    if (isset($input['weight'])) {
        $product->weight = $input['weight'];
    }
    
    if (isset($input['length'])) {
        $product->length = $input['length'];
    }
    
    if (isset($input['width'])) {
        $product->width = $input['width'];
    }
    
    if (isset($input['height'])) {
        $product->height = $input['height'];
    }
    
    if (isset($input['meta_title'])) {
        $product->meta_title = trim($input['meta_title']);
    }
    
    if (isset($input['meta_description'])) {
        $product->meta_description = trim($input['meta_description']);
    }
    
    if (isset($input['meta_keywords'])) {
        $product->meta_keywords = trim($input['meta_keywords']);
    }
    
    if (isset($input['active'])) {
        $product->active = (int)$input['active'];
    }
    
    if (isset($input['featured'])) {
        $product->featured = (int)$input['featured'];
    }
    
    // Check for validation errors
    if ($validator->hasErrors()) {
        Response::error('Errores de validación', 400, $validator->getErrors());
    }
    
    // Update product
    if (!$product->update()) {
        Response::serverError('Error al actualizar el producto');
    }
    
    // Log audit
    AuditLogger::log(
        $db,
        $user['user_id'],
        'update_product',
        'product',
        $product->id,
        "Producto actualizado: {$product->name}"
    );
    
    // Return updated product
    $productData = $product->toArray(true);
    
    Response::success(['product' => $productData], 'Producto actualizado exitosamente');
    
} catch (Exception $e) {
    error_log("Error in update product: " . $e->getMessage());
    Response::serverError('Error al actualizar el producto');
}
