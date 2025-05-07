<?php
namespace SLSupplyHub;

use SLSupplyHub\Database;

class Session {
    private $db;
    private const MAX_LOGIN_ATTEMPTS = 10;
    private const LOCKOUT_DURATION = 43200;
    private $isSecure;
    private $lifetime;

    public function __construct() {
        $this->isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $this->lifetime = 43200;
        
        // Configure all session parameters before starting the session
        $this->configureSessionParams();
        
        // Now start the session
        session_start();
        
        $this->db = Database::getInstance();
    }

      private function configureSessionParams() {
        // Set session name
        session_name('SLSupplyHub');
        
        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => $this->lifetime,
            'path' => '/',
            'domain' => '', // Leave empty to use current domain
            'secure' => $this->isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        // Additional security headers
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
    }

    public function login($user) {
        try {
            if (!is_array($user) || !isset($user['id']) || !isset($user['email']) || !isset($user['fullname']) || !isset($user['role'])) {
                error_log("Invalid user data provided to session login");
                return false;
            }
            
            session_regenerate_id(true);
            $_SESSION["id"] = $user["id"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["fullname"] = $user["fullname"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["last_activity"] = time();
            $_SESSION["loggedin"] = true;
            return true;
        } catch (\Exception $e) {
            error_log("Session login error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserId() {
        return $_SESSION['id'] ?? null;
    }

    public function getUserRole() {
        return $_SESSION['role'] ?? null;
    }

    public function getUserName() {
        return $_SESSION['fullname'] ?? null;
    }

    public function getUserEmail() {
        return $_SESSION['email'] ?? null;
    }
    
    public function getCsrfToken() {
        return $_SESSION['csrf_token'] ?? null;
    }

    public function validateCsrfToken($token) {
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public function isLoggedIn() {
        return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
    }

    public function checkLoginAttempts($email) {
        $attempts = $_SESSION['login_attempts'][$email] ?? [
            'count' => 0,
            'first_attempt' => time()
        ];

        // Reset if lockout duration has passed
        if (time() - $attempts['first_attempt'] > self::LOCKOUT_DURATION) {
            $this->resetLoginAttempts($email);
            return true;
        }

        // Check if max attempts exceeded
        if ($attempts['count'] >= self::MAX_LOGIN_ATTEMPTS) {
            return false;
        }

        return true;
    }

    public function incrementLoginAttempts($email) {
        if (!isset($_SESSION['login_attempts'][$email])) {
            $_SESSION['login_attempts'][$email] = [
                'count' => 0,
                'first_attempt' => time()
            ];
        }

        $_SESSION['login_attempts'][$email]['count']++;
    }

    public function resetLoginAttempts($email = null) {
        if ($email === null) {
            $_SESSION['login_attempts'] = [];
        } else {
            unset($_SESSION['login_attempts'][$email]);
        }
    }

    public function getRemainingAttempts($email) {
        $attempts = $_SESSION['login_attempts'][$email]['count'] ?? 0;
        return max(0, self::MAX_LOGIN_ATTEMPTS - $attempts);
    }

    public function getLockoutTimeRemaining($email) {
        $firstAttempt = $_SESSION['login_attempts'][$email]['first_attempt'] ?? time();
        $timeElapsed = time() - $firstAttempt;
        return max(0, self::LOCKOUT_DURATION - $timeElapsed);
    }

    public function enforceRoleAccess($allowedRoles) {
        if (!$this->isLoggedIn()) {
            header('Location: /auth-login.php');
            exit;
        }

        if (!in_array($this->getRole(), $allowedRoles)) {
            header('HTTP/1.1 403 Forbidden');
            include '403.php';
            exit;
        }
    }

    public function refreshSession() {
        if (!$this->isLoggedIn()) {
            return;
        }

        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration']) ||
            time() - $_SESSION['last_regeneration'] > 300) { // Every 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }

        // Update last activity time
        $_SESSION['last_activity'] = time();
    }

    public function checkSessionTimeout() {
        if (!$this->isLoggedIn()) {
            return;
        }

        $timeout = 1800; // 30 minutes
        if (time() - $_SESSION['last_activity'] > $timeout) {
            $this->logout();
            header('Location: /auth-login.php?timeout=1');
            exit;
        }
    }

    public function requireRole($allowedRoles) {
        $this->requireLogin();
        
        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }
        
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
            header("Location: dashboard.php");
            exit;
        }
    }

    public function validateCSRF($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }

    // Prevent session fixation
    public function regenerateSession() {
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $role = $_SESSION['role'];
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = $role;
        }
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: auth-login.php");
            exit;
        }
    }

    public function setUserData($userData) {
        $_SESSION['id'] = $userData['id'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['fullname'] = $userData['fullname'];
        $_SESSION['role'] = $userData['role'];
        $_SESSION['loggedin'] = true;
    }

    public function regenerate() {
        return session_regenerate_id(true);
    }

    public function destroy() {
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === false;
        
        // Destroy the session
        session_destroy();
    }

    public function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function has($key) {
        return isset($_SESSION[$key]);
    }

    public function clear() {
        $_SESSION = array();
    }

    public function updateActivity() {
        $_SESSION['last_activity'] = time();
    }

    public function checkActivity() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $this->lifetime)) {
            $this->logout();
            header("Location: auth-login.php?timeout=1");
            exit;
        }
        $this->updateActivity();
    }

    public function getRole() {
        return $this->getUserRole();
    }

    public function logout() {
        // Clear all session data
        $this->clear();
        // Destroy the session
        $this->destroy();
    }
}