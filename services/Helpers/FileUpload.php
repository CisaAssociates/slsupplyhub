<?php
namespace SLSupplyHub\Helpers;

class FileUpload {
    private $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
    private $allowedDocTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    private $maxFileSize = 5242880; // 5MB
    private $uploadPath;
    
    public function __construct($uploadPath = null) {
        $this->uploadPath = $uploadPath ?? dirname(__DIR__, 2) . '/uploads';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    public function handleUpload($file, $type = 'image', $customName = null) {
        try {
            // Validate file
            $this->validateFile($file, $type);
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $customName ?? uniqid('file_', true);
            $filename .= '.' . $extension;
            
            // Create subdirectory based on file type
            $subdir = $type === 'image' ? 'images' : 'documents';
            $targetDir = $this->uploadPath . '/' . $subdir;
            
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            
            $targetPath = $targetDir . '/' . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new \Exception('Failed to move uploaded file');
            }
            
            // Return relative path for database storage
            return 'uploads/' . $subdir . '/' . $filename;
            
        } catch (\Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function validateFile($file, $type) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('File upload failed with error code: ' . $file['error']);
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new \Exception('File size exceeds limit of 5MB');
        }
        
        // Verify MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        $allowedTypes = $type === 'image' ? $this->allowedImageTypes : $this->allowedDocTypes;
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new \Exception('Invalid file type');
        }
        
        // Additional security checks
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('Invalid upload attempt');
        }
    }
    
    public function deleteFile($filepath) {
        $fullPath = dirname(__DIR__, 2) . '/' . ltrim($filepath, '/');
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    public function setMaxFileSize($size) {
        $this->maxFileSize = $size;
    }
    
    public function addAllowedType($type, $mimeType) {
        if ($type === 'image') {
            $this->allowedImageTypes[] = $mimeType;
        } else {
            $this->allowedDocTypes[] = $mimeType;
        }
    }
    
    public function getUploadErrors($code) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        return $errors[$code] ?? 'Unknown upload error';
    }
    
    public function validateImageDimensions($file, $maxWidth = 2000, $maxHeight = 2000) {
        $imageInfo = getimagesize($file['tmp_name']);
        
        if ($imageInfo === false) {
            throw new \Exception('Invalid image file');
        }
        
        list($width, $height) = $imageInfo;
        
        if ($width > $maxWidth || $height > $maxHeight) {
            throw new \Exception("Image dimensions exceed maximum allowed (${maxWidth}x${maxHeight})");
        }
        
        return true;
    }
    
    public function resizeImage($sourcePath, $targetPath, $maxWidth = 800, $maxHeight = 800) {
        list($width, $height, $type) = getimagesize($sourcePath);
        
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourcePath);
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new \Exception('Unsupported image type');
        }
        
        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $targetPath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $targetPath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $targetPath);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($newImage);
        
        return true;
    }
}