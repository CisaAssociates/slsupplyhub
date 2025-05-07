<?php
require_once '../review.php';
session_start();

header('Content-Type: application/json');

$review = new Review();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'submit':
        $response = $review->submitReview([
            'type' => $_POST['type'] ?? '',
            'type_id' => $_POST['type_id'] ?? '',
            'rating' => $_POST['rating'] ?? '',
            'comment' => $_POST['comment'] ?? ''
        ]);
        echo json_encode($response);
        break;

    case 'get':
        $type = $_GET['type'] ?? '';
        $type_id = $_GET['type_id'] ?? '';
        $page = intval($_GET['page'] ?? 1);
        
        $reviews = $review->getReviews($type, $type_id, $page);
        echo json_encode($reviews);
        break;

    case 'get_user_review':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            break;
        }
        
        $type = $_GET['type'] ?? '';
        $type_id = $_GET['type_id'] ?? '';
        $user_review = $review->getUserReview($type, $type_id, $_SESSION['user_id']);
        echo json_encode(['success' => true, 'review' => $user_review]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}