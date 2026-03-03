<?php
// Modelo de Plan de Energía
class Plan {
    private $conn;
    private $table_name = "energy_plans";

    public $id;
    public $provider_id;
    public $seller_id;
    public $name;
    public $description;
    public $price_per_kwh;
    public $monthly_fee;
    public $contract_duration_months;
    public $renewable_energy_percentage;
    public $features;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los planes
     */
    public function readAll($active_only = false) {
        $query = "SELECT p.*, pr.name as provider_name, 
                  CONCAT(u.first_name, ' ', u.last_name) as seller_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN energy_providers pr ON p.provider_id = pr.id
                  LEFT JOIN users u ON p.seller_id = u.id";
        
        if ($active_only) {
            $query .= " WHERE p.is_active = 1";
        }
        
        $query .= " ORDER BY pr.name ASC, p.price_per_kwh ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Obtener un plan por ID
     */
    public function readOne() {
        $query = "SELECT p.*, pr.name as provider_name,
                  CONCAT(u.first_name, ' ', u.last_name) as seller_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN energy_providers pr ON p.provider_id = pr.id
                  LEFT JOIN users u ON p.seller_id = u.id
                  WHERE p.id = :id 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->provider_id = $row['provider_id'];
            $this->seller_id = $row['seller_id'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->price_per_kwh = $row['price_per_kwh'];
            $this->monthly_fee = $row['monthly_fee'];
            $this->contract_duration_months = $row['contract_duration_months'];
            $this->renewable_energy_percentage = $row['renewable_energy_percentage'];
            $this->features = $row['features'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }

        return false;
    }

    /**
     * Obtener planes por proveedor
     */
    public function getPlansByProvider($provider_id, $active_only = true) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE provider_id = :provider_id";
        
        if ($active_only) {
            $query .= " AND is_active = 1";
        }
        
        $query .= " ORDER BY price_per_kwh ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":provider_id", $provider_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Crear nuevo plan
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET provider_id=:provider_id,
                      seller_id=:seller_id,
                      name=:name,
                      description=:description,
                      price_per_kwh=:price_per_kwh,
                      monthly_fee=:monthly_fee,
                      contract_duration_months=:contract_duration_months,
                      renewable_energy_percentage=:renewable_energy_percentage,
                      features=:features,
                      is_active=:is_active";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $description = $this->description ? htmlspecialchars(strip_tags($this->description)) : null;
        $monthly_fee = $this->monthly_fee !== null ? $this->monthly_fee : 0.00;
        $contract_duration = $this->contract_duration_months !== null ? $this->contract_duration_months : 12;
        $renewable_percentage = $this->renewable_energy_percentage !== null ? $this->renewable_energy_percentage : 0;
        $is_active = $this->is_active !== null ? $this->is_active : 1;
        
        // Convertir features a JSON si es un array
        $features_json = is_array($this->features) ? json_encode($this->features) : $this->features;

        $stmt->bindParam(":provider_id", $this->provider_id);
        $stmt->bindParam(":seller_id", $this->seller_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price_per_kwh", $this->price_per_kwh);
        $stmt->bindParam(":monthly_fee", $monthly_fee);
        $stmt->bindParam(":contract_duration_months", $contract_duration);
        $stmt->bindParam(":renewable_energy_percentage", $renewable_percentage);
        $stmt->bindParam(":features", $features_json);
        $stmt->bindParam(":is_active", $is_active);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Actualizar plan
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name,
                      seller_id=:seller_id,
                      description=:description,
                      price_per_kwh=:price_per_kwh,
                      monthly_fee=:monthly_fee,
                      contract_duration_months=:contract_duration_months,
                      renewable_energy_percentage=:renewable_energy_percentage,
                      features=:features,
                      is_active=:is_active
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $description = $this->description ? htmlspecialchars(strip_tags($this->description)) : null;
        
        // Convertir features a JSON si es un array
        $features_json = is_array($this->features) ? json_encode($this->features) : $this->features;

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":seller_id", $this->seller_id);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price_per_kwh", $this->price_per_kwh);
        $stmt->bindParam(":monthly_fee", $this->monthly_fee);
        $stmt->bindParam(":contract_duration_months", $this->contract_duration_months);
        $stmt->bindParam(":renewable_energy_percentage", $this->renewable_energy_percentage);
        $stmt->bindParam(":features", $features_json);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Eliminar plan
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Obtener plan genérico de un proveedor o crear uno
     */
    public function getOrCreateDefaultPlan($provider_id, $seller_id = null) {
        // Intentar obtener un plan existente del proveedor
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE provider_id = :provider_id 
                  AND is_active = 1 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":provider_id", $provider_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['id'];
        }

        // Crear plan genérico si no existe
        $this->provider_id = $provider_id;
        $this->seller_id = $seller_id;
        $this->name = "Plan Estándar";
        $this->description = "Plan genérico de energía";
        $this->price_per_kwh = 0.15;
        $this->monthly_fee = 0.00;
        $this->contract_duration_months = 12;
        $this->renewable_energy_percentage = 0;
        $this->features = json_encode([]);
        $this->is_active = 1;

        if ($this->create()) {
            return $this->id;
        }

        return null;
    }
}
?>
