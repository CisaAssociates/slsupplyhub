<?php
// Prevent any unwanted output
ob_start();

require_once __DIR__ . '/../../vendor/autoload.php';

use SLSupplyHub\Cart;
use SLSupplyHub\Product;
use SLSupplyHub\Session;

try {
    $session = new Session();
    
    // Validate request
    if (!isset($_POST['action'])) {
        throw new Exception('Invalid request');
    }

    $action = $_POST['action'];
    $customerId = $session->getUserId();

    // Initialize models
    $cartModel = new Cart();
    $productModel = new Product();

    switch ($action) {
        case 'add':
            if (!isset($_POST['product_id'])) {
                throw new Exception('Product ID is required');
            }
            $productId = (int)$_POST['product_id'];
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

            // Verify product exists
            $product = $productModel->find($productId);
            if (!$product) {
                throw new Exception('Product not found');
            }

            // Check stock availability
            if ($quantity > $product['stock']) {
                throw new Exception('Insufficient stock available');
            }

            $result = $cartModel->addToCart($customerId, $productId, $quantity);
            break;

        case 'update':
            if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
                throw new Exception('Product ID and quantity are required');
            }
            $productId = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];

            // Verify product exists
            $product = $productModel->find($productId);
            if (!$product) {
                throw new Exception('Product not found');
            }

            // Check stock availability
            if ($quantity > $product['stock']) {
                throw new Exception('Insufficient stock available');
            }

            $result = $cartModel->updateQuantity($customerId, $productId, $quantity);
            break;

        case 'remove':
            if (!isset($_POST['product_id'])) {
                throw new Exception('Product ID is required');
            }
            $productId = (int)$_POST['product_id'];
            $result = $cartModel->removeFromCart($customerId, $productId);
            break;

        case 'clear':
            $result = $cartModel->clearCart($customerId);
            break;

        default:
            throw new Exception('Invalid action');
    }

    if (isset($result['error'])) {
        throw new Exception($result['error']);
    }

    // Get updated cart info
    $cart = $cartModel->getCartItems($customerId);

    // Clear any output buffer
    ob_clean();
    
    // Set proper JSON header
    header('Content-Type: application/json');

    // Ensure we're sending a proper JSON response
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully',
        'cart_count' => (int)$cart['count'],
        'cart_subtotal' => number_format($cart['total'], 2),
        'cart_shipping' => number_format($cart['shipping'], 2),
        'cart_tax' => number_format($cart['total'] * 0.05, 2),
        'cart_total' => number_format($cart['total'] + $cart['shipping'] + ($cart['total'] * 0.05), 2)
    ]);
    exit;

} catch (Exception $e) {
    // Clear any output buffer
    ob_clean();
    
    // Set proper JSON header
    header('Content-Type: application/json');
    
    // Ensure we're sending a proper JSON response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}