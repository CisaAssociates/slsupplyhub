<?php
require_once __DIR__ . '/../../vendor/autoload.php';
header('Content-Type: application/json');

use SLSupplyHub\Address;
use SLSupplyHub\Session;

$session = new Session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $addressModel = new Address();
    
    // Prepare address data
    $addressData = [
        'user_id' => $session->getUserId(),
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'street' => $_POST['street'],
        'barangay' => $_POST['barangay'],
        'city' => $_POST['city'],
        'postal_code' => $_POST['postal_code'],
        'is_default' => isset($_POST['is_default']) && $_POST['is_default'] === true ?? false
    ];
    
    // Create address
    $addressId = $addressModel->addAddress($addressData);
    
    if ($addressId) {
        echo json_encode(['success' => true, 'message' => 'Address added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add address']);
    }
} catch (Exception $e) {
    error_log("Error adding address: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding the address']);
}