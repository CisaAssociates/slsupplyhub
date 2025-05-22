<?php
require_once 'vendor/autoload.php';

// Initialize database connection
$db = SLSupplyHub\Database::getInstance();
$conn = $db->getConnection();

echo "Starting product data fix...\n";

// Get all products with their current supplier_id
$stmt = $conn->query("SELECT id, supplier_id FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($products) . " products to check.\n";

// Get all suppliers with their user_id
$stmt = $conn->query("SELECT id, user_id FROM suppliers");
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create a mapping from user_id to supplier_id
$userToSupplierMap = [];
foreach ($suppliers as $supplier) {
    $userToSupplierMap[$supplier['user_id']] = $supplier['id'];
}

echo "Found " . count($suppliers) . " suppliers.\n";
echo "User ID to Supplier ID mapping: " . json_encode($userToSupplierMap) . "\n\n";

// Check and fix each product
$updatedCount = 0;
$errorCount = 0;

foreach ($products as $product) {
    $productId = $product['id'];
    $currentSupplierId = $product['supplier_id'];
    
    // Check if the current supplier_id is a user_id that needs to be mapped to a supplier_id
    if (isset($userToSupplierMap[$currentSupplierId])) {
        $newSupplierId = $userToSupplierMap[$currentSupplierId];
        
        echo "Product ID $productId: Current supplier_id is $currentSupplierId (user_id), should be $newSupplierId (supplier_id)\n";
        
        // Update the product
        try {
            $updateStmt = $conn->prepare("UPDATE products SET supplier_id = ? WHERE id = ?");
            $result = $updateStmt->execute([$newSupplierId, $productId]);
            
            if ($result) {
                echo "  Updated successfully\n";
                $updatedCount++;
            } else {
                echo "  Failed to update\n";
                $errorCount++;
            }
        } catch (Exception $e) {
            echo "  Error updating: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    } else {
        // Check if this supplier_id exists in the suppliers table
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM suppliers WHERE id = ?");
        $checkStmt->execute([$currentSupplierId]);
        $exists = $checkStmt->fetchColumn();
        
        if ($exists) {
            echo "Product ID $productId: supplier_id $currentSupplierId is valid\n";
        } else {
            echo "Product ID $productId: supplier_id $currentSupplierId is invalid (not found in suppliers table)\n";
            $errorCount++;
            
            // If we have at least one supplier, assign the product to the first one
            if (!empty($suppliers)) {
                $firstSupplierId = $suppliers[0]['id'];
                echo "  Assigning to supplier ID $firstSupplierId\n";
                
                try {
                    $updateStmt = $conn->prepare("UPDATE products SET supplier_id = ? WHERE id = ?");
                    $result = $updateStmt->execute([$firstSupplierId, $productId]);
                    
                    if ($result) {
                        echo "  Updated successfully\n";
                        $updatedCount++;
                    } else {
                        echo "  Failed to update\n";
                    }
                } catch (Exception $e) {
                    echo "  Error updating: " . $e->getMessage() . "\n";
                }
            }
        }
    }
}

echo "\nProduct data fix completed.\n";
echo "Updated $updatedCount products.\n";
echo "Encountered $errorCount errors.\n";

// Now verify the data
echo "\nVerifying product data:\n";
$stmt = $conn->query("
    SELECT p.id, p.name, p.supplier_id, s.id AS actual_supplier_id
    FROM products p
    LEFT JOIN suppliers s ON p.supplier_id = s.id
");
$verification = $stmt->fetchAll(PDO::FETCH_ASSOC);

$validCount = 0;
$invalidCount = 0;

foreach ($verification as $item) {
    if ($item['supplier_id'] == $item['actual_supplier_id']) {
        $validCount++;
    } else {
        $invalidCount++;
        echo "Invalid product: ID " . $item['id'] . ", Name: " . $item['name'] . 
             ", Supplier ID: " . $item['supplier_id'] . 
             ", Actual Supplier ID: " . ($item['actual_supplier_id'] ?? 'NULL') . "\n";
    }
}

echo "\nVerification complete: $validCount valid products, $invalidCount invalid products.\n"; 