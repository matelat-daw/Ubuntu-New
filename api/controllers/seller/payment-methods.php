<?php
/**
 * Seller Payment Methods Controller
 * Endpoint: GET/POST /api/controllers/seller/payment-methods.php
 * Manage seller's accepted payment methods
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Verify authentication
    $userData = AuthMiddleware::verifyToken();
    $userId = $userData['user_id'];
    $userRole = $userData['role'];
    
    // Only sellers can manage payment methods
    if (!str_contains($userRole, 'seller')) {
        Response::error('Solo los vendedores pueden gestionar métodos de pago', 403);
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    if ($method === 'GET') {
        // Get seller's payment methods
        $query = "
            SELECT 
                id,
                payment_method,
                is_active,
                creation_date,
                modification_date
            FROM seller_payment_methods
            WHERE seller_id = ?
            ORDER BY payment_method
        ";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $methods = $stmt->fetchAll();
        
        Response::success([
            'payment_methods' => $methods,
            'available_methods' => ['stripe', 'paypal', 'mercadopago', 'transferencia', 'efectivo']
        ]);
        
    } elseif ($method === 'POST') {
        // Add or update payment method
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['payment_method'])) {
            Response::error('El método de pago es requerido');
        }
        
        $paymentMethod = $input['payment_method'];
        $isActive = isset($input['is_active']) ? (bool)$input['is_active'] : true;
        $config = isset($input['config']) ? json_encode($input['config']) : null;
        
        $validMethods = ['stripe', 'paypal', 'mercadopago', 'transferencia', 'efectivo'];
        if (!in_array($paymentMethod, $validMethods)) {
            Response::error('Método de pago no válido');
        }
        
        // Check if already exists
        $checkQuery = "SELECT id FROM seller_payment_methods WHERE seller_id = ? AND payment_method = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$userId, $paymentMethod]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            // Update
            $updateQuery = "UPDATE seller_payment_methods SET is_active = ?, config = ?, modification_date = CURRENT_TIMESTAMP WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$isActive, $config, $existing['id']]);
            
            Response::success(['message' => 'Método de pago actualizado']);
        } else {
            // Insert
            $insertQuery = "INSERT INTO seller_payment_methods (seller_id, payment_method, is_active, config) VALUES (?, ?, ?, ?)";
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->execute([$userId, $paymentMethod, $isActive, $config]);
            
            Response::success(['message' => 'Método de pago agregado'], 201);
        }
        
    } elseif ($method === 'DELETE') {
        // Remove payment method
        $methodId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$methodId) {
            Response::error('ID del método de pago requerido');
        }
        
        // Verify ownership
        $verifyQuery = "SELECT id FROM seller_payment_methods WHERE id = ? AND seller_id = ?";
        $verifyStmt = $db->prepare($verifyQuery);
        $verifyStmt->execute([$methodId, $userId]);
        
        if (!$verifyStmt->fetch()) {
            Response::error('Método de pago no encontrado', 404);
        }
        
        // Delete
        $deleteQuery = "DELETE FROM seller_payment_methods WHERE id = ?";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->execute([$methodId]);
        
        Response::success(['message' => 'Método de pago eliminado']);
        
    } else {
        Response::error('Método no permitido', 405);
    }
    
} catch (Exception $e) {
    error_log("Error in payment methods: " . $e->getMessage());
    Response::serverError('Error al gestionar métodos de pago');
}
