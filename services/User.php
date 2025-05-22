<?php

namespace SLSupplyHub;

use PDO;
use Exception;
use SLSupplyHub\MailService;
use SLSupplyHub\Database;

class User
{
    private $db;
    private $mail;
    private $conn;
    private $table_name = "users";

    public function __construct($db = null)
    {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
        
        // Try to initialize MailService, but handle failures gracefully
        try {
            $this->mail = new MailService();
        } catch (\Exception $e) {
            // Log the error but continue without mail service
            error_log("WARNING: Mail service initialization failed: " . $e->getMessage());
            $this->mail = null;
        }
    }

    public function login($email, $password)
    {
        $query = "SELECT id, fullname, email, password, role, email_verified FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (password_verify($password, $row['password'])) {
                unset($row['password']);
                return $row;
            }
        }
        return false;
    }

    public function register($data, $role = 'customer')
    {
        try {
            $pdo = $this->db->getConnection();

            // Check if email already exists
            if ($this->emailExists($data['email'])) {
                return ['success' => false, 'error' => 'Email already registered'];
            }

            // Start transaction
            $pdo->beginTransaction();

            try {
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (
                        fullname, 
                        email, 
                        password, 
                        role, 
                        email_verified,
                        created_at
                    ) VALUES (
                        :fullname, 
                        :email, 
                        :password, 
                        :role, 
                        0,
                        NOW()
                    )
                ");

                $stmt->execute([
                    ':fullname' => $data['fullname'],
                    ':email' => $data['email'],
                    ':password' => $hashedPassword,
                    ':role' => $role,
                ]);

                $pdo->commit();
                return ['success' => true];
            } catch (\Exception $e) {
                $pdo->rollBack();
                error_log("Registration error: " . $e->getMessage());
                return ['success' => false, 'error' => 'Registration failed. Please try again.'];
            }
        } catch (\Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        }
    }

    public function redirectToDashboard($role)
    {
        switch ($role) {
            case 'customer':
                header('Location: ' . base_url('customer/'));
                break;
            case 'supplier':
                header('Location: ' . base_url('supplier/'));
                break;
            case 'admin':
                header('Location: ' . base_url('admin/'));
                break;
            case 'driver':
                header('Location: ' . base_url('driver/'));
                break;
            default:
                header('Location: ' . base_url());
                break;
        }
        exit();
    }

    public function emailExists($email)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function phoneExists($phone)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE phone = :phone";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":phone", $phone);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function getUserById($id)
    {
        $query = "SELECT id, fullname, email, role, created_at FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function verifyEmail($token)
    {
        try {
            $pdo = $this->db->getConnection();

            $stmt = $pdo->prepare("
                SELECT user_id 
                FROM verification_tokens 
                WHERE token = ? AND expires_at > NOW() 
                AND used = false
            ");
            $stmt->execute([$token]);
            $result = $stmt->fetch();

            if (!$result) {
                return ['error' => 'Invalid or expired verification token'];
            }

            $pdo->beginTransaction();

            // Mark email as verified
            $stmt = $pdo->prepare("UPDATE users SET email_verified = true WHERE id = ?");
            $stmt->execute([$result['user_id']]);

            // Mark token as used
            $stmt = $pdo->prepare("UPDATE verification_tokens SET used = true WHERE token = ?");
            $stmt->execute([$token]);

            $pdo->commit();

            return ['success' => true, 'message' => 'Email verified successfully'];
        } catch (Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            error_log("Email verification error: " . $e->getMessage());
            return ['error' => 'Verification failed. Please try again.'];
        }
    }

    public function resetPassword($email)
    {
        try {
            $pdo = $this->db->getConnection();

            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                // Return success anyway to prevent email enumeration
                return ['success' => true, 'message' => 'If your email is registered, you will receive password reset instructions.'];
            }

            $resetToken = bin2hex(random_bytes(32));

            $stmt = $pdo->prepare("
                INSERT INTO password_resets (user_id, token, expires_at)
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
            ");
            $stmt->execute([$user['id'], $resetToken]);

            // Send password reset email
            $this->mail->sendPasswordResetEmail($email, $user['name'], $resetToken);

            return ['success' => true, 'message' => 'If your email is registered, you will receive password reset instructions.'];
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['error' => 'Password reset failed. Please try again.'];
        }
    }

    public function updatePassword($userId, $currentPassword, $newPassword)
    {
        try {
            $pdo = $this->db->getConnection();

            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['error' => 'Current password is incorrect'];
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE " . $this->table_name . " SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);

            return ['success' => true, 'message' => 'Password updated successfully'];
        } catch (Exception $e) {
            error_log("Password update error: " . $e->getMessage());
            return ['error' => 'Password update failed. Please try again.'];
        }
    }

    public function getUserProfile($userId)
    {
        try {
            $pdo = $this->db->getConnection();

            $stmt = $pdo->prepare("
                SELECT u.id, u.name, u.email, u.role, u.created_at,
                       CASE 
                           WHEN u.role = 'customer' THEN c.id
                           WHEN u.role = 'supplier' THEN s.id
                           ELSE NULL
                       END as role_id,
                       s.business_name,
                       s.verified as supplier_verified
                FROM users u
                LEFT JOIN customers c ON u.id = c.user_id AND u.role = 'customer'
                LEFT JOIN suppliers s ON u.id = s.user_id AND u.role = 'supplier'
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['error' => 'User not found'];
            }

            // Get additional role-specific information
            if ($user['role'] === 'customer') {
                // Get loyalty info
                $stmt = $pdo->prepare("
                    SELECT transaction_count, tier, reward_amount
                    FROM loyalty_rewards
                    WHERE customer_id = ?
                ");
                $stmt->execute([$user['role_id']]);
                $user['loyalty'] = $stmt->fetch();

                // Get saved addresses
                $stmt = $pdo->prepare("
                    SELECT id, recipient_name, address_line, barangay, 
                           municipality, province, landmark, is_default
                    FROM customer_addresses
                    WHERE customer_id = ?
                ");
                $stmt->execute([$user['role_id']]);
                $user['addresses'] = $stmt->fetchAll();
            }

            return ['success' => true, 'user' => $user];
        } catch (Exception $e) {
            error_log("Get user profile error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve user profile'];
        }
    }

    public function updateProfile($userId, $data)
    {
        try {
            $pdo = $this->db->getConnection();

            $pdo->beginTransaction();

            // Update basic user info
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?
                WHERE id = ?
            ");
            $stmt->execute([$data['name'], $userId]);

            // Update role-specific information
            $user = $this->getUserProfile($userId);
            if ($user['success']) {
                switch ($user['user']['role']) {
                    case 'supplier':
                        if (isset($data['business_name'])) {
                            $stmt = $pdo->prepare("
                                UPDATE suppliers 
                                SET business_name = ?
                                WHERE user_id = ?
                            ");
                            $stmt->execute([$data['business_name'], $userId]);
                        }
                        break;
                }
            }

            $pdo->commit();
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } catch (Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            error_log("Update profile error: " . $e->getMessage());
            return ['error' => 'Profile update failed. Please try again.'];
        }
    }

    public function createUser($data) {
        try {
            // Validate required fields
            $requiredFields = ['fullname', 'email', 'password', 'role'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return ['error' => ucfirst($field) . ' is required'];
                }
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['error' => 'Invalid email format'];
            }

            if ($this->emailExists(trim($_POST["email"]))) {
                throw new Exception('Email already registered');
            }
            
            if ($this->phoneExists($_POST["phone"])) {
                throw new Exception('Phone number already registered');
            }

            // Get PDO connection
            $pdo = $this->conn;

            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Start transaction
            $pdo->beginTransaction();

            try {
                // Insert user
                $stmt = $pdo->prepare("
                    INSERT INTO users (fullname, email, password, role, phone, status) 
                    VALUES (?, ?, ?, ?, ?, 1)
                ");

                $stmt->execute([
                    $data['fullname'],
                    $data['email'],
                    $data['password'],
                    $data['role'],
                    $data['phone'] ?? null
                ]);

                $userId = $pdo->lastInsertId();

                $pdo->commit();
                return ['success' => true];

            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Create user error: " . $e->getMessage());
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Create user error: " . $e->getMessage());
            return ['error' => 'Failed to create user: ' . $e->getMessage()];
        }
    }

    public function updateUser($userId, $data)
    {
        try {
            // Validate required fields
            $requiredFields = ['fullname', 'email', 'role'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return ['error' => ucfirst($field) . ' is required'];
                }
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['error' => 'Invalid email format'];
            }

            // Check if email exists for other users
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $userId]);
            if ($stmt->fetch()) {
                return ['error' => 'Email already exists'];
            }

            // Start transaction
            $this->conn->beginTransaction();

            // Update user
            $stmt = $this->conn->prepare("
                UPDATE users 
                SET fullname = ?, email = ?, role = ?, phone = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $data['fullname'],
                $data['email'],
                $data['role'],
                $data['phone'] ?? null,
                $userId
            ]);

            $this->conn->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Update user error: " . $e->getMessage());
            return ['error' => 'Failed to update user'];
        }
    }

    public function deleteUser($userId)
    {
        try {
            // Check if user exists
            $stmt = $this->conn->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['error' => 'User not found'];
            }

            // Start transaction
            $this->conn->beginTransaction();

            // Delete user
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);

            $this->conn->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Delete user error: " . $e->getMessage());
            return ['error' => 'Failed to delete user'];
        }
    }

    public function toggleStatus($userId)
    {
        try {
            $stmt = $this->conn->prepare("
                UPDATE users 
                SET status = NOT status 
                WHERE id = ?
            ");

            $stmt->execute([$userId]);

            if ($stmt->rowCount() === 0) {
                return ['error' => 'User not found'];
            }

            return ['success' => true];
        } catch (Exception $e) {
            error_log("Toggle user status error: " . $e->getMessage());
            return ['error' => 'Failed to update user status'];
        }
    }

    // In User class - getAllUsers method
    public function getAllUsers()
    {
        try {
            $stmt = $this->conn->query("
            SELECT id, fullname, email, phone, role, status 
            FROM users 
            WHERE role IN ('supplier', 'driver')
            ORDER BY created_at DESC
        ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get all users error: " . $e->getMessage());
            return [];
        }
    }
}
