<?php

namespace SLSupplyHub;

use PDO;
use Exception;
use PDOException;
use SLSupplyHub\Config;

class Database
{
    private static $instance = null;
    private $connection;
    private $encryption_key;
    private $iv;

    private function __construct()
    {
        $config = Config::getInstance();

        // Get database configuration from Config
        $host = $config->get('DB_HOST');
        $db_name = $config->get('DB_NAME');
        $username = $config->get('DB_USER');
        $password = $config->get('DB_PASS');

        // Encryption configuration
        $this->encryption_key = $config->get('ENCRYPTION_KEY', ''); // Should be set in production
        $this->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

        try {
            if (empty($host) || empty($db_name)) {
                throw new Exception("Database configuration is incomplete. Please check your .env file.");
            }

            $this->connection = new PDO(
                "mysql:host={$host};dbname={$db_name};charset=utf8mb4",
                $username,
                $password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                )
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check your configuration.");
        }
    }

    // Singleton pattern to ensure single database connection
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        try {
            if (!$this->connection) {
                throw new Exception("Database connection not established");
            }
            return $this->connection; // Remove the SELECT 1 query
        } catch (Exception $e) {
            error_log("[Database] Connection error: " . $e->getMessage());
            throw $e;
        }
    }

    public function executeQuery($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);

            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("[Database] SQL Error: " . json_encode($error));
                throw new Exception("Database error: {$error[2]}");
            }

            // Return lastInsertId for INSERT operations
            if (stripos($sql, 'INSERT') === 0) {
                return $this->connection->lastInsertId();
            }

            return $stmt;
        } catch (Exception $e) {
            error_log("[Database] Exception: " . $e->getMessage());
            throw $e;
        }
    }

    // Encryption method with better error handling
    public function encrypt($data)
    {
        if (empty($this->encryption_key)) {
            throw new Exception("Encryption key not set");
        }

        $encrypted = openssl_encrypt(
            $data,
            'aes-256-cbc',
            $this->encryption_key,
            0,
            $this->iv
        );

        if ($encrypted === false) {
            throw new Exception("Encryption failed");
        }

        return $encrypted;
    }

    // Decryption method with better error handling
    public function decrypt($encryptedData)
    {
        if (empty($this->encryption_key)) {
            throw new Exception("Encryption key not set");
        }

        $decrypted = openssl_decrypt(
            $encryptedData,
            'aes-256-cbc',
            $this->encryption_key,
            0,
            $this->iv
        );

        if ($decrypted === false) {
            throw new Exception("Decryption failed");
        }

        return $decrypted;
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Create connection
$database = Database::getInstance();
$conn = $database->getConnection();
