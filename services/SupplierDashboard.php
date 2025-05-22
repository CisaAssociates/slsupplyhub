<?php
namespace SLSupplyHub;

use PDO;
use Exception;

class SupplierDashboard extends Model {
    protected $db;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getPerformanceMetrics($supplierId, $period = 30) {
        try {
            $pdo = $this->db->getConnection();
            
            // Sales metrics
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'successful' THEN 1 ELSE 0 END) as successful_orders,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_orders,
                    SUM(total_amount) as total_revenue,
                    SUM(delivery_fee) as total_delivery_fees
                FROM orders
                WHERE supplier_id = ?
                AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
            ");
            $stmt->execute([$supplierId, $period]);
            $salesMetrics = $stmt->fetch();
            
            // Product performance
            $stmt = $pdo->prepare("
                SELECT 
                    p.id,
                    p.name,
                    p.category,
                    COUNT(oi.id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * oi.price) as total_revenue
                FROM products p
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id
                WHERE p.supplier_id = ?
                AND o.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
                GROUP BY p.id, p.name, p.category
                ORDER BY total_revenue DESC
                LIMIT 10
            ");
            $stmt->execute([$supplierId, $period]);
            $productPerformance = $stmt->fetchAll();
            
            // Municipality distribution
            $stmt = $pdo->prepare("
                SELECT 
                    a.municipality,
                    COUNT(DISTINCT o.id) as order_count,
                    COUNT(DISTINCT o.customer_id) as customer_count,
                    SUM(o.total_amount) as total_revenue
                FROM orders o
                JOIN customer_addresses a ON o.address_id = a.id
                WHERE o.supplier_id = ?
                AND o.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
                GROUP BY a.municipality
                ORDER BY order_count DESC
            ");
            $stmt->execute([$supplierId, $period]);
            $municipalityStats = $stmt->fetchAll();
            
            // Daily trend
            $stmt = $pdo->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as order_count,
                    SUM(total_amount) as revenue
                FROM orders
                WHERE supplier_id = ?
                AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ");
            $stmt->execute([$supplierId, $period]);
            $dailyTrend = $stmt->fetchAll();
            
            // Delivery performance
            $stmt = $pdo->prepare("
                SELECT 
                    u.name as driver_name,
                    COUNT(*) as total_deliveries,
                    COUNT(CASE WHEN o.status = 'successful' THEN 1 END) as successful_deliveries,
                    COUNT(CASE WHEN o.status = 'failed' THEN 1 END) as failed_deliveries,
                    AVG(TIMESTAMPDIFF(HOUR, o.created_at, 
                        CASE WHEN o.status != 'pending' 
                        THEN o.updated_at 
                        ELSE CURRENT_TIMESTAMP END)) as avg_delivery_hours
                FROM orders o
                JOIN users u ON o.driver_id = u.id
                WHERE o.supplier_id = ?
                AND o.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
                GROUP BY u.id, u.name
            ");
            $stmt->execute([$supplierId, $period]);
            $driverPerformance = $stmt->fetchAll();
            
            // Stock alerts
            $stmt = $pdo->prepare("
                SELECT id, name, stock, category
                FROM products
                WHERE supplier_id = ?
                AND stock <= 5
                ORDER BY stock ASC
            ");
            $stmt->execute([$supplierId]);
            $lowStockAlerts = $stmt->fetchAll();
            
            return [
                'success' => true,
                'metrics' => [
                    'sales' => $salesMetrics,
                    'top_products' => $productPerformance,
                    'municipality_distribution' => $municipalityStats,
                    'daily_trend' => $dailyTrend,
                    'driver_performance' => $driverPerformance,
                    'low_stock_alerts' => $lowStockAlerts
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Get supplier metrics error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve performance metrics'];
        }
    }
    
    public function getCustomerFeedback($supplierId, $period = 30) {
        try {
            $pdo = $this->db->getConnection();
            
            $stmt = $pdo->prepare("
                SELECT 
                    o.id as order_id,
                    u.name as customer_name,
                    o.feedback_rating,
                    o.feedback_comment,
                    o.created_at
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
                JOIN users u ON c.user_id = u.id
                WHERE o.supplier_id = ?
                AND o.feedback_rating IS NOT NULL
                AND o.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
                ORDER BY o.created_at DESC
            ");
            $stmt->execute([$supplierId, $period]);
            
            return [
                'success' => true,
                'feedback' => $stmt->fetchAll()
            ];
            
        } catch (Exception $e) {
            error_log("Get customer feedback error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve customer feedback'];
        }
    }
    
    public function getDriverManagement($supplierId) {
        try {
            $pdo = $this->db->getConnection();
            
            // Get active drivers
            $stmt = $pdo->prepare("
                SELECT 
                    u.id,
                    u.name,
                    u.email,
                    u.created_at as joined_date,
                    COUNT(o.id) as total_deliveries,
                    COUNT(CASE WHEN o.status = 'successful' THEN 1 END) as successful_deliveries,
                    COUNT(CASE WHEN o.status = 'failed' THEN 1 END) as failed_deliveries
                FROM users u
                LEFT JOIN orders o ON u.id = o.driver_id
                WHERE u.role = 'driver'
                AND EXISTS (
                    SELECT 1 FROM driver_suppliers ds 
                    WHERE ds.driver_id = u.id 
                    AND ds.supplier_id = ?
                )
                GROUP BY u.id, u.name, u.email, u.created_at
            ");
            $stmt->execute([$supplierId]);
            $drivers = $stmt->fetchAll();
            
            // Get unassigned orders
            $stmt = $pdo->prepare("
                SELECT o.id, o.created_at, o.total_amount,
                       a.municipality, a.barangay
                FROM orders o
                JOIN customer_addresses a ON o.address_id = a.id
                WHERE o.supplier_id = ?
                AND o.status = 'pending'
                AND o.driver_id IS NULL
            ");
            $stmt->execute([$supplierId]);
            $unassignedOrders = $stmt->fetchAll();
            
            return [
                'success' => true,
                'drivers' => $drivers,
                'unassigned_orders' => $unassignedOrders
            ];
            
        } catch (Exception $e) {
            error_log("Get driver management error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve driver management data'];
        }
    }
    
    public function exportSalesReport($supplierId, $startDate, $endDate) {
        try {
            $pdo = $this->db->getConnection();
            
            $stmt = $pdo->prepare("
                SELECT 
                    o.id as order_id,
                    o.created_at as order_date,
                    p.name as product_name,
                    p.category,
                    oi.quantity,
                    oi.price as unit_price,
                    (oi.quantity * oi.price) as subtotal,
                    o.delivery_fee,
                    o.total_amount,
                    o.status,
                    CONCAT(a.municipality, ', ', a.barangay) as delivery_location,
                    u.name as driver_name
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                JOIN customer_addresses a ON o.address_id = a.id
                LEFT JOIN users u ON o.driver_id = u.id
                WHERE o.supplier_id = ?
                AND o.created_at BETWEEN ? AND ?
                ORDER BY o.created_at DESC
            ");
            $stmt->execute([$supplierId, $startDate, $endDate]);
            
            return [
                'success' => true,
                'report_data' => $stmt->fetchAll()
            ];
            
        } catch (Exception $e) {
            error_log("Export sales report error: " . $e->getMessage());
            return ['error' => 'Failed to generate sales report'];
        }
    }
    
    public function getOverviewStats($supplierId, $period = 'today') {
        try {
            $dateCondition = $this->getDateCondition($period);
            
            // Get total orders and revenue
            $sql = "SELECT 
                   COUNT(*) as total_orders,
                   SUM(total_amount) as total_revenue,
                   COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                   COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_orders,
                   COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
                   COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders
                   FROM orders
                   WHERE supplier_id = ? $dateCondition";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$supplierId]);
            $orderStats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Get product stats
            $sql = "SELECT 
                   COUNT(*) as total_products,
                   COUNT(CASE WHEN stock < 10 THEN 1 END) as low_stock_products,
                   SUM(stock) as total_stock
                   FROM products
                   WHERE supplier_id = ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$supplierId]);
            $productStats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Get average rating
            $sql = "SELECT AVG(rating) as average_rating,
                   COUNT(*) as total_reviews
                   FROM feedback
                   WHERE supplier_id = ? AND type = 'supplier' $dateCondition";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$supplierId]);
            $ratingStats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return [
                'orders' => $orderStats,
                'products' => $productStats,
                'ratings' => $ratingStats
            ];
            
        } catch (\Exception $e) {
            error_log("Get overview stats error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve dashboard statistics'];
        }
    }
    
    public function getSalesAnalytics($supplierId, $period = 'last_30_days') {
        try {
            $dateCondition = $this->getDateCondition($period);
            
            // Get daily sales data
            $sql = "SELECT 
                   DATE(created_at) as date,
                   COUNT(*) as order_count,
                   SUM(total_amount) as revenue
                   FROM orders
                   WHERE supplier_id = ? $dateCondition
                   GROUP BY DATE(created_at)
                   ORDER BY date";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$supplierId]);
            $salesData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get top selling products
            $sql = "SELECT 
                   p.id, p.name, p.price,
                   SUM(oi.quantity) as total_quantity,
                   SUM(oi.quantity * oi.price) as total_revenue
                   FROM order_items oi
                   JOIN orders o ON oi.order_id = o.id
                   JOIN products p ON oi.product_id = p.id
                   WHERE o.supplier_id = ? $dateCondition
                   GROUP BY p.id, p.name, p.price
                   ORDER BY total_quantity DESC
                   LIMIT 5";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$supplierId]);
            $topProducts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'sales_trend' => $salesData,
                'top_products' => $topProducts
            ];
            
        } catch (\Exception $e) {
            error_log("Get sales analytics error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve sales analytics'];
        }
    }
    
    public function getInventoryReport($supplierId) {
        try {
            $sql = "SELECT 
                   p.*,
                   COUNT(oi.id) as times_ordered,
                   COALESCE(SUM(oi.quantity), 0) as total_quantity_sold
                   FROM products p
                   LEFT JOIN order_items oi ON p.id = oi.product_id
                   WHERE p.supplier_id = ?
                   GROUP BY p.id
                   ORDER BY p.stock ASC";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$supplierId]);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Calculate additional metrics
            foreach ($products as &$product) {
                $product['stock_status'] = $this->getStockStatus($product['stock']);
                $product['reorder_suggestion'] = $this->calculateReorderSuggestion(
                    $product['stock'],
                    $product['total_quantity_sold']
                );
            }
            
            return $products;
            
        } catch (\Exception $e) {
            error_log("Get inventory report error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve inventory report'];
        }
    }
    
    public function getCustomerAnalytics($supplierId, $period = 'last_30_days') {
        try {
            $dateCondition = $this->getDateCondition($period);
            
            // Get top customers
            $sql = "SELECT 
                   u.id, u.fullname, u.email,
                   COUNT(o.id) as order_count,
                   SUM(o.total_amount) as total_spent
                   FROM orders o
                   JOIN users u ON o.customer_id = u.id
                   WHERE o.supplier_id = ? $dateCondition
                   GROUP BY u.id, u.fullname, u.email
                   ORDER BY total_spent DESC
                   LIMIT 10";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$supplierId]);
            $topCustomers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get customer retention rate
            $sql = "WITH CustomerOrders AS (
                SELECT customer_id,
                       MIN(created_at) as first_order,
                       MAX(created_at) as last_order,
                       COUNT(*) as order_count
                FROM orders
                WHERE supplier_id = ? $dateCondition
                GROUP BY customer_id
            )
            SELECT 
                COUNT(*) as total_customers,
                COUNT(CASE WHEN order_count > 1 THEN 1 END) as returning_customers,
                COUNT(CASE WHEN DATE(last_order) >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) THEN 1 END) as active_customers
            FROM CustomerOrders";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$supplierId]);
            $retention = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($retention['total_customers'] > 0) {
                $retention['retention_rate'] = 
                    ($retention['returning_customers'] / $retention['total_customers']) * 100;
            }
            
            return [
                'top_customers' => $topCustomers,
                'retention_metrics' => $retention
            ];
            
        } catch (\Exception $e) {
            error_log("Get customer analytics error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve customer analytics'];
        }
    }
    
    public function getSupplierDetails($supplierId) {
        try {
            $sql = "SELECT s.*, u.email, u.fullname,
                   (SELECT COUNT(*) FROM orders o WHERE o.supplier_id = s.id) as total_orders,
                   (SELECT AVG(rating) FROM feedback f WHERE f.supplier_id = s.id AND f.type = 'supplier') as rating
                   FROM suppliers s
                   JOIN users u ON s.user_id = u.id
                   WHERE s.id = ?";

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$supplierId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Get supplier details error: " . $e->getMessage());
            return null;
        }
    }

    public function updateSupplier($supplierId, $data) {
        try {
            $this->db->beginTransaction();

            // Validate required fields
            $required = ['business_name', 'business_address', 'business_phone', 'business_email'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['error' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
                }
            }

            // Update supplier information
            $sql = "UPDATE suppliers SET
                   business_name = ?,
                   business_address = ?,
                   business_phone = ?,
                   business_email = ?,
                   business_permit_number = ?,
                   tax_id = ?,
                   updated_at = CURRENT_TIMESTAMP
                   WHERE id = ?";

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                $data['business_name'],
                $data['business_address'],
                $data['business_phone'],
                $data['business_email'],
                $data['business_permit_number'] ?? null,
                $data['tax_id'] ?? null,
                $supplierId
            ]);

            $this->db->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Update supplier error: " . $e->getMessage());
            return ['error' => 'Failed to update supplier information'];
        }
    }
    
    private function getDateCondition($period) {
        switch ($period) {
            case 'today':
                return "AND DATE(created_at) = CURRENT_DATE";
            case 'yesterday':
                return "AND DATE(created_at) = DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)";
            case 'this_week':
                return "AND YEARWEEK(created_at) = YEARWEEK(CURRENT_DATE)";
            case 'last_week':
                return "AND YEARWEEK(created_at) = YEARWEEK(DATE_SUB(CURRENT_DATE, INTERVAL 1 WEEK))";
            case 'this_month':
                return "AND YEAR(created_at) = YEAR(CURRENT_DATE) AND MONTH(created_at) = MONTH(CURRENT_DATE)";
            case 'last_month':
                return "AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)) 
                       AND MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))";
            case 'last_30_days':
                return "AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
            case 'last_90_days':
                return "AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 90 DAY)";
            case 'this_year':
                return "AND YEAR(created_at) = YEAR(CURRENT_DATE)";
            default:
                return "";
        }
    }
    
    private function getStockStatus($stock) {
        if ($stock <= 0) {
            return 'out_of_stock';
        } elseif ($stock < 10) {
            return 'low_stock';
        } elseif ($stock < 20) {
            return 'moderate_stock';
        } else {
            return 'good_stock';
        }
    }
    
    private function calculateReorderSuggestion($currentStock, $totalSold) {
        $averageMonthlySales = $totalSold / 3; // Based on last 3 months
        $reorderPoint = ceil($averageMonthlySales * 0.5); // 2 weeks of stock
        $maxStock = ceil($averageMonthlySales * 2); // 2 months of stock
        
        if ($currentStock <= $reorderPoint) {
            $suggestedOrder = $maxStock - $currentStock;
            return [
                'should_reorder' => true,
                'suggested_quantity' => $suggestedOrder,
                'urgency' => $currentStock <= ($reorderPoint / 2) ? 'high' : 'medium'
            ];
        }
        
        return [
            'should_reorder' => false,
            'suggested_quantity' => 0,
            'urgency' => 'low'
        ];
    }
}