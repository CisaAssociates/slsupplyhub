<?php
require_once '../services.php';
require_once '../services/init.php';
use SLSupplyHub\Order;
use SLSupplyHub\DriverService;

// Include supplier approval check
include 'check-approval.php';

// Get order ID and new status
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$newStatus = isset($_GET['status']) ? $_GET['status'] : '';

if (!$orderId || !$newStatus) {
    $_SESSION['error'] = 'Invalid request parameters';
    header('Location: products.php');
    exit;
}

// Initialize services
$orderService = new Order();
$driverService = new DriverService();

// Verify order belongs to this supplier
$order = $orderService->getOrderDetails($orderId);
if (!$order || $order['supplier_id'] != $_SESSION['supplier_id']) {
    $_SESSION['error'] = 'Order not found or access denied';
    header('Location: products.php');
    exit;
}

// Validate status transition
$validTransitions = [
    'pending' => ['processing', 'cancelled'],
    'processing' => ['ready', 'cancelled'],
    'ready' => ['cancelled'],
    'assigned' => ['cancelled'],
    'picked_up' => ['cancelled'],
];

if (!isset($validTransitions[$order['status']]) || !in_array($newStatus, $validTransitions[$order['status']])) {
    $_SESSION['error'] = 'Invalid status transition';
    header('Location: order-detail.php?id=' . $orderId);
    exit;
}

// Update order status
$result = $orderService->updateOrderStatus($orderId, $newStatus);

if ($result['success']) {
    $_SESSION['success'] = 'Order status updated successfully';
} else {
    $_SESSION['error'] = $result['error'] ?? 'Failed to update order status';
}

// Redirect back to order detail page
header('Location: order-detail.php?id=' . $orderId);
exit;