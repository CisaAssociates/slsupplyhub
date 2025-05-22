<?php
require_once '../services/init.php';
use SLSupplyHub\Order;
use SLSupplyHub\DriverService;

// Include supplier approval check
include 'check-approval.php';

// Check if user is logged in and is a supplier
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'supplier') {
    header('Location: ../auth-login.php');
    exit;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: products.php');
    exit;
}

// Get order ID and driver ID from POST data
$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$driverId = isset($_POST['driver_id']) ? (int)$_POST['driver_id'] : 0;

if (!$orderId || !$driverId) {
    $_SESSION['error'] = 'Invalid request parameters';
    header('Location: products.php');
    exit;
}

// Initialize services
$orderService = new Order();
$driverService = new DriverService();

// Verify order belongs to this supplier and is in pending status
$order = $orderService->getOrderDetails($orderId);
if (!$order || 
    $order['supplier_id'] != $supplierUserId || 
    $order['status'] !== 'pending' ||
    $order['driver_id']) {
    $_SESSION['error'] = 'Order not found or cannot be assigned';
    header('Location: products.php');
    exit;
}

// Verify driver is available
$availableDrivers = $driverService->getAvailableDrivers();
$driverAvailable = false;
foreach ($availableDrivers as $driver) {
    if ($driver['id'] == $driverId) {
        $driverAvailable = true;
        break;
    }
}

if (!$driverAvailable) {
    $_SESSION['error'] = 'Selected driver is not available';
    header('Location: order-detail.php?id=' . $orderId);
    exit;
}

// Assign driver to order
$result = $driverService->assignOrderToDriver($orderId, $driverId);

if ($result['success']) {
    $_SESSION['success'] = 'Driver assigned successfully';
} else {
    $_SESSION['error'] = $result['error'] ?? 'Failed to assign driver';
}

// Redirect back to order detail page
header('Location: order-detail.php?id=' . $orderId);
exit;