<?php
/**
 * Billing Report Controller
 * Endpoint: GET /api/controllers/reports/billing.php
 * Requires admin authentication
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/ExcelExporter.php';

// Only allow GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

try {
    // Require admin privileges
    $user = AuthMiddleware::requireAdmin();
    
    // Get date range from query parameters
    $startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
    $endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today
    $format = $_GET['format'] ?? 'excel'; // excel or json
    
    // Validate dates
    if (!strtotime($startDate) || !strtotime($endDate)) {
        Response::error('Fechas inválidas', 400);
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Get orders within date range
    $query = "SELECT 
                o.id,
                o.created_at,
                o.status,
                o.total,
                o.tax,
                o.subtotal,
                CONCAT(u.name, ' ', u.surname1) as customer_name,
                u.email as customer_email
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.id
              WHERE DATE(o.created_at) BETWEEN :start_date AND :end_date
              ORDER BY o.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->execute();
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate summary
    $summary = [
        'total_orders' => count($orders),
        'total_revenue' => array_sum(array_column($orders, 'total')),
        'total_tax' => array_sum(array_column($orders, 'tax')),
        'total_subtotal' => array_sum(array_column($orders, 'subtotal')),
        'period' => [
            'start' => $startDate,
            'end' => $endDate
        ]
    ];
    
    // Return based on format
    if ($format === 'excel') {
        // Generate Excel file
        $exporter = new ExcelExporter();
        $filePath = $exporter->generateBillingReport($orders, $startDate, $endDate);
        
        // Download file
        ExcelExporter::download($filePath);
        exit;
        
    } else {
        // Return JSON
        Response::success([
            'summary' => $summary,
            'orders' => $orders
        ], 'Reporte de facturación');
    }
    
} catch (Exception $e) {
    error_log("Error in billing report: " . $e->getMessage());
    Response::serverError('Error al generar el reporte');
}
