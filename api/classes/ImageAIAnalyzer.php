<?php
/**
 * Image AI Analyzer Class
 * Uses OpenAI Vision API to analyze product images and select the best one
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use OpenAI;

class ImageAIAnalyzer {
    
    private $client;
    private $apiKey;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Get API key from config or environment
        $this->apiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : getenv('OPENAI_API_KEY');
        
        if (!$this->apiKey) {
            throw new Exception('OpenAI API key not configured');
        }
        
        $this->client = OpenAI::client($this->apiKey);
    }
    
    /**
     * Analyze multiple images and select the best one as primary
     * 
     * @param array $imagePaths Array of image file paths
     * @param string $productName Product name for context
     * @param string $productDescription Product description for context
     * @return array Analysis results with scores and best image index
     */
    public function analyzeBatch($imagePaths, $productName = '', $productDescription = '') {
        $results = [];
        
        foreach ($imagePaths as $index => $imagePath) {
            try {
                $analysis = $this->analyzeImage($imagePath, $productName, $productDescription);
                $results[$index] = $analysis;
            } catch (Exception $e) {
                error_log("Error analyzing image {$imagePath}: " . $e->getMessage());
                $results[$index] = [
                    'score' => 0,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        // Find image with highest score
        $bestIndex = $this->findBestImage($results);
        
        return [
            'analyses' => $results,
            'best_image_index' => $bestIndex,
            'best_score' => $results[$bestIndex]['score'] ?? 0
        ];
    }
    
    /**
     * Analyze a single image using OpenAI Vision API
     * 
     * @param string $imagePath Path to image file
     * @param string $productName Product name
     * @param string $productDescription Product description
     * @return array Analysis result
     */
    public function analyzeImage($imagePath, $productName = '', $productDescription = '') {
        // Read image and convert to base64
        $imageData = file_get_contents($imagePath);
        $base64Image = base64_encode($imageData);
        $mimeType = mime_content_type($imagePath);
        $imageSize = getimagesize($imagePath);
        
        // Create prompt for analysis
        $prompt = $this->createAnalysisPrompt($productName, $productDescription);
        
        // Call OpenAI Vision API
        $response = $this->client->chat()->create([
            'model' => 'gpt-4o-mini', // Updated model name
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$base64Image}"
                            ]
                        ]
                    ]
                ]
            ],
            'max_tokens' => 500
        ]);
        
        // Parse response
        $analysisText = $response->choices[0]->message->content;
        $parsedAnalysis = $this->parseAnalysisResponse($analysisText);
        
        return [
            'score' => $parsedAnalysis['score'],
            'quality' => $parsedAnalysis['quality'],
            'composition' => $parsedAnalysis['composition'],
            'lighting' => $parsedAnalysis['lighting'],
            'focus' => $parsedAnalysis['focus'],
            'background' => $parsedAnalysis['background'],
            'product_visibility' => $parsedAnalysis['product_visibility'],
            'recommendation' => $parsedAnalysis['recommendation'],
            'width' => $imageSize[0] ?? null,
            'height' => $imageSize[1] ?? null,
            'file_size' => filesize($imagePath),
            'full_analysis' => $analysisText
        ];
    }
    
    /**
     * Create analysis prompt for OpenAI
     */
    private function createAnalysisPrompt($productName, $productDescription) {
        return "Analiza esta imagen de producto para e-commerce y evalúa su calidad como imagen principal de venta.

Producto: {$productName}
Descripción: {$productDescription}

Evalúa los siguientes aspectos y proporciona una puntuación de 0-100:

1. **Calidad general** (resolución, nitidez, profesionalismo)
2. **Composición** (encuadre, centrado del producto, proporciones)
3. **Iluminación** (claridad, ausencia de sombras duras, colores naturales)
4. **Enfoque** (producto nítido y bien definido)
5. **Fondo** (limpio, no distrae, apropiado)
6. **Visibilidad del producto** (se ven características importantes)

Responde en el siguiente formato JSON:
{
  \"score\": 85,
  \"quality\": \"alta/media/baja\",
  \"composition\": \"descripción breve\",
  \"lighting\": \"descripción breve\",
  \"focus\": \"descripción breve\",
  \"background\": \"descripción breve\",
  \"product_visibility\": \"descripción breve\",
  \"recommendation\": \"usar como principal / secundaria / mejorar\"
}";
    }
    
    /**
     * Parse AI response to extract scores and details
     */
    private function parseAnalysisResponse($text) {
        // Try to extract JSON from response
        if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return $json;
            }
        }
        
        // Fallback: try to extract score with regex
        $score = 50; // default
        if (preg_match('/score["\']?\s*:\s*(\d+)/', $text, $matches)) {
            $score = (int)$matches[1];
        }
        
        return [
            'score' => $score,
            'quality' => 'media',
            'composition' => 'No se pudo analizar',
            'lighting' => 'No se pudo analizar',
            'focus' => 'No se pudo analizar',
            'background' => 'No se pudo analizar',
            'product_visibility' => 'No se pudo analizar',
            'recommendation' => 'revisar manualmente'
        ];
    }
    
    /**
     * Find the best image based on scores
     */
    private function findBestImage($results) {
        $bestIndex = 0;
        $bestScore = 0;
        
        foreach ($results as $index => $result) {
            $score = $result['score'] ?? 0;
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIndex = $index;
            }
        }
        
        return $bestIndex;
    }
    
    /**
     * Simplified analysis using basic image properties (fallback if no API key)
     */
    public function analyzeImageBasic($imagePath) {
        $imageSize = getimagesize($imagePath);
        $fileSize = filesize($imagePath);
        
        // Calculate score based on image properties
        $score = 0;
        
        // Resolution score (higher is better, up to 40 points)
        $pixels = $imageSize[0] * $imageSize[1];
        if ($pixels >= 2000000) $score += 40; // 2MP+
        elseif ($pixels >= 1000000) $score += 30; // 1MP+
        elseif ($pixels >= 500000) $score += 20; // 500K+
        else $score += 10;
        
        // Aspect ratio score (closer to square is better for products, up to 30 points)
        $ratio = $imageSize[0] / $imageSize[1];
        if ($ratio >= 0.8 && $ratio <= 1.2) $score += 30; // Nearly square
        elseif ($ratio >= 0.7 && $ratio <= 1.4) $score += 20;
        else $score += 10;
        
        // File size score (not too small, not too large, up to 30 points)
        $sizeMB = $fileSize / 1048576;
        if ($sizeMB >= 0.5 && $sizeMB <= 5) $score += 30; // 500KB - 5MB
        elseif ($sizeMB >= 0.2 && $sizeMB <= 10) $score += 20;
        else $score += 10;
        
        return [
            'score' => $score,
            'quality' => $score >= 70 ? 'alta' : ($score >= 40 ? 'media' : 'baja'),
            'width' => $imageSize[0],
            'height' => $imageSize[1],
            'file_size' => $fileSize,
            'recommendation' => $score >= 70 ? 'usar como principal' : 'secundaria',
            'method' => 'basic'
        ];
    }
}
