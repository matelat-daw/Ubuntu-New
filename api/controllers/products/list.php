<?php
/**
 * List Products Controller
 * Endpoint: GET /api/controllers/products/list.php
 * Public endpoint with optional filters
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Response.php';

// Only allow GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

try {
    // Get query parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
    $offset = ($page - 1) * $limit;
    
    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $sellerId = isset($_GET['seller_id']) ? (int)$_GET['seller_id'] : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    $featured = isset($_GET['featured']) ? (int)$_GET['featured'] : null;
    $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
    $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
    $sortBy = $_GET['sort_by'] ?? 'creation_date';
    $sortOrder = strtoupper($_GET['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
    
    // Validate sort_by - use actual column names from database
    $allowedSortFields = ['creation_date', 'price', 'name', 'stock', 'featured'];
    if (!in_array($sortBy, $allowedSortFields)) {
        $sortBy = 'creation_date';
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Build query
    $where = ['p.active = 1']; // Only show active products
    $params = [];
    
    if ($categoryId) {
        $where[] = 'p.category_id = ?';
        $params[] = $categoryId;
    }
    
    if ($sellerId) {
        $where[] = 'p.seller_id = ?';
        $params[] = $sellerId;
    }
    
    if ($featured !== null) {
        $where[] = 'p.featured = ?';
        $params[] = $featured;
    }
    
    if ($search) {
        $where[] = '(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)';
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($minPrice !== null) {
        $where[] = 'p.price >= ?';
        $params[] = $minPrice;
    }
    
    if ($maxPrice !== null) {
        $where[] = 'p.price <= ?';
        $params[] = $maxPrice;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Count total products (PDO version)
    $countQuery = "SELECT COUNT(*) as total FROM products p WHERE {$whereClause}";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($params);
    $totalProducts = $countStmt->fetch()['total'];
    
    // Get products (PDO version)
    $query = "
        SELECT 
            p.*,
            u.name as seller_name,
            u.email as seller_email,
            c.name as category_name,
            (SELECT path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
            (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as image_count
        FROM products p
        LEFT JOIN users u ON p.seller_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE {$whereClause}
        ORDER BY p.{$sortBy} {$sortOrder}
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $db->prepare($query);
    
    // Add limit and offset to params
    $allParams = array_merge($params, [$limit, $offset]);
    $stmt->execute($allParams);
    
    $products = [];
    while ($row = $stmt->fetch()) {
        $products[] = [
            'id' => (int)$row['id'],
            'seller_id' => (int)$row['seller_id'],
            'seller_name' => $row['seller_name'],
            'category_id' => $row['category_id'] ? (int)$row['category_id'] : null,
            'category_name' => $row['category_name'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'sku' => $row['sku'],
            'description' => $row['description'],
            'short_description' => $row['short_description'],
            'price' => (float)$row['price'],
            'compare_price' => $row['compare_price'] ? (float)$row['compare_price'] : null,
            'stock' => (int)$row['stock'],
            'low_stock_threshold' => (int)$row['low_stock_threshold'],
            'featured' => (bool)$row['featured'],
            'active' => (bool)$row['active'],
            'primary_image' => $row['primary_image'],
            'image_count' => (int)$row['image_count'],
            'created_at' => $row['creation_date'],
            'updated_at' => $row['modification_date']
        ];
    }
    
    // Calculate pagination
    $totalPages = ceil($totalProducts / $limit);
    
    // Return response
    Response::success([
        'products' => $products,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total_products' => (int)$totalProducts,
            'total_pages' => $totalPages,
            'has_more' => $page < $totalPages
        ],
        'filters' => [
            'category_id' => $categoryId,
            'seller_id' => $sellerId,
            'search' => $search,
            'featured' => $featured,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in list products: " . $e->getMessage());
    Response::serverError('Error al obtener productos');
}
