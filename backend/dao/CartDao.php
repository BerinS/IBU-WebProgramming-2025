<?php
require_once 'BaseDao.php';

class CartDao extends BaseDao {
    public function __construct() {
        parent::__construct("cart");
    }

    public function getByUser($user_id) {
        $stmt = $this->connection->prepare("SELECT * FROM cart WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch();
    }
}
?>