<?php
require_once __DIR__ . '/config.php';
  
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
            $stmt = $this->connection->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in delete for table {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }
}
?>