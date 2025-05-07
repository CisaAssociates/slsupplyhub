<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use SLSupplyHub\Cart;
use SLSupplyHub\Order;
use SLSupplyHub\Product;

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'order_id' => null
];

try {
    // Validate required fields
    $required_fields = [
        'billing_first_name' => 'First Name',
        'billing_last_name' => 'Last Name',
        'billing_email' => 'Email',
        'billing_phone' => 'Phone',
        'billing_address' => 'Address',
        'billing_city' => 'City',
        'billing_province' => 'Province',
        'billing_zip' => 'ZIP Code',
        'payment_method' => 'Payment Method'
    ];

    $errors = [];
    foreach ($required_fields as $field => $label) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $errors[] = "$label is required";
        }
    }

    if (!empty($errors)) {
        throw new Exception(implode("\n", $errors));
    }

    // Validate email format
    if (!filter_var($_POST['billing_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }

    // Validate phone number (simple validation for Philippines format)
    $phone = preg_replace('/\D/', '', $_POST['billing_phone']);
    if (strlen($phone) !== 11) {
        throw new Exception('Please enter a valid 11-digit phone number');
    }

    // Validate ZIP code (4 digits for Philippines)
    $zip = preg_replace('/\D/', '', $_POST['billing_zip']);
    if (strlen($zip) !== 4) {
        throw new Exception('Please enter a valid 4-digit ZIP code');
    }

    // Initialize models
    $cartModel = new Cart();
    $orderModel = new Order();
    $productModel = new Product();

    // Get cart items
    $cart = $cartModel->getCartItems($session->getUserId());
    if (empty($cart['items'])) {
        throw new Exception('Your cart is empty');
    }

    // Verify stock availability for all items
    foreach ($cart['items'] as $item) {
        $product = $productModel->find($item['product_id']);
        if (!$product) {
            throw new Exception("Product '{$item['name']}' is no longer available");
        }
        if ($product['stock'] < $item['quantity']) {
            throw new Exception("Sorry, only {$product['stock']} units of '{$item['name']}' are available");
        }
    }

    // Prepare order data
    $orderData = [
        'user_id' => $session->getUserId(),
        'billing_first_name' => trim($_POST['billing_first_name']),
        'billing_last_name' => trim($_POST['billing_last_name']),
        'billing_email' => trim($_POST['billing_email']),
        'billing_phone' => $phone,
        'billing_address' => trim($_POST['billing_address']),
        'billing_city' => trim($_POST['billing_city']),
        'billing_province' => trim($_POST['billing_province']),
        'billing_zip' => $zip,
        'payment_method' => $_POST['payment_method'],
        'order_notes' => trim($_POST['order_notes'] ?? ''),
        'subtotal' => $cart['total'],
        'shipping' => $cart['shipping'],
        'tax' => $cart['total'] * 0.05,
        'total' => $cart['total'] + $cart['shipping'] + ($cart['total'] * 0.05),
        'items' => $cart['items'],
        'status' => 'pending',
        'payment_status' => $_POST['payment_method'] === 'cod' ? 'pending' : 'awaiting_payment'
    ];

    // Create order
    $orderItems = $cart['items'];
    $result = $orderModel->createOrder($orderData, $orderItems);

    if (!$result || isset($result['error'])) {
        throw new Exception($result['error'] ?? 'Failed to create order');
    }

    $order_id = $result['order_id'];

    // Clear the cart
    $cartModel->clearCart($session->getUserId());

    $response['success'] = true;
    $response['message'] = 'Order placed successfully';
    $response['order_id'] = $order_id;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);