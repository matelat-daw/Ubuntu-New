<?php
// Controlador de Proveedores de Energía
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Provider.php';

class ProviderController {
    private $db;
    private $provider;

    public function __construct() {
        global $conn;
        $this->db = $conn;
        $this->provider = new Provider($this->db);
    }

    /**
     * Listar todos los proveedores
     */
    public function index() {
        $active_only = isset($_GET['active_only']) && $_GET['active_only'] === 'true';
        $stmt = $this->provider->readAll($active_only);
        $providers = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $providers[] = $row;
        }

        return $this->sendResponse(200, true, "Proveedores obtenidos exitosamente", $providers);
    }

    /**
     * Obtener un proveedor específico
     */
    public function show($id) {
        $this->provider->id = $id;

        if ($this->provider->readOne()) {
            $provider_data = [
                'id' => $this->provider->id,
                'name' => $this->provider->name,
                'description' => $this->provider->description,
                'logo' => $this->provider->logo,
                'contact_email' => $this->provider->contact_email,
                'contact_phone' => $this->provider->contact_phone,
                'website' => $this->provider->website,
                'is_active' => $this->provider->is_active,
                'created_at' => $this->provider->created_at,
                'updated_at' => $this->provider->updated_at
            ];

            return $this->sendResponse(200, true, "Proveedor encontrado", $provider_data);
        }

        return $this->sendResponse(404, false, "Proveedor no encontrado");
    }

    /**
     * Crear nuevo proveedor
     */
    public function store($data) {
        // Validar datos requeridos
        if (empty($data['name'])) {
            return $this->sendResponse(400, false, "El nombre del proveedor es requerido");
        }

        $this->provider->name = $data['name'];
        $this->provider->description = isset($data['description']) ? $data['description'] : null;
        $this->provider->logo = isset($data['logo']) ? $data['logo'] : null;
        $this->provider->contact_email = isset($data['contact_email']) ? $data['contact_email'] : null;
        $this->provider->contact_phone = isset($data['contact_phone']) ? $data['contact_phone'] : null;
        $this->provider->website = isset($data['website']) ? $data['website'] : null;
        $this->provider->is_active = isset($data['is_active']) ? $data['is_active'] : 1;

        if ($this->provider->create()) {
            $provider_data = [
                'id' => $this->provider->id,
                'name' => $this->provider->name
            ];

            return $this->sendResponse(201, true, "Proveedor creado exitosamente", $provider_data);
        }

        return $this->sendResponse(500, false, "Error al crear proveedor");
    }

    /**
     * Actualizar proveedor
     */
    public function update($data, $id) {
        $this->provider->id = $id;

        if (!$this->provider->readOne()) {
            return $this->sendResponse(404, false, "Proveedor no encontrado");
        }

        // Actualizar campos
        if (isset($data['name'])) {
            $this->provider->name = $data['name'];
        }
        if (isset($data['description'])) {
            $this->provider->description = $data['description'];
        }
        if (isset($data['logo'])) {
            $this->provider->logo = $data['logo'];
        }
        if (isset($data['contact_email'])) {
            $this->provider->contact_email = $data['contact_email'];
        }
        if (isset($data['contact_phone'])) {
            $this->provider->contact_phone = $data['contact_phone'];
        }
        if (isset($data['website'])) {
            $this->provider->website = $data['website'];
        }
        if (isset($data['is_active'])) {
            $this->provider->is_active = $data['is_active'];
        }

        if ($this->provider->update()) {
            return $this->sendResponse(200, true, "Proveedor actualizado exitosamente");
        }

        return $this->sendResponse(500, false, "Error al actualizar proveedor");
    }

    /**
     * Eliminar proveedor
     */
    public function destroy($id) {
        $this->provider->id = $id;

        if (!$this->provider->readOne()) {
            return $this->sendResponse(404, false, "Proveedor no encontrado");
        }

        if ($this->provider->delete()) {
            return $this->sendResponse(200, true, "Proveedor eliminado exitosamente");
        }

        return $this->sendResponse(500, false, "Error al eliminar proveedor");
    }

    /**
     * Obtener planes de un proveedor
     */
    public function getPlans($id) {
        $this->provider->id = $id;

        if (!$this->provider->readOne()) {
            return $this->sendResponse(404, false, "Proveedor no encontrado");
        }

        $stmt = $this->provider->getPlans();
        $plans = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $plans[] = $row;
        }

        return $this->sendResponse(200, true, "Planes obtenidos exitosamente", $plans);
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
