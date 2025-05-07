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
    
    // Set as default
    $success = $addressModel->setDefault($_POST['address_id']);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Default address updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update default address']);
    }
} catch (Exception $e) {
    error_log("Error setting default address: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating the default address']);
}