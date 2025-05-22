<?php
require_once __DIR__ . '/vendor/autoload.php';

use SLSupplyHub\Database;

// Get database connection
$db = Database::getInstance();

// Get all suppliers with their user details
echo "All Suppliers:\n";
$stmt = $db->executeQuery("
    SELECT 
        s.id as supplier_id, 
        s.user_id, 
        s.business_name, 
        s.status as supplier_status,
        u.fullname,
        u.email,
        u.role,
        u.status as user_status
    FROM 
        suppliers s
    JOIN 
        users u ON s.user_id = u.id
");
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($suppliers as $supplier) {
    echo "Supplier ID: {$supplier['supplier_id']}, User ID: {$supplier['user_id']}, Name: {$supplier['business_name']}, Status: {$supplier['supplier_status']}\n";
    echo "User: {$supplier['fullname']}, Email: {$supplier['email']}, Role: {$supplier['role']}, Status: {$supplier['user_status']}\n\n";
}

// Get all products with their supplier details
echo "\nAll Products:\n";
$stmt = $db->executeQuery("
    SELECT 
        p.id as product_id, 
        p.name as product_name,
        p.supplier_id,
        u.fullname as user_name,
        u.role as user_role,
        s.id as supplier_table_id,
        s.business_name
    FROM 
        products p
    JOIN 
        users u ON p.supplier_id = u.id
    LEFT JOIN 
        suppliers s ON u.id = s.user_id
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $product) {
    echo "Product ID: {$product['product_id']}, Name: {$product['product_name']}\n";
    echo "Supplier ID (user_id): {$product['supplier_id']}, User: {$product['user_name']}, Role: {$product['user_role']}\n";
    echo "Supplier Table ID: {$product['supplier_table_id']}, Business: {$product['business_name']}\n\n";
}

// Check the foreign key constraints
echo "\nForeign Key Constraints:\n";
$stmt = $db->executeQuery("
    SELECT 
        TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM
        INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
        REFERENCED_TABLE_NAME IN ('users', 'suppliers')
        AND TABLE_NAME IN ('products', 'orders')
        AND CONSTRAINT_SCHEMA = DATABASE()
");
$constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($constraints as $constraint) {
    echo "{$constraint['TABLE_NAME']}.{$constraint['COLUMN_NAME']} -> {$constraint['REFERENCED_TABLE_NAME']}.{$constraint['REFERENCED_COLUMN_NAME']} ({$constraint['CONSTRAINT_NAME']})\n";
} 