<?php

function base_url($path = '') {
    try {
        $config = \SLSupplyHub\Config::getInstance();
        $baseUrl = rtrim($config->get('APP_URL'), '/');
        
        if (!$baseUrl) {
            throw new Exception('APP_URL is not configured in .env file');
        }
        
        // Normalize the path
        if (!empty($path)) {
            $path = '/' . ltrim($path, '/');
        }
        
        return $baseUrl . $path;
    } catch (Exception $e) {
        error_log('Error in base_url(): ' . $e->getMessage());
        return '/';
    }
}

function asset_url($path = '') {
    return base_url('assets/' . ltrim($path, '/'));
}

function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function is_current_url($path) {
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $checkPath = '/' . ltrim($path, '/');
    return $currentPath === $checkPath;
}

function redirect($path) {
    header('Location: ' . base_url($path));
    exit;
}