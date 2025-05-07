<?php
include '../partials/main.php';
use SLSupplyHub\Order;
use SLSupplyHub\Feedback;
use SLSupplyHub\DriverService;

// Initialize models
$orderModel = new Order();
$feedbackModel = new Feedback();
$driverService = new DriverService();

// Get order ID from URL
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get order details
$order = $orderModel->getOrderDetails($orderId);
if (!$order || $order['supplier_id'] != $_SESSION['supplier_id']) {
    header('Location: index.php');
    exit;
}

// Get available drivers if order is pending
$availableDrivers = [];
if ($order['status'] === 'pending') {
    $availableDrivers = $driverService->getAvailableDrivers();
}

$title = "Order #" . $order['order_number'];
?>

<head>
    <?php include '../partials/title-meta.php'; ?>
    <?php include '../partials/head-css.php'; ?>
</head>

<body>
    <div id="wrapper">
        <?php include 'sidenav.php'; ?>
        <div class="content-page">
            <?php include '../partials/topbar.php'; ?>
            
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <h4>Order Details</h4>
                                            <p class="mb-1">Order #: <?php echo $order['order_number']; ?></p>
                                            <p class="mb-1">Date: <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                            <p class="mb-1">Status: 
                                                <span class="badge bg-<?php 
                                                    echo match($order['status']) {
                                                        'pending' => 'warning',
                                                        'processing' => 'info',
                                                        'assigned' => 'primary',
                                                        'picked_up' => 'info',
                                                        'delivered' => 'success',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>"><?php echo ucfirst($order['status']); ?></span>
                                            </p>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'delivered'): ?>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                        Update Status
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <?php if ($order['status'] === 'pending'): ?>
                                                            <a class="dropdown-item" href="update-order.php?id=<?php echo $order['id']; ?>&status=processing">
                                                                Mark Processing
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($order['status'] === 'processing'): ?>
                                                            <a class="dropdown-item" href="update-order.php?id=<?php echo $order['id']; ?>&status=ready">
                                                                Mark Ready for Pickup
                                                            </a>
                                                        <?php endif; ?>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" 
                                                           href="update-order.php?id=<?php echo $order['id']; ?>&status=cancelled"
                                                           onclick="return confirm('Are you sure you want to cancel this order?')">
                                                            Cancel Order
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Customer Information</h5>
                                            <p class="mb-1">Name: <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                            <p class="mb-1">Email: <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                            <p class="mb-1">Contact: <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Delivery Information</h5>
                                            <p class="mb-1">Address: <?php echo htmlspecialchars($order['delivery_address']); ?></p>
                                            <?php if ($order['driver_name']): ?>
                                                <p class="mb-1">Driver: <?php echo htmlspecialchars($order['driver_name']); ?></p>
                                            <?php elseif ($order['status'] === 'pending' && !empty($availableDrivers)): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#assignDriverModal">
                                                    Assign Driver
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <h5>Order Items</h5>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-centered mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Product</th>
                                                            <th>Quantity</th>
                                                            <th>Price</th>
                                                            <th>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($order['items'] as $item): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                                <td><?php echo $item['quantity']; ?></td>
                                                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                                                <td>₱<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        <tr>
                                                            <td colspan="3" class="text-end"><strong>Delivery Fee:</strong></td>
                                                            <td>₱<?php echo number_format($order['delivery_fee'], 2); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                                            <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($order['status'] === 'delivered'): ?>
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <h5 class="card-title">Customer Feedback</h5>
                                                    <?php 
                                                    $feedback = $feedbackModel->getOrderFeedback($orderId);
                                                    if ($feedback): ?>
                                                        <div class="text-warning mb-2">
                                                            <?php 
                                                            $rating = $feedback['rating'];
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                echo '<i class="mdi mdi-star' . ($i <= $rating ? '' : '-outline') . '"></i>';
                                                            }
                                                            ?>
                                                        </div>
                                                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                                                    <?php else: ?>
                                                        <p class="text-muted">No feedback received yet.</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../partials/footer.php'; ?>
        </div>
    </div>

    <?php include '../partials/right-sidebar.php'; ?>
    <?php include '../partials/footer-scripts.php'; ?>

    <!-- Driver Assignment Modal -->
    <?php if ($order['status'] === 'pending' && !empty($availableDrivers)): ?>
    <div class="modal fade" id="assignDriverModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Driver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="assignDriverForm" method="POST" action="assign-driver.php">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="mb-3">
                            <label for="driver_id" class="form-label">Select Driver</label>
                            <select class="form-select" name="driver_id" id="driver_id" required>
                                <option value="">Choose a driver...</option>
                                <?php foreach ($availableDrivers as $driver): ?>
                                    <option value="<?php echo $driver['id']; ?>">
                                        <?php echo htmlspecialchars($driver['fullname']); ?> 
                                        (Rating: <?php echo number_format($driver['rating'], 1); ?>★)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Assign Driver</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>