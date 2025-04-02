<?php
class Database {
  private static $host = 'localhost';
  private static $dbName = 'watchland';
  private static $dbPort = 3306;
  private static $username = 'root';
  private static $password = 'Berin1235';
  private static $connection = null;

  public static function connect() {
      if (self::$connection === null) {
          try {
              self::$connection = new PDO(
                  "mysql:host=" . self::$host . ";dbname=" . self::$dbName . ";port=" . self::$dbPort,
                  self::$username,
                  self::$password,
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

