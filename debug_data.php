<?php
require_once __DIR__ . '/vendor/autoload.php';

use SLSupplyHub\Database;

// Get database connection
$db = Database::getInstance();

// Check products table
echo "Products:\n";
$stmt = $db->executeQuery("SELECT id, supplier_id, name FROM products LIMIT 5");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($products);

// Check suppliers table
echo "\nSuppliers:\n";
$stmt = $db->executeQuery("SELECT id, user_id, business_name FROM suppliers LIMIT 5");
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($suppliers);

// Check users table for suppliers
echo "\nSupplier Users:\n";
$stmt = $db->executeQuery("SELECT id, email, fullname, role FROM users WHERE role = 'supplier' LIMIT 5");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($users);

// Check the relationship between products and suppliers
echo "\nProduct-Supplier Relationship:\n";
$stmt = $db->executeQuery("
    SELECT 
        p.id as product_id, 
        p.name as product_name,
        p.supplier_id,
        s.id as supplier_table_id,
        s.user_id as supplier_user_id,
        u.id as user_id,
        u.fullname as user_name
    FROM products p
    LEFT JOIN suppliers s ON p.supplier_id = s.user_id
    LEFT JOIN users u ON p.supplier_id = u.id
    LIMIT 5
");
$relationships = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($relationships); 