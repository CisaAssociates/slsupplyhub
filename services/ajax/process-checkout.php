<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use SLSupplyHub\Session;
use SLSupplyHub\Cart;
use SLSupplyHub\Order;
use SLSupplyHub\Address;

try {
    $session = new Session();
    
    // Check if user is logged in
    if (!$session->getUserId()) {
        throw new Exception('Please login to continue');
    }

    // Validate request data
    $requiredFields = ['first_name', 'last_name', 'phone', 'email', 'city', 'barangay', 'street', 'postal_code'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Please fill in your {$field}");
        }
    }

    $userId = $session->getUserId();
    
    // Save shipping address
    $addressModel = new Address();
    $shippingAddress = [
        'user_id' => $userId,
        'address_line1' => $_POST['street'],
        'city' => $_POST['city'],
        'province' => $_POST['barangay'],
        'postal_code' => $_POST['postal_code'],
        'phone' => $_POST['phone']
    ];
    $addressId = $addressModel->create($shippingAddress);

    // Get cart items
    $cartModel = new Cart();
    $cartItems = $cartModel->getCartItems($userId);
    
    if (empty($cartItems)) {
        throw new Exception('Your cart is empty');
    }

    // Create order
    $orderModel = new Order();
    $orderData = [
        'user_id' => $userId,
        'address_id' => $addressId,
        'status' => 'pending',
        'items' => $cartItems
    ];
    
    $orderId = $orderModel->create($orderData);

    // Clear cart after successful order
    $cartModel->clearCart($userId);

    echo json_encode(['success' => true, 'order_id' => $orderId]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}