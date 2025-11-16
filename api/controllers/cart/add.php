<?php
/**
 * Add Item to Cart Controller
 * Endpoint: POST /api/controllers/cart/add.php
 * Adds a product to the user's cart or updates quantity if already exists
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
    if (!isset($input['product_id'])) {
        Response::error('El ID del producto es requerido');
    }
    
    $productId = (int)$input['product_id'];
    $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
    
    if ($quantity < 1) {
        Response::error('La cantidad debe ser mayor a 0');
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Start transaction for stock reservation
    $db->beginTransaction();
    
    try {
        // Verify product exists and is active (with lock)
        $productQuery = "SELECT id, name, price, stock, reserved_stock, active, seller_id FROM products WHERE id = ? FOR UPDATE";
        $productStmt = $db->prepare($productQuery);
        $productStmt->execute([$productId]);
        $product = $productStmt->fetch();
        
        if (!$product) {
            throw new Exception('Producto no encontrado');
        }
        
        if (!$product['active']) {
            throw new Exception('Este producto no está disponible');
        }
        
        // Prevent sellers from adding their own products
        if ($product['seller_id'] == $userId) {
            throw new Exception('No puedes agregar tus propios productos al carrito');
        }
        
        // Calculate available stock (stock - reserved_stock)
        $availableStock = $product['stock'] - $product['reserved_stock'];
        
        // Check stock availability
        if ($availableStock < $quantity) {
            throw new Exception('Stock insuficiente. Disponible: ' . $availableStock);
        }
    
        // Get or create active cart for user
        $cartQuery = "SELECT id, expires_at FROM carts WHERE user_id = ? AND status = 'active' LIMIT 1";
        $cartStmt = $db->prepare($cartQuery);
        $cartStmt->execute([$userId]);
        $cart = $cartStmt->fetch();
        
        if (!$cart) {
            // Create new cart with expiration date (10 days from now)
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 days'));
            $createCartQuery = "INSERT INTO carts (user_id, status, expires_at) VALUES (?, 'active', ?)";
            $createCartStmt = $db->prepare($createCartQuery);
            $createCartStmt->execute([$userId, $expiresAt]);
            $cartId = $db->lastInsertId();
        } else {
            $cartId = $cart['id'];
            // Update cart expiration and modification date
            $updateCartQuery = "UPDATE carts SET modification_date = CURRENT_TIMESTAMP WHERE id = ?";
            $updateCartStmt = $db->prepare($updateCartQuery);
            $updateCartStmt->execute([$cartId]);
        }
        
        // Check if product already in cart
        $checkQuery = "SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$cartId, $productId]);
        $existingItem = $checkStmt->fetch();
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            
            // Check stock for new quantity
            if ($availableStock < $newQuantity - $existingItem['quantity']) {
                throw new Exception('Stock insuficiente. Disponible: ' . $availableStock . ', en carrito: ' . $existingItem['quantity']);
            }
            
            // Reserve additional stock
            $additionalQty = $quantity;
            $updateStockQuery = "UPDATE products SET reserved_stock = reserved_stock + ? WHERE id = ?";
            $updateStockStmt = $db->prepare($updateStockQuery);
            $updateStockStmt->execute([$additionalQty, $productId]);
            
            $updateQuery = "UPDATE cart_items SET quantity = ?, modification_date = CURRENT_TIMESTAMP WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$newQuantity, $existingItem['id']]);
            
            $message = 'Cantidad actualizada en el carrito';
        } else {
            // Reserve stock for new item
            $updateStockQuery = "UPDATE products SET reserved_stock = reserved_stock + ? WHERE id = ?";
            $updateStockStmt = $db->prepare($updateStockQuery);
            $updateStockStmt->execute([$quantity, $productId]);
            
            // Add new item
            $insertQuery = "INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->execute([$cartId, $productId, $quantity, $product['price']]);
            
            $message = 'Producto agregado al carrito';
        }
        
        // Commit transaction
        $db->commit();
    
        // Get cart summary
        $summaryQuery = "
            SELECT 
                COUNT(*) as total_items,
                SUM(quantity) as total_quantity,
                SUM(quantity * price) as total_price
            FROM cart_items 
            WHERE cart_id = ?
        ";
        $summaryStmt = $db->prepare($summaryQuery);
        $summaryStmt->execute([$cartId]);
        $summary = $summaryStmt->fetch();
        
        Response::success([
            'message' => $message,
            'cart_id' => $cartId,
            'product_name' => $product['name'],
            'quantity' => $quantity,
            'cart_summary' => [
                'total_items' => (int)$summary['total_items'],
                'total_quantity' => (int)$summary['total_quantity'],
                'total_price' => (float)$summary['total_price']
            ]
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error in add to cart: " . $e->getMessage());
        Response::error($e->getMessage());
    }
    
} catch (Exception $e) {
    error_log("Error in add to cart: " . $e->getMessage());
    Response::serverError('Error al agregar producto al carrito');
}
