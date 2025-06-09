<?php
require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao {
    public function __construct() {
        parent::__construct("users");
    }

    public function get_user_by_email($email) {
        return $this->query_unique("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    }

    // Alias for compatibility with UserService
    public function getByEmail($email) {
        return $this->get_user_by_email($email);
    }

    // Create method for compatibility - uses BaseDao's insert
    public function create($data) {
        return $this->insert($data);
    }

    // Override update to return the updated record
    public function update($id, $data) {
        parent::update($id, $data);
        return $this->get_by_id($id);
    }

    public function delete($id) {
        return $this->query("DELETE FROM users WHERE id = :id", ['id' => $id]);
    }

    // Method to get all users with sensitive data removed (for admin view)
    public function get_all_users_safe() {
        $users = $this->get_all();
        // Remove password hashes for security
        return array_map(function($user) {
            unset($user['password_hash']);
            return $user;
        }, $users);
    }

    // Helper method for unique queries
    private function query_unique($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    private function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }

    private function update_by_id($id, $data) {
        $fields = "";
        foreach ($data as $key => $value) {
            $fields .= "$key = :$key, ";
        }
        $fields = rtrim($fields, ", ");
        $sql = "UPDATE " . $this->table . " SET $fields WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }
}
?>