<?php
require_once 'vendor/autoload.php';

// Initialize database connection
$db = SLSupplyHub\Database::getInstance();
$conn = $db->getConnection();

// Check products table structure
echo "=== PRODUCTS TABLE STRUCTURE ===\n";
$stmt = $conn->query("SHOW CREATE TABLE products");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo $result['Create Table'] . "\n\n";

// Check suppliers table structure
echo "=== SUPPLIERS TABLE STRUCTURE ===\n";
$stmt = $conn->query("SHOW CREATE TABLE suppliers");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo $result['Create Table'] . "\n\n";

// Check users table structure
echo "=== USERS TABLE STRUCTURE ===\n";
$stmt = $conn->query("SHOW CREATE TABLE users");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo $result['Create Table'] . "\n\n";

// Check foreign key constraints
echo "=== FOREIGN KEY CONSTRAINTS ===\n";
$stmt = $conn->query("SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'users' AND TABLE_NAME = 'products'");
$constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($constraints as $constraint) {
    echo "Constraint: " . $constraint['CONSTRAINT_NAME'] . "\n";
    echo "  Table: " . $constraint['TABLE_NAME'] . "\n";
    echo "  Column: " . $constraint['COLUMN_NAME'] . "\n";
    echo "  Referenced Table: " . $constraint['REFERENCED_TABLE_NAME'] . "\n";
    echo "  Referenced Column: " . $constraint['REFERENCED_COLUMN_NAME'] . "\n\n";
} 