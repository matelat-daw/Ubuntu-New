<?php
// Modelo de Proveedor de Energía
class Provider {
    private $conn;
    private $table_name = "energy_providers";

    public $id;
    public $name;
    public $description;
    public $logo;
    public $contact_email;
    public $contact_phone;
    public $website;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los proveedores
     */
    public function readAll($active_only = false) {
        $query = "SELECT id, name, description, logo, contact_email, contact_phone, 
                         website, is_active, created_at, updated_at 
                  FROM " . $this->table_name;
        
        if ($active_only) {
            $query .= " WHERE is_active = 1";
        }
        
        $query .= " ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Obtener un proveedor por ID
     */
    public function readOne() {
        $query = "SELECT id, name, description, logo, contact_email, contact_phone, 
                         website, is_active, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->logo = $row['logo'];
            $this->contact_email = $row['contact_email'];
            $this->contact_phone = $row['contact_phone'];
            $this->website = $row['website'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }

        return false;
    }

    /**
     * Crear nuevo proveedor
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name,
                      description=:description,
                      logo=:logo,
                      contact_email=:contact_email,
                      contact_phone=:contact_phone,
                      website=:website,
                      is_active=:is_active";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $description = $this->description ? htmlspecialchars(strip_tags($this->description)) : null;
        $contact_email = $this->contact_email ? htmlspecialchars(strip_tags($this->contact_email)) : null;
        $contact_phone = $this->contact_phone ? htmlspecialchars(strip_tags($this->contact_phone)) : null;
        $website = $this->website ? htmlspecialchars(strip_tags($this->website)) : null;
        $is_active = $this->is_active !== null ? $this->is_active : 1;

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":logo", $this->logo);
        $stmt->bindParam(":contact_email", $contact_email);
        $stmt->bindParam(":contact_phone", $contact_phone);
        $stmt->bindParam(":website", $website);
        $stmt->bindParam(":is_active", $is_active);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Actualizar proveedor
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name,
                      description=:description,
                      logo=:logo,
                      contact_email=:contact_email,
                      contact_phone=:contact_phone,
                      website=:website,
                      is_active=:is_active
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $description = $this->description ? htmlspecialchars(strip_tags($this->description)) : null;
        $contact_email = $this->contact_email ? htmlspecialchars(strip_tags($this->contact_email)) : null;
        $contact_phone = $this->contact_phone ? htmlspecialchars(strip_tags($this->contact_phone)) : null;
        $website = $this->website ? htmlspecialchars(strip_tags($this->website)) : null;

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":logo", $this->logo);
        $stmt->bindParam(":contact_email", $contact_email);
        $stmt->bindParam(":contact_phone", $contact_phone);
        $stmt->bindParam(":website", $website);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Eliminar proveedor
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Obtener planes de un proveedor
     */
    public function getPlans() {
        require_once __DIR__ . '/Plan.php';
        $plan = new Plan($this->conn);
        return $plan->getPlansByProvider($this->id);
    }

    /**
     * Buscar proveedor por nombre
     */
    public function getByName($name) {
        $query = "SELECT id, name, description, logo, contact_email, contact_phone, 
                         website, is_active, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE LOWER(name) = LOWER(:name) 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->logo = $row['logo'];
            $this->contact_email = $row['contact_email'];
            $this->contact_phone = $row['contact_phone'];
            $this->website = $row['website'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }

        return false;
    }

    /**
     * Buscar o crear proveedor por nombre
     */
    public function findOrCreate($name) {
        // Intentar buscar primero
        if ($this->getByName($name)) {
            return $this->id;
        }

        // Crear si no existe
        $this->name = $name;
        $this->description = "Proveedor de energía";
        $this->logo = null;
        $this->contact_email = null;
        $this->contact_phone = null;
        $this->website = null;
        $this->is_active = 1;

        if ($this->create()) {
            return $this->id;
        }

        return null;
    }
}
?>
