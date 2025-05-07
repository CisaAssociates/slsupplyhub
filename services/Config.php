<?php
namespace SLSupplyHub;

class Config {
    private static $instance = null;
    private $config = [];
    
    private function __construct() {
        // Load environment variables
        $this->loadEnv();
        
        // Set default configurations
        $this->setDefaults();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadEnv() {
        $envFile = dirname(__DIR__) . '/.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
                        $value = $matches[2];
                    }
                    
                    $this->set($name, $value);
                }
            }
        }
    }

    // 'APP_URL' => 'https://slsupplyhub.slsuisa.com',
            
    // 'DB_HOST' => 'localhost',
    // 'DB_NAME' => 'u347279731_slsupplyhub_db',
    // 'DB_USER' => 'u347279731_slsupplyhub',
    // 'DB_PASS' => 'Slsupplyhub2025',
    
    private function setDefaults() {
        $defaults = [
            'APP_NAME' => 'SL Supply Hub',
            'APP_ENV' => 'development',
            'APP_DEBUG' => true,
            'APP_URL' => 'http://localhost/slsupplyhub2',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'slsupplyhub',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'MAIL_HOST' => 'smtp.mailtrap.io',
            'MAIL_PORT' => 587,
            'MAIL_USERNAME' => null,
            'MAIL_PASSWORD' => null,
            'MAIL_ENCRYPTION' => 'tls',
            'MAIL_FROM_ADDRESS' => 'noreply@slsupplyhub.com',
            'MAIL_FROM_NAME' => 'SL Supply Hub',
            
            'SESSION_LIFETIME' => 120,
            'SESSION_SECURE' => false,
            
            'FILE_UPLOAD_MAX_SIZE' => 5242880, // 5MB
            'ALLOWED_IMAGE_TYPES' => ['jpg', 'jpeg', 'png', 'gif'],
            'ALLOWED_DOC_TYPES' => ['pdf', 'doc', 'docx'],
            
            'PAGINATION_PER_PAGE' => 10,
            
            'CURRENCY' => 'PHP',
            'CURRENCY_SYMBOL' => '₱',
            
            'TIME_ZONE' => 'Asia/Manila'
        ];
        
        foreach ($defaults as $key => $value) {
            if (!isset($this->config[$key])) {
                $this->config[$key] = $value;
            }
        }
    }
    
    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    public function set($key, $value) {
        $this->config[$key] = $value;
    }
    
    public function has($key) {
        return isset($this->config[$key]);
    }
    
    public function all() {
        return $this->config;
    }
    
    public function getMailConfig() {
        return [
            'host' => $this->get('MAIL_HOST'),
            'port' => $this->get('MAIL_PORT'),
            'username' => $this->get('MAIL_USERNAME'),
            'password' => $this->get('MAIL_PASSWORD'),
            'encryption' => $this->get('MAIL_ENCRYPTION'),
            'from' => [
                'address' => $this->get('MAIL_FROM_ADDRESS'),
                'name' => $this->get('MAIL_FROM_NAME')
            ]
        ];
    }
    
    public function getDatabaseConfig() {
        return [
            'host' => $this->get('DB_HOST'),
            'name' => $this->get('DB_NAME'),
            'user' => $this->get('DB_USER'),
            'pass' => $this->get('DB_PASS')
        ];
    }
    
    public function getAppConfig() {
        return [
            'name' => $this->get('APP_NAME'),
            'env' => $this->get('APP_ENV'),
            'debug' => $this->get('APP_DEBUG'),
            'url' => $this->get('APP_URL'),
            'timezone' => $this->get('TIME_ZONE')
        ];
    }
    
    public function getFileUploadConfig() {
        return [
            'maxSize' => $this->get('FILE_UPLOAD_MAX_SIZE'),
            'allowedImageTypes' => $this->get('ALLOWED_IMAGE_TYPES'),
            'allowedDocTypes' => $this->get('ALLOWED_DOC_TYPES')
        ];
    }
}