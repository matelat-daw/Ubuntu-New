<?php
// Modelo de Contrato
class Contract {
    private $conn;
    private $table_name = "contracts";

    public $id;
    public $client_id;
    public $seller_id;
    public $plan_id;
    public $start_date;
    public $end_date;
    public $status;
    public $total_amount;
    public $commission_amount;
    public $notes;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los contratos
     */
    public function readAll() {
        $query = "SELECT c.*, 
                         u_client.username as client_name, u_client.email as client_email,
                         u_seller.username as seller_name, u_seller.email as seller_email,
                         p.name as plan_name, pr.name as provider_name
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u_client ON c.client_id = u_client.id
                  LEFT JOIN users u_seller ON c.seller_id = u_seller.id
                  LEFT JOIN energy_plans p ON c.plan_id = p.id
                  LEFT JOIN energy_providers pr ON p.provider_id = pr.id
                  ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Obtener contratos por cliente
     */
    public function getContractsByClient($client_id) {
        $query = "SELECT c.*, 
                         u_seller.username as seller_name,
                         p.name as plan_name, pr.name as provider_name
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u_seller ON c.seller_id = u_seller.id
                  LEFT JOIN energy_plans p ON c.plan_id = p.id
                  LEFT JOIN energy_providers pr ON p.provider_id = pr.id
                  WHERE c.client_id = :client_id
                  ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":client_id", $client_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Obtener contratos por vendedor
     */
    public function getContractsBySeller($seller_id) {
        $query = "SELECT c.*, 
                         u_client.username as client_name, u_client.email as client_email,
                         p.name as plan_name, pr.name as provider_name
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u_client ON c.client_id = u_client.id
                  LEFT JOIN energy_plans p ON c.plan_id = p.id
                  LEFT JOIN energy_providers pr ON p.provider_id = pr.id
                  WHERE c.seller_id = :seller_id
                  ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":seller_id", $seller_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Obtener un contrato por ID
     */
    public function readOne() {
        $query = "SELECT c.*, 
                         u_client.username as client_name, u_client.email as client_email,
                         u_seller.username as seller_name, u_seller.email as seller_email,
                         p.name as plan_name, pr.name as provider_name
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u_client ON c.client_id = u_client.id
                  LEFT JOIN users u_seller ON c.seller_id = u_seller.id
                  LEFT JOIN energy_plans p ON c.plan_id = p.id
                  LEFT JOIN energy_providers pr ON p.provider_id = pr.id
                  WHERE c.id = :id 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->client_id = $row['client_id'];
            $this->seller_id = $row['seller_id'];
            $this->plan_id = $row['plan_id'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->status = $row['status'];
            $this->total_amount = $row['total_amount'];
            $this->commission_amount = $row['commission_amount'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }

        return false;
    }

    /**
     * Crear nuevo contrato
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET client_id=:client_id,
                      seller_id=:seller_id,
                      plan_id=:plan_id,
                      start_date=:start_date,
                      end_date=:end_date,
                      status=:status,
                      total_amount=:total_amount,
                      commission_amount=:commission_amount,
                      notes=:notes";

        $stmt = $this->conn->prepare($query);

        $seller_id = $this->seller_id ? $this->seller_id : null;
        $end_date = $this->end_date ? $this->end_date : null;
        $status = $this->status ? $this->status : 'pending';
        $total_amount = $this->total_amount !== null ? $this->total_amount : 0.00;
        $commission_amount = $this->commission_amount !== null ? $this->commission_amount : 0.00;
        $notes = $this->notes ? htmlspecialchars(strip_tags($this->notes)) : null;

        $stmt->bindParam(":client_id", $this->client_id);
        $stmt->bindParam(":seller_id", $seller_id);
        $stmt->bindParam(":plan_id", $this->plan_id);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":end_date", $end_date);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":total_amount", $total_amount);
        $stmt->bindParam(":commission_amount", $commission_amount);
        $stmt->bindParam(":notes", $notes);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Actualizar contrato
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET status=:status,
                      end_date=:end_date,
                      total_amount=:total_amount,
                      commission_amount=:commission_amount,
                      notes=:notes
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $notes = $this->notes ? htmlspecialchars(strip_tags($this->notes)) : null;

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":commission_amount", $this->commission_amount);
        $stmt->bindParam(":notes", $notes);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Eliminar contrato
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Obtener todos los contratos hechos a través de vendedores (admin)
     */
    public function getAllSellerContracts() {
        $query = "SELECT c.*,
                         COALESCE(u_client.first_name, '[Usuario eliminado]') as client_first_name,
                         COALESCE(u_client.last_name, '') as client_last_name,
                         COALESCE(u_client.email, 'N/A') as client_email,
                         u_seller.first_name as seller_first_name,
                         u_seller.last_name as seller_last_name,
                         u_seller.username as seller_username,
                         p.name as plan_name, pr.name as provider_name
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u_client ON c.client_id = u_client.id
                  INNER JOIN users u_seller ON c.seller_id = u_seller.id
                  LEFT JOIN energy_plans p ON c.plan_id = p.id
                  LEFT JOIN energy_providers pr ON p.provider_id = pr.id
                  WHERE c.seller_id IS NOT NULL
                  ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtener todos los contratos hechos directamente por usuarios sin vendedor (admin)
     */
    public function getAllDirectContracts() {
        $query = "SELECT c.*,
                         COALESCE(u_client.first_name, '[Usuario eliminado]') as client_first_name,
                         COALESCE(u_client.last_name, '') as client_last_name,
                         COALESCE(u_client.email, 'N/A') as client_email,
                         COALESCE(u_client.username, 'N/A') as client_username,
                         p.name as plan_name, pr.name as provider_name
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u_client ON c.client_id = u_client.id
                  LEFT JOIN energy_plans p ON c.plan_id = p.id
                  LEFT JOIN energy_providers pr ON p.provider_id = pr.id
                  WHERE c.seller_id IS NULL
                  ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtener estadísticas de contratos por vendedor
     */
    public function getSellerStats($seller_id) {
        $query = "SELECT 
                      COUNT(*) as total_contracts,
                      COUNT(CASE WHEN status = 'active' THEN 1 END) as active_contracts,
                      COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_contracts,
                      SUM(commission_amount) as total_commission
                  FROM " . $this->table_name . "
                  WHERE seller_id = :seller_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":seller_id", $seller_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
