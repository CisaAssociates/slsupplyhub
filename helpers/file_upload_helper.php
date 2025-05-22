<?php
/**
 * Helper functions for file uploads
 */

/**
 * Upload a file to the specified directory
 * 
 * @param array $file The file from $_FILES array
 * @param string $directory The target directory (relative to upload base path)
 * @return string|false The file path if successful, false otherwise
 */
function uploadFile($file, $directory) {
    $uploadBase = __DIR__ . '/../uploads/';
    $targetDir = $uploadBase . $directory . '/';
    
    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $directory . '/' . $filename;
    }
    
    return false;
}

/**
 * Delete a file from the uploads directory
 * 
 * @param string $filePath The relative path to the file
 * @return boolean True if successful, false otherwise
 */
function deleteFile($filePath) {
    $fullPath = __DIR__ . '/../uploads/' . $filePath;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}
