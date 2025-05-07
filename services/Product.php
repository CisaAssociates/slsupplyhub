<?php
namespace SLSupplyHub;

use SLSupplyHub\Helpers\FileUpload;

class Product extends Model {
    protected $table = 'products';
    protected $fillable = [
        'supplier_id', 'name', 'description', 'price', 
        'stock', 'image_path', 'status', 'unit', 'category_id',
        'regular_price', 'minimum_order'
    ];
    
    protected $validationRules = [
        'supplier_id' => ['required', 'numeric'],
        'name' => ['required', 'max:100'],
        'description' => ['max:1000'],
        'price' => ['required', 'numeric'],
        'stock' => ['required', 'numeric'],
        'unit' => ['required'],
        'category_id' => ['required', 'numeric']
    ];
    
    private $fileUpload;
    
    public function __construct() {
        parent::__construct();
        $this->fileUpload = new FileUpload();
    }
    
    public function createProduct($data, $image = null) {
        try {
            $errors = $this->validate($data);
            if (!empty($errors)) {
                return ['error' => $errors];
            }
            
            $this->beginTransaction();
            
            // Handle image upload if provided
            if ($image && $image['error'] === UPLOAD_ERR_OK) {
                try {
                    $imagePath = $this->fileUpload->handleUpload($image, 'image');
                    $data['image_path'] = $imagePath;
                } catch (\Exception $e) {
                    return ['error' => ['image' => [$e->getMessage()]]];
                }
            }
            
            // Create product
            $productId = $this->create($data);
            
            $this->commit();
            
            return ['success' => true, 'product_id' => $productId];
            
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Product creation error: " . $e->getMessage());
            return ['error' => 'Failed to create product. Please try again.'];
        }
    }
    
    public function updateProduct($id, $data, $image = null) {
        try {
            // Validate update data
            $errors = $this->validate($data);
            if (!empty($errors)) {
                return ['error' => $errors];
            }
            
            $this->beginTransaction();
            
            // Get current product
            $currentProduct = $this->find($id);
            if (!$currentProduct) {
                return ['error' => 'Product not found'];
            }
            
            // Handle image upload if provided
            if ($image && $image['error'] === UPLOAD_ERR_OK) {
                try {
                    // Delete old image if exists
                    if ($currentProduct['image_path']) {
                        $this->fileUpload->deleteFile($currentProduct['image_path']);
                    }
                    
                    $imagePath = $this->fileUpload->handleUpload($image, 'image');
                    $data['image_path'] = $imagePath;
                } catch (\Exception $e) {
                    return ['error' => ['image' => [$e->getMessage()]]];
                }
            }
            
            // Update product
            $this->update($id, $data);
            
            $this->commit();
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Product update error: " . $e->getMessage());
            return ['error' => 'Failed to update product. Please try again.'];
        }
    }
    
    public function deleteProduct($id) {
        try {
            $this->beginTransaction();
            
            // Get product details
            $product = $this->find($id);
            if (!$product) {
                return ['error' => 'Product not found'];
            }
            
            // Delete product image if exists
            if ($product['image_path']) {
                $this->fileUpload->deleteFile($product['image_path']);
            }
            
            // Delete product
            $this->delete($id);
            
            $this->commit();
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Product deletion error: " . $e->getMessage());
            return ['error' => 'Failed to delete product. Please try again.'];
        }
    }
    
    public function getProductsBySupplier($supplierId, $page = 1, $perPage = 10) {
        try {
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $stmt = $this->db->getConnection()->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE supplier_id = ?"
            );
            $stmt->execute([$supplierId]);
            $total = $stmt->fetchColumn();
            
            // Get products
            $stmt = $this->db->getConnection()->prepare(
                "SELECT * FROM {$this->table} WHERE supplier_id = ? LIMIT ? OFFSET ?"
            );
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
            error_log("Get products error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve products'];
        }
    }
    
