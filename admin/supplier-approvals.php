<?php include '../partials/main.php'; ?>

<head>
    <?php
    $title = "Supplier Applications";
    $sub_title = "Home";
    $sub_title2 = "Supplier Applications";
    include '../partials/title-meta.php'; ?>

    <?php include '../partials/head-css.php'; ?>
    <link rel="stylesheet" href="<?php asset_url('libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?php asset_url('libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') ?>">
    
    <style>
        /* Custom styles for responsive tables */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px;
        }
        
        @media (max-width: 767px) {
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                text-align: left;
                margin-bottom: 10px;
            }
            
            .dt-buttons {
                margin-bottom: 10px;
            }
            
            .dtr-details {
                width: 100%;
            }
            
            /* Full width modal on mobile */
            .modal-dialog {
                max-width: 95%;
                margin: 10px auto;
            }
            
            /* Better table display on small screens */
            .supplier-details .table-responsive {
                margin-bottom: 15px;
            }
            
            /* Give more room for action buttons on mobile */
            .button-list {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }
        }
        
        /* Keep action buttons visible and properly spaced on mobile */
        .btn-sm {
            margin: 2px;
            white-space: nowrap;
        }
        
        /* Highlight child rows */
        table.dataTable>tbody>tr.child:hover {
            background-color: #f8f9fa !important;
        }
        
        /* Improved child row styling */
        table.dataTable>tbody>tr.child ul.dtr-details>li {
            border-bottom: 1px solid #efefef;
            padding: 0.5rem 0;
        }
        
        /* Supplier details modal fixes */
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        
        #viewDetailsModal .loading-spinner {
            padding: 30px 0;
        }
        
        #supplier-details-content {
            width: 100%;
        }
        
        .supplier-details table {
            margin-bottom: 0;
        }
        
        .supplier-details .table-responsive {
            overflow-x: auto;
        }
        
        .store-photos-gallery img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
    </style>
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
                    $sub_title = "Suppliers";
                    $title = "Applications";
                    include '../partials/page-title.php'; ?>
                    
                    <?php
                    // Handle direct approval links
                    if (isset($_GET['direct_approve']) && is_numeric($_GET['direct_approve']) && intval($_GET['direct_approve']) > 0) {
                        $supplier_id = intval($_GET['direct_approve']);
                        
                        // Debug info
                        error_log("Processing direct approval for supplier ID: {$supplier_id}");
                        
                        try {
                            $db = \SLSupplyHub\Database::getInstance();
                            $conn = $db->getConnection();
                            
                            // Check if the supplier exists first
                            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM suppliers WHERE id = ?");
                            $checkStmt->execute([$supplier_id]);
                            $supplierExists = (int)$checkStmt->fetchColumn();
                            
                            if (!$supplierExists) {
                                throw new Exception("Supplier with ID {$supplier_id} does not exist in the database.");
                            }
                            
                            // Update supplier status to approved
                            $stmt = $conn->prepare("UPDATE suppliers SET status = 'approved' WHERE id = ?");
                            $result = $stmt->execute([$supplier_id]);
                            
                            if (!$result) {
                                throw new Exception("Failed to update supplier status.");
                            }
                            
                            echo '<div class="alert alert-success">Supplier #' . $supplier_id . ' has been approved successfully via direct link!</div>';
                            error_log("Direct approval successful for supplier ID: {$supplier_id}");
                            
                            // Redirect to remove the GET parameter from URL to prevent accidental repeat approvals
                            echo '<script>
                                setTimeout(function() {
                                    window.location.href = window.location.pathname;
                                }, 2000);
                            </script>';
                            
                        } catch (Exception $e) {
                            error_log("Direct approval error: " . $e->getMessage());
                            echo '<div class="alert alert-danger">Error during direct approval: ' . $e->getMessage() . '</div>';
                        }
                    }
                    
                    // Debug POST data
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        error_log("POST data received: " . json_encode($_POST));
                    }
                    
                    // Process supplier approval/rejection
                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['supplier_id'])) {
                        // Validate supplier ID
                        if (empty($_POST['supplier_id']) || !is_numeric($_POST['supplier_id']) || intval($_POST['supplier_id']) <= 0) {
                            echo '<div class="alert alert-danger">Error: Invalid supplier ID provided: "' . htmlspecialchars($_POST['supplier_id']) . '"</div>';
                            error_log("Invalid supplier ID received: " . var_export($_POST['supplier_id'], true));
                            goto skip_processing;
                        }
                        
                        $supplier_id = intval($_POST['supplier_id']);
                        $action = $_POST['action'];
                        $status = ($action === 'approve') ? 'approved' : 'suspended';
                        $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
                        
                        // Debug info
                        error_log("Processing {$action} for supplier ID: {$supplier_id}");
                        
                        try {
                            $db = \SLSupplyHub\Database::getInstance();
                            $conn = $db->getConnection();
                            
                            // Check if the supplier exists first
                            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM suppliers WHERE id = ?");
                            $checkStmt->execute([$supplier_id]);
                            $supplierExists = (int)$checkStmt->fetchColumn();
                            
                            if (!$supplierExists) {
                                throw new Exception("Supplier with ID {$supplier_id} does not exist in the database.");
                            }
                            
                            // Update supplier status
                            $stmt = $conn->prepare("UPDATE suppliers SET status = ? WHERE id = ?");
                            $result = $stmt->execute([$status, $supplier_id]);
                            
                            if (!$result) {
                                throw new Exception("Failed to update supplier status.");
                            }
                            
                            error_log("Updated supplier status to {$status} for ID: {$supplier_id}");
                            
                            // Get supplier email
                            $stmt = $conn->prepare("
                                SELECT u.email, u.fullname, s.business_name, u.id as user_id
                                FROM suppliers s
                                JOIN users u ON s.user_id = u.id
                                WHERE s.id = ?
                            ");
                            $stmt->execute([$supplier_id]);
                            $supplierData = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if (!$supplierData) {
                                throw new Exception("Supplier data not found for ID: {$supplier_id}");
                            }
                            
                            error_log("Found supplier data: " . json_encode($supplierData));
                            
                                // Send notification to supplier
                            try {
                                require_once __DIR__ . '/../services/MailService.php';
                                $mail = new \SLSupplyHub\MailService();
                                
                                if ($action === 'approve') {
                                    // Try to send email
                                    try {
                                    $mail->sendSupplierApprovalNotification($supplierData['email'], [
                                        'fullname' => $supplierData['fullname'],
                                        'business_name' => $supplierData['business_name']
                                    ]);
                                        error_log("Sent approval email to: {$supplierData['email']}");
                                    } catch (Exception $e) {
                                        error_log("Error sending approval email: " . $e->getMessage());
                                        // Continue even if email fails
                                    }
                                    
                                    // Add system notification
                                    try {
                                    $stmt = $conn->prepare("
                                        INSERT INTO notifications (user_id, title, message, type, reference_id, reference_type)
                                            VALUES (?, 'Application Approved', 'Your supplier application has been approved. You can now start selling on our platform.', 'supplier_approval', ?, 'supplier')
                                    ");
                                        $stmt->execute([$supplierData['user_id'], $supplier_id]);
                                        error_log("Added approval notification for user ID: {$supplierData['user_id']}");
                                    } catch (Exception $e) {
                                        error_log("Error adding notification: " . $e->getMessage());
                                        // Continue even if notification fails
                                    }
                                    
                                    echo '<div class="alert alert-success">Supplier application approved successfully!</div>';
                                } else {
                                    // Try to send email
                                    try {
                                    $mail->sendSupplierRejectionNotification($supplierData['email'], [
                                        'fullname' => $supplierData['fullname'],
                                        'business_name' => $supplierData['business_name'],
                                        'reason' => $notes
                                    ]);
                                        error_log("Sent rejection email to: {$supplierData['email']}");
                                    } catch (Exception $e) {
                                        error_log("Error sending rejection email: " . $e->getMessage());
                                        // Continue even if email fails
                                    }
                                    
                                    // Add system notification
                                    try {
                                        $message = 'Your supplier application has been rejected.' . ($notes ? ' Reason: ' . $notes : '');
                                    $stmt = $conn->prepare("
                                        INSERT INTO notifications (user_id, title, message, type, reference_id, reference_type)
                                            VALUES (?, 'Application Rejected', ?, 'supplier_rejection', ?, 'supplier')
                                        ");
                                        $stmt->execute([$supplierData['user_id'], $message, $supplier_id]);
                                        error_log("Added rejection notification for user ID: {$supplierData['user_id']}");
                                    } catch (Exception $e) {
                                        error_log("Error adding notification: " . $e->getMessage());
                                        // Continue even if notification fails
                                    }
                                    
                                    echo '<div class="alert alert-warning">Supplier application rejected!</div>';
                                }
                            } catch (Exception $e) {
                                error_log("Notification error: " . $e->getMessage());
                                // Still show success since the status was updated
                                echo '<div class="alert alert-success">Supplier status updated, but notification failed: ' . $e->getMessage() . '</div>';
                            }
                            
                            // Redirect to refresh the page and prevent form resubmission
                            echo '<script>
                                setTimeout(function() {
                                    window.location.href = window.location.pathname;
                                }, 2000);
                            </script>';
                            
                        } catch (Exception $e) {
                            error_log("Supplier approval/rejection error: " . $e->getMessage());
                            echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                        }
                    }
                    
                    // Skip processing label for validation errors
                    skip_processing:
                    ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Pending Supplier Applications</h4>
                                    <p class="text-muted font-14 mb-4">
                                        Review and approve or reject supplier applications.
                                    </p>

                                    <ul class="nav nav-tabs nav-bordered mb-3">
                                        <li class="nav-item">
                                            <a href="#pending-applications" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                                <i class="mdi mdi-clock-outline d-md-none d-block"></i>
                                                <span class="d-none d-md-block">Pending Applications</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#approved-applications" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                <i class="mdi mdi-check-circle-outline d-md-none d-block"></i>
                                                <span class="d-none d-md-block">Approved Applications</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#rejected-applications" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                <i class="mdi mdi-close-circle-outline d-md-none d-block"></i>
                                                <span class="d-none d-md-block">Rejected Applications</span>
                                            </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content">
                                        <div class="tab-pane show active" id="pending-applications">
                                            <div class="table-responsive">
                                                <table class="table table-centered table-striped dt-responsive nowrap w-100" id="pending-suppliers-datatable">
                                                    <thead>
                                                        <tr>
                                                            <th>Business Name</th>
                                                            <th>Owner</th>
                                                            <th>Contact</th>
                                                            <th>Business Type</th>
                                                            <th>Applied On</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $db = \SLSupplyHub\Database::getInstance();
                                                        $conn = $db->getConnection();
                                                        
                                                        $query = "
                                                            SELECT s.*, u.fullname as owner_name, u.email, u.phone, 
                                                                   sbi.business_type, s.created_at as applied_date
                                                            FROM suppliers s
                                                            JOIN users u ON s.user_id = u.id
                                                            LEFT JOIN supplier_business_info sbi ON s.id = sbi.supplier_id
                                                            WHERE s.status = 'pending'
                                                            ORDER BY s.created_at DESC
                                                        ";
                                                        
                                                        $stmt = $conn->prepare($query);
                                                        $stmt->execute();
                                                        $pendingSuppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                        
                                                        foreach ($pendingSuppliers as $supplier) {
                                                            echo '<tr>';
                                                            echo '<td data-title="Business Name">' . htmlspecialchars($supplier['business_name']) . '</td>';
                                                            echo '<td data-title="Owner">' . htmlspecialchars($supplier['owner_name']) . '</td>';
                                                            echo '<td data-title="Contact">' . htmlspecialchars($supplier['business_email']) . '<br>' . htmlspecialchars($supplier['business_phone']) . '</td>';
                                                            echo '<td data-title="Business Type">' . htmlspecialchars($supplier['business_type'] ?? 'N/A') . '</td>';
                                                            echo '<td data-title="Applied On">' . date('M d, Y', strtotime($supplier['applied_date'])) . '</td>';
                                                            echo '<td data-title="Actions">';
                                                            echo '<div class="button-list">';
                                                            echo '<a href="#" class="btn btn-sm btn-info view-details" data-bs-toggle="modal" data-bs-target="#viewDetailsModal" data-id="' . $supplier['id'] . '"><i class="mdi mdi-eye"></i> View</a> ';
                                                            echo '<a href="#" class="btn btn-sm btn-success approve-supplier" data-id="' . $supplier['id'] . '"><i class="mdi mdi-check"></i> Approve</a> ';
                                                            echo '<a href="#" class="btn btn-sm btn-danger reject-supplier" data-id="' . $supplier['id'] . '"><i class="mdi mdi-close"></i> Reject</a>';
                                                            
                                                            // Add direct access links for debugging
                                                            echo ' <a href="?direct_approve=' . $supplier['id'] . '" class="btn btn-sm btn-outline-success"><i class="mdi mdi-link"></i> Direct Approve</a>';
                                                            
                                                            echo '</div>';
                                                            echo '</td>';
                                                            echo '</tr>';
                                                        }
                                                        
                                                        if (count($pendingSuppliers) === 0) {
                                                            echo '<tr><td colspan="6" class="text-center">No pending applications found</td></tr>';
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <div class="tab-pane" id="approved-applications">
                                            <div class="table-responsive">
                                                <table class="table table-centered table-striped dt-responsive nowrap w-100" id="approved-suppliers-datatable">
                                                    <thead>
                                                        <tr>
                                                            <th>Business Name</th>
                                                            <th>Owner</th>
                                                            <th>Contact</th>
                                                            <th>Business Type</th>
                                                            <th>Approved On</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $query = "
                                                            SELECT s.*, u.fullname as owner_name, u.email, u.phone, 
                                                                   sbi.business_type, s.updated_at as approved_date
                                                            FROM suppliers s
                                                            JOIN users u ON s.user_id = u.id
                                                            LEFT JOIN supplier_business_info sbi ON s.id = sbi.supplier_id
                                                            WHERE s.status = 'approved'
                                                            ORDER BY s.updated_at DESC
                                                        ";
                                                        
                                                        $stmt = $conn->prepare($query);
                                                        $stmt->execute();
                                                        $approvedSuppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                        
                                                        foreach ($approvedSuppliers as $supplier) {
                                                            echo '<tr>';
                                                            echo '<td data-title="Business Name">' . htmlspecialchars($supplier['business_name']) . '</td>';
                                                            echo '<td data-title="Owner">' . htmlspecialchars($supplier['owner_name']) . '</td>';
                                                            echo '<td data-title="Contact">' . htmlspecialchars($supplier['business_email']) . '<br>' . htmlspecialchars($supplier['business_phone']) . '</td>';
                                                            echo '<td data-title="Business Type">' . htmlspecialchars($supplier['business_type'] ?? 'N/A') . '</td>';
                                                            echo '<td data-title="Approved On">' . date('M d, Y', strtotime($supplier['approved_date'])) . '</td>';
                                                            echo '<td data-title="Actions">';
                                                            echo '<div class="button-list">';
                                                            echo '<a href="#" class="btn btn-sm btn-info view-details" data-bs-toggle="modal" data-bs-target="#viewDetailsModal" data-id="' . $supplier['id'] . '"><i class="mdi mdi-eye"></i> View</a> ';
                                                            echo '<a href="#" class="btn btn-sm btn-danger suspend-supplier" data-bs-toggle="modal" data-bs-target="#rejectModal" data-id="' . $supplier['id'] . '"><i class="mdi mdi-account-off"></i> Suspend</a>';
                                                            echo '</div>';
                                                            echo '</td>';
                                                            echo '</tr>';
                                                        }
                                                        
                                                        if (count($approvedSuppliers) === 0) {
                                                            echo '<tr><td colspan="6" class="text-center">No approved applications found</td></tr>';
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <div class="tab-pane" id="rejected-applications">
                                            <div class="table-responsive">
                                                <table class="table table-centered table-striped dt-responsive nowrap w-100" id="rejected-suppliers-datatable">
                                                    <thead>
                                                        <tr>
                                                            <th>Business Name</th>
                                                            <th>Owner</th>
                                                            <th>Contact</th>
                                                            <th>Business Type</th>
                                                            <th>Rejected On</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $query = "
                                                            SELECT s.*, u.fullname as owner_name, u.email, u.phone, 
                                                                   sbi.business_type, s.updated_at as rejected_date
                                                            FROM suppliers s
                                                            JOIN users u ON s.user_id = u.id
                                                            LEFT JOIN supplier_business_info sbi ON s.id = sbi.supplier_id
                                                            WHERE s.status = 'suspended'
                                                            ORDER BY s.updated_at DESC
                                                        ";
                                                        
                                                        $stmt = $conn->prepare($query);
                                                        $stmt->execute();
                                                        $rejectedSuppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                        
                                                        foreach ($rejectedSuppliers as $supplier) {
                                                            echo '<tr>';
                                                            echo '<td data-title="Business Name">' . htmlspecialchars($supplier['business_name']) . '</td>';
                                                            echo '<td data-title="Owner">' . htmlspecialchars($supplier['owner_name']) . '</td>';
                                                            echo '<td data-title="Contact">' . htmlspecialchars($supplier['business_email']) . '<br>' . htmlspecialchars($supplier['business_phone']) . '</td>';
                                                            echo '<td data-title="Business Type">' . htmlspecialchars($supplier['business_type'] ?? 'N/A') . '</td>';
                                                            echo '<td data-title="Rejected On">' . date('M d, Y', strtotime($supplier['rejected_date'])) . '</td>';
                                                            echo '<td data-title="Actions">';
                                                            echo '<div class="button-list">';
                                                            echo '<a href="#" class="btn btn-sm btn-info view-details" data-bs-toggle="modal" data-bs-target="#viewDetailsModal" data-id="' . $supplier['id'] . '"><i class="mdi mdi-eye"></i> View</a> ';
                                                            echo '<a href="#" class="btn btn-sm btn-success approve-supplier" data-bs-toggle="modal" data-bs-target="#approveModal" data-id="' . $supplier['id'] . '"><i class="mdi mdi-account-check"></i> Approve</a>';
                                                            echo '</div>';
                                                            echo '</td>';
                                                            echo '</tr>';
                                                        }
                                                        
                                                        if (count($rejectedSuppliers) === 0) {
                                                            echo '<tr><td colspan="6" class="text-center">No rejected applications found</td></tr>';
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- View Details Modal -->
                    <div id="viewDetailsModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="viewDetailsModalLabel">Supplier Application Details</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                                </div>
                                <div class="modal-body position-relative">
                                    <div class="text-center loading-spinner">
                                        <div class="spinner-border text-primary m-2" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p>Loading supplier details...</p>
                                    </div>
                                    <div id="supplier-details-content" style="display: none;">
                                        <!-- Content will be loaded dynamically -->
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Approve Modal -->
                    <div id="approveModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="approveModalLabel">Approve Supplier Application</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                                </div>
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="approve-supplier-form">
                                    <div class="modal-body">
                                        <p>Are you sure you want to approve this supplier application?</p>
                                        <p>The supplier will be notified and can start selling on the platform.</p>
                                        <input type="hidden" name="supplier_id" id="approve-supplier-id" value="">
                                        <input type="hidden" name="action" value="approve">
                                        <div id="approve-supplier-id-display"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success" id="approve-submit-btn">Approve</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reject Modal -->
                    <div id="rejectModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="rejectModalLabel">Reject Supplier Application</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                                </div>
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="reject-supplier-form">
                                    <div class="modal-body">
                                        <p>Are you sure you want to reject this supplier application?</p>
                                        <div class="mb-3">
                                            <label for="rejection-notes" class="form-label">Reason for Rejection</label>
                                            <textarea class="form-control" id="rejection-notes" name="notes" rows="3" placeholder="Provide a reason for rejection (will be sent to the supplier)"></textarea>
                                        </div>
                                        <input type="hidden" name="supplier_id" id="reject-supplier-id" value="">
                                        <input type="hidden" name="action" value="reject">
                                        <div id="reject-supplier-id-display"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger" id="reject-submit-btn">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div> <!-- container -->

            </div> <!-- content -->

            <?php include '../partials/footer.php'; ?>

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->


    </div>
    <!-- END wrapper -->

    <?php include '../partials/right-sidebar.php'; ?>

    <?php include '../partials/footer-scripts.php'; ?>
    
    <!-- Datatable js -->
    <script src="<?= asset_url() ?>libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?= asset_url() ?>libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?= asset_url() ?>libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="<?= asset_url() ?>libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            try {
                console.log("Document ready");
                
                // Define global event handlers for the view details functionality
                $(document).on('click', '.view-details', function(e) {
                    e.preventDefault();
                    var supplierId = $(this).data('id');
                    console.log("View details clicked for supplier ID:", supplierId);
                    loadSupplierDetails(supplierId);
                });
                
                // Function to load supplier details
                function loadSupplierDetails(supplierId) {
                    $('#supplier-details-content').hide();
                    $('.loading-spinner').show();
                    
                    console.log("Loading supplier details for ID:", supplierId);
                    
                    // AJAX call to get supplier details
                    $.ajax({
                        url: 'ajax/get_supplier_details.php',
                        type: 'POST',
                        data: {supplier_id: supplierId},
                        dataType: 'html',
                        success: function(response) {
                            console.log("AJAX Success, response length:", response.length);
                            $('.loading-spinner').hide();
                            $('#supplier-details-content').html(response).show();
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                            console.error("Response:", xhr.responseText);
                            $('.loading-spinner').hide();
                            $('#supplier-details-content').html('<div class="alert alert-danger">Error loading supplier details: ' + error + '</div>').show();
                        },
                        complete: function() {
                            // Ensure spinner is hidden in any case
                            $('.loading-spinner').hide();
                    }
                    });
                }
                
                // Initialize modal events
                $('#viewDetailsModal').on('hidden.bs.modal', function() {
                    $('#supplier-details-content').html('').hide();
                    $('.loading-spinner').hide();
                });
                
                $('#viewDetailsModal').on('shown.bs.modal', function() {
                    console.log("Modal shown");
                });
                
                // Initialize datatables
                var tableOptions = {
                    responsive: {
                        details: {
                            display: $.fn.dataTable.Responsive.display.childRowImmediate,
                            type: 'column',
                            renderer: function(api, rowIdx, columns) {
                                var data = $.map(columns, function(col, i) {
                                    return col.hidden ?
                                        '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                                        '<td>' + col.title + ':</td> ' +
                                        '<td>' + col.data + '</td>' +
                                        '</tr>' :
                                        '';
                                }).join('');

                                return data ?
                                    $('<table class="table table-sm table-bordered"/>').append(data) :
                                    false;
                            }
                        }
                    },
                    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                        },
                        emptyTable: "No data available",
                        zeroRecords: "No matching records found"
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                    },
                    columnDefs: [
                        { targets: '_all', defaultContent: '' },
                        { responsivePriority: 1, targets: 0 }, // Business Name
                        { responsivePriority: 2, targets: -1 }, // Actions
                        { responsivePriority: 3, targets: 4 } // Applied On
                    ],
                    ordering: true,
                    order: [[4, 'desc']], // Sort by Applied On by default
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]]
                };
                
                // Only initialize tables that exist in the DOM
                if ($('#pending-suppliers-datatable').length) {
                    $('#pending-suppliers-datatable').DataTable(tableOptions);
                }
                
                if ($('#approved-suppliers-datatable').length) {
                    $('#approved-suppliers-datatable').DataTable(tableOptions);
                }
                
                if ($('#rejected-suppliers-datatable').length) {
                    $('#rejected-suppliers-datatable').DataTable(tableOptions);
                }
                
                // Add document-level event delegation for modals
                $(document).on('click', '.approve-supplier', function(e) {
                    e.preventDefault();
                    
                    // Get the supplier ID directly from the button's href or the row
                    var $btn = $(this);
                    var supplierId = $btn.attr('data-id');
                    
                    // Fallback: try to get ID from HTML
                    if (!supplierId) {
                        // Extract supplier ID from the button's HTML
                        var btnHtml = $btn.get(0).outerHTML;
                        var idMatch = btnHtml.match(/data-id=['"](\d+)['"]/);
                        if (idMatch && idMatch[1]) {
                            supplierId = idMatch[1];
                            console.log("Extracted ID from HTML:", supplierId);
                    }
                    }
                    
                    console.log("Approve clicked for supplier ID:", supplierId);
                    
                    // Direct approach: create a new form and submit
                    if (supplierId && supplierId > 0) {
                        var $form = $('<form>', {
                            action: window.location.href,
                            method: 'post',
                            style: 'display: none'
                        });
                        
                        $form.append($('<input>', {
                            type: 'hidden',
                            name: 'supplier_id',
                            value: supplierId
                        }));
                        
                        $form.append($('<input>', {
                            type: 'hidden',
                            name: 'action',
                            value: 'approve'
                        }));
            
                        // Confirm with the user
                        if (confirm('Are you sure you want to approve this supplier application? ID: ' + supplierId)) {
                            $('body').append($form);
                            $form.submit();
                        }
                    } else {
                        alert('Error: Could not find supplier ID. Please contact support.');
                        console.error('No supplier ID found for approval button:', $btn.get(0));
                    }
            });
            
                $(document).on('click', '.reject-supplier, .suspend-supplier', function(e) {
                    e.preventDefault();
                    
                    // Get the supplier ID directly from the button's href or the row
                    var $btn = $(this);
                    var supplierId = $btn.attr('data-id');
                    
                    // Fallback: try to get ID from HTML
                    if (!supplierId) {
                        // Extract supplier ID from the button's HTML
                        var btnHtml = $btn.get(0).outerHTML;
                        var idMatch = btnHtml.match(/data-id=['"](\d+)['"]/);
                        if (idMatch && idMatch[1]) {
                            supplierId = idMatch[1];
                            console.log("Extracted ID from HTML:", supplierId);
                        }
                    }
                    
                    console.log("Reject/Suspend clicked for supplier ID:", supplierId);
                
                    // Check if it's a rejection or suspension
                    var isSuspend = $btn.hasClass('suspend-supplier');
                    var actionTitle = isSuspend ? 'Suspend' : 'Reject';
                    var actionText = isSuspend ? 'suspend' : 'reject';
                    
                    // Direct approach: create a new form and submit
                    if (supplierId && supplierId > 0) {
                        var notes = prompt('Please provide a reason to ' + actionText + ' this supplier (ID: ' + supplierId + '):');
                        
                        if (notes !== null) { // Only proceed if user didn't cancel
                            var $form = $('<form>', {
                                action: window.location.href,
                                method: 'post',
                                style: 'display: none'
                            });
                            
                            $form.append($('<input>', {
                                type: 'hidden',
                                name: 'supplier_id',
                                value: supplierId
                            }));
                            
                            $form.append($('<input>', {
                                type: 'hidden',
                                name: 'action',
                                value: 'reject'
                            }));
                            
                            $form.append($('<input>', {
                                type: 'hidden',
                                name: 'notes',
                                value: notes
                            }));
                            
                            $('body').append($form);
                            $form.submit();
                        }
                } else {
                        alert('Error: Could not find supplier ID. Please contact support.');
                        console.error('No supplier ID found for ' + actionText + ' button:', $btn.get(0));
                }
            });
            
                // Debugging function to check all supplier IDs in the tables
                function debugSupplierButtons() {
                    console.log("Debugging supplier buttons:");
                    
                    // Check all approve buttons
                    $('.approve-supplier').each(function(index) {
                        var btn = $(this);
                        var id = btn.data('id');
                        console.log("Approve button #" + index + " has ID: " + id);
                        
                        // Add a direct attribute check
                        var rawAttr = btn.attr('data-id');
                        console.log("Raw data-id attribute: " + rawAttr);
                        
                        // Flag any issues
                        if (!id || id === 0) {
                            console.error("Problem with approve button #" + index + ":");
                            console.error(btn.get(0));
                        }
                    });
                
                    // Check all reject buttons
                    $('.reject-supplier, .suspend-supplier').each(function(index) {
                        var btn = $(this);
                        var id = btn.data('id');
                        console.log("Reject/Suspend button #" + index + " has ID: " + id);
                        
                        // Add a direct attribute check
                        var rawAttr = btn.attr('data-id');
                        console.log("Raw data-id attribute: " + rawAttr);
                        
                        // Flag any issues
                        if (!id || id === 0) {
                            console.error("Problem with reject/suspend button #" + index + ":");
                            console.error(btn.get(0));
                        }
                    });
                }
                
                // Run debugging function after tables are initialized
                setTimeout(debugSupplierButtons, 1000);
                
                // Fix for DataTables destroying data attributes
                function fixDataAttributes() {
                    // Find all table cells with action buttons
                    $('.button-list').each(function() {
                        var $cell = $(this).closest('td');
                        var $row = $cell.closest('tr');
                        
                        // Try to find supplier ID directly in the row data
                        $('.approve-supplier, .reject-supplier, .suspend-supplier', this).each(function() {
                            // Check if data-id is missing or zero
                            if (!$(this).data('id')) {
                                // Find the row data
                                var rowData = $row.find('td:first').text().trim();
                                console.log("Attempting to repair button in row with data:", rowData);
                    }
                });
            });
                }
                
                // Run fix after tables are initialized
                setTimeout(fixDataAttributes, 500);
            } catch (e) {
                console.error("DataTables initialization error:", e);
            }
        });
    </script>

</body>

</html> 