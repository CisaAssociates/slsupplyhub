<?php
namespace SLSupplyHub;
use SLSupplyHub\Database;
use SLSupplyHub\Model;

class Review extends Model {
    private $db;
    protected $table = 'feedback';

    public function __construct() {
        parent::__construct();
        $this->db = new Database();
    }

    public function submitReview($data) {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            return ['success' => false, 'message' => 'User must be logged in to submit a review'];
        }

        $required_fields = ['type', 'type_id', 'rating', 'comment'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return ['success' => false, 'message' => 'Missing required field: ' . $field];
            }
        }

        $allowed_types = ['product', 'supplier', 'driver'];
        if (!in_array($data['type'], $allowed_types)) {
            return ['success' => false, 'message' => 'Invalid review type'];
        }

        // Check if user has already reviewed this item
        $existing = $this->db->query(
            "SELECT id FROM feedback WHERE user_id = ? AND type = ? AND type_id = ?",
            [$user_id, $data['type'], $data['type_id']]
        );

        if ($existing->num_rows > 0) {
            return ['success' => false, 'message' => 'You have already reviewed this ' . $data['type']];
        }

        $rating = min(max(intval($data['rating']), 1), 5);
        $result = $this->db->query(
            "INSERT INTO feedback (user_id, type, type_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
            [$user_id, $data['type'], $data['type_id'], $rating, $data['comment']]
        );

        if ($result) {
            // Update average rating for the item
            $this->updateAverageRating($data['type'], $data['type_id']);
            return ['success' => true, 'message' => 'Review submitted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to submit review'];
    }

    public function getReviews($type, $type_id, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $reviews = $this->db->query(
            "SELECT f.*, u.name as user_name 
             FROM feedback f 
             JOIN users u ON f.user_id = u.id 
             WHERE f.type = ? AND f.type_id = ? 
             ORDER BY f.created_at DESC 
             LIMIT ? OFFSET ?",
            [$type, $type_id, $limit, $offset]
        );

        $total = $this->db->query(
            "SELECT COUNT(*) as total FROM feedback WHERE type = ? AND type_id = ?",
            [$type, $type_id]
        )->fetch_assoc()['total'];

        $ratings_distribution = $this->db->query(
            "SELECT rating, COUNT(*) as count 
             FROM feedback 
             WHERE type = ? AND type_id = ? 
             GROUP BY rating 
             ORDER BY rating DESC",
            [$type, $type_id]
        );

        $distribution = [];
        while ($row = $ratings_distribution->fetch_assoc()) {
            $distribution[$row['rating']] = $row['count'];
        }

        return [
            'reviews' => $reviews->fetch_all(MYSQLI_ASSOC),
            'total' => $total,
            'distribution' => $distribution,
            'pages' => ceil($total / $limit)
        ];
    }

    private function updateAverageRating($type, $type_id) {
        $avg = $this->db->query(
            "SELECT AVG(rating) as avg_rating FROM feedback WHERE type = ? AND type_id = ?",
            [$type, $type_id]
        )->fetch_assoc()['avg_rating'];

        $table = '';
        $column = '';
        
        switch ($type) {
            case 'product':
                $table = 'products';
                $column = 'rating';
                break;
            case 'supplier':
                $table = 'suppliers';
                $column = 'rating';
                break;
            case 'driver':
                $table = 'drivers';
                $column = 'rating';
                break;
        }

        if ($table && $column) {
            $this->db->query(
                "UPDATE {$table} SET {$column} = ? WHERE id = ?",
                [$avg, $type_id]
            );
        }
    }

    public function getUserReview($type, $type_id, $user_id) {
        $result = $this->db->query(
            "SELECT * FROM feedback WHERE type = ? AND type_id = ? AND user_id = ?",
            [$type, $type_id, $user_id]
        );
        
        return $result->fetch_assoc();
    }
}