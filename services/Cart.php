<?php

namespace SLSupplyHub;

class Cart extends Model
{
    protected $table = 'cart';
    protected $fillable = ['customer_id', 'product_id', 'quantity'];

    public function getCartItems($customerId)
    {
        $sql = "SELECT c.*, p.name, p.price, p.stock, p.image_path, p.regular_price, p.supplier_id,
                       CASE 
                           WHEN p.regular_price > p.price THEN 
                               ROUND(((p.regular_price - p.price) / p.regular_price) * 100) 
                           ELSE 0 
                       END as discount_percent
                FROM {$this->table} c
                JOIN products p ON c.product_id = p.id
                WHERE c.customer_id = ?";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$customerId]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $total = 0;
        foreach ($items as &$item) {
            $item['subtotal'] = $item['quantity'] * $item['price'];
            $total += $item['subtotal'];
        }

        // Calculate shipping
        $shipping = $total >= 3000 ? 0 : 90; // Free shipping for orders over ₱3000

        return [
            'items' => $items,
            'total' => $total,
            'shipping' => $shipping,
            'count' => count($items)
        ];
    }

    public function addToCart($customerId, $productId, $quantity = 1)
    {
        try {
            $sql = "INSERT INTO {$this->table} (customer_id, product_id, quantity)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE quantity = quantity + ?";

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$customerId, $productId, $quantity, $quantity]);

            return ['success' => true];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function updateQuantity($customerId, $productId, $quantity)
    {
        try {
            if ($quantity <= 0) {
                return $this->removeFromCart($customerId, $productId);
            }

            $sql = "UPDATE {$this->table} 
                    SET quantity = ? 
                    WHERE customer_id = ? AND product_id = ?";

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$quantity, $customerId, $productId]);

            return ['success' => true];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function removeFromCart($customerId, $productId)
    {
        try {
            $sql = "DELETE FROM {$this->table} 
                    WHERE customer_id = ? AND product_id = ?";

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$customerId, $productId]);

            return ['success' => true];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function clearCart($customerId)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE customer_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$customerId]);

            return ['success' => true];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function validateStock($customerId)
    {
        $sql = "SELECT c.product_id, c.quantity, p.stock, p.name
                FROM {$this->table} c
                JOIN products p ON c.product_id = p.id
                WHERE c.customer_id = ? AND c.quantity > p.stock";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function validateCartItems($userId)
    {
        try {
            // First verify if supplier exists
            $sql = "SELECT c.*, p.name, p.supplier_id, s.business_name as supplier_name,
                       s.status as supplier_status, u.status as user_status,
                       CASE 
                           WHEN s.id IS NULL THEN 'Supplier no longer exists'
                           WHEN s.status != 'approved' THEN 'Supplier not approved'
                           WHEN u.status != 'active' THEN 'Supplier account inactive'
                           WHEN p.stock < c.quantity THEN 'Insufficient stock'
                           WHEN p.status != 'active' THEN 'Product no longer available'
                       END as reason
                FROM {$this->table} c   
                JOIN products p ON c.product_id = p.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id 
                LEFT JOIN users u ON s.user_id = u.id
                WHERE c.customer_id = ?
                HAVING reason IS NOT NULL";  // Only return items that have a reason (are invalid)

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$userId]);
            $invalidItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Additional debug logging
            error_log("[Cart Debug] Validation Query: " . $sql);
            error_log("[Cart Debug] Found Invalid Items: " . print_r($invalidItems, true));
            
            return $invalidItems;
            
        } catch (\Exception $e) {
            error_log("[Cart Error] Validation error: " . $e->getMessage());
            return [];
        }
    }

    public function removeInvalidItems($customerId)
    {
        try {
            $this->beginTransaction();
            
            // Get invalid items first
            $invalidItems = $this->validateCartItems($customerId);
            
            if (!empty($invalidItems)) {
                // Remove only the invalid items
                $sql = "DELETE c FROM {$this->table} c 
                        JOIN products p ON c.product_id = p.id
                        LEFT JOIN suppliers s ON p.supplier_id = s.id 
                        LEFT JOIN users u ON s.user_id = u.id
                        WHERE c.customer_id = ? 
                        AND (s.id IS NULL 
                             OR s.status != 'approved' 
                             OR u.status != 'active'
                             OR p.stock < c.quantity
                             OR p.status != 'active')";
                
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([$customerId]);
            }
            
            $this->commit();
            
            return [
                'success' => true,
                'removed_count' => count($invalidItems),
                'removed_items' => $invalidItems
            ];
            
        } catch (\Exception $e) {
            $this->rollback();
            error_log("[Cart Error] Remove invalid items error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
