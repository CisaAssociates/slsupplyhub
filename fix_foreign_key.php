<?php
require_once 'vendor/autoload.php';

// Initialize database connection
$db = SLSupplyHub\Database::getInstance();
$conn = $db->getConnection();

echo "Starting foreign key constraint fix...\n";

// First, get the exact constraint name
try {
    echo "Getting current constraint name...\n";
    $stmt = $conn->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = 'products' 
        AND COLUMN_NAME = 'supplier_id' 
        AND REFERENCED_TABLE_NAME = 'users'
        LIMIT 1
    ");
    $constraint = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($constraint) {
        $constraintName = $constraint['CONSTRAINT_NAME'];
        echo "Found constraint: $constraintName\n";
        
        // Try to drop the constraint
        try {
            echo "Dropping constraint '$constraintName'...\n";
            $conn->exec("ALTER TABLE products DROP FOREIGN KEY `$constraintName`");
            echo "Successfully dropped '$constraintName'\n";
            
            // Try to add the correct foreign key constraint
            try {
                echo "Adding correct foreign key constraint...\n";
                $conn->exec("ALTER TABLE products ADD CONSTRAINT products_supplier_id_fk FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE ON UPDATE RESTRICT");
                echo "Successfully added new foreign key constraint 'products_supplier_id_fk'\n";
            } catch (Exception $e) {
                echo "Error adding new constraint: " . $e->getMessage() . "\n";
            }
        } catch (Exception $e) {
            echo "Error dropping constraint: " . $e->getMessage() . "\n";
        }
    } else {
        echo "No constraint found linking products.supplier_id to users table\n";
        
        // Try to add the correct foreign key constraint directly
        try {
            echo "Adding foreign key constraint...\n";
            $conn->exec("ALTER TABLE products ADD CONSTRAINT products_supplier_id_fk FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE ON UPDATE RESTRICT");
            echo "Successfully added new foreign key constraint 'products_supplier_id_fk'\n";
        } catch (Exception $e) {
            echo "Error adding constraint: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error querying constraints: " . $e->getMessage() . "\n";
}

echo "Foreign key constraint update process completed.\n";

// Verify the current constraints
echo "\nVerifying current constraints:\n";
try {
    $stmt = $conn->query("SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'products' AND COLUMN_NAME = 'supplier_id'");
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($constraints) > 0) {
        foreach ($constraints as $constraint) {
            echo "Constraint: " . $constraint['CONSTRAINT_NAME'] . "\n";
            echo "  Referenced Table: " . ($constraint['REFERENCED_TABLE_NAME'] ?? 'None') . "\n";
            echo "  Referenced Column: " . ($constraint['REFERENCED_COLUMN_NAME'] ?? 'None') . "\n";
        }
    } else {
        echo "No constraints found for products.supplier_id\n";
    }
} catch (Exception $e) {
    echo "Error verifying constraints: " . $e->getMessage() . "\n";
} 