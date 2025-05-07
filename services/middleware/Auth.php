<?php

namespace SLSupplyHub\Middleware;

use SLSupplyHub\ServiceProvider;
use SLSupplyHub\Session;

class Auth
{
    private $services;

    public function __construct()
    {
        $this->services = ServiceProvider::getInstance();
    }

    public function handle($allowedRoles = null)
    {
        $session = $this->services->get('session');

        // Check if user is logged in
        if (!$session->isLoggedIn()) {
            header('Location: ' . base_url('auth-login.php'));
            exit;
        }

        // Check session timeout
        $session->checkActivity();

        // Validate role if specified
        if ($allowedRoles !== null) {
            if (!is_array($allowedRoles)) {
                $allowedRoles = [$allowedRoles];
            }

            $userRole = $session->getUserRole();
            if (!in_array($userRole, $allowedRoles)) {
                header('Location: /403.php');
                exit;
            }
        }

        // Update last activity
        $session->updateActivity();

        return true;
    }

    public function verifyCSRF()
    {
        $session = $this->services->get('session');
        $token = $_POST['csrf_token'] ?? '';
        
        if (!$session->validateCsrfToken($token)) {
            header('HTTP/1.1 403 Forbidden');
            exit('CSRF token validation failed');
        }

        return true;
    }

    public function requireEmailVerified()
    {
        $session = $this->services->get('session');
        $user = $this->services->get('user');

        if (!$session->isLoggedIn()) {
            header('Location: /auth-login.php');
            exit;
        }

        $userData = $user->getUserById($session->getUserId());
        if (!$userData['email_verified']) {
            header('Location: /verify-email-notice.php');
            exit;
        }

        return true;
    }
}
