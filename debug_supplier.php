<?php
require_once __DIR__ . '/vendor/autoload.php';

use SLSupplyHub\Database;
use SLSupplyHub\Cart;
use SLSupplyHub\Session;

// Get the current user session
$session = new Session();
$userId = $session->getUserId();

echo "Current user ID: $userId\n\n";

// Get cart items
$cartModel = new Cart();
$cart = $cartModel->getCartItems($userId);

if (empty($cart['items'])) {
    echo "Cart is empty\n";
    exit;
}

// Get the supplier_id from the first cart item
$supplierIdFromCart = $cart['items'][0]['supplier_id'];
echo "Supplier ID from cart: $supplierIdFromCart\n\n";

// Get database connection
$db = Database::getInstance();

// Check if this ID exists in users table
$stmt = $db->executeQuery("SELECT id, email, fullname, role, status FROM users WHERE id = ?", [$supplierIdFromCart]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "User record:\n";
print_r($user);
echo "\n";

// Check if this user has a record in suppliers table
$stmt = $db->executeQuery("SELECT * FROM suppliers WHERE user_id = ?", [$supplierIdFromCart]);
$supplier = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Supplier record:\n";
print_r($supplier);
echo "\n";

// Check the products in the cart
echo "Products in cart:\n";
foreach ($cart['items'] as $item) {
    echo "Product ID: {$item['product_id']}, Name: {$item['name']}, Supplier ID: {$item['supplier_id']}\n";
}
echo "\n";

// Check the foreign key constraints
echo "Foreign key constraints:\n";
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
print_r($constraints); 