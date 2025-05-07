<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use SLSupplyHub\Address;

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'addresses' => []
];

try {

    // Get user's addresses
    $addressModel = new Address();
    $addresses = $addressModel->getUserAddresses($session->getUserId());

    if ($addresses) {
        $response['success'] = true;
        $response['addresses'] = $addresses;
    } else {
        $response['message'] = 'No saved addresses found';
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);