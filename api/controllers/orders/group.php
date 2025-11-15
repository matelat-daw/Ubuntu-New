<?php
/**
 * Get Order Group Controller
 * Endpoint: GET /api/controllers/orders/group.php?group_id=X
 * Returns all orders within a group with payment status
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

// Only allow GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

try {
    // Verify authentication
    $userData = AuthMiddleware::verifyToken();
    $userId = $userData['user_id'];
    
    // Get group_id
    $groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : null;
    
    if (!$groupId) {
        Response::error('ID de grupo requerido');
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Get order group
    $groupQuery = "
        SELECT 
            id,
            buyer_id,
            group_number,
            total_amount,
            status,
            creation_date,
            modification_date
        FROM order_groups
        WHERE id = ? AND buyer_id = ?
    ";
    $groupStmt = $db->prepare($groupQuery);
    $groupStmt->execute([$groupId, $userId]);
    $group = $groupStmt->fetch();
    
    if (!$group) {
        Response::error('Grupo de órdenes no encontrado', 404);
    }
    
    // Get all orders in group
    $ordersQuery = "
        SELECT 
            o.id,
            o.seller_id,
            o.order_number,
            o.subtotal,
            o.total,
            o.status,
            o.payment_status,
            o.seller_payment_method,
            o.platform_commission_amount,
            o.seller_amount,
            o.creation_date,
            u.name as seller_name,
            u.email as seller_email
        FROM orders o
        JOIN users u ON o.seller_id = u.id
        WHERE o.order_group_id = ?
        ORDER BY o.id
    ";
    $ordersStmt = $db->prepare($ordersQuery);
    $ordersStmt->execute([$groupId]);
    $orders = $ordersStmt->fetchAll();
    
    // Get items for each order
    $ordersWithItems = [];
    
    foreach ($orders as $order) {
        $itemsQuery = "
            SELECT 
                product_id,
                product_name,
                product_sku,
                quantity,
                price,
                total
            FROM order_items
            WHERE order_id = ?
        ";
        $itemsStmt = $db->prepare($itemsQuery);
        $itemsStmt->execute([$order['id']]);
        $items = $itemsStmt->fetchAll();
        
        // Get payment transaction
        $transactionQuery = "
            SELECT 
                id,
                payment_method,
                amount,
                status,
                transaction_id,
                error_message,
                creation_date,
                completed_at
            FROM payment_transactions
            WHERE order_id = ?
            ORDER BY creation_date DESC
            LIMIT 1
        ";
        $transactionStmt = $db->prepare($transactionQuery);
        $transactionStmt->execute([$order['id']]);
        $transaction = $transactionStmt->fetch();
        
        $ordersWithItems[] = [
            'order_id' => (int)$order['id'],
            'order_number' => $order['order_number'],
            'seller' => [
                'id' => (int)$order['seller_id'],
                'name' => $order['seller_name'],
                'email' => $order['seller_email']
            ],
            'amount' => (float)$order['total'],
            'seller_amount' => (float)$order['seller_amount'],
            'platform_commission' => (float)$order['platform_commission_amount'],
            'status' => $order['status'],
            'payment_status' => $order['payment_status'],
            'payment_method' => $order['seller_payment_method'],
            'payment_transaction' => $transaction ? [
                'transaction_id' => $transaction['transaction_id'],
                'status' => $transaction['status'],
                'error_message' => $transaction['error_message'],
                'completed_at' => $transaction['completed_at']
            ] : null,
            'items' => array_map(function($item) {
                return [
                    'product_id' => (int)$item['product_id'],
                    'name' => $item['product_name'],
                    'sku' => $item['product_sku'],
                    'quantity' => (int)$item['quantity'],
                    'price' => (float)$item['price'],
                    'total' => (float)$item['total']
                ];
            }, $items),
            'created_at' => $order['creation_date']
        ];
    }
    
    Response::success([
        'group' => [
            'id' => (int)$group['id'],
            'group_number' => $group['group_number'],
            'total_amount' => (float)$group['total_amount'],
            'status' => $group['status'],
            'created_at' => $group['creation_date'],
            'updated_at' => $group['modification_date']
        ],
        'orders' => $ordersWithItems,
        'summary' => [
            'total_orders' => count($ordersWithItems),
            'total_amount' => (float)$group['total_amount'],
            'paid_orders' => count(array_filter($ordersWithItems, fn($o) => $o['payment_status'] === 'paid')),
            'pending_orders' => count(array_filter($ordersWithItems, fn($o) => $o['payment_status'] === 'pending'))
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in get order group: " . $e->getMessage());
    Response::serverError('Error al obtener grupo de órdenes');
}
