<?php
include '../partials/main.php';
use SLSupplyHub\Order;

// Include supplier approval check
include 'check-approval.php';

// Initialize Order service
$orderService = new Order();

// Get current page and filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status = isset($_GET['status']) ? $_GET['status'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Debug info
error_log("Supplier orders page - Using supplier ID: $supplierId, User ID: $supplierUserId");

// Get orders with pagination
// Use the supplier's user_id since orders.supplier_id references suppliers.user_id
$orders = $orderService->getSupplierOrders($supplierUserId, $page, 10, $status);

// Debug the result
error_log("Supplier orders result: " . json_encode($orders['total'] ?? 0) . " orders found");
?>

<head>
    <?php
    $title = "Customer Orders";
    $sub_title = "Menu";
    $page_title = "Orders";
    include '../partials/title-meta.php'; ?>
    <?php include '../partials/head-css.php'; ?>
    <link href="<?= asset_url() ?>libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= asset_url() ?>libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div id="wrapper">
        <?php include 'sidenav.php'; ?>
        <div class="content-page">
            <?php include '../partials/topbar.php'; ?>
            
            <div class="content">
                <div class="container-fluid">
                    
                    <?php include '../partials/page-title.php'; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row justify-content-between mb-3">
                                        <div class="col-auto">
                                            <form action="" method="GET" class="d-flex flex-wrap align-items-center">
                                                <div class="me-2">
                                                    <input type="search" 
                                                           class="form-control my-1 my-lg-0" 
                                                           id="search" 
                                                           name="search"
                                                           value="<?php echo htmlspecialchars($search); ?>"
                                                           placeholder="Search order number or customer...">
                                                </div>
                                                <div class="me-2">
                                                    <select class="form-select my-1 my-lg-0" id="status" name="status">
                                                        <option value="">All Status</option>
                                                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                        <option value="assigned" <?php echo $status === 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                                        <option value="picked_up" <?php echo $status === 'picked_up' ? 'selected' : ''; ?>>Picked Up</option>
                                                        <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Filter</button>
                                            </form>
                                        </div>
                                        <div class="col-auto">
                                            <div class="text-sm-end">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="mdi mdi-export me-1"></i> Export <i class="mdi mdi-chevron-down"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Export as CSV</a>
                                                        <a class="dropdown-item" href="#">Export as Excel</a>
                                                        <a class="dropdown-item" href="#">Export as PDF</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Order Stats -->
                                    <div class="row mb-3">
                                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                                            <div class="card border mb-0">
                                                <div class="card-body p-2 text-center">
                                                    <h5 class="mt-0 mb-1 text-truncate">Pending</h5>
                                                    <p class="mb-0 text-muted">
                                                        <span class="badge bg-warning">
                                                            <?php 
                                                            $pendingCount = 0;
                                                            if (!empty($orders['items'])) {
                                                                foreach ($orders['items'] as $order) {
                                                                    if ($order['status'] === 'pending') {
                                                                        $pendingCount++;
                                                                    }
                                                                }
                                                            }
                                                            echo $pendingCount;
                                                            ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                                            <div class="card border mb-0">
                                                <div class="card-body p-2 text-center">
                                                    <h5 class="mt-0 mb-1 text-truncate">Processing</h5>
                                                    <p class="mb-0 text-muted">
                                                        <span class="badge bg-info">
                                                            <?php 
                                                            $processingCount = 0;
                                                            if (!empty($orders['items'])) {
                                                                foreach ($orders['items'] as $order) {
                                                                    if ($order['status'] === 'processing') {
                                                                        $processingCount++;
                                                                    }
                                                                }
                                                            }
                                                            echo $processingCount;
                                                            ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                                            <div class="card border mb-0">
                                                <div class="card-body p-2 text-center">
                                                    <h5 class="mt-0 mb-1 text-truncate">Assigned</h5>
                                                    <p class="mb-0 text-muted">
                                                        <span class="badge bg-primary">
                                                            <?php 
                                                            $assignedCount = 0;
                                                            if (!empty($orders['items'])) {
                                                                foreach ($orders['items'] as $order) {
                                                                    if ($order['status'] === 'assigned') {
                                                                        $assignedCount++;
                                                                    }
                                                                }
                                                            }
                                                            echo $assignedCount;
                                                            ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                                            <div class="card border mb-0">
                                                <div class="card-body p-2 text-center">
                                                    <h5 class="mt-0 mb-1 text-truncate">Delivered</h5>
                                                    <p class="mb-0 text-muted">
                                                        <span class="badge bg-success">
                                                            <?php 
                                                            $deliveredCount = 0;
                                                            if (!empty($orders['items'])) {
                                                                foreach ($orders['items'] as $order) {
                                                                    if ($order['status'] === 'delivered') {
                                                                        $deliveredCount++;
                                                                    }
                                                                }
                                                            }
                                                            echo $deliveredCount;
                                                            ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                                            <div class="card border mb-0">
                                                <div class="card-body p-2 text-center">
                                                    <h5 class="mt-0 mb-1 text-truncate">Cancelled</h5>
                                                    <p class="mb-0 text-muted">
                                                        <span class="badge bg-danger">
                                                            <?php 
                                                            $cancelledCount = 0;
                                                            if (!empty($orders['items'])) {
                                                                foreach ($orders['items'] as $order) {
                                                                    if ($order['status'] === 'cancelled') {
                                                                        $cancelledCount++;
                                                                    }
                                                                }
                                                            }
                                                            echo $cancelledCount;
                                                            ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                                            <div class="card border mb-0">
                                                <div class="card-body p-2 text-center">
                                                    <h5 class="mt-0 mb-1 text-truncate">Total</h5>
                                                    <p class="mb-0 text-muted">
                                                        <span class="badge bg-secondary">
                                                            <?php echo $orders['total'] ?? 0; ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Order #</th>
                                                    <th>Customer</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($orders['items'])): ?>
                                                    <?php foreach ($orders['items'] as $order): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                                            <td>
                                                                <div>
                                                                    <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                                                </div>
                                                                <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                                            </td>
                                                            <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                                            <td>
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
                                                                ?>">
                                                                    <?php echo ucfirst($order['status']); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" 
                                                                       class="btn btn-sm btn-primary">
                                                                        <i class="mdi mdi-eye"></i>
                                                                    </a>
                                                                    <?php if ($order['status'] === 'pending'): ?>
                                                                        <a href="update-order.php?id=<?php echo $order['id']; ?>&status=processing" 
                                                                           class="btn btn-sm btn-info" 
                                                                           onclick="return confirm('Mark this order as processing?')">
                                                                            <i class="mdi mdi-progress-check"></i>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                    <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'delivered'): ?>
                                                                        <a href="update-order.php?id=<?php echo $order['id']; ?>&status=cancelled" 
                                                                           class="btn btn-sm btn-danger" 
                                                                           onclick="return confirm('Are you sure you want to cancel this order?')">
                                                                            <i class="mdi mdi-close-circle"></i>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No orders found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <?php if (isset($orders['last_page']) && $orders['last_page'] > 1): ?>
                                    <div class="row mt-4">
                                        <div class="col-sm-12 col-md-5">
                                            <div class="dataTables_info">
                                                Showing <?php echo ($orders['current_page'] - 1) * $orders['per_page'] + 1; ?> to 
                                                <?php echo min($orders['current_page'] * $orders['per_page'], $orders['total']); ?> of 
                                                <?php echo $orders['total']; ?> entries
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-7">
                                            <div class="d-flex justify-content-end">
                                                <nav>
                                                    <ul class="pagination pagination-rounded mb-0">
                                                        <?php if ($orders['current_page'] > 1): ?>
                                                            <li class="page-item">
                                                                <a class="page-link" href="?page=<?php echo $orders['current_page'] - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                                                    <i class="mdi mdi-chevron-left"></i>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>

                                                        <?php for ($i = 1; $i <= $orders['last_page']; $i++): ?>
                                                            <li class="page-item <?php echo $i === $orders['current_page'] ? 'active' : ''; ?>">
                                                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                                                    <?php echo $i; ?>
                                                                </a>
                                                            </li>
                                                        <?php endfor; ?>

                                                        <?php if ($orders['current_page'] < $orders['last_page']): ?>
                                                            <li class="page-item">
                                                                <a class="page-link" href="?page=<?php echo $orders['current_page'] + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                                                    <i class="mdi mdi-chevron-right"></i>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </nav>
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

    <!-- Datatable js -->
    <script src="<?= asset_url() ?>libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?= asset_url() ?>libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?= asset_url() ?>libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="<?= asset_url() ?>libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
</body>
</html> 