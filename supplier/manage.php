<?php
include '../partials/main.php';

// Include supplier approval check
include 'check-approval.php';

use SLSupplyHub\SupplierDashboard;

// Check if user is logged in and is a supplier
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'supplier') {
    header('Location: ../auth-login.php');
    exit;
}

$supplierId = $_SESSION['supplier_id'];
$supplierDashboard = new SupplierDashboard();
$supplier = $supplierDashboard->getSupplierDetails($supplierId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateData = [
        'business_name' => $_POST['business_name'],
        'business_address' => $_POST['business_address'],
        'business_phone' => $_POST['business_phone'],
        'business_email' => $_POST['business_email'],
        'business_permit_number' => $_POST['business_permit_number'],
        'tax_id' => $_POST['tax_id']
    ];
    
    $result = $supplierDashboard->updateSupplier($supplierId, $updateData);
    
    if ($result['success']) {
        $_SESSION['success'] = 'Business information updated successfully';
        $supplier = $supplierDashboard->getSupplierDetails($supplierId); // Refresh data
    } else {
        $_SESSION['error'] = $result['error'] ?? 'Failed to update business information';
    }
}
?>

<head>
    <?php
    $title = "Manage Business";
    include '../partials/title-meta.php'; ?>
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
                                    <h4 class="header-title mb-3">Business Information</h4>

                                    <?php if (isset($_SESSION['success'])): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <?php 
                                            echo $_SESSION['success'];
                                            unset($_SESSION['success']);
                                            ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isset($_SESSION['error'])): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <?php 
                                            echo $_SESSION['error'];
                                            unset($_SESSION['error']);
                                            ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <form action="" method="POST">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="business_name" class="form-label">Business Name</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="business_name" 
                                                           name="business_name" 
                                                           value="<?php echo htmlspecialchars($supplier['business_name'] ?? ''); ?>" 
                                                           required>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="business_email" class="form-label">Business Email</label>
                                                    <input type="email" 
                                                           class="form-control" 
                                                           id="business_email" 
                                                           name="business_email" 
                                                           value="<?php echo htmlspecialchars($supplier['business_email'] ?? ''); ?>" 
                                                           required>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="business_phone" class="form-label">Business Phone</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="business_phone" 
                                                           name="business_phone" 
                                                           value="<?php echo htmlspecialchars($supplier['business_phone'] ?? ''); ?>" 
                                                           required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="business_address" class="form-label">Business Address</label>
                                                    <textarea class="form-control" 
                                                              id="business_address" 
                                                              name="business_address" 
                                                              rows="3" 
                                                              required><?php echo htmlspecialchars($supplier['business_address'] ?? ''); ?></textarea>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="business_permit_number" class="form-label">Business Permit Number</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="business_permit_number" 
                                                           name="business_permit_number" 
                                                           value="<?php echo htmlspecialchars($supplier['business_permit_number'] ?? ''); ?>">
                                                </div>

                                                <div class="mb-3">
                                                    <label for="tax_id" class="form-label">Tax ID</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="tax_id" 
                                                           name="tax_id" 
                                                           value="<?php echo htmlspecialchars($supplier['tax_id'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-success waves-effect waves-light">Save Changes</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Business Statistics</h4>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <h5>Rating</h5>
                                                    <div class="text-warning mb-2">
                                                        <?php 
                                                        $rating = $supplier['rating'] ?? 0;
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            echo '<i class="mdi mdi-star' . ($i <= $rating ? '' : '-outline') . '"></i>';
                                                        }
                                                        ?>
                                                    </div>
                                                    <p class="text-muted mb-0">Based on customer feedback</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <h5>Total Orders</h5>
                                                    <h3><?php echo number_format($supplier['total_orders'] ?? 0); ?></h3>
                                                    <p class="text-muted mb-0">All time orders</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <h5>Status</h5>
                                                    <span class="badge bg-<?php 
                                                        echo match($supplier['status'] ?? 'pending') {
                                                            'approved' => 'success',
                                                            'suspended' => 'danger',
                                                            default => 'warning'
                                                        };
                                                    ?>">
                                                        <?php echo ucfirst($supplier['status'] ?? 'pending'); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
</body>
</html>