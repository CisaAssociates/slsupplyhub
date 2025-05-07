<?php
namespace SLSupplyHub;

class ServiceProvider {
    private static $instance = null;
    private $services = [];
    
    private function __construct() {
        // Initialize core services
        $this->services['database'] = function() {
            return Database::getInstance();
        };
        
        $this->services['session'] = function() {
            return new Session();
        };
        
        $this->services['mail'] = function() {
            return new MailService();
        };
        
        $this->services['user'] = function() {
            return new User($this->get('database')->getConnection());
        };
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get($serviceName) {
        if (!isset($this->services[$serviceName])) {
            throw new \Exception("Service '{$serviceName}' not found");
        }
        
        if (is_callable($this->services[$serviceName])) {
            $this->services[$serviceName] = $this->services[$serviceName]();
        }
        
        return $this->services[$serviceName];
    }
    
    public function register($serviceName, $factory) {
        $this->services[$serviceName] = $factory;
    }
    
    private function __clone() {}
    
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}