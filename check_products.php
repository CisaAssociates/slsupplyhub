<?php
require_once __DIR__ . '/vendor/autoload.php';

use SLSupplyHub\Database;

// Get database connection
$db = Database::getInstance();

// Check products
echo "Products:\n";
$stmt = $db->executeQuery("SELECT id, name, supplier_id FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $product) {
    echo "Product ID: {$product['id']}, Name: {$product['name']}, Supplier ID: {$product['supplier_id']}\n";
}

// Check users
echo "\nUsers:\n";
$stmt = $db->executeQuery("SELECT id, email, fullname, role FROM users WHERE role = 'supplier'");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    echo "User ID: {$user['id']}, Name: {$user['fullname']}, Email: {$user['email']}, Role: {$user['role']}\n";
}

// Check suppliers
echo "\nSuppliers:\n";
$stmt = $db->executeQuery("SELECT id, user_id, business_name, status FROM suppliers");
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($suppliers as $supplier) {
    echo "Supplier ID: {$supplier['id']}, User ID: {$supplier['user_id']}, Name: {$supplier['business_name']}, Status: {$supplier['status']}\n";
} 