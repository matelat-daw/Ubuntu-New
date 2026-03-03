<?php
// Controlador de Planes de Energía
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Plan.php';

class PlanController {
    private $db;
    private $plan;

    public function __construct() {
        global $conn;
        $this->db = $conn;
        $this->plan = new Plan($this->db);
    }

    /**
     * Listar todos los planes
     */
    public function index() {
        $active_only = isset($_GET['active_only']) && $_GET['active_only'] === 'true';
        $stmt = $this->plan->readAll($active_only);
        $plans = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Decodificar features JSON
            if ($row['features']) {
                $row['features'] = json_decode($row['features'], true);
            }
            $plans[] = $row;
        }

        return $this->sendResponse(200, true, "Planes obtenidos exitosamente", $plans);
    }

    /**
     * Obtener un plan específico
     */
    public function show($id) {
        $this->plan->id = $id;

        if ($this->plan->readOne()) {
            $plan_data = [
                'id' => $this->plan->id,
                'provider_id' => $this->plan->provider_id,
                'name' => $this->plan->name,
                'description' => $this->plan->description,
                'price_per_kwh' => $this->plan->price_per_kwh,
                'monthly_fee' => $this->plan->monthly_fee,
                'contract_duration_months' => $this->plan->contract_duration_months,
                'renewable_energy_percentage' => $this->plan->renewable_energy_percentage,
                'features' => $this->plan->features ? json_decode($this->plan->features, true) : null,
                'is_active' => $this->plan->is_active,
                'created_at' => $this->plan->created_at,
                'updated_at' => $this->plan->updated_at
            ];

            return $this->sendResponse(200, true, "Plan encontrado", $plan_data);
        }

        return $this->sendResponse(404, false, "Plan no encontrado");
    }

    /**
     * Crear nuevo plan
     */
    public function store($data) {
        // Validar datos requeridos
        if (empty($data['provider_id']) || empty($data['name']) || !isset($data['price_per_kwh'])) {
            return $this->sendResponse(400, false, "Proveedor, nombre y precio por kWh son requeridos");
        }

        $this->plan->provider_id = $data['provider_id'];
        $this->plan->seller_id = isset($data['seller_id']) ? $data['seller_id'] : null;
        $this->plan->name = $data['name'];
        $this->plan->description = isset($data['description']) ? $data['description'] : null;
        $this->plan->price_per_kwh = $data['price_per_kwh'];
        $this->plan->monthly_fee = isset($data['monthly_fee']) ? $data['monthly_fee'] : 0.00;
        $this->plan->contract_duration_months = isset($data['contract_duration_months']) ? $data['contract_duration_months'] : 12;
        $this->plan->renewable_energy_percentage = isset($data['renewable_energy_percentage']) ? $data['renewable_energy_percentage'] : 0;
        $this->plan->features = isset($data['features']) ? $data['features'] : null;
        $this->plan->is_active = isset($data['is_active']) ? $data['is_active'] : 1;

        if ($this->plan->create()) {
            $plan_data = [
                'id' => $this->plan->id,
                'name' => $this->plan->name
            ];

            return $this->sendResponse(201, true, "Plan creado exitosamente", $plan_data);
        }

        return $this->sendResponse(500, false, "Error al crear plan");
    }

    /**
     * Actualizar plan
     */
    public function update($data, $id) {
        $this->plan->id = $id;

        if (!$this->plan->readOne()) {
            return $this->sendResponse(404, false, "Plan no encontrado");
        }

        // Actualizar campos
        if (isset($data['name'])) {
            $this->plan->name = $data['name'];
        }
        if (isset($data['seller_id'])) {
            $this->plan->seller_id = $data['seller_id'];
        }
        if (isset($data['description'])) {
            $this->plan->description = $data['description'];
        }
        if (isset($data['price_per_kwh'])) {
            $this->plan->price_per_kwh = $data['price_per_kwh'];
        }
        if (isset($data['monthly_fee'])) {
            $this->plan->monthly_fee = $data['monthly_fee'];
        }
        if (isset($data['contract_duration_months'])) {
            $this->plan->contract_duration_months = $data['contract_duration_months'];
        }
        if (isset($data['renewable_energy_percentage'])) {
            $this->plan->renewable_energy_percentage = $data['renewable_energy_percentage'];
        }
        if (isset($data['features'])) {
            $this->plan->features = $data['features'];
        }
        if (isset($data['is_active'])) {
            $this->plan->is_active = $data['is_active'];
        }

        if ($this->plan->update()) {
            return $this->sendResponse(200, true, "Plan actualizado exitosamente");
        }

        return $this->sendResponse(500, false, "Error al actualizar plan");
    }

    /**
     * Eliminar plan
     */
    public function destroy($id) {
        $this->plan->id = $id;

        if (!$this->plan->readOne()) {
            return $this->sendResponse(404, false, "Plan no encontrado");
        }

        if ($this->plan->delete()) {
            return $this->sendResponse(200, true, "Plan eliminado exitosamente");
        }

        return $this->sendResponse(500, false, "Error al eliminar plan");
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
