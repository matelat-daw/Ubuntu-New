<?php
/**
 * Product Model
 * Handles all product-related database operations
 */

class Product {
    private $db;
    private $table = 'products';
    
    // Product properties
    public $id;
    public $category_id;
    public $seller_id;
    public $name;
    public $slug;
    public $sku;
    public $description;
    public $short_description;
    public $price;
    public $compare_price;
    public $cost_price;
    public $stock;
    public $low_stock_threshold;
    public $weight;
    public $length;
    public $width;
    public $height;
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    public $active;
    public $featured;
    public $creation_date;
    public $modification_date;
    
    /**
     * Constructor
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Create a new product
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
            (category_id, seller_id, name, slug, sku, description, short_description,
             price, compare_price, cost_price, stock, low_stock_threshold,
             weight, length, width, height, meta_title, meta_description, meta_keywords,
             active, featured) 
            VALUES 
            (:category_id, :seller_id, :name, :slug, :sku, :description, :short_description,
             :price, :compare_price, :cost_price, :stock, :low_stock_threshold,
             :weight, :length, :width, :height, :meta_title, :meta_description, :meta_keywords,
             :active, :featured)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':seller_id', $this->seller_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':sku', $this->sku);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':short_description', $this->short_description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':compare_price', $this->compare_price);
        $stmt->bindParam(':cost_price', $this->cost_price);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':low_stock_threshold', $this->low_stock_threshold);
        $stmt->bindParam(':weight', $this->weight);
        $stmt->bindParam(':length', $this->length);
        $stmt->bindParam(':width', $this->width);
        $stmt->bindParam(':height', $this->height);
        $stmt->bindParam(':meta_title', $this->meta_title);
        $stmt->bindParam(':meta_description', $this->meta_description);
        $stmt->bindParam(':meta_keywords', $this->meta_keywords);
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':featured', $this->featured);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Add image to product
     */
    public function addImage($path, $width, $height, $fileSize, $altText = null, $isPrimary = 0, $aiScore = null, $aiAnalysis = null) {
        // If this is set as primary, unset others
        if ($isPrimary) {
            $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = :product_id")
                ->execute([':product_id' => $this->id]);
        }
        
        // Get next sort order
        $sortOrder = $this->db->query("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order 
                                       FROM product_images WHERE product_id = " . $this->id)
            ->fetch(PDO::FETCH_ASSOC)['next_order'];
        
        $query = "INSERT INTO product_images 
            (product_id, path, width, height, file_size, alt_text, sort_order, is_primary, ai_score, ai_analysis) 
            VALUES 
            (:product_id, :path, :width, :height, :file_size, :alt_text, :sort_order, :is_primary, :ai_score, :ai_analysis)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $this->id);
        $stmt->bindParam(':path', $path);
        $stmt->bindParam(':width', $width);
        $stmt->bindParam(':height', $height);
        $stmt->bindParam(':file_size', $fileSize);
        $stmt->bindParam(':alt_text', $altText);
        $stmt->bindParam(':sort_order', $sortOrder);
        $stmt->bindParam(':is_primary', $isPrimary);
        $stmt->bindParam(':ai_score', $aiScore);
        
        $aiAnalysisJson = $aiAnalysis ? json_encode($aiAnalysis) : null;
        $stmt->bindParam(':ai_analysis', $aiAnalysisJson);
        
        return $stmt->execute();
    }
    
    /**
     * Get product images
     */
    public function getImages() {
        $query = "SELECT * FROM product_images 
                  WHERE product_id = :product_id 
                  ORDER BY is_primary DESC, sort_order ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $this->id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Set primary image
     */
    public function setPrimaryImage($imageId) {
        // Unset all primary flags
        $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = :product_id")
            ->execute([':product_id' => $this->id]);
        
        // Set new primary
        $query = "UPDATE product_images SET is_primary = 1 
                  WHERE id = :image_id AND product_id = :product_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':image_id', $imageId);
        $stmt->bindParam(':product_id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Find product by ID
     */
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->hydrate($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate unique slug
     */
    public static function generateSlug($name, $db = null) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        
        if ($db) {
            // Check if slug exists and make unique
            $originalSlug = $slug;
            $counter = 1;
            
            while (true) {
                $query = "SELECT id FROM products WHERE slug = :slug LIMIT 1";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':slug', $slug);
                $stmt->execute();
                
                if ($stmt->rowCount() == 0) {
                    break;
                }
                
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }
        
        return $slug;
    }
    
    /**
     * Generate unique SKU
     */
    public static function generateSKU($prefix = 'PROD') {
        return $prefix . '-' . strtoupper(substr(uniqid(), -8));
    }
    
    /**
     * Hydrate object from array
     */
    private function hydrate($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Get product as array
     */
    public function toArray($includeImages = false) {
        $data = [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'seller_id' => $this->seller_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => (float)$this->price,
            'compare_price' => $this->compare_price ? (float)$this->compare_price : null,
            'cost_price' => $this->cost_price ? (float)$this->cost_price : null,
            'stock' => (int)$this->stock,
            'low_stock_threshold' => (int)$this->low_stock_threshold,
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'active' => (bool)$this->active,
            'featured' => (bool)$this->featured,
            'views' => (int)$this->views,
            'creation_date' => $this->creation_date,
            'modification_date' => $this->modification_date
        ];
        
        if ($includeImages) {
            $data['images'] = $this->getImages();
        }
        
        return $data;
    }
    
    /**
     * Update product
     */
    public function update() {
        $query = "
            UPDATE products SET
                category_id = ?,
                name = ?,
                slug = ?,
                description = ?,
                short_description = ?,
                price = ?,
                compare_price = ?,
                cost_price = ?,
                stock = ?,
                low_stock_threshold = ?,
                weight = ?,
                length = ?,
                width = ?,
                height = ?,
                meta_title = ?,
                meta_description = ?,
                meta_keywords = ?,
                active = ?,
                featured = ?,
                modification_date = CURRENT_TIMESTAMP
            WHERE id = ?
        ";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bind_param(
            'issssdddiiidddsssiiii',
            $this->category_id,
            $this->name,
            $this->slug,
            $this->description,
            $this->short_description,
            $this->price,
            $this->compare_price,
            $this->cost_price,
            $this->stock,
            $this->low_stock_threshold,
            $this->weight,
            $this->length,
            $this->width,
            $this->height,
            $this->meta_title,
            $this->meta_description,
            $this->meta_keywords,
            $this->active,
            $this->featured,
            $this->id
        );
        
        return $stmt->execute();
    }
    
    /**
     * Find product by slug
     */
    public static function findBySlug($db, $slug) {
        $query = "SELECT * FROM products WHERE slug = ? LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $row = $result->fetch_assoc();
        $product = new self($db);
        $product->hydrate($row);
        
        return $product;
    }
}
