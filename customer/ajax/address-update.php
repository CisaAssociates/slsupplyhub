<?php
require_once __DIR__ . '/../../vendor/autoload.php';
header('Content-Type: application/json');

use SLSupplyHub\Address;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $addressModel = new Address();
    
    // Verify address belongs to user
    $address = $addressModel->find($_POST['address_id']);
    if (!$address || $address['user_id'] !== $session->getUserId()) {
        echo json_encode(['success' => false, 'message' => 'Address not found']);
        exit;
    }
    
    // Prepare address data
    $addressData = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'street' => $_POST['street'],
        'barangay' => $_POST['barangay'],
        'city' => $_POST['city'],
        'postal_code' => $_POST['postal_code'],
        'is_default' => isset($_POST['is_default']) && $_POST['is_default'] === 'on'
    ];
    
    // Update address
    $success = $addressModel->update($_POST['address_id'], $addressData);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Address updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update address']);
    }
} catch (Exception $e) {
    error_log("Error updating address: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating the address']);
}