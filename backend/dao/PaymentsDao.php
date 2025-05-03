<?php
require_once 'BaseDao.php';

class PaymentsDao extends BaseDao {
    public function __construct() {
        parent::__construct("payments");
    }

    public function getByOrder($order_id) {
        $stmt = $this->connection->prepare("SELECT * FROM payments WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetch();
    }
}
?>