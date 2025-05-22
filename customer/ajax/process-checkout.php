<?php
require_once __DIR__ . '/../../vendor/autoload.php';
header('Content-Type: application/json');

use SLSupplyHub\Order;
use SLSupplyHub\Cart;
use SLSupplyHub\Address;
use SLSupplyHub\Session;
use SLSupplyHub\Database;

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

    // Get the product's supplier_id from the first cart item
    $productSupplierId = $cart['items'][0]['supplier_id'];
    
    // Debug info
    error_log("Checkout Debug - Product supplier_id: " . $productSupplierId);
    
    // Get database connection
    $db = Database::getInstance();
    
    // IMPORTANT: The orders.supplier_id foreign key references suppliers.user_id
    // We need to find the supplier's user_id regardless of whether product.supplier_id
    // is a reference to suppliers.id or users.id
    
    // First check if this is a supplier.id
    $stmt = $db->executeQuery("
        SELECT s.id, s.user_id, s.business_name, s.status, u.role, u.status as user_status
        FROM suppliers s
        JOIN users u ON s.user_id = u.id
        WHERE s.id = ?
    ", [$productSupplierId]);
    
    $supplier = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if ($supplier) {
        // If found as supplier.id, use the supplier's user_id for the order
        error_log("Checkout Debug - Found supplier by id: " . json_encode($supplier));
        $supplier_id = $supplier['user_id']; // Use user_id to satisfy the foreign key constraint
    } else {
        // If not found as supplier.id, check if it's a user_id
        $stmt = $db->executeQuery("
            SELECT s.id, s.user_id, s.business_name, s.status, u.role, u.status as user_status
            FROM suppliers s
            JOIN users u ON s.user_id = u.id
            WHERE s.user_id = ?
        ", [$productSupplierId]);
        
        $supplier = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($supplier) {
            // If found as user_id, use it directly
            error_log("Checkout Debug - Found supplier by user_id: " . json_encode($supplier));
            $supplier_id = $productSupplierId; // This is already a user_id
        } else {
            error_log("Checkout Debug - Supplier not found by any method");
            throw new \Exception('Supplier not found. Please contact support.');
        }
    }
    
    // Debug info
    error_log("Checkout Debug - Using supplier_id for order: " . $supplier_id);
    
    if ($supplier['status'] !== 'approved') {
        throw new \Exception('This supplier is not currently approved to accept orders.');
    }
    
    if ($supplier['user_status'] !== 'active') {
        throw new \Exception('This supplier account is not currently active.');
    }
    
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
