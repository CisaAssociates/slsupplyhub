<?php
/**
 * Supplier Approval Check
 * 
 * This file checks if the current user has an approved supplier account.
 * If not, it redirects them to the appropriate page with a message.
 */

// Check if user is logged in
if (!$session->getUserId()) {
    header('Location: ' . base_url('/auth-login.php'));
    exit;
}

// Check if the user has a supplier account and if it's approved
$db = \SLSupplyHub\Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT s.id, s.user_id, s.status FROM suppliers s WHERE s.user_id = ?");
$stmt->execute([$session->getUserId()]);
$supplier = $stmt->fetch(PDO::FETCH_ASSOC);

$supplierId = $supplier ? $supplier['id'] : null;
$supplierUserId = $supplier ? $supplier['user_id'] : null;
$supplierStatus = $supplier ? $supplier['status'] : null;

// Check if supplier exists and is approved
if (!$supplierId) {
    // No supplier account found
    $_SESSION['error_message'] = "You don't have a supplier account. Please apply to become a supplier first.";
    header("Location: " . base_url('/sell-with-us.php'));
    exit;
} elseif ($supplierStatus !== 'approved') {
    // Supplier exists but not approved
    $_SESSION['warning_message'] = "Your supplier application is pending approval. You'll be notified once it's approved.";
    header("Location: " . base_url('/application-status.php'));
    exit;
}

// If we get here, the supplier is approved
// Set the supplier ID for use in the page
$supplierId = $supplier['id']; 
// Also set the supplier user_id for use in orders
$supplierUserId = $supplier['user_id']; 

// Debug info
error_log("Supplier check - ID: $supplierId, User ID: $supplierUserId"); 