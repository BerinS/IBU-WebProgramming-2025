<?php
require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao {
    public function __construct() {
        parent::__construct("users");
    }

    public function get_user_by_email($email) {
        return $this->query_unique("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    }

    public function update($id, $data) {
        $this->update_by_id($id, $data);
        return $this->get_by_id($id);
    }

    public function delete($id) {
        return $this->query("DELETE FROM users WHERE id = :id", ['id' => $id]);
    }
}
?>