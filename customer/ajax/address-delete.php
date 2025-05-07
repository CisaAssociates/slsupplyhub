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
    
    // Verify address belongs to user
    $address = $addressModel->find($_POST['address_id']);
    if (!$address || $address['user_id'] !== $session->getUserId()) {
        echo json_encode(['success' => false, 'message' => 'Address not found']);
        exit;
    }
    
    // Don't allow deletion of default address
    if ($address['is_default']) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete default address. Please set another address as default first.']);
        exit;
    }
    
    // Delete address
    $success = $addressModel->delete($_POST['address_id']);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Address deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete address']);
    }
} catch (Exception $e) {
    error_log("Error deleting address: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the address']);
}