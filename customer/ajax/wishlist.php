<?php
// Prevent any unwanted output
ob_start();

require_once __DIR__ . '/../../vendor/autoload.php';
use SLSupplyHub\Product;
use SLSupplyHub\Database;
use SLSupplyHub\Session;

try {
    $session = new Session();

    // Validate request
    if (!isset($_POST['action']) || !isset($_POST['product_id'])) {
        throw new Exception('Invalid request');
    }

    $action = $_POST['action'];
    $productId = (int)$_POST['product_id'];
    $userId = $session->getUserId();

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Verify product exists
    $productModel = new Product();
    $product = $productModel->find($productId);

    if (!$product) {
        throw new Exception('Product not found');
    }

    switch ($action) {
        case 'add':
            $stmt = $pdo->prepare(
                "INSERT IGNORE INTO wishlists (customer_id, product_id) VALUES (?, ?)"
            );
            $stmt->execute([$userId, $productId]);
            break;

        case 'remove':
            $stmt = $pdo->prepare(
                "DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?"
            );
            $stmt->execute([$userId, $productId]);
            break;

        default:
            throw new Exception('Invalid action');
    }

    // Get updated wishlist count
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM wishlists WHERE customer_id = ?"
    );
    $stmt->execute([$userId]);
    $wishlistCount = $stmt->fetchColumn();

    // Clear any output buffer
    ob_clean();
    
    // Set proper JSON header
    header('Content-Type: application/json');
    
    $response = [
        'success' => true,
        'message' => 'Wishlist updated successfully',
        'wishlist_count' => $wishlistCount
    ];
    echo json_encode($response);
    exit;

} catch (Exception $e) {
    // Clear any output buffer
    ob_clean();
    
    // Set proper JSON header
    header('Content-Type: application/json');
    
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    echo json_encode($response);
    exit;
}