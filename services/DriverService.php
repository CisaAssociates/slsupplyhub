<?php

namespace SLSupplyHub;

class DriverService extends Model
{
    protected $table = 'drivers';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get available drivers for delivery assignments
     * 
     * @return array List of available drivers
     */
    public function getAvailableDrivers()
    {
        try {
            $sql = "SELECT d.id, u.fullname, d.vehicle_type, d.rating, d.status
                   FROM drivers d
                   JOIN users u ON d.user_id = u.id
                   WHERE d.status = 'available' AND u.status = 'active'
                   ORDER BY d.rating DESC";
            
            $stmt = $this->db->executeQuery($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error getting available drivers: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Assign a driver to an order
     * 
     * @param int $orderId The order ID
     * @param int $driverId The driver ID
     * @return array Result of the operation
     */
    public function assignDriver($orderId, $driverId)
    {
        try {
            $this->beginTransaction();
            
            // Check if driver exists and is available
            $driver = $this->find($driverId);
            if (!$driver || $driver['status'] !== 'available') {
                throw new \Exception("Driver is not available");
            }
            
            // Update order with driver ID and change status to 'assigned'
            $orderModel = new Order();
            $result = $orderModel->updateOrderStatus($orderId, 'assigned', $driverId);
            
            if (!isset($result['success']) || !$result['success']) {
                throw new \Exception("Failed to update order status");
            }
            
            // Update driver status to busy
            $this->update($driverId, ['status' => 'busy']);
            
            $this->commit();
            return ['success' => true];
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Error assigning driver: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Get driver details
     * 
     * @param int $driverId The driver ID
     * @return array|null Driver details or null if not found
     */
    public function getDriverDetails($driverId)
    {
        try {
            $sql = "SELECT d.*, u.fullname, u.email, u.phone
                   FROM drivers d
                   JOIN users u ON d.user_id = u.id
                   WHERE d.id = ?";
            
            $stmt = $this->db->executeQuery($sql, [$driverId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error getting driver details: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update driver status
     * 
     * @param int $driverId The driver ID
     * @param string $status The new status
     * @return array Result of the operation
     */
    public function updateDriverStatus($driverId, $status)
    {
        try {
            $validStatuses = ['available', 'busy', 'offline'];
            if (!in_array($status, $validStatuses)) {
                throw new \Exception("Invalid status");
            }
            
            $this->update($driverId, ['status' => $status]);
            return ['success' => true];
        } catch (\Exception $e) {
            error_log("Error updating driver status: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
} 