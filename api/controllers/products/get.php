<?php
/**
 * Get Single Product Controller
 * Endpoint: GET /api/controllers/products/get.php?id={id} or ?slug={slug}
 * Public endpoint
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../classes/Response.php';

// Only allow GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

try {
    // Get product ID or slug
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;
    
    if (!$id && !$slug) {
        Response::error('Debes proporcionar id o slug del producto', 400);
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Get product
    if ($id) {
        $product = Product::findById($db, $id);
    } else {
        $product = Product::findBySlug($db, $slug);
    }
    
    if (!$product) {
        Response::error('Producto no encontrado', 404);
    }
    
    // Get product data with images
    $productData = $product->toArray(true);
    
    // Get seller info
    $sellerQuery = "
        SELECT user_id, name, email, role, created_at
        FROM users
        WHERE user_id = ?
    ";
    $stmt = $db->prepare($sellerQuery);
    $stmt->bind_param('i', $product->seller_id);
    $stmt->execute();
    $sellerResult = $stmt->get_result();
    $seller = $sellerResult->fetch_assoc();
    $stmt->close();
    
    $productData['seller'] = [
        'id' => (int)$seller['user_id'],
        'name' => $seller['name'],
        'email' => $seller['email'],
        'role' => $seller['role'],
        'member_since' => $seller['created_at']
    ];
    
    // Get category info if available
    if ($product->category_id) {
        $categoryQuery = "
            SELECT id, name, slug, description
            FROM categories
            WHERE id = ?
        ";
        $stmt = $db->prepare($categoryQuery);
        $stmt->bind_param('i', $product->category_id);
        $stmt->execute();
        $categoryResult = $stmt->get_result();
        $category = $categoryResult->fetch_assoc();
        $stmt->close();
        
        if ($category) {
            $productData['category'] = [
                'id' => (int)$category['id'],
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description']
            ];
        }
    }
    
    // Increment views counter (optional)
    $updateViewsQuery = "UPDATE products SET views = views + 1 WHERE id = ?";
    $stmt = $db->prepare($updateViewsQuery);
    $stmt->bind_param('i', $product->id);
    $stmt->execute();
    $stmt->close();
    
    Response::success(['product' => $productData]);
    
} catch (Exception $e) {
    error_log("Error in get product: " . $e->getMessage());
    Response::serverError('Error al obtener el producto');
}
