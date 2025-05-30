<?php
require_once __DIR__ . '/BaseDao.php';


class AuthDao extends BaseDao {
   protected $table_name;


   public function __construct() {
       $this->table_name = "users";
       parent::__construct($this->table_name);
   }


   public function get_user_by_email($email) {
       try {
           error_log("AuthDao: Searching for user with email: " . $email);
           $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
           error_log("AuthDao: Executing query: " . $query);
           
           $stmt = $this->connection->prepare($query);
           $stmt->bindParam(':email', $email);
           
           if (!$stmt->execute()) {
               $error = $stmt->errorInfo();
               error_log("AuthDao: SQL Error in get_user_by_email: " . json_encode($error));
               throw new PDOException("Query failed: " . $error[2]);
           }
           
           $result = $stmt->fetch(PDO::FETCH_ASSOC);
           error_log("AuthDao: User search result: " . ($result ? "User found" : "User not found"));
           
           return $result;
       } catch (PDOException $e) {
           error_log("AuthDao: Database error in get_user_by_email: " . $e->getMessage());
           throw $e;
       } catch (Exception $e) {
           error_log("AuthDao: General error in get_user_by_email: " . $e->getMessage());
           throw $e;
       }
   }

   public function query_unique($query, $params = []) {
       try {
           error_log("AuthDao: Executing unique query: " . $query);
           error_log("AuthDao: With parameters: " . json_encode($params));
           
           $stmt = $this->connection->prepare($query);
           
           if (!$stmt->execute($params)) {
               $error = $stmt->errorInfo();
               error_log("AuthDao: SQL Error in query_unique: " . json_encode($error));
               throw new PDOException("Query failed: " . $error[2]);
           }
           
           $result = $stmt->fetch(PDO::FETCH_ASSOC);
           error_log("AuthDao: Query result: " . ($result ? "Record found" : "No record found"));
           
           return $result;
       } catch (PDOException $e) {
           error_log("AuthDao: Database error in query_unique: " . $e->getMessage());
           throw $e;
       } catch (Exception $e) {
           error_log("AuthDao: General error in query_unique: " . $e->getMessage());
           throw $e;
       }
   }
}
