<?php
namespace SLSupplyHub;

class Category extends Model {
    protected $table = 'categories';
    protected $fillable = ['name', 'description', 'parent_id'];
    
    protected $validationRules = [
        'name' => ['required', 'max:50']
    ];
    
    public function getActiveCategories() {
        try {
            $sql = "SELECT 
                    c.*, 
                    COUNT(p.id) as product_count,
                    parent.name as parent_name
                   FROM {$this->table} c
                   LEFT JOIN categories parent ON c.parent_id = parent.id
                   LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                   GROUP BY c.id
                   ORDER BY c.parent_id NULLS FIRST, c.name";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get categories error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAllCategories() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY name";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Get all categories error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getCategoryHierarchy() {
        $categories = $this->getActiveCategories();
        $hierarchy = [];
        
        // Group categories by parent
        foreach ($categories as $category) {
            if (!$category['parent_id']) {
                if (!isset($hierarchy[$category['id']])) {
                    $hierarchy[$category['id']] = [
                        'id' => $category['id'],
                        'name' => $category['name'],
                        'product_count' => $category['product_count'],
                        'children' => []
                    ];
                } else {
                    $hierarchy[$category['id']]['product_count'] = $category['product_count'];
                }
            } else {
                if (!isset($hierarchy[$category['parent_id']])) {
                    $hierarchy[$category['parent_id']] = [
                        'id' => $category['parent_id'],
                        'name' => $category['parent_name'],
                        'product_count' => 0,
                        'children' => []
                    ];
                }
                $hierarchy[$category['parent_id']]['children'][] = [
                    'id' => $category['id'],
                    'name' => $category['name'],
                    'product_count' => $category['product_count']
                ];
            }
        }
        
        return array_values($hierarchy);
    }
}