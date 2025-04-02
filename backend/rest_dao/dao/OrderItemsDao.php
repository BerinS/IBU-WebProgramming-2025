<?php
require_once 'BaseDao.php';

class OrderItemsDao extends BaseDao {
    public function __construct() {
        parent::__construct("order_items");
    }

    // Order item-specific methods
    public function getByOrder($order_id) {
        $sql = "SELECT oi.*, p.name, p.price 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetchAll();
    }
}
?>