<?php

namespace SLSupplyHub;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'customer_id',
        'supplier_id',
        'driver_id',
        'address_id',
        'order_number',
        'subtotal',
        'total_amount',
        'delivery_fee',  // Added this
        'status',
        'payment_status',
        'payment_method',
        'notes'
    ];

    protected $validationRules = [
        'customer_id' => ['required', 'numeric'],
        'supplier_id' => ['required', 'numeric'],
        'address_id' => ['required', 'numeric'],
        'total_amount' => ['required', 'numeric'],
        'subtotal' => ['required', 'numeric'],  // Add this
        'order_number' => ['required'],
        'payment_method' => ['required']
    ];

    private $product;
    private $mail;

    public function __construct()
    {
        parent::__construct();
        $this->product = new Product();
        $this->mail = new MailService();
    }

    public function createOrder($orderData, $items)
    {
        try {
            // Start transaction
            $this->beginTransaction();

            // Debug order data
            error_log("[Order Creation] Data: " . json_encode($orderData, JSON_PRETTY_PRINT));

            // Calculate subtotal from items
            $subtotal = array_reduce($items, function ($carry, $item) {
                return $carry + ((float)$item['price'] * (int)$item['quantity']);
            }, 0);

            // Add subtotal to order data
            $orderData['subtotal'] = $subtotal;
            
            // Ensure status is set
            $orderData['status'] = $orderData['status'] ?? 'pending';
            $orderData['payment_status'] = $orderData['payment_status'] ?? 'pending';

            // Validate order data
            $errors = $this->validate($orderData);
            if (!empty($errors)) {
                throw new \Exception("Validation failed: " . json_encode($errors));
            }

            // Create order with explicit return
            $orderId = $this->create($orderData);
            if (!$orderId) {
                // Debug failed creation
                error_log("[Order Creation] Failed to create order. Data: " . json_encode($orderData));
                throw new \Exception("Failed to create order record");
            }

            // Verify order was created
            $newOrder = $this->find($orderId);
            if (!$newOrder) {
                throw new \Exception("Order created but not found with ID: $orderId");
            }

            error_log("[Order Creation] Success - Order ID: $orderId");

            // Continue with order items
            foreach ($items as $item) {
                $product = $this->product->find($item['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found: {$item['product_id']}");
                }
                
                // Check if the product belongs to the selected supplier
                // There are two possible cases:
                // 1. product.supplier_id is a reference to users.id (as per the schema)
                // 2. product.supplier_id is a reference to suppliers.id (as appears to be the case in some data)
                
                // First check if product.supplier_id matches order.supplier_id directly
                $supplierMatch = ($product['supplier_id'] == (int)$orderData['supplier_id']);
                
                // If not matched directly, check if product.supplier_id is a suppliers.id that matches the supplier for this order
                if (!$supplierMatch) {
                    // Get the supplier record for this product
                    $stmt = $this->db->getConnection()->prepare("
                        SELECT s.user_id 
                        FROM suppliers s 
                        WHERE s.id = ? AND s.user_id = ?
                    ");
                    $stmt->execute([$product['supplier_id'], (int)$orderData['supplier_id']]);
                    $supplierMatch = ($stmt->rowCount() > 0);
                    
                    // If still no match, check if product.supplier_id is a user_id that corresponds to a supplier
                    if (!$supplierMatch) {
                        $stmt = $this->db->getConnection()->prepare("
                            SELECT s.id 
                            FROM suppliers s 
                            WHERE s.id = ? AND s.user_id = ?
                        ");
                        $stmt->execute([(int)$orderData['supplier_id'], $product['supplier_id']]);
                        $supplierMatch = ($stmt->rowCount() > 0);
                    }
                }
                
                if (!$supplierMatch) {
                    throw new \Exception("Product {$product['name']} does not belong to the selected supplier");
                }
                
                if ($product['stock'] < $item['quantity']) {
                    throw new \Exception("Insufficient stock for: {$product['name']}");
                }

                // Create order item
                if (!$this->createOrderItem($orderId, $item)) {
                    throw new \Exception("Failed to create order item");
                }

                // Update stock after successful order item creation
                $this->product->updateStock($item['product_id'], $item['quantity'], 'subtract');
            }

            $this->commit();
            return ['success' => true, 'order_id' => $orderId];

        } catch (\Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->rollback();
            }
            error_log("[Order Creation Error] " . $e->getMessage());
            error_log("[Order Creation Error] Stack trace: " . $e->getTraceAsString());
            return ['error' => $e->getMessage()];
        }
    }

    private function createOrderItem($orderId, $item)
    {
        // Calculate subtotal for the item
        $subtotal = (float)$item['price'] * (int)$item['quantity'];

        // Use price from cart instead of current product price
        $sql = "INSERT INTO order_items 
                (order_id, product_id, quantity, price, subtotal)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['quantity'],
            $item['price'],
            $subtotal
        ]);
    }

    public function updateOrderStatus($orderId, $status, $driverId = null)
    {
        try {
            $this->beginTransaction();

            $order = $this->find($orderId);
            if (!$order) {
                return ['error' => 'Order not found'];
            }

            $updateData = ['status' => $status];
            if ($driverId) {
                $updateData['driver_id'] = $driverId;
            }

            $this->update($orderId, $updateData);

            $this->commit();

            // Send status update email
            $orderDetails = $this->getOrderDetails($orderId);
            $customerEmail = $orderDetails['customer_email'];
            $customerName = $orderDetails['customer_name'];

            $this->mail->sendOrderStatusUpdate($orderDetails, $customerEmail, $customerName, $status);

            return ['success' => true];
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Order status update error: " . $e->getMessage());
            return ['error' => 'Failed to update order status'];
        }
    }

    public function getOrderDetails($orderId)
    {
        try {
            $sql = "SELECT o.*, 
                   c.fullname as customer_name, c.email as customer_email,
                   COALESCE(s.business_name, su.fullname) as supplier_name,
                   d.fullname as driver_name,
                   a.street, a.barangay, a.city
                   FROM orders o
                   JOIN users c ON o.customer_id = c.id
                   JOIN users su ON o.supplier_id = su.id
                   LEFT JOIN suppliers s ON su.id = s.user_id
                   LEFT JOIN users d ON o.driver_id = d.id
                   LEFT JOIN addresses a ON o.address_id = a.id
                   WHERE o.id = ?";

            $stmt = $this->db->executeQuery($sql, [$orderId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                return null;
            }

            // Get order items
            $sql = "SELECT oi.*, p.name as product_name
                   FROM order_items oi
                   JOIN products p ON oi.product_id = p.id
                   WHERE oi.order_id = ?";

            $stmt = $this->db->executeQuery($sql, [$orderId]);
            $order['items'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $order;
        } catch (\Exception $e) {
            error_log("Get order details error: " . $e->getMessage());
            return null;
        }
    }

    public function getCustomerOrders($customerId, $page = 1, $perPage = 10, $status = null) {
        try {
            $offset = ($page - 1) * $perPage;

            // Base WHERE clause
            $where = "WHERE o.customer_id = ?";
            $params = [$customerId];

            // Add status filter if provided
            if ($status) {
                $where .= " AND o.status = ?";
                $params[] = $status;
            }

            // Get orders with all related information
            $sql = "SELECT o.*,
                   COALESCE(s.business_name, u.fullname) as supplier_name,
                   COALESCE(u.status, 'inactive') as supplier_status,
                   a.street, a.barangay, a.city,
                   DATE_FORMAT(o.created_at, '%Y-%m-%d %H:%i:%s') as created_at
                   FROM {$this->table} o
                   LEFT JOIN users u ON o.supplier_id = u.id
                   LEFT JOIN suppliers s ON u.id = s.user_id
                   LEFT JOIN addresses a ON o.address_id = a.id
                   $where
                   ORDER BY o.created_at DESC
                   LIMIT ? OFFSET ?";

            $params[] = $perPage;
            $params[] = $offset;

            error_log("[getCustomerOrders] SQL: " . $sql);
            error_log("[getCustomerOrders] Params: " . json_encode($params));

            $stmt = $this->db->executeQuery($sql, $params);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get total count
            $countSql = "SELECT COUNT(*) FROM {$this->table} o " . $where;
            $stmt = $this->db->executeQuery($countSql, array_slice($params, 0, -2));
            $total = $stmt->fetchColumn();

            error_log("[getCustomerOrders] Found " . count($items) . " orders");
            
            return [
                'items' => $items,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage)
            ];
        } catch (\Exception $e) {
            error_log("[getCustomerOrders] Error: " . $e->getMessage());
            error_log("[getCustomerOrders] Stack trace: " . $e->getTraceAsString());
            return ['error' => 'Failed to retrieve orders', 'details' => $e->getMessage()];
        }
    }

    public function getSupplierOrders($supplierId, $page = 1, $perPage = 10, $status = null) {
        try {
            $offset = ($page - 1) * $perPage;

            // Base WHERE clause
            // Note: supplierId could be either a user_id or a supplier.id
            // We need to handle both cases
            
            // First, check if this is a supplier.id
            $supplierUserIdQuery = "SELECT user_id FROM suppliers WHERE id = ?";
            $stmt = $this->db->executeQuery($supplierUserIdQuery, [$supplierId]);
            $supplierRecord = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($supplierRecord) {
                // If it's a supplier.id, use the user_id
                $userIdToUse = $supplierRecord['user_id'];
                error_log("[getSupplierOrders] Using user_id {$userIdToUse} for supplier.id {$supplierId}");
            } else {
                // Otherwise, assume it's already a user_id
                $userIdToUse = $supplierId;
                error_log("[getSupplierOrders] Using direct user_id {$supplierId}");
            }
            
            $where = "WHERE o.supplier_id = ?";
            $params = [$userIdToUse];

            // Add status filter if provided
            if ($status) {
                $where .= " AND o.status = ?";
                $params[] = $status;
            }

            // Get orders with customer info and addresses
            $sql = "SELECT o.*, 
                   c.fullname as customer_name, c.email as customer_email,
                   a.street, a.barangay, a.city,
                   DATE_FORMAT(o.created_at, '%Y-%m-%d %H:%i:%s') as created_at
                   FROM {$this->table} o
                   JOIN users c ON o.customer_id = c.id
                   LEFT JOIN addresses a ON o.address_id = a.id
                   $where
                   ORDER BY o.created_at DESC
                   LIMIT ? OFFSET ?";

            $params[] = $perPage;
            $params[] = $offset;

            error_log("[getSupplierOrders] SQL: " . $sql);
            error_log("[getSupplierOrders] Params: " . json_encode($params));

            $stmt = $this->db->executeQuery($sql, $params);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get total count
            $countSql = "SELECT COUNT(*) FROM {$this->table} o " . $where;
            $stmt = $this->db->executeQuery($countSql, array_slice($params, 0, -2));
            $total = $stmt->fetchColumn();

            return [
                'items' => $items,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage)
            ];
        } catch (\Exception $e) {
            error_log("[getSupplierOrders] Error: " . $e->getMessage());
            error_log("[getSupplierOrders] Stack trace: " . $e->getTraceAsString());
            return ['error' => 'Failed to retrieve orders'];
        }
    }

    public function getDriverOrders($driverId, $status = null)
    {
        try {
            $sql = "SELECT o.*, 
                   c.fullname as customer_name,
                   COALESCE(s.business_name, su.fullname) as supplier_name,
                   a.street, a.barangay, a.city,
                   DATE_FORMAT(o.created_at, '%Y-%m-%d %H:%i:%s') as created_at
                   FROM {$this->table} o
                   JOIN users c ON o.customer_id = c.id
                   JOIN users su ON o.supplier_id = su.id
                   LEFT JOIN suppliers s ON su.id = s.user_id
                   LEFT JOIN addresses a ON o.address_id = a.id
                   WHERE o.driver_id = ?";

            $params = [$driverId];

            if ($status) {
                $sql .= " AND o.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY o.created_at DESC";

            error_log("[getDriverOrders] SQL: " . $sql);
            error_log("[getDriverOrders] Params: " . json_encode($params));

            $stmt = $this->db->executeQuery($sql, $params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[getDriverOrders] Error: " . $e->getMessage());
            error_log("[getDriverOrders] Stack trace: " . $e->getTraceAsString());
            return ['error' => 'Failed to retrieve orders'];
        }
    }

    public function updatePaymentStatus($orderId, $status)
    {
        try {
            $order = $this->find($orderId);
            if (!$order) {
                return ['error' => 'Order not found'];
            }

            $this->update($orderId, ['payment_status' => $status]);

            return ['success' => true];
        } catch (\Exception $e) {
            error_log("Payment status update error: " . $e->getMessage());
            return ['error' => 'Failed to update payment status'];
        }
    }
}
