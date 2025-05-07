<?php
namespace SLSupplyHub;

class NotificationService extends Model {
    protected $table = 'notifications';
    
    public function createNotification($data, $realtime = false) {
        try {
            // Create notification record
            $notificationId = $this->create([
                'user_id' => $data['user_id'],
                'title' => $data['title'],
                'message' => $data['message'],
                'type' => $data['type'],
                'reference_id' => $data['reference_id'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
                'read' => false,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$notificationId) {
                throw new \Exception('Failed to create notification');
            }
            
            // If realtime is enabled, emit websocket event
            if ($realtime) {
                $realtime = new RealtimeService();
                $realtime->emit('notification', [
                    'user_id' => $data['user_id'],
                    'notification' => array_merge(
                        ['id' => $notificationId],
                        $data
                    )
                ]);
            }
            
            return $notificationId;
            
        } catch (\Exception $e) {
            error_log("Create notification error: " . $e->getMessage());
            return ['error' => 'Failed to create notification'];
        }
    }
    
    public function getUserNotifications($userId, $page = 1, $perPage = 10) {
        try {
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$userId]);
            $total = $stmt->fetchColumn();
            
            // Get paginated notifications
            $sql = "SELECT * FROM notifications 
                   WHERE user_id = ? 
                   ORDER BY created_at DESC 
                   LIMIT ? OFFSET ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$userId, $perPage, $offset]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'items' => $items,
                'total' => $total,
                'unread_count' => $this->getUnreadCount($userId),
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage)
            ];
            
        } catch (\Exception $e) {
            error_log("Get user notifications error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve notifications'];
        }
    }
    
    public function markAsRead($notificationId, $userId) {
        try {
            $result = $this->update($notificationId, [
                'read' => true,
                'read_at' => date('Y-m-d H:i:s')
            ], "user_id = " . $userId);
            
            if ($result) {
                // Update unread count in realtime
                $realtime = new RealtimeService();
                $realtime->emit('notification_count', [
                    'user_id' => $userId,
                    'unread_count' => $this->getUnreadCount($userId)
                ]);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Mark notification as read error: " . $e->getMessage());
            return ['error' => 'Failed to mark notification as read'];
        }
    }
    
    public function markAllAsRead($userId) {
        try {
            $sql = "UPDATE notifications 
                   SET read = true, 
                       read_at = ? 
                   WHERE user_id = ? 
                   AND read = false";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $result = $stmt->execute([date('Y-m-d H:i:s'), $userId]);
            
            if ($result) {
                // Update unread count in realtime
                $realtime = new RealtimeService();
                $realtime->emit('notification_count', [
                    'user_id' => $userId,
                    'unread_count' => 0
                ]);
            }
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            error_log("Mark all notifications as read error: " . $e->getMessage());
            return ['error' => 'Failed to mark notifications as read'];
        }
    }
    
    public function getUnreadCount($userId) {
        try {
            $sql = "SELECT COUNT(*) FROM notifications 
                   WHERE user_id = ? AND read = false";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchColumn();
            
        } catch (\Exception $e) {
            error_log("Get unread count error: " . $e->getMessage());
            return 0;
        }
    }
    
    public function deleteNotification($notificationId, $userId) {
        try {
            return $this->delete($notificationId, "user_id = " . $userId);
            
        } catch (\Exception $e) {
            error_log("Delete notification error: " . $e->getMessage());
            return ['error' => 'Failed to delete notification'];
        }
    }
    
    public function sendOrderNotification($orderId, $status) {
        try {
            $order = new Order();
            $orderDetails = $order->getOrderDetails($orderId);
            
            if (!$orderDetails) {
                throw new \Exception('Order not found');
            }
            
            $statusMessages = [
                'pending' => 'New order received',
                'confirmed' => 'Order has been confirmed',
                'processing' => 'Order is being processed',
                'ready' => 'Order is ready for pickup',
                'assigned' => 'Driver has been assigned to your order',
                'picked_up' => 'Order has been picked up by driver',
                'delivered' => 'Order has been delivered',
                'cancelled' => 'Order has been cancelled'
            ];
            
            $message = $statusMessages[$status] ?? "Order status updated to: $status";
            
            // Notify customer
            $this->createNotification([
                'user_id' => $orderDetails['customer_id'],
                'title' => 'Order Update',
                'message' => "$message (Order #$orderId)",
                'type' => 'order_update',
                'reference_id' => $orderId,
                'reference_type' => 'order'
            ], true);
            
            // Notify supplier
            $this->createNotification([
                'user_id' => $orderDetails['supplier_user_id'],
                'title' => 'Order Update',
                'message' => "$message (Order #$orderId)",
                'type' => 'order_update',
                'reference_id' => $orderId,
                'reference_type' => 'order'
            ], true);
            
            // Notify driver if assigned
            if ($orderDetails['driver_id'] && in_array($status, ['assigned', 'cancelled'])) {
                $this->createNotification([
                    'user_id' => $orderDetails['driver']['user_id'],
                    'title' => 'Order Update',
                    'message' => "$message (Order #$orderId)",
                    'type' => 'order_update',
                    'reference_id' => $orderId,
                    'reference_type' => 'order'
                ], true);
            }
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            error_log("Send order notification error: " . $e->getMessage());
            return ['error' => 'Failed to send order notifications'];
        }
    }
}