<?php
require_once __DIR__ . '/../config.php';
  
class BaseDao {
    protected $table;
    protected $connection;

    public function __construct($table) {
        try {
            $this->table = $table;
            $this->connection = Database::connect();
            error_log("BaseDao initialized for table: " . $table);
        } catch (Exception $e) {
            error_log("Error initializing BaseDao: " . $e->getMessage());
            throw $e;
        }
    }

    public function get_all() {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM " . $this->table);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in get_all for table {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    public function get_by_id($id) {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM " . $this->table . " WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error in get_by_id for table {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    public function insert($data) {
        try {
            error_log("Attempting insert into table {$this->table}");
            error_log("Insert data: " . json_encode($data));
            
            $columns = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            $sql = "INSERT INTO " . $this->table . " ($columns) VALUES ($placeholders)";
            
            error_log("SQL Query: " . $sql);
            
            $stmt = $this->connection->prepare($sql);
            
            // Log each bound parameter
            foreach ($data as $key => $value) {
                error_log("Binding $key => " . (is_array($value) ? json_encode($value) : $value));
            }
            
            $result = $stmt->execute($data);
            
            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("SQL Error: " . json_encode($error));
                throw new PDOException("Insert failed: " . $error[2]);
            }
            
            $id = $this->connection->lastInsertId();
            error_log("Insert successful. New ID: " . $id);
            return $id;
            
        } catch (PDOException $e) {
            error_log("Database error in insert for table {$this->table}: " . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            error_log("General error in insert for table {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, $data) {
        try {
            $fields = "";
            foreach ($data as $key => $value) {
                $fields .= "$key = :$key, ";
            }
            $fields = rtrim($fields, ", ");
            $sql = "UPDATE " . $this->table . " SET $fields WHERE id = :id";
            $stmt = $this->connection->prepare($sql);
            $data['id'] = $id;
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Error in update for table {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id) {
        try {
            error_log("[BaseDao] Starting delete operation for table {$this->table}, ID: " . $id);
            
            // First check if the record exists
            error_log("[BaseDao] Checking if record exists");
            $check = $this->get_by_id($id);
            if (!$check) {
                error_log("[BaseDao] Record not found in table {$this->table} with ID: " . $id);
                return false;
            }
            error_log("[BaseDao] Found record to delete: " . json_encode($check));

            // Set error mode to throw exceptions
            error_log("[BaseDao] Setting PDO error mode");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Enable query logging
            error_log("[BaseDao] Enabling query logging");
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            // Prepare and execute the delete statement
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            error_log("[BaseDao] Preparing SQL: " . $sql);
            
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                $error = $this->connection->errorInfo();
                error_log("[BaseDao] Prepare statement failed: " . json_encode($error));
                return false;
            }
            
            error_log("[BaseDao] Binding parameter id = " . $id);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            // Execute and get result
            error_log("[BaseDao] Executing delete statement");
            $result = $stmt->execute();
            $rowCount = $stmt->rowCount();
            
            error_log("[BaseDao] Delete execution result: " . ($result ? "success" : "failed"));
            error_log("[BaseDao] Rows affected: " . $rowCount);
            
            // Get any SQL errors
            $errorInfo = $stmt->errorInfo();
            error_log("[BaseDao] Statement error info: " . json_encode($errorInfo));
            
            if ($errorInfo[0] !== '00000') {
                error_log("[BaseDao] SQL Error: " . json_encode($errorInfo));
                throw new PDOException("Delete failed: " . $errorInfo[2]);
            }
            
            // Verify deletion
            error_log("[BaseDao] Verifying deletion");
            $checkAfter = $this->get_by_id($id);
            if ($checkAfter) {
                error_log("[BaseDao] Record still exists after deletion attempt!");
                return false;
            }
            
            error_log("[BaseDao] Delete operation completed successfully");
            return $rowCount > 0;
            
        } catch (PDOException $e) {
            error_log("[BaseDao] PDO Exception in delete: " . $e->getMessage());
            error_log("[BaseDao] SQL State: " . $e->getCode());
            error_log("[BaseDao] Stack trace: " . $e->getTraceAsString());
            throw $e;
        } catch (Exception $e) {
            error_log("[BaseDao] General error in delete: " . $e->getMessage());
            error_log("[BaseDao] Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
}
?>