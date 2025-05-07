<?php
require_once '../partials/main.php';

use SLSupplyHub\Order;
use SLSupplyHub\Feedback;
use SLSupplyHub\Product;

$orderModel = new Order();
$feedbackModel = new Feedback();
$productModel = new Product();

// Check if user is logged in
if (!$session->getUserId()) {
    header('Location: ../auth-login.php');
    exit;
}

// Get order ID from URL
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get order details
$order = $orderModel->getOrderDetails($orderId);

// Verify order exists and belongs to current user
if (!$order || $order['customer_id'] != $session->getUserId()) {
    header('Location: orders.php');
    exit;
}

// Get existing feedback
$feedback = $feedbackModel->getOrderFeedback($orderId);
$existingFeedback = [];
foreach ($feedback as $f) {
    $existingFeedback[$f['type']] = $f;
    if ($f['type'] === 'product') {
        $existingFeedback['product_' . $f['product_id']] = $f;
    }
}

// Check if order is delivered
$canReview = $order['status'] === 'delivered';
?>

<head>
    <?php
    $title = "Order #" . $order['order_number'];
    $page_title = "Order #{$order['order_number']}";
    require '../partials/title-meta.php';
    ?>
    <?php require '../partials/head-css.php'; ?>
</head>

<body>
    <div class="wrapper">
        <?php require 'topbar.php'; ?>
        
        <!-- Start Page Content here -->
        <div class="content-page">
        <?php require 'menu.php'; ?>

            <div class="content">
                <!-- Start Content-->
                <div class="container-fluid">
                    
                    <?php 
                    require '../partials/page-title.php'; 
                    ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <!-- Order Header -->
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <h4 class="header-title mb-2">Order Details</h4>
                                            <h5 class="text-muted font-14">
                                                Order #<?php echo htmlspecialchars($order['order_number']); ?>
                                            </h5>
                                        </div>
                                        <div class="text-end">
                                            <p class="mb-1">Order Date: <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                            <p class="mb-1">Payment Method: <?php echo ucfirst($order['payment_method']); ?></p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Status Information -->
                                        <div class="col-md-6">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <h5 class="card-title">Order Status</h5>
                                                    <div class="mt-3">
                                                        <p class="mb-2">Order Status: 
                                                            <span class="badge bg-<?php 
                                                                echo match($order['status']) {
                                                                    'pending' => 'warning',
                                                                    'confirmed' => 'info',
                                                                    'processing' => 'primary',
                                                                    'assigned' => 'info',
                                                                    'picked_up' => 'primary',
                                                                    'delivered' => 'success',
                                                                    'cancelled' => 'danger',
                                                                    default => 'secondary'
                                                                };
                                                            ?>">
                                                                <?php echo ucfirst($order['status']); ?>
                                                            </span>
                                                        </p>
                                                        <p class="mb-2">Payment Status: 
                                                            <span class="badge bg-<?php 
                                                                echo match($order['payment_status']) {
                                                                    'pending' => 'warning',
                                                                    'paid' => 'success',
                                                                    'failed' => 'danger',
                                                                    'refunded' => 'info',
                                                                    default => 'secondary'
                                                                };
                                                            ?>">
                                                                <?php echo ucfirst($order['payment_status']); ?>
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Supplier Information -->
                                        <div class="col-md-6">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <h5 class="card-title">Supplier Information</h5>
                                                    <div class="mt-3">
                                                        <p class="mb-1">
                                                            <strong>Name:</strong> 
                                                            <?php echo htmlspecialchars($order['supplier_name']); ?>
                                                        </p>
                                                        <?php if ($order['driver_name']): ?>
                                                            <p class="mb-1">
                                                                <strong>Driver:</strong> 
                                                                <?php echo htmlspecialchars($order['driver_name']); ?>
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delivery Address -->
                                        <div class="col-md-6 mt-3">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <h5 class="card-title">Delivery Address</h5>
                                                    <div class="mt-3">
                                                        <p class="mb-1">
                                                            <?php echo htmlspecialchars($order['street']); ?>
                                                        </p>
                                                        <p class="mb-1">
                                                            <?php echo htmlspecialchars($order['barangay']); ?>
                                                        </p>
                                                        <p class="mb-1">
                                                            <?php echo htmlspecialchars($order['city']); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Order Items -->
                                    <div class="mt-4">
                                        <h5 class="card-title">Order Items</h5>
                                        <div class="table-responsive">
                                            <table class="table table-centered table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 100px;">Image</th>
                                                        <th>Product Details</th>
                                                        <th>Unit Price</th>
                                                        <th>Quantity</th>
                                                        <th>Total</th>
                                                        <?php if ($canReview): ?>
                                                            <th style="width: 120px;">Review</th>
                                                        <?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($order['items'] as $item): 
                                                        // Get product details including image
                                                        $productDetails = $productModel->find($item['product_id']);
                                                        $imagePath = $productDetails['image_path'] ? base_url(ltrim($productDetails['image_path'], '/')) : base_url('assets/images/placeholder.png');
                                                    ?>
                                                        <tr>
                                                            <td>
                                                                <img src="<?php echo $imagePath; ?>" 
                                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                                     class="rounded"
                                                                     style="max-height: 64px;">
                                                            </td>
                                                            <td>
                                                                <a href="product-detail.php?id=<?php echo $item['product_id']; ?>" 
                                                                   class="text-body fw-semibold">
                                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                                </a>
                                                                <?php if (!empty($productDetails['description'])): ?>
                                                                    <p class="text-muted mb-0 font-13">
                                                                        <?php echo htmlspecialchars(substr($productDetails['description'], 0, 100)) . (strlen($productDetails['description']) > 100 ? '...' : ''); ?>
                                                                    </p>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                                            <td><?php echo $item['quantity']; ?></td>
                                                            <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                            <?php if ($canReview): ?>
                                                                <td class="text-center">
                                                                    <?php if (isset($existingFeedback['product_' . $item['product_id']])): ?>
                                                                        <div class="text-warning">
                                                                            <?php 
                                                                            $rating = $existingFeedback['product_' . $item['product_id']]['rating'];
                                                                            for ($i = 1; $i <= 5; $i++) {
                                                                                echo '<i class="mdi mdi-star' . ($i <= $rating ? '' : '-outline') . '"></i>';
                                                                            }
                                                                            ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <button type="button" 
                                                                                class="btn btn-sm btn-primary"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#reviewModal"
                                                                                data-type="product"
                                                                                data-id="<?php echo $item['product_id']; ?>"
                                                                                data-name="<?php echo htmlspecialchars($item['product_name']); ?>">
                                                                            <i class="mdi mdi-star me-1"></i> Review
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <td colspan="<?php echo $canReview ? '4' : '3'; ?>" class="text-end">Subtotal:</td>
                                                        <td>₱<?php echo number_format($order['subtotal'], 2); ?></td>
                                                        <?php if ($canReview): ?><td></td><?php endif; ?>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="<?php echo $canReview ? '4' : '3'; ?>" class="text-end">Delivery Fee:</td>
                                                        <td>₱<?php echo number_format($order['delivery_fee'], 2); ?></td>
                                                        <?php if ($canReview): ?><td></td><?php endif; ?>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="<?php echo $canReview ? '4' : '3'; ?>" class="text-end"><strong>Total Amount:</strong></td>
                                                        <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                                        <?php if ($canReview): ?><td></td><?php endif; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Reviews Section -->
                                    <?php if ($canReview): ?>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <div class="card border">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Supplier Review</h5>
                                                        <?php if (isset($existingFeedback['supplier'])): ?>
                                                            <div class="text-warning mb-2">
                                                                <?php 
                                                                $rating = $existingFeedback['supplier']['rating'];
                                                                for ($i = 1; $i <= 5; $i++) {
                                                                    echo '<i class="mdi mdi-star' . ($i <= $rating ? '' : '-outline') . '"></i>';
                                                                }
                                                                ?>
                                                            </div>
                                                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($existingFeedback['supplier']['comment'])); ?></p>
                                                        <?php else: ?>
                                                            <button type="button" 
                                                                    class="btn btn-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#reviewModal"
                                                                    data-type="supplier"
                                                                    data-id="<?php echo $order['supplier_id']; ?>"
                                                                    data-name="<?php echo htmlspecialchars($order['supplier_name']); ?>">
                                                                <i class="mdi mdi-star me-1"></i> Review Supplier
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if ($order['driver_id']): ?>
                                                <div class="col-md-6">
                                                    <div class="card border">
                                                        <div class="card-body">
                                                            <h5 class="card-title">Delivery Review</h5>
                                                            <?php if (isset($existingFeedback['driver'])): ?>
                                                                <div class="text-warning mb-2">
                                                                    <?php 
                                                                    $rating = $existingFeedback['driver']['rating'];
                                                                    for ($i = 1; $i <= 5; $i++) {
                                                                        echo '<i class="mdi mdi-star' . ($i <= $rating ? '' : '-outline') . '"></i>';
                                                                    }
                                                                    ?>
                                                                </div>
                                                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($existingFeedback['driver']['comment'])); ?></p>
                                                            <?php else: ?>
                                                                <button type="button" 
                                                                        class="btn btn-primary"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#reviewModal"
                                                                        data-type="driver"
                                                                        data-id="<?php echo $order['driver_id']; ?>"
                                                                        data-name="<?php echo htmlspecialchars($order['driver_name']); ?>">
                                                                    <i class="mdi mdi-star me-1"></i> Review Delivery
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php require '../partials/footer.php'; ?>
        </div>
    </div>

    <?php require '../partials/right-sidebar.php'; ?>
    <?php require '../partials/footer-scripts.php'; ?>

    <!-- Review Modal -->
    <?php if ($canReview): ?>
        <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Write a Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="reviewForm">
                            <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                            <input type="hidden" name="type" id="review-type">
                            <input type="hidden" name="id" id="review-id">
                            
                            <div class="mb-3">
                                <h6 id="review-target" class="mb-3"></h6>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <div class="rate">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required />
                                        <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars">
                                            <i class="mdi mdi-star"></i>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="review-comment" class="form-label">Your Review</label>
                                <textarea class="form-control" id="review-comment" name="comment" rows="4" required></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="submitReview">Submit Review</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <style>
    .rate {
        display: inline-block;
        border: 0;
    }
    .rate input {
        display: none;
    }
    .rate label {
        float: right;
        color: #ddd;
        font-size: 24px;
        padding: 0 2px;
        cursor: pointer;
    }
    .rate label:hover,
    .rate label:hover ~ label,
    .rate input:checked ~ label {
        color: #f1b44c;
    }
    </style>

    <script>
    $(document).ready(function() {
        // Handle review modal
        $('#reviewModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var type = button.data('type');
            var id = button.data('id');
            var name = button.data('name');
            
            var modal = $(this);
            modal.find('#review-type').val(type);
            modal.find('#review-id').val(id);
            modal.find('#review-target').text('Review for ' + name);
            
            // Reset form
            modal.find('form')[0].reset();
        });

        // Handle review submission
        $('#submitReview').click(function() {
            var form = $('#reviewForm');
            if (!form[0].checkValidity()) {
                form[0].reportValidity();
                return;
            }

            var formData = {
                order_id: <?php echo $orderId; ?>,
                type: $('#review-type').val(),
                id: $('#review-id').val(),
                rating: $('input[name="rating"]:checked').val(),
                comment: $('#review-comment').val()
            };
            
            $.ajax({
                url: 'ajax/reviews.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Your review has been submitted successfully.',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to submit review. Please try again.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to submit review. Please try again.'
                    });
                }
            });
        });
    });
    </script>

</body>
</html>