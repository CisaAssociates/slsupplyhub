<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use SLSupplyHub\Product;
use SLSupplyHub\Feedback;
use SLSupplyHub\Session;

$session = new Session();

session_start();
header('Content-Type: application/json');

// Debug logging helper
function logDebug($message, $data = null) {
    $log = "[Reviews] " . $message;
    if ($data !== null) {
        $log .= ": " . json_encode($data);
    }
    error_log($log);
}

// Handle GET request for fetching reviews
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    logDebug("GET request received", $_GET);
    
    $type = $_GET['type'] ?? null;
    $typeId = isset($_GET['type_id']) ? (int)$_GET['type_id'] : null;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    if (!$type || !$typeId) {
        logDebug("Invalid GET parameters", ['type' => $type, 'typeId' => $typeId]);
        echo json_encode([      
            'success' => false,
            'message' => 'Invalid request parameters'
        ]);
        exit;
    }

    $feedbackModel = new Feedback();
    $reviews = $feedbackModel->getFeedback($type, $typeId, $page);
    
    logDebug("Reviews fetched", ['count' => count($reviews['items'] ?? [])]);
    
    echo json_encode([
        'success' => true,
        'reviews' => $reviews['items'],
        'pagination' => [
            'current_page' => $reviews['current_page'],
            'total_pages' => $reviews['last_page'],
            'total_items' => $reviews['total']
        ]
    ]);
    exit;
}

// Handle POST request for submitting reviews
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    logDebug("POST request received", $_POST);

    // Validate required fields
    $requiredFields = ['order_id', 'type', 'id', 'rating', 'comment'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            logDebug("Missing required field", ['field' => $field]);
            echo json_encode([
                'success' => false,
                'message' => 'Missing required field: ' . $field
            ]);
            exit;
        }
    }

    $orderId = (int)$_POST['order_id'];
    $type = $_POST['type'];
    $id = (int)$_POST['id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    $userId = $session->getUserId();

    logDebug("Review data", [
        'orderId' => $orderId,
        'type' => $type,
        'id' => $id,
        'rating' => $rating,
        'userId' => $userId
    ]);

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        logDebug("Invalid rating value", ['rating' => $rating]);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid rating value'
        ]);
        exit;
    }

    // Validate review type
    if (!in_array($type, ['product', 'supplier', 'driver'])) {
        logDebug("Invalid review type", ['type' => $type]);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid review type'
        ]);
        exit;
    }

    // Initialize feedback model
    $feedbackModel = new Feedback();

    // Check if user has already reviewed this item for this order
    if ($feedbackModel->hasOrderFeedback($orderId, $type, $id)) {
        logDebug("Review already exists", [
            'orderId' => $orderId,
            'type' => $type,
            'id' => $id
        ]);
        echo json_encode([
            'success' => false,
            'message' => 'You have already submitted a review for this ' . $type
        ]);
        exit;
    }

    // Create feedback data
    $feedbackData = [
        'customer_id' => $userId,
        'order_id' => $orderId,
        'rating' => $rating,
        'comment' => $comment,
        'type' => $type
    ];

    // Add type-specific ID
    switch ($type) {
        case 'product':
            $feedbackData['product_id'] = $id;
            break;
        case 'supplier':
            $feedbackData['supplier_id'] = $id;
            break;
        case 'driver':
            $feedbackData['driver_id'] = $id;
            break;
    }

    logDebug("Submitting feedback", $feedbackData);

    // Submit review
    $result = $feedbackModel->createFeedback($feedbackData);

    if (isset($result['error'])) {
        logDebug("Feedback submission error", $result);
        echo json_encode([
            'success' => false,
            'message' => is_array($result['error']) ? implode(', ', $result['error']) : $result['error']
        ]);
        exit;
    }

    logDebug("Feedback submitted successfully", $result);
    echo json_encode([
        'success' => true,
        'message' => ucfirst($type) . ' review submitted successfully'
    ]);
    exit;
}

// Invalid request method
logDebug("Invalid request method", ['method' => $_SERVER['REQUEST_METHOD']]);
echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
]);
exit;