<?php
require_once '../config/database.php';
require_once '../helpers/file_upload_helper.php';
require_once '../helpers/email_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $db->beginTransaction();
    
    // Basic supplier information
    $stmt = $db->prepare("INSERT INTO suppliers (name, email, phone, address, city, password, business_permit_file, tax_certificate_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $businessPermitFile = uploadFile($_FILES['business_permit'], 'documents/permits');
    $taxCertificateFile = uploadFile($_FILES['tax_certificate'], 'documents/certificates');
    
    $stmt->execute([
        $_POST['name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['city'],
        $hashedPassword,
        $businessPermitFile,
        $taxCertificateFile
    ]);
    
    $supplierId = $db->lastInsertId();
    
    // Business information
    $operatingHours = [];
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    foreach ($days as $day) {
        if (isset($_POST[$day . '-open'])) {
            $operatingHours[$day] = [
                'open' => $_POST[$day . '-open'] === 'on',
                'opening_time' => $_POST[$day . '-opening'],
                'closing_time' => $_POST[$day . '-closing']
            ];
        }
    }
    
    $stmt = $db->prepare("INSERT INTO supplier_business_info (supplier_id, business_type, shop_name, shop_description, operating_hours, delivery_areas, return_policy, cod_areas, min_processing_days, max_processing_days) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $supplierId,
        $_POST['business_type'],
        $_POST['shop_name'],
        $_POST['shop_description'],
        json_encode($operatingHours),
        $_POST['delivery_areas'],
        $_POST['return_policy'],
        $_POST['cod_areas'],
        $_POST['min_processing_days'],
        $_POST['max_processing_days']
    ]);
    
    // Store photos
    if (isset($_FILES['store_photos'])) {
        $stmt = $db->prepare("INSERT INTO supplier_photos (supplier_id, photo_path) VALUES (?, ?)");
        
        foreach ($_FILES['store_photos']['tmp_name'] as $key => $tmp_name) {
            $photoFile = [
                'name' => $_FILES['store_photos']['name'][$key],
                'type' => $_FILES['store_photos']['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $_FILES['store_photos']['error'][$key],
                'size' => $_FILES['store_photos']['size'][$key]
            ];
            
            $photoPath = uploadFile($photoFile, 'images/stores');
            if ($photoPath) {
                $stmt->execute([$supplierId, $photoPath]);
            }
        }
    }
    
    // Send confirmation email
    $emailContent = file_get_contents('../email_templates/supplier_application.php');
    $emailContent = str_replace('{{SHOP_NAME}}', $_POST['shop_name'], $emailContent);
    sendEmail($_POST['email'], 'Supplier Application Received', $emailContent);
    
    // Send admin notification
    $adminEmailContent = file_get_contents('../email_templates/admin_notification.php');
    $adminEmailContent = str_replace('{{SHOP_NAME}}', $_POST['shop_name'], $adminEmailContent);
    sendEmail(ADMIN_EMAIL, 'New Supplier Application', $adminEmailContent);
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully'
    ]);
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your application'
    ]);
}
