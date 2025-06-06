<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../services/helpers/url_helper.php';
require_once __DIR__ . '/../services/init.php';
use SLSupplyHub\Product;
use SLSupplyHub\Session;

$session = new Session();

// Include supplier approval check
include 'check-approval.php';

// Get product ID
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$productId) {
    header('Location: products.php');
    exit;
}

// Initialize Product service
$productService = new Product();

// Get product to verify ownership
$product = $productService->find($productId);

// Verify product belongs to this supplier
if (!$product || $product['supplier_id'] != $session->getUserId()) {
    header('Location: products.php');
    exit;
}

// Delete product
$result = $productService->deleteProduct($productId);

if (isset($result['success'])) {
    header('Location: products.php?deleted=1');
} else {
    header('Location: products.php?error=' . urlencode($result['error'] ?? 'Failed to delete product'));
}
exit;