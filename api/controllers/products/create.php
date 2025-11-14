<?php
/**
 * Create Product Controller
 * Endpoint: POST /api/controllers/products/create.php
 * Requires seller authentication
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Response.php';
require_once __DIR__ . '/../../classes/Validator.php';
require_once __DIR__ . '/../../classes/AuditLogger.php';
require_once __DIR__ . '/../../classes/ImageAIAnalyzer.php';

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método no permitido', 405);
}

try {
    // Require seller privileges
    $user = AuthMiddleware::requireSeller();
    
    // Get input data (support both JSON and FormData)
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'multipart/form-data') !== false) {
        $input = $_POST;
    } else if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido');
        }
    } else {
        $input = $_POST;
    }
    
    // Initialize validator
    $validator = new Validator();
    
    // Extract and validate required fields
    $name = trim($input['name'] ?? '');
    $description = trim($input['description'] ?? '');
    $short_description = trim($input['short_description'] ?? '');
    $price = $input['price'] ?? 0;
    $stock = $input['stock'] ?? 0;
    $category_id = $input['category_id'] ?? null;
    
    // Validate required fields
    $validator->validateRequired($name, 'name');
    $validator->validateRequired($description, 'description');
    
    if (!is_numeric($price) || $price <= 0) {
        $validator->addError('price', 'El precio debe ser mayor a 0');
    }
    
    if (!is_numeric($stock) || $stock < 0) {
        $validator->addError('stock', 'El stock no puede ser negativo');
    }
    
    // Validate images
    if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
        $validator->addError('images', 'Debes subir al menos una imagen del producto');
    }
    
    // Check for validation errors
    if ($validator->hasErrors()) {
        Response::error('Errores de validación', 400, $validator->getErrors());
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Create product
    $product = new Product($db);
    $product->seller_id = $user['user_id'];
    $product->category_id = $category_id;
    $product->name = $name;
    $product->slug = Product::generateSlug($name, $db);
    $product->sku = Product::generateSKU();
    $product->description = $description;
    $product->short_description = $short_description;
    $product->price = $price;
    $product->compare_price = $input['compare_price'] ?? null;
    $product->cost_price = $input['cost_price'] ?? null;
    $product->stock = $stock;
    $product->low_stock_threshold = $input['low_stock_threshold'] ?? 5;
    $product->weight = $input['weight'] ?? null;
    $product->length = $input['length'] ?? null;
    $product->width = $input['width'] ?? null;
    $product->height = $input['height'] ?? null;
    $product->meta_title = $input['meta_title'] ?? $name;
    $product->meta_description = $input['meta_description'] ?? $short_description;
    $product->meta_keywords = $input['meta_keywords'] ?? null;
    $product->active = isset($input['active']) ? (int)$input['active'] : 1;
    $product->featured = isset($input['featured']) ? (int)$input['featured'] : 0;
    
    if (!$product->create()) {
        Response::serverError('Error al crear el producto');
    }
    
    // Process images
    $uploadedImages = [];
    $uploadErrors = [];
    
    // Create product directory
    $productDir = PRODUCT_UPLOAD_PATH . $product->id;
    if (!is_dir($productDir)) {
        mkdir($productDir, 0755, true);
    }
    
    // Upload all images first
    $files = $_FILES['images'];
    $fileCount = count($files['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $files['tmp_name'][$i];
            $originalName = $files['name'][$i];
            
            // Validate file
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tmpName);
            finfo_close($finfo);
            
            if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
                $uploadErrors[] = "Archivo {$originalName}: tipo no permitido";
                continue;
            }
            
            if ($files['size'][$i] > UPLOAD_MAX_SIZE) {
                $uploadErrors[] = "Archivo {$originalName}: demasiado grande";
                continue;
            }
            
            // Process image
            $image = null;
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = imagecreatefromjpeg($tmpName);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($tmpName);
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($tmpName);
                    break;
            }
            
            if (!$image) {
                $uploadErrors[] = "Archivo {$originalName}: error al procesar";
                continue;
            }
            
            // Get dimensions
            $width = imagesx($image);
            $height = imagesy($image);
            
            // Resize if needed (max 2000x2000 for products)
            $maxSize = 2000;
            if ($width > $maxSize || $height > $maxSize) {
                $ratio = min($maxSize / $width, $maxSize / $height);
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);
                
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
                $width = $newWidth;
                $height = $newHeight;
            }
            
            // Save as WebP
            $filename = 'product_' . $i . '_' . time() . '.webp';
            $filepath = $productDir . '/' . $filename;
            imagewebp($image, $filepath, 90);
            imagedestroy($image);
            
            $uploadedImages[] = [
                'path' => $filepath,
                'relative_path' => 'assets/products/' . $product->id . '/' . $filename,
                'width' => $width,
                'height' => $height,
                'file_size' => filesize($filepath)
            ];
        }
    }
    
    if (empty($uploadedImages)) {
        Response::error('No se pudo subir ninguna imagen', 400, $uploadErrors);
    }
    
    // Analyze images with AI
    $aiAnalysis = null;
    $bestImageIndex = 0;
    
    try {
        if (USE_AI_IMAGE_ANALYSIS) {
            $analyzer = new ImageAIAnalyzer();
            $imagePaths = array_column($uploadedImages, 'path');
            
            $aiAnalysis = $analyzer->analyzeBatch(
                $imagePaths,
                $product->name,
                $product->description
            );
            
            $bestImageIndex = $aiAnalysis['best_image_index'];
            
            error_log("AI Analysis completed. Best image: {$bestImageIndex} with score: {$aiAnalysis['best_score']}");
        } else {
            // Fallback: use basic analysis for all images
            $analyzer = new ImageAIAnalyzer();
            $scores = [];
            
            foreach ($uploadedImages as $index => $imgData) {
                $basicAnalysis = $analyzer->analyzeImageBasic($imgData['path']);
                $scores[$index] = $basicAnalysis['score'];
                $uploadedImages[$index]['ai_analysis'] = $basicAnalysis;
            }
            
            // Find best score
            arsort($scores);
            $bestImageIndex = array_key_first($scores);
            
            error_log("Basic analysis completed. Best image: {$bestImageIndex} with score: {$scores[$bestImageIndex]}");
        }
    } catch (Exception $e) {
        error_log("Image analysis error: " . $e->getMessage());
        // Continue without AI analysis
    }
    
    // Save images to database
    foreach ($uploadedImages as $index => $imgData) {
        $isPrimary = ($index === $bestImageIndex) ? 1 : 0;
        
        $aiScore = null;
        $aiData = null;
        
        if ($aiAnalysis && isset($aiAnalysis['analyses'][$index])) {
            $aiScore = $aiAnalysis['analyses'][$index]['score'];
            $aiData = $aiAnalysis['analyses'][$index];
        } else if (isset($imgData['ai_analysis'])) {
            $aiScore = $imgData['ai_analysis']['score'];
            $aiData = $imgData['ai_analysis'];
        }
        
        $product->addImage(
            $imgData['relative_path'],
            $imgData['width'],
            $imgData['height'],
            $imgData['file_size'],
            $product->name,
            $isPrimary,
            $aiScore,
            $aiData
        );
    }
    
    // Log audit
    AuditLogger::log(
        $db,
        $user['user_id'],
        'create_product',
        'product',
        $product->id,
        "Producto creado: {$product->name} (SKU: {$product->sku})"
    );
    
    // Get product with images
    $productData = $product->toArray(true);
    
    // Add AI analysis summary
    $productData['ai_analysis_summary'] = [
        'enabled' => USE_AI_IMAGE_ANALYSIS,
        'images_analyzed' => count($uploadedImages),
        'best_image_index' => $bestImageIndex,
        'best_score' => $aiAnalysis ? $aiAnalysis['best_score'] : ($uploadedImages[$bestImageIndex]['ai_analysis']['score'] ?? null)
    ];
    
    // Return success response
    Response::success([
        'product' => $productData,
        'upload_errors' => $uploadErrors
    ], 'Producto creado exitosamente', 201);
    
} catch (Exception $e) {
    error_log("Error in create product: " . $e->getMessage());
    Response::serverError('Error al crear el producto: ' . $e->getMessage());
}
