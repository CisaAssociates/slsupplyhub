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