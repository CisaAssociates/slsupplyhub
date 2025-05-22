<?php
require_once 'vendor/autoload.php';

// Initialize database connection
$db = SLSupplyHub\Database::getInstance();
$conn = $db->getConnection();

echo "Creating a workaround solution...\n";

// First, let's check the current products and their mapping
echo "Current products in the database:\n";
$stmt = $conn->query("
    SELECT p.id, p.name, p.supplier_id, s.id AS supplier_table_id, s.user_id, u.fullname
    FROM products p
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    LEFT JOIN users u ON s.user_id = u.id
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $product) {
    echo "Product ID: " . $product['id'] . 
         " | Name: " . $product['name'] . 
         " | Supplier ID: " . $product['supplier_id'] . 
         " | Supplier Table ID: " . ($product['supplier_table_id'] ?? 'NULL') . 
         " | User ID: " . ($product['user_id'] ?? 'NULL') . 
         " | Supplier Name: " . ($product['fullname'] ?? 'NULL') . "\n";
}

// Create a modified version of the getProductsBySupplier method
echo "\nCreating modified Product class with updated getProductsBySupplier method...\n";

$modifiedFile = <<<'EOD'
<?php
namespace SLSupplyHub;

class ProductFixed extends Product {
    public function getProductsBySupplier($supplierId, $page = 1, $perPage = 10) {
        try {
            $offset = ($page - 1) * $perPage;
            
            // Get supplier user_id if needed
            $stmt = $this->db->getConnection()->prepare(
                "SELECT user_id FROM suppliers WHERE id = ?"
            );
            $stmt->execute([$supplierId]);
            $supplierUserId = $stmt->fetchColumn();
            
            if (!$supplierUserId) {
                return ['items' => [], 'total' => 0, 'current_page' => $page, 'per_page' => $perPage, 'last_page' => 0];
            }
            
            // Get total count - check both supplier_id = supplier.id and supplier_id = user_id
            $stmt = $this->db->getConnection()->prepare(
                "SELECT COUNT(*) FROM {$this->table} 
                 WHERE supplier_id = ? OR supplier_id = ?"
            );
            $stmt->execute([$supplierId, $supplierUserId]);
            $total = $stmt->fetchColumn();
            
            // Get products - check both supplier_id = supplier.id and supplier_id = user_id
            $stmt = $this->db->getConnection()->prepare(
                "SELECT * FROM {$this->table} 
                 WHERE supplier_id = ? OR supplier_id = ?
                 LIMIT ? OFFSET ?"
            );
            $stmt->execute([$supplierId, $supplierUserId, $perPage, $offset]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'items' => $items,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage)
            ];
            
        } catch (\Exception $e) {
            error_log("Get products error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve products'];
        }
    }
}

// Create an instance to test
$productFixed = new ProductFixed();
EOD;

// Save to a temporary file
file_put_contents('ProductFixed.php', $modifiedFile);
echo "Created ProductFixed.php\n";

// Now let's create a test script
$testScript = <<<'EOD'
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
EOD;

// Save the test script
file_put_contents('test_product_fixed.php', $testScript);
echo "Created test_product_fixed.php\n";

echo "\nWorkaround solution created. Please run 'php test_product_fixed.php' to test it.\n";
echo "If the test is successful, you can modify services/Product.php to use the fixed getProductsBySupplier method.\n"; 