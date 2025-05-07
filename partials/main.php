<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../services/Helpers/url_helper.php';

use SLSupplyHub\Session;

$session = new Session();

define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

$currentScript = basename($_SERVER['SCRIPT_NAME']);
$authPages = ['auth-login.php', 'auth-register.php', 'auth-recoverpw.php'];

// Redirect logged-in users away from auth pages
if (in_array($currentScript, $authPages)) {
    if ($session->isLoggedIn()) {
        $userRole = $session->getUserRole();
        $redirectUrl = match ($userRole) {
            'admin' => '/admin/',
            'staff' => '/staff/',
            'supplier' => '/supplier/',
            'customer' => '/customer/',
            default => '/'
        };
        header("Location: " . base_url($redirectUrl));
        exit;
    }
}

// Validate user roles for logged-in users
if ($session->isLoggedIn() && !in_array($currentScript, $authPages)) {
    $allowedRoles = ['admin', 'staff', 'supplier', 'customer', 'driver'];
    $userRole = $session->getUserRole();
    
    if (!in_array($userRole, $allowedRoles)) {
        header("Location: " . base_url('403.php'));
        exit;
    }
}
?>
 
<!DOCTYPE html>
<html lang="en" data-layout="<?= $session->getUserRole() === 'customer' ? 'horizontal' : 'detached'; ?>" data-topbar="horizontal">
