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
        // For production, this should come from environment variables
        if (Environment::isProduction()) {
            return $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
        }
        // Keep existing password for local development
        return $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: 'Berin1235';
    }
    
    public static function DB_HOST()
    {
        // For production, typically 'localhost' or specific DB host
        // For local development, keep existing 'localhost'
        return $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
    }
 
    public static function JWT_SECRET() {
        // Use environment variable for production, fallback for local
        return $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: 'secret_key';
    }
    
    public static function FRONTEND_URL() {
        if (Environment::isLocal()) {
            return 'http://localhost/IBU-WebProgramming-2025';
        }
        // For production, this will be your Digital Ocean domain
        return $_ENV['FRONTEND_URL'] ?? getenv('FRONTEND_URL') ?: 'https://your-domain.com';
    }
    
    public static function BACKEND_URL() {
        if (Environment::isLocal()) {
            return 'http://localhost/IBU-WebProgramming-2025/backend';
        }
        // For production, this will be your Digital Ocean domain + backend path
        return $_ENV['BACKEND_URL'] ?? getenv('BACKEND_URL') ?: 'https://your-domain.com/backend';
    }
}

class Database {
    private static $connection = null;

    public static function connect() {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO(
                    "mysql:host=" . JWTConfig::DB_HOST() . 
                    ";dbname=" . JWTConfig::DB_NAME() . 
                    ";port=" . JWTConfig::DB_PORT(),
                    JWTConfig::DB_USER(),
                    JWTConfig::DB_PASSWORD(),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
?>

