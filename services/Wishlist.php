<?php

namespace SLSupplyHub;

class Wishlist extends Model {
    protected $table = 'wishlists';
    
    public function getWishlistItems($userId) {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT product_id FROM {$this->table} WHERE customer_id = ?"
        );
        $stmt->execute([$userId]);
        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'product_id');
    }

    public function addToWishlist($userId, $productId) {
        $stmt = $this->db->getConnection()->prepare(
            "INSERT IGNORE INTO {$this->table} (customer_id, product_id) VALUES (?, ?)"
        );
        return $stmt->execute([$userId, $productId]);
    }

    public function removeFromWishlist($userId, $productId) {
        $stmt = $this->db->getConnection()->prepare(
            "DELETE FROM {$this->table} WHERE customer_id = ? AND product_id = ?"
        );
        return $stmt->execute([$userId, $productId]);
    }

    public function getWishlistCount($userId) {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE customer_id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
}