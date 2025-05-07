<?php
require_once '../partials/main.php';

use SLSupplyHub\Order;

try {
    $orderModel = new Order();

    // Check if user is logged in
    if (!$session->getUserId()) {
        header('Location: ../auth-login.php');
        exit;
    }

    // Get customer ID from session
    $customerId = $session->getUserId();
    error_log("[Orders Page] Customer ID: " . $customerId);

    // Get status filter and page from URL
    $filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    // Get orders with filter
    $orders = $orderModel->getCustomerOrders($customerId, $page, 10, $filterStatus);
    error_log("[Orders Page] Orders result: " . json_encode($orders));

    // Check for errors in orders response
    if (isset($orders['error'])) {
        throw new Exception($orders['error']);
    }

} catch (Exception $e) {
    error_log("[Orders Page] Error: " . $e->getMessage());
    $error = $e->getMessage();
}
?>

<head>
    <?php
    $title = "Orders";
    $page_title = "My Orders";
    require '../partials/title-meta.php';
    ?>
    <?php require '../partials/head-css.php'; ?>
    <link rel="stylesheet" href="<?= asset_url('libs/sweetalert2/sweetalert2.min.css') ?>">
    <link href="<?= asset_url('libs/toastr/build/toastr.min.css') ?>" rel="stylesheet" type="text/css" />
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
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>

                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-lg-8">
                                            <!-- Status filter dropdown -->
                                            <form class="form-inline">
                                                <div class="form-group mx-sm-3">
                                                    <select class="form-select" id="status-select" name="status">
                                                        <option value="">All Orders</option>
                                                        <option value="pending" <?php echo $filterStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="processing" <?php echo $filterStatus === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                        <option value="shipped" <?php echo $filterStatus === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="delivered" <?php echo $filterStatus === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                        <option value="cancelled" <?php echo $filterStatus === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Supplier</th>
                                                    <th>Date</th>
                                                    <th>Payment Status</th>
                                                    <th>Total</th>
                                                    <th>Payment Method</th>
                                                    <th>Order Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($orders['items'])): ?>
                                                    <?php foreach ($orders['items'] as $order): ?>
                                                        <tr>
                                                            <td>
                                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="text-body fw-bold">
                                                                    <?php echo htmlspecialchars($order['order_number']); ?>
                                                                </a>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($order['supplier_name']); ?></td>
                                                            <td>
                                                                <?php
                                                                $date = new DateTime($order['created_at']);
                                                                echo $date->format('M d, Y');
                                                                ?>
                                                                <small class="text-muted"><?php echo $date->format('h:i A'); ?></small>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $paymentStatusClass = [
                                                                    'pending' => 'bg-warning-subtle text-warning',
                                                                    'paid' => 'bg-success-subtle text-success',
                                                                    'failed' => 'bg-danger-subtle text-danger'
                                                                ][$order['payment_status']] ?? 'bg-secondary-subtle text-secondary';
                                                                ?>
                                                                <h5><span class="badge <?php echo $paymentStatusClass; ?>">
                                                                        <?php echo ucfirst(htmlspecialchars($order['payment_status'])); ?>
                                                                    </span></h5>
                                                            </td>
                                                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                                            <td><?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?></td>
                                                            <td>
                                                                <?php
                                                                $statusClass = [
                                                                    'pending' => 'bg-warning',
                                                                    'processing' => 'bg-info',
                                                                    'shipped' => 'bg-primary',
                                                                    'delivered' => 'bg-success',
                                                                    'cancelled' => 'bg-danger'
                                                                ][$order['status']] ?? 'bg-secondary';
                                                                ?>
                                                                <h5><span class="badge <?php echo $statusClass; ?>">
                                                                        <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                                                    </span></h5>
                                                            </td>
                                                            <td>
                                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>"
                                                                    class="action-icon"
                                                                    data-bs-toggle="tooltip"
                                                                    title="View Order Details">
                                                                    <i class="mdi mdi-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center">No orders found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <?php if (!empty($orders['items'])): ?>
                                        <div class="pagination pagination-rounded justify-content-end my-3">
                                            <nav>
                                                <ul class="pagination">
                                                    <li class="page-item <?php echo ($orders['current_page'] <= 1) ? 'disabled' : ''; ?>">
                                                        <a class="page-link" href="?page=<?php echo $orders['current_page'] - 1; ?><?php echo $filterStatus ? '&status=' . $filterStatus : ''; ?>">Previous</a>
                                                    </li>

                                                    <?php for ($i = 1; $i <= $orders['last_page']; $i++): ?>
                                                        <li class="page-item <?php echo ($orders['current_page'] == $i) ? 'active' : ''; ?>">
                                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filterStatus ? '&status=' . $filterStatus : ''; ?>"><?php echo $i; ?></a>
                                                        </li>
                                                    <?php endfor; ?>

                                                    <li class="page-item <?php echo ($orders['current_page'] >= $orders['last_page']) ? 'disabled' : ''; ?>">
                                                        <a class="page-link" href="?page=<?php echo $orders['current_page'] + 1; ?><?php echo $filterStatus ? '&status=' . $filterStatus : ''; ?>">Next</a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php require '../partials/footer.php'; ?>
    </div>

    <?php require '../partials/right-sidebar.php'; ?>
    <?php require '../partials/footer-scripts.php'; ?>
    <script src="<?= asset_url('libs/sweetalert2/sweetalert2.all.min.js') ?>"></script>
    <script src="<?= asset_url('libs/datatables.net/js/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= asset_url('libs/toastr/build/toastr.min.js') ?>"></script>

    <script>
        $(document).ready(function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Handle status filter changes
            $('#status-select').change(function() {
                window.location.href = 'orders.php' + (this.value ? '?status=' + this.value : '');
            });

            // Debug output
            console.log('Page loaded with filter status:', <?php echo json_encode($filterStatus); ?>);
            console.log('Current page:', <?php echo json_encode($page); ?>);
        });
    </script>

</body>

</html>