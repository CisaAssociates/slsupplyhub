<?php
namespace SLSupplyHub;

use PDO;
use Exception;

class Admin {
    private $db;
    private $mail;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->mail = new MailService();
    }
    
    public function verifySupplier($supplierId, $verified) {
        try {
            $pdo = $this->db->getConnection();
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                UPDATE suppliers 
                SET verified = ?
                WHERE id = ?
            ");
            $stmt->execute([$verified, $supplierId]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception('Supplier not found');
            }
            
            // Get supplier details for notification
            $stmt = $pdo->prepare("
                SELECT s.*, u.email, u.name
                FROM suppliers s
                JOIN users u ON s.user_id = u.id
                WHERE s.id = ?
            ");
            $stmt->execute([$supplierId]);
            $supplier = $stmt->fetch();
            
            $pdo->commit();
            
            // Send verification status email
            $this->mail->sendVerificationStatusEmail($supplier['email'], $supplier['name'], $verified);
            
            return [
                'success' => true,
                'message' => 'Supplier verification status updated successfully'
            ];
            
        } catch (Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            error_log("Verify supplier error: " . $e->getMessage());
            return ['error' => 'Failed to update supplier verification status'];
        }
    }
    
    public function getDashboardStats() {
        try {
            $pdo = $this->db->getConnection();
            
            // Total orders and revenue
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_orders,
                       SUM(total_amount) as total_revenue,
                       COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_orders
                FROM orders
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute();
            $orderStats = $stmt->fetch();
            
            // Supplier stats
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_suppliers,
                       COUNT(CASE WHEN verified = 1 THEN 1 END) as verified_suppliers
                FROM suppliers
            ");
            $stmt->execute();
            $supplierStats = $stmt->fetch();
            
            // Customer stats
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_customers,
                       COUNT(CASE WHEN tier = 'Gold' THEN 1 END) as gold_customers,
                       COUNT(CASE WHEN tier = 'Silver' THEN 1 END) as silver_customers
                FROM customers c
                JOIN loyalty_rewards lr ON c.id = lr.customer_id
            ");
            $stmt->execute();
            $customerStats = $stmt->fetch();
            
            // Unassigned deliveries
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as unassigned_deliveries
                FROM orders
                WHERE status = 'pending' AND driver_id IS NULL
            ");
            $stmt->execute();
            $deliveryStats = $stmt->fetch();
            
            return [
                'success' => true,
                'stats' => [
                    'orders' => $orderStats,
                    'suppliers' => $supplierStats,
                    'customers' => $customerStats,
                    'deliveries' => $deliveryStats
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Get dashboard stats error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve dashboard statistics'];
        }
    }
    
    public function generateSalesReport($filters = []) {
        try {
            $pdo = $this->db->getConnection();
            
            $where = [];
            $params = [];
            
            if (!empty($filters['start_date'])) {
                $where[] = "o.created_at >= ?";
                $params[] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $where[] = "o.created_at <= ?";
                $params[] = $filters['end_date'];
            }
            
            if (!empty($filters['supplier_id'])) {
                $where[] = "o.supplier_id = ?";
                $params[] = $filters['supplier_id'];
            }
            
            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            $stmt = $pdo->prepare("
                SELECT 
                    DATE(o.created_at) as date,
                    s.business_name as supplier,
                    COUNT(DISTINCT o.id) as orders,
                    SUM(o.total_amount) as revenue,
                    SUM(o.delivery_fee) as delivery_fees,
                    COUNT(CASE WHEN o.status = 'failed' THEN 1 END) as failed_orders
                FROM orders o
                JOIN suppliers s ON o.supplier_id = s.id
                $whereClause
                GROUP BY DATE(o.created_at), s.id
                ORDER BY date DESC, supplier ASC
            ");
            $stmt->execute($params);
            
            return [
                'success' => true,
                'report' => $stmt->fetchAll()
            ];
            
        } catch (Exception $e) {
            error_log("Generate sales report error: " . $e->getMessage());
            return ['error' => 'Failed to generate sales report'];
        }
    }
    
    public function generateImpactReport() {
        try {
            $pdo = $this->db->getConnection();
            
            // Get municipality-wise statistics
            $stmt = $pdo->prepare("
                SELECT 
                    a.municipality,
                    COUNT(DISTINCT o.customer_id) as active_customers,
                    COUNT(DISTINCT o.supplier_id) as active_suppliers,
                    COUNT(DISTINCT o.id) as total_orders,
                    SUM(o.total_amount) as total_revenue,
                    AVG(o.total_amount) as average_order_value
                FROM orders o
                JOIN customer_addresses a ON o.address_id = a.id
                WHERE o.status = 'successful'
                GROUP BY a.municipality
                ORDER BY total_orders DESC
            ");
            $stmt->execute();
            $municipalityStats = $stmt->fetchAll();
            
            // Get supplier growth
            $stmt = $pdo->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as new_suppliers,
                    COUNT(CASE WHEN verified = 1 THEN 1 END) as verified_suppliers
                FROM suppliers
                GROUP BY month
                ORDER BY month DESC
                LIMIT 12
            ");
            $stmt->execute();
            $supplierGrowth = $stmt->fetchAll();
            
            // Get customer loyalty distribution
            $stmt = $pdo->prepare("
                SELECT 
                    tier,
                    COUNT(*) as customer_count,
                    AVG(transaction_count) as avg_transactions
                FROM loyalty_rewards
                GROUP BY tier
            ");
            $stmt->execute();
            $loyaltyDistribution = $stmt->fetchAll();
            
            return [
                'success' => true,
                'report' => [
                    'municipality_stats' => $municipalityStats,
                    'supplier_growth' => $supplierGrowth,
                    'loyalty_distribution' => $loyaltyDistribution
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Generate impact report error: " . $e->getMessage());
            return ['error' => 'Failed to generate impact report'];
        }
    }
    
    public function monitorLatePayments() {
        try {
            $pdo = $this->db->getConnection();
            
            // Get orders that are successful but COD payment not collected
            // Assuming payment should be collected within 24 hours
            $stmt = $pdo->prepare("
                SELECT o.*, 
                       s.business_name as supplier_name,
                       u.name as driver_name,
                       TIMESTAMPDIFF(HOUR, o.created_at, NOW()) as hours_elapsed
                FROM orders o
                JOIN suppliers s ON o.supplier_id = s.id
                LEFT JOIN users u ON o.driver_id = u.id
                WHERE o.status = 'successful'
                  AND o.created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY o.created_at ASC
            ");
            $stmt->execute();
            
            $latePayments = $stmt->fetchAll();
            
            // Send alerts for late payments
            foreach ($latePayments as $payment) {
                $this->mail->sendLatePaymentAlert($payment);
            }
            
            return [
                'success' => true,
                'late_payments' => $latePayments
            ];
            
        } catch (Exception $e) {
            error_log("Monitor late payments error: " . $e->getMessage());
            return ['error' => 'Failed to monitor late payments'];
        }
    }
}