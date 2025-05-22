<?php
require_once 'vendor/autoload.php';
require_once 'ProductFixed.php';

// Get supplier ID from session
session_start();
$db = SLSupplyHub\Database::getInstance();
$conn = $db->getConnection();

// Get a test supplier ID
$stmt = $conn->query("SELECT id FROM suppliers LIMIT 1");
$supplierId = $stmt->fetchColumn();

echo "Testing with supplier ID: $supplierId\n\n";

// Test the original method
$productOriginal = new SLSupplyHub\Product();
$productsOriginal = $productOriginal->getProductsBySupplier($supplierId);

echo "Original method results:\n";
echo "Total products: " . ($productsOriginal['total'] ?? 0) . "\n";
echo "Items returned: " . (is_array($productsOriginal['items']) ? count($productsOriginal['items']) : 0) . "\n";

// Test the fixed method
$productFixed = new SLSupplyHub\ProductFixed();
$productsFixed = $productFixed->getProductsBySupplier($supplierId);

echo "\nFixed method results:\n";
echo "Total products: " . ($productsFixed['total'] ?? 0) . "\n";
echo "Items returned: " . (is_array($productsFixed['items']) ? count($productsFixed['items']) : 0) . "\n";

if (!empty($productsFixed['items'])) {
    echo "\nProducts found:\n";
    foreach ($productsFixed['items'] as $product) {
        echo "ID: " . $product['id'] . " | Name: " . $product['name'] . " | Supplier ID: " . $product['supplier_id'] . "\n";
    }
}