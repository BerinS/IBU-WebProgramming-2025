<?php
// JWT Secret Key Definition
class JWTConfig {
    
    public static function DB_NAME()
    {
        return 'watchland'; 
    }
    public static function DB_PORT()
    {
        return 3306;
    }
    public static function DB_USER()
    {
        return 'root';
    }
    public static function DB_PASSWORD()
    {
        return 'Berin1235';
    }
    public static function DB_HOST()
    {
        return 'localhost';
    }
 
    public static function JWT_SECRET() {
        return 'secret_key';
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

