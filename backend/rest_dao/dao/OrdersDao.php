<?php
require_once 'BaseDao.php';

class OrdersDao extends BaseDao {
    public function __construct() {
        parent::__construct("orders");
    }

    // Order-specific methods
    public function getByUserId($user_id) {
        $stmt = $this->connection->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll();
    }

    public function updateStatus($order_id, $status) {
        $stmt = $this->connection->prepare("UPDATE orders SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $order_id]);
    }
}
?>