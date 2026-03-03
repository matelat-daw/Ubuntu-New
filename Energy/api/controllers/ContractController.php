<?php
// Controlador de Contratos
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../models/Contract.php';

class ContractController {
    private $db;
    private $contract;

    public function __construct() {
        global $conn;
        $this->db = $conn;
        $this->contract = new Contract($this->db);
    }

    /**
     * Obtener todos los contratos para el administrador (separados por origen)
     */
    public function adminContracts() {
        $token = $this->getTokenFromRequest();

        if (!$token) {
            return $this->sendResponse(401, false, "No autorizado");
        }

        $decoded = JWT::decode($token);

        if (!$decoded) {
            return $this->sendResponse(401, false, "Token inválido");
        }

        if (!in_array('admin', $decoded['roles'])) {
            return $this->sendResponse(403, false, "Acceso denegado. Se requiere rol de administrador");
        }

        // Contratos gestionados por vendedores
        $stmtSeller = $this->contract->getAllSellerContracts();
        $sellerContracts = [];
        while ($row = $stmtSeller->fetch(PDO::FETCH_ASSOC)) {
            $sellerContracts[] = $row;
        }

        // Contratos realizados directamente por usuarios
        $stmtDirect = $this->contract->getAllDirectContracts();
        $directContracts = [];
        while ($row = $stmtDirect->fetch(PDO::FETCH_ASSOC)) {
            $directContracts[] = $row;
        }

        return $this->sendResponse(200, true, "Contratos obtenidos exitosamente", [
            'seller_contracts' => $sellerContracts,
            'direct_contracts' => $directContracts
        ]);
    }

    /**
     * Listar todos los contratos
     */
    public function index() {
        $stmt = $this->contract->readAll();
        $contracts = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $contracts[] = $row;
        }

