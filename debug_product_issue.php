<?php
require_once 'vendor/autoload.php';

// Initialize database connection
$db = SLSupplyHub\Database::getInstance();
$conn = $db->getConnection();

// Check all suppliers
echo "\n=== ALL SUPPLIERS ===\n";
$stmt = $conn->query("SELECT s.id, s.user_id, s.status, u.fullname FROM suppliers s JOIN users u ON s.user_id = u.id");
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($suppliers as $supplier) {
    echo "Supplier ID: " . $supplier['id'] . " | User ID: " . $supplier['user_id'] . 
         " | Status: " . $supplier['status'] . " | Name: " . $supplier['fullname'] . "\n";
}

// Check all products
echo "\n=== ALL PRODUCTS ===\n";
$stmt = $conn->query("SELECT id, name, supplier_id, status, created_at FROM products ORDER BY created_at DESC LIMIT 10");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($products as $product) {
    echo "ID: " . $product['id'] . " | Name: " . $product['name'] . " | Supplier ID: " . $product['supplier_id'] . 
         " | Status: " . $product['status'] . " | Created: " . $product['created_at'] . "\n";
}

// Get products for each supplier
echo "\n=== PRODUCTS BY SUPPLIER ===\n";
foreach ($suppliers as $supplier) {
    $supplierId = $supplier['id'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE supplier_id = ?");
    $stmt->execute([$supplierId]);
    $productCount = $stmt->fetchColumn();
    
    echo "Supplier ID: " . $supplierId . " | Name: " . $supplier['fullname'] . " | Product Count: " . $productCount . "\n";
    
    if ($productCount > 0) {
        $stmt = $conn->prepare("SELECT id, name FROM products WHERE supplier_id = ? LIMIT 3");
        $stmt->execute([$supplierId]);
        $supplierProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($supplierProducts as $product) {
            echo "  - Product ID: " . $product['id'] . " | Name: " . $product['name'] . "\n";
        }
    }
}

// Check the product_edit.php file for supplier_id assignment
echo "\n=== CHECKING PRODUCT CREATION CODE ===\n";
echo "In product-edit.php, supplier_id is now set to: \$supplierId\n";

// Check the getProductsBySupplier method
echo "\n=== TESTING getProductsBySupplier METHOD ===\n";
$productService = new SLSupplyHub\Product();

// Test with the first supplier ID we found
if (!empty($suppliers)) {
    $testSupplierId = $suppliers[0]['id'];
    echo "Testing with supplier ID: " . $testSupplierId . "\n";
    
    $products = $productService->getProductsBySupplier($testSupplierId, 1);
    
    if (isset($products['error'])) {
        echo "Error: " . $products['error'] . "\n";
    } else {
        echo "Total products found: " . ($products['total'] ?? 0) . "\n";
        echo "Items returned: " . (is_array($products['items']) ? count($products['items']) : 0) . "\n";
        
        if (!empty($products['items'])) {
            echo "First product: " . $products['items'][0]['name'] . " (ID: " . $products['items'][0]['id'] . ")\n";
        }
    }
}

// Add a test product directly to the database for a test supplier
echo "\n=== ADDING TEST PRODUCT ===\n";
if (!empty($suppliers)) {
    $testSupplierId = $suppliers[0]['id'];
    echo "Using supplier ID: " . $testSupplierId . "\n";
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO products (supplier_id, name, description, price, stock, unit, category_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $testSupplierId,
            'Debug Test Product ' . date('Y-m-d H:i:s'),
            'This is a test product created by the debug script',
            99.99,
            10,
            'piece',
            1, // Assuming category ID 1 exists
            'active'
        ]);
        
        if ($result) {
            $newId = $conn->lastInsertId();
            echo "Test product created successfully with ID: " . $newId . "\n";
            
            // Now verify it can be retrieved
            $products = $productService->getProductsBySupplier($testSupplierId, 1);
            echo "After adding test product, total products for supplier: " . ($products['total'] ?? 0) . "\n";
        } else {
            echo "Failed to create test product\n";
        }
    } catch (Exception $e) {
        echo "Error creating test product: " . $e->getMessage() . "\n";
    }
} 