    public function searchProducts($filters = []) {
        try {
            $conditions = [];  // Removed 'p.status = "active"' to check if status is the issue
            $params = [];
            
            // Search by name or description
            if (!empty($filters['search'])) {
                $searchTerm = "%{$filters['search']}%";
                $conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Filter by category
            if (!empty($filters['category'])) {
                $conditions[] = "p.category_id = ?";
                $params[] = $filters['category'];
            }

            // Filter by price range
            if (!empty($filters['min_price'])) {
                $conditions[] = "p.price >= ?";
                $params[] = $filters['min_price'];
            }
            if (!empty($filters['max_price'])) {
                $conditions[] = "p.price <= ?";
                $params[] = $filters['max_price'];
            }

            // Filter by rating
            if (!empty($filters['rating'])) {
                $conditions[] = "p.rating >= ?";
                $params[] = $filters['rating'];
            }

            // Filter by stock status
            if (isset($filters['in_stock']) && $filters['in_stock']) {
                $conditions[] = "p.stock > 0";
            }

            // Build WHERE clause
            $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

            // Build ORDER BY clause
            $orderBy = match($filters['sort'] ?? 'newest') {
                'price_low' => 'p.price ASC',
                'price_high' => 'p.price DESC',
                'popular' => 'p.review_count DESC, p.rating DESC',
                'rating' => 'p.rating DESC, p.review_count DESC',
                default => 'p.created_at DESC'
            };

            // Pagination
            $page = max(1, $filters['page'] ?? 1);
            $perPage = max(1, $filters['per_page'] ?? 10);
            $offset = ($page - 1) * $perPage;
            
            // Debug query - Get total count
            $countSql = "SELECT COUNT(*) FROM {$this->table} p $where";
            error_log("Count SQL: " . $countSql);
            error_log("Count Params: " . print_r($params, true));
            
            $stmt = $this->db->getConnection()->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            error_log("Total products found: " . $total);
            
            // Get products with supplier info
            $sql = "SELECT p.*,
                          s.business_name as supplier_name,
                          COALESCE(p.rating, 0) as rating,
                          COALESCE(p.review_count, 0) as review_count,
                          CASE 
                              WHEN p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 
                              ELSE 0 
                          END as is_new,
                          CASE 
                              WHEN p.regular_price > p.price THEN 
                                  ROUND(((p.regular_price - p.price) / p.regular_price) * 100) 
                              ELSE 0 
                          END as discount_percent
                   FROM {$this->table} p
                   LEFT JOIN suppliers s ON p.supplier_id = s.id
                   $where
                   ORDER BY $orderBy
                   LIMIT ? OFFSET ?";
            
            // Debug query
            error_log("Product SQL: " . $sql);
            error_log("Product Params: " . print_r(array_merge($params, [$perPage, $offset]), true));
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $params[] = $perPage;
            $params[] = $offset;
            $stmt->execute($params);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            error_log("Products found: " . count($items));
            if (empty($items)) {
                error_log("No products found in query");
            } else {
                error_log("First product: " . print_r($items[0], true));
            }
            
            return [
                'items' => $items,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage)
            ];
            
        } catch (\Exception $e) {
            error_log("Product search error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return ['error' => 'Failed to search products', 'debug' => $e->getMessage()];
        }
    }
    
    public function updateStock($id, $quantity, $operation = 'add') {
        try {
            $this->beginTransaction();
            
            // Get current stock
            $product = $this->find($id);
            if (!$product) {
                return ['error' => 'Product not found'];
            }
            
            // Calculate new stock
            $newStock = $operation === 'add' 
                ? $product['stock'] + $quantity 
                : $product['stock'] - $quantity;
            
            if ($newStock < 0) {
                return ['error' => 'Insufficient stock'];
            }
            
            // Update stock
            $this->update($id, ['stock' => $newStock]);
            
            $this->commit();
            
            return ['success' => true, 'new_stock' => $newStock];
            
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Stock update error: " . $e->getMessage());
            return ['error' => 'Failed to update stock'];
        }
    }

    public function hasUserPurchased($userId, $productId) {
        try {
            $sql = "SELECT COUNT(*) FROM orders o
                    JOIN order_items oi ON o.id = oi.order_id
                    WHERE o.customer_id = ? 
                    AND oi.product_id = ?
                    AND o.status = 'delivered'";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$userId, $productId]);
            return (bool)$stmt->fetchColumn();
            
        } catch (\Exception $e) {
            error_log("Check user purchase error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getLastOrderId($userId, $productId) {
        try {
            $sql = "SELECT o.id FROM orders o
                    JOIN order_items oi ON o.id = oi.order_id
                    WHERE o.customer_id = ? 
                    AND oi.product_id = ?
                    AND o.status = 'delivered'
                    ORDER BY o.created_at DESC
                    LIMIT 1";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$userId, $productId]);
            return $stmt->fetchColumn();
            
        } catch (\Exception $e) {
            error_log("Get last order ID error: " . $e->getMessage());
            return null;
        }
    }
    
    public function getProductImages($productId) {
        try {
            $sql = "SELECT * FROM product_images 
                   WHERE product_id = ? 
                   ORDER BY is_primary DESC, id ASC";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$productId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get product images error: " . $e->getMessage());
            return [];
        }
    }

    public function find($id) {
        $sql = "SELECT p.*,
                       s.business_name as supplier_name,
                       COALESCE(p.rating, 0) as rating,
                       COALESCE(p.review_count, 0) as review_count,
                       CASE 
                           WHEN p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 
                           ELSE 0 
                       END as is_new,
                       CASE 
                           WHEN p.regular_price > p.price THEN 
                               ROUND(((p.regular_price - p.price) / p.regular_price) * 100) 
                           ELSE 0 
                       END as discount_percent
                FROM {$this->table} p
                LEFT JOIN suppliers s ON s.user_id = p.supplier_id
                WHERE p.id = ?";
                
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($product) {
            // Ensure these fields are always set
            $product['supplier_name'] = $product['supplier_name'] ?? 'Unknown Supplier';
            $product['rating'] = $product['rating'] ?? 0;
            $product['review_count'] = $product['review_count'] ?? 0;
            $product['discount_percent'] = $product['discount_percent'] ?? 0;
            $product['discounted_price'] = $product['price'];
            $product['regular_price'] = $product['regular_price'] ?? $product['price'];
            
            if ($product['discount_percent'] > 0) {
                $product['discounted_price'] = round($product['regular_price'] * (1 - $product['discount_percent'] / 100), 2);
            }
        }
        
        return $product;
    }
}