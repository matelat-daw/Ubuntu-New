<?php
/**
 * Sales Report Controller
 * Endpoint: GET /api/controllers/reports/sales.php
 * Requires admin or seller authentication
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/ExcelExporter.php';
require_once __DIR__ . '/../../classes/RoleManager.php';

// Only allow GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

try {
    // Authenticate user
    $user = AuthMiddleware::authenticate();
    
    // Check permissions (admin or seller)
    if (!RoleManager::isAdmin($user['role']) && !RoleManager::isSeller($user['role'])) {
        Response::forbidden('Solo administradores y vendedores pueden ver este reporte');
    }
    
    // Get format from query parameters
    $format = $_GET['format'] ?? 'excel'; // excel or json
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Build query based on user role
    $whereClause = '';
    $params = [];
    
    // If seller, only show their products
    if (RoleManager::isSeller($user['role'])) {
        $whereClause = 'WHERE p.seller_id = :seller_id';
        $params[':seller_id'] = $user['user_id'];
    }
    
    // Get products with sales data
    $query = "SELECT 
                p.id,
                p.name,
                p.price,
                p.stock,
                COALESCE(SUM(oi.quantity), 0) as units_sold,
                COALESCE(SUM(oi.quantity * oi.price), 0) as total_sales
              FROM products p
              LEFT JOIN order_items oi ON p.id = oi.product_id
              $whereClause
              GROUP BY p.id
              ORDER BY total_sales DESC";
    
    $stmt = $db->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate summary
    $summary = [
        'total_products' => count($products),
        'total_units_sold' => array_sum(array_column($products, 'units_sold')),
        'total_revenue' => array_sum(array_column($products, 'total_sales'))
    ];
    
    // Return based on format
    if ($format === 'excel') {
        // Generate Excel file
        $exporter = new ExcelExporter();
        $filePath = $exporter->generateSalesReport($products);
        
        // Download file
        ExcelExporter::download($filePath);
        exit;
        
    } else {
        // Return JSON
        Response::success([
            'summary' => $summary,
            'products' => $products
        ], 'Reporte de ventas');
    }
    
} catch (Exception $e) {
    error_log("Error in sales report: " . $e->getMessage());
    Response::serverError('Error al generar el reporte');
}
