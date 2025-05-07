<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use SLSupplyHub\User;
use SLSupplyHub\Session;

// Prevent any output before JSON response
ob_start();

// Set JSON content type
header('Content-Type: application/json');

// Error handling to catch any PHP errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    $session = new Session();

    $user = new User();
    $response = ['success' => false, 'message' => 'Invalid request'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'list':
                $users = $user->getAllUsers();
                $response = [
                    'success' => true,
                    'data' => $users
                ];
                break;

            case 'create':
                $userData = [
                    'fullname' => $_POST['fullname'],
                    'email' => trim($_POST['email']),
                    'phone' => $_POST['phone'],
                    'role' => $_POST['role'],
                    'password' => $_POST['password']
                ];

                $result = $user->createUser($userData);
                
                if (isset($result['error'])) {
                    throw new Exception($result['error']);
                }

                $response = [
                    'success' => true,
                    'message' => 'User created successfully'
                ];
                break;

            case 'update':
                $userId = $_POST['id'];
                $userData = [
                    'fullname' => $_POST['fullname'],
                    'email' => $_POST['email'],
                    'phone' => $_POST['phone'],
                    'role' => $_POST['role']
                ];

                $result = $user->updateUser($userId, $userData);
                
                if (isset($result['error'])) {
                    throw new Exception($result['error']);
                }

                $response = [
                    'success' => true,
                    'message' => 'User updated successfully'
                ];
                break;

            case 'delete':
                $userId = $_POST['id'];
                $result = $user->deleteUser($userId);
                
                if (isset($result['error'])) {
                    throw new Exception($result['error']);
                }

                $response = [
                    'success' => true,
                    'message' => 'User deleted successfully'
                ];
                break;

            case 'toggle-status':
                $userId = $_POST['id'];
                $result = $user->toggleStatus($userId);
                
                if (isset($result['error'])) {
                    throw new Exception($result['error']);
                }

                $response = [
                    'success' => true,
                    'message' => 'User status updated successfully'
                ];
                break;

            default:
                throw new Exception('Invalid action');
        }
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Clean any output buffers before sending JSON
while (ob_get_level()) {
    ob_end_clean();
}

// Ensure proper JSON encoding
if (false === ($jsonResponse = json_encode($response))) {
    $jsonResponse = json_encode([
        'success' => false,
        'message' => 'JSON encoding failed: ' . json_last_error_msg()
    ]);
}

echo $jsonResponse;