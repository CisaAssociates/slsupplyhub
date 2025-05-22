<?php
require_once '../../partials/main.php';

use SLSupplyHub\Database;

// Error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and is admin
if (!$session->isLoggedIn() || $session->getUserRole() !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if supplier_id is provided
if (!isset($_POST['supplier_id']) || empty($_POST['supplier_id'])) {
    echo '<div class="alert alert-danger">Invalid request. Supplier ID is required.</div>';
    exit;
}

$supplier_id = intval($_POST['supplier_id']);

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get supplier details with joins to related tables
    $query = "
        SELECT s.*, u.fullname as owner_name, u.email, u.phone,
               sbi.business_type, sbi.shop_name, sbi.shop_description,
               sbi.operating_hours, sbi.delivery_areas, sbi.return_policy,
               sbi.min_processing_days, sbi.max_processing_days,
               s.business_permit_file, s.tax_certificate_file, s.store_photos_json
        FROM suppliers s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN supplier_business_info sbi ON s.id = sbi.supplier_id
        WHERE s.id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$supplier) {
        echo '<div class="alert alert-danger">Supplier not found with ID: ' . $supplier_id . '</div>';
        exit;
    }
    
    // Initialize empty arrays for documents and photos
    $documents = [];
    $photos = [];

    // Check if the supplier_documents table exists before querying
    try {
        $checkTable = $conn->query("SHOW TABLES LIKE 'supplier_documents'");
        if ($checkTable->rowCount() > 0) {
    // Get supplier documents
    $stmt = $conn->prepare("
        SELECT document_type, file_path 
        FROM supplier_documents 
        WHERE supplier_id = ?
    ");
    $stmt->execute([$supplier_id]);
    $documents = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }
    } catch (Exception $e) {
        // Table doesn't exist, just continue with empty documents array
        error_log("supplier_documents table error: " . $e->getMessage());
    }
    
    // Check if the supplier_photos table exists before querying
    try {
        $checkTable = $conn->query("SHOW TABLES LIKE 'supplier_photos'");
        if ($checkTable->rowCount() > 0) {
    // Get supplier photos
    $stmt = $conn->prepare("
        SELECT photo_path 
        FROM supplier_photos 
        WHERE supplier_id = ?
    ");
    $stmt->execute([$supplier_id]);
    $photos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    } catch (Exception $e) {
        // Table doesn't exist, just continue with empty photos array
        error_log("supplier_photos table error: " . $e->getMessage());
    }
    
    // Output supplier details
    ?>
    <div class="supplier-details">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">Business Information</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <tbody>
                        <tr>
                                <th width="40%">Business Name</th>
                            <td><?= htmlspecialchars($supplier['business_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Owner</th>
                            <td><?= htmlspecialchars($supplier['owner_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Business Email</th>
                            <td><?= htmlspecialchars($supplier['business_email']) ?></td>
                        </tr>
                        <tr>
                            <th>Business Phone</th>
                            <td><?= htmlspecialchars($supplier['business_phone']) ?></td>
                        </tr>
                        <tr>
                            <th>Business Address</th>
                            <td><?= htmlspecialchars($supplier['business_address']) ?></td>
                        </tr>
                        <tr>
                            <th>Business Type</th>
                            <td><?= htmlspecialchars($supplier['business_type'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php
                                $statusClass = [
                                    'pending' => 'bg-warning',
                                    'approved' => 'bg-success',
                                    'suspended' => 'bg-danger'
                                ];
                                $status = $supplier['status'];
                                echo '<span class="badge ' . $statusClass[$status] . '">' . ucfirst($status) . '</span>';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Applied On</th>
                            <td><?= date('M d, Y h:i A', strtotime($supplier['created_at'])) ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-md-6">
                <h5 class="mb-3">Shop Information</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <tbody>
                        <tr>
                                <th width="40%">Shop Name</th>
                            <td><?= htmlspecialchars($supplier['shop_name'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Shop Description</th>
                            <td><?= htmlspecialchars($supplier['shop_description'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Delivery Areas</th>
                            <td><?= htmlspecialchars($supplier['delivery_areas'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Return Policy</th>
                            <td><?= htmlspecialchars($supplier['return_policy'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Processing Days</th>
                            <td>
                                <?php
                                if (isset($supplier['min_processing_days']) && isset($supplier['max_processing_days'])) {
                                    echo htmlspecialchars($supplier['min_processing_days']) . ' - ' . 
                                         htmlspecialchars($supplier['max_processing_days']) . ' days';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <?php if (isset($supplier['operating_hours']) && !empty($supplier['operating_hours'])): ?>
        <div class="row mt-3">
            <div class="col-12">
                <h5 class="mb-3">Operating Hours</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th width="20%">Day</th>
                                <th width="25%">Open</th>
                                <th width="25%">Close</th>
                                <th width="30%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $operatingHours = json_decode($supplier['operating_hours'], true);
                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            
                            foreach ($days as $day) {
                                echo '<tr>';
                                echo '<td>' . ucfirst($day) . '</td>';
                                
                                if (isset($operatingHours[$day])) {
                                    $hours = $operatingHours[$day];
                                    if (isset($hours['closed']) && $hours['closed']) {
                                        echo '<td colspan="2" class="text-center">Closed</td>';
                                        echo '<td><span class="badge bg-danger">Closed</span></td>';
                                    } else {
                                        echo '<td>' . htmlspecialchars($hours['open'] ?? 'N/A') . '</td>';
                                        echo '<td>' . htmlspecialchars($hours['close'] ?? 'N/A') . '</td>';
                                        echo '<td><span class="badge bg-success">Open</span></td>';
                                    }
                                } else {
                                    echo '<td>N/A</td>';
                                    echo '<td>N/A</td>';
                                    echo '<td><span class="badge bg-secondary">Not Set</span></td>';
                                }
                                
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <h5 class="mb-3">Documents</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th width="50%">Document Type</th>
                                <th width="50%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Business Permit</td>
                                <td>
                                    <?php if (!empty($supplier['business_permit_file'])): ?>
                                        <a href="<?= base_url($supplier['business_permit_file']) ?>" 
                                           target="_blank" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-file-document"></i> View
                                        </a>
                                    <?php elseif (isset($documents['business_permit'])): ?>
                                        <a href="<?= base_url('uploads/supplier_documents/' . $documents['business_permit']) ?>" 
                                           target="_blank" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-file-document"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Not Uploaded</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Tax Certificate</td>
                                <td>
                                    <?php if (!empty($supplier['tax_certificate_file'])): ?>
                                        <a href="<?= base_url($supplier['tax_certificate_file']) ?>" 
                                           target="_blank" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-file-document"></i> View
                                        </a>
                                    <?php elseif (isset($documents['tax_certificate'])): ?>
                                        <a href="<?= base_url('uploads/supplier_documents/' . $documents['tax_certificate']) ?>" 
                                           target="_blank" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-file-document"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Not Uploaded</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-md-6">
                <h5 class="mb-3">Store Photos</h5>
                <?php 
                $storePhotos = [];
                
                // Check both sources for store photos
                if (!empty($photos)) {
                    $storePhotos = $photos;
                } elseif (!empty($supplier['store_photos_json'])) {
                    $jsonPhotos = json_decode($supplier['store_photos_json'], true);
                    if (is_array($jsonPhotos)) {
                        $storePhotos = $jsonPhotos;
                    }
                }
                
                if (!empty($storePhotos)): 
                ?>
                    <div class="row store-photos-gallery">
                        <?php foreach ($storePhotos as $photo): ?>
                        <div class="col-6 col-md-6 col-lg-4 mb-3">
                            <a href="<?= base_url($photo) ?>" target="_blank" class="img-thumbnail d-block">
                                <img src="<?= base_url($photo) ?>" alt="Store Photo" class="img-fluid rounded" onerror="this.src='<?= asset_url() ?>images/placeholder.jpg'">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No store photos uploaded.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
} catch (Exception $e) {
    error_log("Supplier details error: " . $e->getMessage());
    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
} 
?> 