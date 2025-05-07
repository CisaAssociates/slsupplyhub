<?php
require_once __DIR__ . '/../../vendor/autoload.php';
header('Content-Type: application/json');

use SLSupplyHub\Order;
use SLSupplyHub\Cart;
use SLSupplyHub\Address;
use SLSupplyHub\Session;

$session = new Session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $userId = $session->getUserId();
    
    // Verify address exists and belongs to user
    $addressModel = new Address();
    $address = $addressModel->find($_POST['saved_addresses']);
    
    if (!$address || $address['user_id'] !== $userId) {
        throw new \Exception('Invalid delivery address');
    }
    
    // Get cart items
    $cartModel = new Cart();
    $cart = $cartModel->getCartItems($userId);
    
    if (empty($cart['items'])) {
        throw new \Exception('Your cart is empty');
    }

    // Use the user_id directly as the supplier_id since that's what the foreign key expects
    $supplier_id = $cart['items'][0]['supplier_id'];
    
    // Generate unique order number (timestamp + user ID)
    $order_number = 'ORD' . time() . '-' . $userId;
    
    // Calculate totals
    $subtotal = $cart['total'];
    $delivery_fee = $cart['shipping'];
    $tax = $cart['total'] * 0.05;
    $total_amount = $subtotal + $delivery_fee + $tax;
    
    // Create order
    $orderModel = new Order();
    $orderData = [
        'customer_id' => $userId,
        'supplier_id' => $supplier_id,
        'address_id' => $address['id'],
        'order_number' => $order_number,
        'subtotal' => $subtotal,
        'delivery_fee' => $delivery_fee,
        'total_amount' => $total_amount,
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => $_POST['payment_method'] ?? 'cod', // Default to cash if not specified
        'notes' => $_POST['notes'] ?? ''
    ];
    
    $result = $orderModel->createOrder($orderData, $cart['items']);
    
    if (isset($result['success']) && $result['success']) {
        // Clear cart after successful order
        $cartModel->clearCart($userId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully',
            'order_id' => $result['order_id']
        ]);
    } else {
        throw new \Exception(isset($result['error']) ? $result['error'] : 'Failed to create order');
    }
} catch (\Exception $e) {
    error_log("Checkout error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