        return $this->sendResponse(200, true, "Contratos obtenidos exitosamente", $contracts);
    }

    /**
     * Obtener contratos del usuario autenticado
     */
    public function myContracts() {
        $token = $this->getTokenFromRequest();
        
        if (!$token) {
            return $this->sendResponse(401, false, "No autorizado");
        }

        $decoded = JWT::decode($token);
        
        if (!$decoded) {
            return $this->sendResponse(401, false, "Token inválido");
        }

        $user_id = $decoded['user_id'];
        $roles = $decoded['roles'];

        // Si es vendedor, obtener contratos como vendedor
        if (in_array('seller', $roles)) {
            $stmt = $this->contract->getContractsBySeller($user_id);
        } else {
            // Si es cliente, obtener contratos como cliente
            $stmt = $this->contract->getContractsByClient($user_id);
        }

        $contracts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $contracts[] = $row;
        }

        return $this->sendResponse(200, true, "Contratos obtenidos exitosamente", $contracts);
    }

    /**
     * Obtener estadísticas de vendedor
     */
    public function sellerStats() {
        $token = $this->getTokenFromRequest();
        
        if (!$token) {
            return $this->sendResponse(401, false, "No autorizado");
        }

        $decoded = JWT::decode($token);
        
        if (!$decoded) {
            return $this->sendResponse(401, false, "Token inválido");
        }

        // Verificar que sea vendedor
        if (!in_array('seller', $decoded['roles'])) {
            return $this->sendResponse(403, false, "No tienes permisos de vendedor");
        }

        $user_id = $decoded['user_id'];
        $stats = $this->contract->getSellerStats($user_id);

        return $this->sendResponse(200, true, "Estadísticas obtenidas exitosamente", $stats);
    }

    /**
     * Obtener un contrato específico
     */
    public function show($id) {
        $this->contract->id = $id;

        if ($this->contract->readOne()) {
            $contract_data = [
                'id' => $this->contract->id,
                'client_id' => $this->contract->client_id,
                'seller_id' => $this->contract->seller_id,
                'plan_id' => $this->contract->plan_id,
                'start_date' => $this->contract->start_date,
                'end_date' => $this->contract->end_date,
                'status' => $this->contract->status,
                'total_amount' => $this->contract->total_amount,
                'commission_amount' => $this->contract->commission_amount,
                'notes' => $this->contract->notes,
                'created_at' => $this->contract->created_at,
                'updated_at' => $this->contract->updated_at
            ];

            return $this->sendResponse(200, true, "Contrato encontrado", $contract_data);
        }

        return $this->sendResponse(404, false, "Contrato no encontrado");
    }

    /**
     * Crear nuevo contrato
     */
    public function store($data) {
        // Validar datos requeridos
        if (empty($data['client_id']) || empty($data['plan_id']) || empty($data['start_date'])) {
            return $this->sendResponse(400, false, "Cliente, plan y fecha de inicio son requeridos");
        }

        $this->contract->client_id = $data['client_id'];
        $this->contract->plan_id = $data['plan_id'];
        
        // Si no se proporciona seller_id, obtenerlo del plan
        if (!isset($data['seller_id']) || empty($data['seller_id'])) {
            // Obtener el plan para conseguir el seller_id
            require_once __DIR__ . '/../models/Plan.php';
            $plan = new Plan($this->db);
            $plan->id = $data['plan_id'];
            if ($plan->readOne() && !empty($plan->seller_id)) {
                $this->contract->seller_id = $plan->seller_id;
            } else {
                $this->contract->seller_id = null;
            }
        } else {
            $this->contract->seller_id = $data['seller_id'];
        }
        
        $this->contract->start_date = $data['start_date'];
        $this->contract->end_date = isset($data['end_date']) ? $data['end_date'] : null;
        $this->contract->status = isset($data['status']) ? $data['status'] : 'pending';
        $this->contract->total_amount = isset($data['total_amount']) ? $data['total_amount'] : 0.00;
        $this->contract->commission_amount = isset($data['commission_amount']) ? $data['commission_amount'] : 0.00;
        $this->contract->notes = isset($data['notes']) ? $data['notes'] : null;

        if ($this->contract->create()) {
            $contract_data = [
                'id' => $this->contract->id,
                'seller_id' => $this->contract->seller_id,
                'status' => $this->contract->status
            ];

            return $this->sendResponse(201, true, "Contrato creado exitosamente", $contract_data);
        }

        return $this->sendResponse(500, false, "Error al crear contrato");
    }

    /**
     * Actualizar contrato
     */
    public function update($data, $id) {
        $this->contract->id = $id;

        if (!$this->contract->readOne()) {
            return $this->sendResponse(404, false, "Contrato no encontrado");
        }

        // Actualizar campos
        if (isset($data['status'])) {
            $this->contract->status = $data['status'];
        }
        if (isset($data['end_date'])) {
            $this->contract->end_date = $data['end_date'];
        }
        if (isset($data['total_amount'])) {
            $this->contract->total_amount = $data['total_amount'];
        }
        if (isset($data['commission_amount'])) {
            $this->contract->commission_amount = $data['commission_amount'];
        }
        if (isset($data['notes'])) {
            $this->contract->notes = $data['notes'];
        }

        if ($this->contract->update()) {
            return $this->sendResponse(200, true, "Contrato actualizado exitosamente");
        }

        return $this->sendResponse(500, false, "Error al actualizar contrato");
    }

    /**
     * Eliminar contrato
     */
    public function destroy($id) {
        $this->contract->id = $id;

        if (!$this->contract->readOne()) {
            return $this->sendResponse(404, false, "Contrato no encontrado");
        }

        if ($this->contract->delete()) {
            return $this->sendResponse(200, true, "Contrato eliminado exitosamente");
        }

        return $this->sendResponse(500, false, "Error al eliminar contrato");
    }

    /**
     * Obtener token del request
     */
    private function getTokenFromRequest() {
        // Intentar obtener de cookie primero
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }

        // Intentar obtener de header Authorization
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Enviar respuesta JSON
     */
    private function sendResponse($code, $success, $message, $data = null) {
        http_response_code($code);
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
    }
}
?>
