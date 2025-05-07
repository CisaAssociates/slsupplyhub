<?php
namespace SLSupplyHub;

use PDO;
use Exception;

require_once 'database.php';
require_once 'mail.php';
require_once 'email_templates/delivery_update.php';

class DriverService extends Model {
    protected $table = 'drivers';
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->mail = new MailService();
    }
    
    public function registerDriver($userData, $driverData) {
        try {
            $this->db->beginTransaction();
            
            // Create user account first
            $user = new User();
            $userData['user_type'] = 'driver';
            $userId = $user->createUser($userData);
            
            if (!$userId || isset($userId['error'])) {
                throw new \Exception('Failed to create user account');
            }
            
            // Create driver profile
            $driverData['user_id'] = $userId;
            $driverId = $this->create($driverData);
            
            if (!$driverId) {
                throw new \Exception('Failed to create driver profile');
            }
            
            $this->db->commit();
            return $driverId;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Driver registration error: " . $e->getMessage());
            return ['error' => 'Failed to register driver'];
        }
    }
    
    public function updateDriverStatus($driverId, $status) {
        try {
            $validStatuses = ['available', 'busy', 'offline'];
            if (!in_array($status, $validStatuses)) {
                return ['error' => 'Invalid status'];
            }
            
            $this->update($driverId, ['status' => $status]);
            return ['success' => true];
            
        } catch (\Exception $e) {
            error_log("Update driver status error: " . $e->getMessage());
            return ['error' => 'Failed to update driver status'];
        }
    }
    
    public function getAvailableDrivers($city = null) {
        try {
            $sql = "SELECT d.*, u.fullname, u.phone_number, u.email 
                   FROM drivers d
                   JOIN users u ON d.user_id = u.id
                   WHERE d.status = 'available'";
            
            $params = [];
            
            if ($city) {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM addresses a 
                    WHERE a.user_id = u.id 
                    AND a.city = ?
                )";
                $params[] = $city;
            }
            
            $sql .= " ORDER BY d.rating DESC";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get available drivers error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve available drivers'];
        }
    }
    
    public function assignDriverToSupplier($driverId, $supplierId) {
        try {
            $pdo = $this->db->getConnection();
            
            // Verify driver exists and has driver role
            $stmt = $pdo->prepare("
                SELECT id FROM users 
                WHERE id = ? AND role = 'driver'
            ");
            $stmt->execute([$driverId]);
            if (!$stmt->fetch()) {
                return ['error' => 'Invalid driver ID or user is not a driver'];
            }
            
            // Verify supplier exists
            $stmt = $pdo->prepare("
                SELECT id FROM suppliers 
                WHERE id = ?
            ");
            $stmt->execute([$supplierId]);
            if (!$stmt->fetch()) {
                return ['error' => 'Invalid supplier ID'];
            }
            
            // Create association
            $stmt = $pdo->prepare("
                INSERT INTO driver_suppliers (driver_id, supplier_id)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$driverId, $supplierId]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Driver-supplier assignment error: " . $e->getMessage());
            return ['error' => 'Failed to assign driver to supplier'];
        }
    }
    
    public function removeDriverFromSupplier($driverId, $supplierId) {
        try {
            $pdo = $this->db->getConnection();
            
            $stmt = $pdo->prepare("
                DELETE FROM driver_suppliers 
                WHERE driver_id = ? AND supplier_id = ?
            ");
            $stmt->execute([$driverId, $supplierId]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Driver-supplier removal error: " . $e->getMessage());
            return ['error' => 'Failed to remove driver from supplier'];
        }
    }
    
    public function getSupplierDrivers($supplierId) {
        try {
            $pdo = $this->db->getConnection();
            
            $stmt = $pdo->prepare("
                SELECT u.id, u.name, u.email,
                    COUNT(DISTINCT o.id) as total_deliveries,
                    AVG(o.feedback_rating) as average_rating
                FROM users u
                JOIN driver_suppliers ds ON u.id = ds.driver_id
                LEFT JOIN orders o ON u.id = o.driver_id
                WHERE ds.supplier_id = ?
                GROUP BY u.id, u.name, u.email
            ");
            $stmt->execute([$supplierId]);
            
            return [
                'success' => true,
                'drivers' => $stmt->fetchAll()
            ];
            
        } catch (Exception $e) {
            error_log("Get supplier drivers error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve supplier drivers'];
        }
    }
    
    public function assignOrderToDriver($orderId, $driverId) {
        try {
            $this->db->beginTransaction();
            
            // Update order with driver assignment
            $order = new Order();
            $result = $order->update($orderId, [
                'driver_id' => $driverId,
                'status' => 'assigned'
            ]);
            
            if (!$result || isset($result['error'])) {
                throw new \Exception('Failed to update order');
            }
            
            // Update driver status to busy
            $this->updateDriverStatus($driverId, 'busy');
            
            // Create order status history entry
            $orderHistory = new OrderStatusHistory();
            $orderHistory->create([
                'order_id' => $orderId,
                'status' => 'assigned',
                'notes' => "Order assigned to driver #$driverId"
            ]);
            
            // Send notifications
            $notification = new Notification();
            
            // Get order details for notifications
            $orderDetails = $order->getOrderDetails($orderId);
            
            // Notify driver
            $notification->createNotification([
                'user_id' => $orderDetails['driver']['user_id'],
                'title' => 'New Order Assignment',
                'message' => "You have been assigned to order #{$orderId}",
                'type' => 'order_assignment',
                'reference_id' => $orderId,
                'reference_type' => 'order'
            ], true);
            
            // Notify customer
            $notification->createNotification([
                'user_id' => $orderDetails['customer_id'],
                'title' => 'Order Update',
                'message' => "A driver has been assigned to your order #{$orderId}",
                'type' => 'order_update',
                'reference_id' => $orderId,
                'reference_type' => 'order'
            ], true);
            
            $this->db->commit();
            return ['success' => true];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Assign order to driver error: " . $e->getMessage());
            return ['error' => 'Failed to assign order to driver'];
        }
    }
    
    public function updateDeliveryStatus($orderId, $status, $notes = null) {
        try {
            $this->db->beginTransaction();
            
            $validStatuses = ['picked_up', 'delivered'];
            if (!in_array($status, $validStatuses)) {
                return ['error' => 'Invalid status'];
            }
            
            // Update order status
            $order = new Order();
            $result = $order->update($orderId, ['status' => $status]);
            
            if (!$result || isset($result['error'])) {
                throw new \Exception('Failed to update order status');
            }
            
            // Create order status history entry
            $orderHistory = new OrderStatusHistory();
            $orderHistory->create([
                'order_id' => $orderId,
                'status' => $status,
                'notes' => $notes
            ]);
            
            // If delivered, update driver status and stats
            if ($status === 'delivered') {
                $orderDetails = $order->getOrderDetails($orderId);
                $driverId = $orderDetails['driver_id'];
                
                // Update driver status to available
                $this->updateDriverStatus($driverId, 'available');
                
                // Update driver stats
                $sql = "UPDATE drivers 
                       SET total_deliveries = total_deliveries + 1
                       WHERE id = ?";
                
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([$driverId]);
            }
            
            // Send notifications
            $notification = new Notification();
            $orderDetails = $order->getOrderDetails($orderId);
            
            // Get status message
            $customerMessage = $status === 'picked_up' 
                ? "Your order #{$orderId} has been picked up by the driver"
                : "Your order #{$orderId} has been delivered";
            
            // Notify customer
            $notification->createNotification([
                'user_id' => $orderDetails['customer_id'],
                'title' => 'Order Update',
                'message' => $customerMessage,
                'type' => 'order_update',
                'reference_id' => $orderId,
                'reference_type' => 'order'
            ], true);
            
            $this->db->commit();
            return ['success' => true];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Update delivery status error: " . $e->getMessage());
            return ['error' => 'Failed to update delivery status'];
        }
    }
    
    public function getDriverDeliveries($driverId, $status = null, $page = 1, $perPage = 10) {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT o.*, 
                   u.fullname as customer_name,
                   s.business_name as supplier_name,
                   a.address_line1, a.city, a.province
                   FROM orders o
                   JOIN users u ON o.customer_id = u.id
                   JOIN suppliers s ON o.supplier_id = s.id
                   JOIN addresses a ON o.address_id = a.id
                   WHERE o.driver_id = ?";
            
            $params = [$driverId];
            
            if ($status) {
                $sql .= " AND o.status = ?";
                $params[] = $status;
            }
            
            // Get total count
            $countSql = "SELECT COUNT(*) FROM ($sql) as t";
            $stmt = $this->db->getConnection()->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            
            // Get paginated results
            $sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'items' => $items,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage)
            ];
            
        } catch (\Exception $e) {
            error_log("Get driver deliveries error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve driver deliveries'];
        }
    }
    
    public function getDriverStats($driverId) {
        try {
            // Get driver profile and basic stats
            $sql = "SELECT d.*, u.fullname, u.email, u.phone_number,
                   COUNT(DISTINCT o.id) as total_orders,
                   COUNT(DISTINCT CASE WHEN o.status = 'delivered' THEN o.id END) as completed_deliveries,
                   COUNT(DISTINCT CASE WHEN o.status IN ('assigned', 'picked_up') THEN o.id END) as active_deliveries,
                   AVG(f.rating) as average_rating
                   FROM drivers d
                   JOIN users u ON d.user_id = u.id
                   LEFT JOIN orders o ON d.id = o.driver_id
                   LEFT JOIN feedback f ON d.id = f.driver_id
                   WHERE d.id = ?
                   GROUP BY d.id";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$driverId]);
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$stats) {
                return ['error' => 'Driver not found'];
            }
            
            // Get recent feedback
            $sql = "SELECT f.*, u.fullname as customer_name
                   FROM feedback f
                   JOIN users u ON f.customer_id = u.id
                   WHERE f.driver_id = ? AND f.type = 'driver'
                   ORDER BY f.created_at DESC
                   LIMIT 5";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$driverId]);
            $recentFeedback = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Add feedback to stats
            $stats['recent_feedback'] = $recentFeedback;
            
            return $stats;
            
        } catch (\Exception $e) {
            error_log("Get driver stats error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve driver statistics'];
        }
    }
    
    private function notifyCustomerAboutDelivery($orderId) {
        try {
            $pdo = $this->db->getConnection();
            
            $stmt = $pdo->prepare("
                SELECT o.*, s.business_name,
                    d.name as driver_name,
                    u.email, u.name as customer_name
                FROM orders o
                JOIN suppliers s ON o.supplier_id = s.id
                JOIN users d ON o.driver_id = d.id
                JOIN customers c ON o.customer_id = c.id
                JOIN users u ON c.user_id = u.id
                WHERE o.id = ?
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            if ($order) {
                $this->mail->sendDeliveryAssignmentNotification($order);
            }
            
        } catch (Exception $e) {
            error_log("Delivery notification error: " . $e->getMessage());
            // Don't throw, just log the error
        }
    }

    public function sendDeliveryStatusEmail($orderId, $status, $failureReason = null) {
        global $conn;
        
        // Get order details including customer information
        $sql = "SELECT o.*, c.name, c.email, s.business_name, 
                a.recipient_name, a.contact_number, a.address_line, 
                a.barangay, a.municipality, a.province, a.landmark
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
                JOIN sellers s ON o.seller_id = s.id
                JOIN addresses a ON o.delivery_address_id = a.id
                WHERE o.id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $emailData = array_merge($row, [
                'status' => $status,
                'failure_reason' => $failureReason
            ]);
            
            $mailService = new MailService();
            $template = getDeliveryUpdateEmailTemplate($emailData);
            
            $subject = "Delivery Update for Order #{$orderId}";
            return $mailService->sendEmail($row['email'], $subject, $template);
        }
        
        return false;
    }
}