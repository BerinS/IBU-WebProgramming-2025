<?php
// Environment Detection
class Environment {
    public static function detect() {
        // Check if we're on localhost or production
        $serverName = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        $isLocal = in_array($serverName, ['localhost', '127.0.0.1']) || 
                   strpos($serverName, 'localhost') !== false || 
                   strpos($serverName, '127.0.0.1') !== false;
        
        return $isLocal ? 'local' : 'production';
    }
    
    public static function isLocal() {
        return self::detect() === 'local';
    }
    
    public static function isProduction() {
        return self::detect() === 'production';
    }
}

// JWT Secret Key Definition
class JWTConfig {
    
    public static function DB_NAME()
    {
        // Use environment variable if available, otherwise default
        return $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'watchland'; 
    }
    
    public static function DB_PORT()
    {
        return $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306;
    }
    
    public static function DB_USER()
    {
        return $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
    }
    
    public static function DB_PASSWORD()
    {
        if (Environment::isProduction()) {
            return $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
        }
        return $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: 'Berin1235';
    }
    
    public static function DB_HOST()
    {
        return $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
    }
 
    public static function JWT_SECRET() {
        // environment variable for production, fallback for local
        return $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: 'secret_key';
    }
    
    public static function FRONTEND_URL() {
        if (Environment::isLocal()) {
            return 'http://localhost/IBU-WebProgramming-2025';
        }
        return $_ENV['FRONTEND_URL'] ?? getenv('FRONTEND_URL') ?: 'https://watchland-kg5xq.ondigitalocean.app';
    }
    
    public static function BACKEND_URL() {
        if (Environment::isLocal()) {
            return 'http://localhost/IBU-WebProgramming-2025/backend';
        }
        return $_ENV['BACKEND_URL'] ?? getenv('BACKEND_URL') ?: 'https://watchland-kg5xq.ondigitalocean.app/backend';
    }
}

class Database {
    private static $connection = null;

    public static function connect() {
        if (self::$connection === null) {
            try {
                // Build DSN
                $dsn = "mysql:host=" . JWTConfig::DB_HOST() . 
                       ";dbname=" . JWTConfig::DB_NAME() . 
                       ";port=" . JWTConfig::DB_PORT();
                
                // PDO options
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ];
                
                // Add SSL options for production (DigitalOcean requires SSL)
                if (Environment::isProduction()) {
                    $sslMode = $_ENV['DB_SSL_MODE'] ?? getenv('DB_SSL_MODE') ?? 'REQUIRED';
                    if ($sslMode === 'REQUIRED') {
                        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                    }
                }
                
                self::$connection = new PDO(
                    $dsn,
                    JWTConfig::DB_USER(),
                    JWTConfig::DB_PASSWORD(),
                    $options
                );
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
?>

