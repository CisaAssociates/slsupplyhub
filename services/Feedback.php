<?php
namespace SLSupplyHub;

class Feedback extends Model {
    protected $table = 'feedback';
    protected $fillable = [
        'customer_id', 'order_id', 'supplier_id', 'driver_id',
        'product_id', 'rating', 'comment', 'type'
    ];
    
    protected $validationRules = [
        'customer_id' => ['required', 'numeric'],
        'order_id' => ['required', 'numeric'],
        'rating' => ['required', 'numeric', 'min:1', 'max:5'],
        'comment' => ['max:1000'],
        'type' => ['required']
    ];
    
    public function createFeedback($data) {
        try {
            // Validate feedback data
            $errors = $this->validate($data);
            if (!empty($errors)) {
                error_log("[Feedback] Validation errors: " . json_encode($errors));
                return ['error' => $errors];
            }
            
            $this->beginTransaction();
            
            // Create feedback
            $feedbackId = $this->create($data);
            
            if (!$feedbackId) {
                throw new \Exception("Failed to create feedback record");
            }

            // Update average ratings based on type
            switch ($data['type']) {
                case 'supplier':
                    if (isset($data['supplier_id'])) {
                        $this->updateSupplierRating($data['supplier_id']);
                    }
                    break;
                case 'driver':
                    if (isset($data['driver_id'])) {
                        $this->updateDriverRating($data['driver_id']);
                    }
                    break;
                case 'product':
                    if (isset($data['product_id'])) {
                        $this->updateProductRating($data['product_id']);
                    }
                    break;
            }
            
            $this->commit();
            
            error_log("[Feedback] Created successfully with ID: " . $feedbackId);
            return ['success' => true, 'feedback_id' => $feedbackId];
            
        } catch (\Exception $e) {
            $this->rollback();
            error_log("[Feedback] Creation error: " . $e->getMessage());
            error_log("[Feedback] Stack trace: " . $e->getTraceAsString());
            return ['error' => 'Failed to submit feedback'];
        }
    }

    private function updateProductRating($productId) {
        try {
            $sql = "UPDATE products SET 
                    rating = (
                        SELECT AVG(rating) 
                        FROM {$this->table} 
                        WHERE product_id = ? AND type = 'product'
                    )
                    WHERE id = ?";
            
            $stmt = $this->db->executeQuery($sql, [$productId, $productId]);
            return true;
        } catch (\Exception $e) {
            error_log("[Feedback] Update product rating error: " . $e->getMessage());
            return false;
        }
    }

    private function updateSupplierRating($supplierId) {
        try {
            $sql = "UPDATE users SET 
                    rating = (
                        SELECT AVG(rating) 
                        FROM {$this->table} 
                        WHERE supplier_id = ? AND type = 'supplier'
                    )
                    WHERE id = ?";
            
            $stmt = $this->db->executeQuery($sql, [$supplierId, $supplierId]);
            return true;
        } catch (\Exception $e) {
            error_log("[Feedback] Update supplier rating error: " . $e->getMessage());
            return false;
        }
    }
    
    private function updateDriverRating($driverId) {
        try {
            $sql = "UPDATE users SET 
                    rating = (
                        SELECT AVG(rating) 
                        FROM {$this->table} 
                        WHERE driver_id = ? AND type = 'driver'
                    )
                    WHERE id = ?";
            
            $stmt = $this->db->executeQuery($sql, [$driverId, $driverId]);
            return true;
        } catch (\Exception $e) {
            error_log("[Feedback] Update driver rating error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getSupplierFeedback($supplierId, $page = 1, $perPage = 10) {
        try {
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $stmt = $this->db->getConnection()->prepare(
                "SELECT COUNT(*) FROM {$this->table} 
                WHERE supplier_id = ? AND type = 'supplier'"
            );
            $stmt->execute([$supplierId]);
            $total = $stmt->fetchColumn();
            
            // Get feedback with customer info
            $sql = "SELECT f.*, u.fullname as customer_name, o.created_at as order_date
                   FROM {$this->table} f 
                   JOIN users u ON f.customer_id = u.id
                   JOIN orders o ON f.order_id = o.id
                   WHERE f.supplier_id = ? AND f.type = 'supplier'
                   ORDER BY f.created_at DESC
                   LIMIT ? OFFSET ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$supplierId, $perPage, $offset]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'items' => $items,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage)
            ];
            
        } catch (\Exception $e) {
            error_log("Get supplier feedback error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve feedback'];
        }
    }
    
