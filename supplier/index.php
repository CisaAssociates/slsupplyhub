<?php include '../partials/main.php'; 

use SLSupplyHub\SupplierDashboard;
use SLSupplyHub\Order;

// Include supplier approval check
include 'check-approval.php';

$userId = $session->getUserId();

// Get dashboard data
$supplierDashboard = new SupplierDashboard();
$orderService = new Order();

// Get overview stats
$overviewStats = $supplierDashboard->getOverviewStats($supplierId);
$salesAnalytics = $supplierDashboard->getSalesAnalytics($supplierId);
$inventoryReport = $supplierDashboard->getInventoryReport($supplierId);

// Get recent orders - last 5 orders
$recentOrders = $orderService->getSupplierOrders($supplierId, 1, 5);
?>

<head>
    <?php
    $title = "Supplier Dashboard";
    include '../partials/title-meta.php'; ?>

    <!-- plugin css -->
    <link href="<?= asset_url() ?>libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link href="<?= asset_url() ?>libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />

    <?php include '../partials/head-css.php'; ?>
</head>

<body>

    <!-- Begin page -->
    <div id="wrapper">

        <?php include 'sidenav.php'; ?>

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">

            <?php include '../partials/topbar.php'; ?>

            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">
                    
                    <?php
                    $sub_title = "Supplier";
                    $title = "Dashboard";
                    include '../partials/page-title.php'; ?> 

                    <!-- Stats cards -->
                    <div class="row">
                        <div class="col-md-6 col-xl-3">
                            <div class="widget-rounded-circle card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="avatar-lg rounded bg-soft-primary">
                                                <i class="dripicons-wallet font-24 avatar-title text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-end">
                                                <h3 class="text-dark mt-1">₱<span data-plugin="counterup"><?php echo number_format($overviewStats['total_revenue'] ?? 0, 2, '.', ','); ?></span></h3>
                                                <p class="text-muted mb-1 text-truncate">Total Revenue</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <div class="widget-rounded-circle card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="avatar-lg rounded bg-soft-success">
                                                <i class="dripicons-basket font-24 avatar-title text-success"></i>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-end">
                                                <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $overviewStats['total_orders'] ?? 0; ?></span></h3>
                                                <p class="text-muted mb-1 text-truncate">Orders</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <div class="widget-rounded-circle card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="avatar-lg rounded bg-soft-info">
                                                <i class="dripicons-store font-24 avatar-title text-info"></i>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-end">
                                                <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $overviewStats['products']['total_products'] ?? 0; ?></span></h3>
                                                <p class="text-muted mb-1 text-truncate">Products</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <div class="widget-rounded-circle card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="avatar-lg rounded bg-soft-warning">
                                                <i class="dripicons-user-group font-24 avatar-title text-warning"></i>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-end">
                                                <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $overviewStats['total_customers'] ?? 0; ?></span></h3>
                                                <p class="text-muted mb-1 text-truncate">Customers</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Analytics and Revenue -->
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-body pb-2">
                                    <div class="float-end d-none d-md-inline-block">
                                        <div class="btn-group mb-2">
                                            <button type="button" class="btn btn-xs btn-light">Today</button>
                                            <button type="button" class="btn btn-xs btn-light">Weekly</button>
                                            <button type="button" class="btn btn-xs btn-secondary">Monthly</button>
                                        </div>
                                    </div>
    
                                    <h4 class="header-title mb-3">Sales Analytics</h4>
    
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <p class="text-muted mb-0 mt-3">Current Week</p>
                                            <h2 class="fw-normal mb-3">
                                                <small class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1"></small>
                                                <span>₱<?php echo number_format($salesAnalytics['current_week'] ?? 0, 2); ?></span>
                                            </h2>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="text-muted mb-0 mt-3">Previous Week</p>
                                            <h2 class="fw-normal mb-3">
                                                <small class="mdi mdi-checkbox-blank-circle text-success align-middle me-1"></small>
                                                <span>₱<?php echo number_format($salesAnalytics['previous_week'] ?? 0, 2); ?></span>
                                            </h2>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="text-muted mb-0 mt-3">Target</p>
                                            <h2 class="fw-normal mb-3">
                                                <small class="mdi mdi-checkbox-blank-circle text-success align-middle me-1"></small>
                                                <span>₱<?php echo number_format(($salesAnalytics['current_week'] ?? 0) * 1.2, 2); ?></span>
                                            </h2>
                                        </div>
                                    </div>
                                    <div id="revenue-chart" class="apex-charts mt-3" data-colors="#6658dd,#1abc9c"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-0">Low Stock Products</h4>
    
                                    <div class="widget-chart text-center" dir="ltr">
                                        <?php if (!empty($inventoryReport['low_stock'])): ?>
                                            <div class="table-responsive mt-3">
                                                <table class="table table-sm table-centered mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Product</th>
                                                            <th>Stock</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach (array_slice($inventoryReport['low_stock'], 0, 5) as $product): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                                <td><?php echo $product['stock']; ?></td>
                                                                <td>
                                                                    <span class="badge bg-<?php echo $product['stock'] <= 0 ? 'danger' : 'warning'; ?>">
                                                                        <?php echo $product['stock'] <= 0 ? 'Out of Stock' : 'Low Stock'; ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="mt-3">
                                                <a href="products.php" class="btn btn-sm btn-primary">Manage Inventory</a>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-3">
                                                <i class="mdi mdi-check-circle text-success" style="font-size: 48px;"></i>
                                                <h5 class="mt-2">All products have sufficient stock</h5>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h4 class="header-title">Recent Orders</h4>
                                        <a href="orders.php" class="btn btn-sm btn-primary">View All Orders</a>
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
                                                <?php if (!empty($recentOrders['items'])): ?>
                                                    <?php foreach ($recentOrders['items'] as $order): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
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
                                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No recent orders found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Feedback -->
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Customer Feedback</h4>
                                    <?php 
                                    $feedback = $supplierDashboard->getCustomerFeedback($supplierId);
                                    if (!empty($feedback['feedback'])): 
                                    ?>
                                        <div class="table-responsive">
                                            <table class="table table-centered table-nowrap mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Customer</th>
                                                        <th>Rating</th>
                                                        <th>Comment</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (array_slice($feedback['feedback'], 0, 5) as $item): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($item['customer_name']); ?></td>
                                                            <td>
                                                                <div class="text-warning">
                                                                    <?php 
                                                                    for ($i = 1; $i <= 5; $i++) {
                                                                        echo '<i class="mdi mdi-star' . ($i <= $item['feedback_rating'] ? '' : '-outline') . '"></i>';
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </td>
                                                            <td><?php echo htmlspecialchars(substr($item['feedback_comment'], 0, 50)) . (strlen($item['feedback_comment']) > 50 ? '...' : ''); ?></td>
                                                            <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <i class="mdi mdi-comment-outline text-muted" style="font-size: 48px;"></i>
                                            <h5 class="mt-2">No feedback received yet</h5>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Top Products</h4>
                                    <?php 
                                    $analytics = $supplierDashboard->getPerformanceMetrics($supplierId);
                                    if (!empty($analytics['metrics']['top_products'])): 
                                    ?>
                                        <div class="table-responsive">
                                            <table class="table table-centered table-nowrap mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Orders</th>
                                                        <th>Quantity</th>
                                                        <th>Revenue</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (array_slice($analytics['metrics']['top_products'], 0, 5) as $product): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                            <td><?php echo $product['order_count']; ?></td>
                                                            <td><?php echo $product['total_quantity']; ?></td>
                                                            <td>₱<?php echo number_format($product['total_revenue'], 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <i class="mdi mdi-package-variant text-muted" style="font-size: 48px;"></i>
                                            <h5 class="mt-2">No product sales data available</h5>
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

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->


    </div>
    <!-- END wrapper -->

    <?php include '../partials/right-sidebar.php'; ?>
    
    <?php include '../partials/footer-scripts.php'; ?>

    <!-- Third Party js-->
    <script src="<?= asset_url() ?>libs/apexcharts/apexcharts.min.js"></script>

    <!-- Dashboard init js -->
    <script src="<?= asset_url() ?>js/pages/dashboard.init.js"></script>
    
</body>
</html>