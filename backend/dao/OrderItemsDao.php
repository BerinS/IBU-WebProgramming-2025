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

    public function createOrderItem($order_id, $product_id, $quantity, $price_at_purchase) {
        $stmt = $this->connection->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) 
            VALUES (:order_id, :product_id, :quantity, :price_at_purchase)
        ");
        return $stmt->execute([
            ':order_id' => $order_id,
            ':product_id' => $product_id,
            ':quantity' => $quantity,
            ':price_at_purchase' => $price_at_purchase
        ]);
    }

    public function createMultipleOrderItems($order_items) {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES ";
        $placeholders = [];
        $values = [];
        
        foreach ($order_items as $index => $item) {
            $placeholders[] = "(:order_id_{$index}, :product_id_{$index}, :quantity_{$index}, :price_at_purchase_{$index})";
            $values[":order_id_{$index}"] = $item['order_id'];
            $values[":product_id_{$index}"] = $item['product_id'];
            $values[":quantity_{$index}"] = $item['quantity'];
            $values[":price_at_purchase_{$index}"] = $item['price_at_purchase'];
        }
        
        $sql .= implode(', ', $placeholders);
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($values);
    }
}
?>