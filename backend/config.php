<?php
// Simple Environment Detection
class Environment {
    public static function isLocal() {
        $host = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $host === 'localhost' || $host === '127.0.0.1' || strpos($host, 'localhost') !== false;
    }
}

// Configuration Settings
class Config {
    
    // Database Configuration
    public static function getDBConfig() {
        if (Environment::isLocal()) {
            // Local Development Settings
            return [
                'host' => 'localhost',
                'name' => 'watchland',
                'user' => 'root',
                'password' => 'Berin1235',
                'port' => 3306
            ];
        } else {
            // Production Settings (from environment variables)
            return [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'name' => $_ENV['DB_NAME'] ?? 'watchland',
                'user' => $_ENV['DB_USER'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'port' => $_ENV['DB_PORT'] ?? 3306
            ];
        }
    }
    
    // JWT Secret Key
    public static function getJWTSecret() {
        return $_ENV['JWT_SECRET'] ?? 'secret_key';
    }
    
    // URL Configuration
    public static function getURLs() {
        if (Environment::isLocal()) {
            return [
                'frontend' => 'http://localhost/IBU-WebProgramming-2025',
                'backend' => 'http://localhost/IBU-WebProgramming-2025/backend'
            ];
        } else {
            return [
                'frontend' => $_ENV['FRONTEND_URL'] ?? 'https://watchland-kg5xq.ondigitalocean.app',
                'backend' => $_ENV['BACKEND_URL'] ?? 'https://watchland-kg5xq.ondigitalocean.app/backend'
            ];
        }
    }
}

// Database Connection
class Database {
    private static $connection = null;

    public static function connect() {
        if (self::$connection === null) {
            try {
                // Get database configuration
                $dbConfig = Config::getDBConfig();
                
                // Build connection string
                $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};port={$dbConfig['port']}";
                
                // Connection options
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ];
                
                // Add SSL for production if needed
                if (!Environment::isLocal()) {
                    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                }
                
                // Create connection
                self::$connection = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], $options);
                
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}

// Compatibility layer for existing code that uses JWTConfig
class JWTConfig {
    public static function DB_NAME() { return Config::getDBConfig()['name']; }
    public static function DB_HOST() { return Config::getDBConfig()['host']; }
    public static function DB_USER() { return Config::getDBConfig()['user']; }
    public static function DB_PASSWORD() { return Config::getDBConfig()['password']; }
    public static function DB_PORT() { return Config::getDBConfig()['port']; }
    public static function JWT_SECRET() { return Config::getJWTSecret(); }
    public static function FRONTEND_URL() { return Config::getURLs()['frontend']; }
    public static function BACKEND_URL() { return Config::getURLs()['backend']; }
}
?>

