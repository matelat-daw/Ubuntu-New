<?php
/**
 * Image Upload Helper Class
 * Centralized image processing for profile images
 */

class ImageUploader {
    
    /**
     * Process and upload profile image
     * Always converts to WebP for optimization
     * 
     * @param array $file $_FILES array element
     * @param int|null $userId User ID (if updating existing user), null for new user
     * @return array ['success' => bool, 'path' => string, 'error' => string]
     */
    public static function uploadProfileImage($file, $userId = null) {
        // Validate file size
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return ['success' => false, 'error' => 'La imagen es demasiado grande (máx 5MB)'];
        }
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            return ['success' => false, 'error' => 'Tipo de archivo no permitido'];
        }
        
        // Process image
        try {
            $image = self::createImageFromFile($file['tmp_name'], $mimeType);
            
            if (!$image) {
                return ['success' => false, 'error' => 'Error al procesar la imagen'];
            }
            
            // Resize if needed (max 500px)
            $image = self::resizeImage($image, 500);
            
            // Determine save path
            if ($userId) {
                // Existing user - save directly to user directory
                $userDir = PROFILE_UPLOAD_PATH . $userId;
                if (!is_dir($userDir)) {
                    mkdir($userDir, 0755, true);
                }
                
                // Delete old profile images if exist (any extension)
                $oldFiles = glob($userDir . '/profile.*');
                foreach ($oldFiles as $oldFile) {
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                
                // Save as WebP for optimization
                $finalPath = $userDir . '/profile.webp';
                imagewebp($image, $finalPath, 90);
                imagedestroy($image);
                
                return [
                    'success' => true,
                    'path' => 'assets/profiles/' . $userId . '/profile.webp'
                ];
                
            } else {
                // New user - save to temp location
                $tempPath = PROFILE_UPLOAD_PATH . 'temp_' . bin2hex(random_bytes(8)) . '.webp';
                
                imagewebp($image, $tempPath, 90);
                imagedestroy($image);
                
                return [
                    'success' => true,
                    'temp_path' => $tempPath,
                    'path' => 'temp'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Image processing error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al procesar la imagen'];
        }
    }
    
    /**
     * Create image resource from file
     */
    private static function createImageFromFile($filePath, $mimeType) {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($filePath);
            case 'image/png':
                return imagecreatefrompng($filePath);
            case 'image/webp':
                return imagecreatefromwebp($filePath);
            default:
                return false;
        }
    }
    
    /**
     * Resize image if larger than max size
     */
    private static function resizeImage($image, $maxSize) {
        $width = imagesx($image);
        $height = imagesy($image);
        
        if ($width > $maxSize || $height > $maxSize) {
            $ratio = min($maxSize / $width, $maxSize / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);
            
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            
            return $resized;
        }
        
        return $image;
    }
}