    public function getDriverFeedback($driverId, $page = 1, $perPage = 10) {
        try {
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $stmt = $this->db->getConnection()->prepare(
                "SELECT COUNT(*) FROM {$this->table} 
                WHERE driver_id = ? AND type = 'driver'"
            );
            $stmt->execute([$driverId]);
            $total = $stmt->fetchColumn();
            
            // Get feedback with customer info
            $sql = "SELECT f.*, u.fullname as customer_name, o.created_at as order_date
                   FROM {$this->table} f 
                   JOIN users u ON f.customer_id = u.id
                   JOIN orders o ON f.order_id = o.id
                   WHERE f.driver_id = ? AND f.type = 'driver'
                   ORDER BY f.created_at DESC
                   LIMIT ? OFFSET ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$driverId, $perPage, $offset]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'items' => $items,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage)
            ];
            
        } catch (\Exception $e) {
            error_log("Get driver feedback error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve feedback'];
        }
    }
    
    public function getOrderFeedback($orderId) {
        try {
            $sql = "SELECT f.*, u.fullname as customer_name,
                   CASE f.type 
                       WHEN 'supplier' THEN s.business_name
                       WHEN 'driver' THEN CONCAT(d.vehicle_type, ' - ', d.vehicle_plate)
                       WHEN 'product' THEN p.name
                   END as target_name
                   FROM {$this->table} f
                   JOIN users u ON f.customer_id = u.id
                   LEFT JOIN suppliers s ON f.supplier_id = s.id AND f.type = 'supplier'
                   LEFT JOIN drivers d ON f.driver_id = d.id AND f.type = 'driver'
                   LEFT JOIN products p ON f.product_id = p.id AND f.type = 'product'
                   WHERE f.order_id = ?
                   ORDER BY f.created_at DESC";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$orderId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get order feedback error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve feedback'];
        }
    }
    
    public function getFeedbackSummary($type, $id) {
        try {
            $sql = "SELECT 
                   COUNT(*) as total_reviews,
                   AVG(rating) as average_rating,
                   COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                   COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                   COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                   COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                   COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
                   FROM {$this->table}
                   WHERE " . ($type === 'product' ? 'product_id' : ($type === 'supplier' ? 'supplier_id' : 'driver_id')) . " = ? 
                   AND type = ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id, $type]);
            $summary = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($summary['total_reviews'] > 0) {
                $summary['rating_distribution'] = [
                    5 => ($summary['five_star'] / $summary['total_reviews']) * 100,
                    4 => ($summary['four_star'] / $summary['total_reviews']) * 100,
                    3 => ($summary['three_star'] / $summary['total_reviews']) * 100,
                    2 => ($summary['two_star'] / $summary['total_reviews']) * 100,
                    1 => ($summary['one_star'] / $summary['total_reviews']) * 100
                ];
            } else {
                $summary['rating_distribution'] = [
                    5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0
                ];
                $summary['average_rating'] = 0;
            }
            
            return $summary;
            
        } catch (\Exception $e) {
            error_log("Get feedback summary error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve feedback summary'];
        }
    }
    
    public function hasUserReviewed($userId, $productId) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}
                   WHERE customer_id = ? 
                   AND product_id = ?
                   AND type = 'product'";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$userId, $productId]);
            return (bool)$stmt->fetchColumn();
            
        } catch (\Exception $e) {
            error_log("Check user review error: " . $e->getMessage());
            return false;
        }
    }
    
    public function hasOrderFeedback($orderId, $type, $id) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}
                   WHERE order_id = ? 
                   AND type = ?
                   AND " . ($type === 'product' ? 'product_id' : ($type === 'supplier' ? 'supplier_id' : 'driver_id')) . " = ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$orderId, $type, $id]);
            return (bool)$stmt->fetchColumn();
            
        } catch (\Exception $e) {
            error_log("Check order feedback error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getProductReviews($productId, $page = 1, $perPage = 5) {
        try {
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $stmt = $this->db->getConnection()->prepare(
                "SELECT COUNT(*) FROM {$this->table} 
                WHERE product_id = ? AND type = 'product'"
            );
            $stmt->execute([$productId]);
            $total = $stmt->fetchColumn();
            
            // Get reviews with customer info
            $sql = "SELECT f.*, u.fullname as customer_name
                   FROM {$this->table} f
                   JOIN users u ON f.customer_id = u.id
                   WHERE f.product_id = ? 
                   AND f.type = 'product'
                   ORDER BY f.created_at DESC
                   LIMIT ? OFFSET ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$productId, $perPage, $offset]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'items' => $items,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage)
            ];
            
        } catch (\Exception $e) {
            error_log("Get product reviews error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve reviews'];
        }
    }
}