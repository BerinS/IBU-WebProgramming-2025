<?php
require_once 'BaseDao.php';

class OrdersDao extends BaseDao {
    public function __construct() {
        parent::__construct("orders");
    }

    // Order-specific methods
    public function getByUserId($user_id) {
        $stmt = $this->connection->prepare("
            SELECT o.*, 
                   COUNT(oi.id) as item_count,
                   GROUP_CONCAT(CONCAT(p.name, ' x', oi.quantity) SEPARATOR ', ') as items_summary
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE o.user_id = :user_id 
            GROUP BY o.id 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll();
    }

    public function getWithItems($order_id) {
        $stmt = $this->connection->prepare("
            SELECT o.*, u.first_name, u.last_name, u.email
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = :order_id
        ");
        $stmt->execute([':order_id' => $order_id]);
        $order = $stmt->fetch();
        
        if ($order) {
            // Get order items
            $stmt = $this->connection->prepare("
                SELECT oi.*, p.name as product_name, p.brand, p.image_url
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id
            ");
            $stmt->execute([':order_id' => $order_id]);
            $order['items'] = $stmt->fetchAll();
        }
        
        return $order;
    }

    public function updateStatus($order_id, $status) {
        $stmt = $this->connection->prepare("
            UPDATE orders 
            SET status = :status, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        return $stmt->execute([':status' => $status, ':id' => $order_id]);
    }

    public function createOrder($user_id, $total_price) {
        $stmt = $this->connection->prepare("
            INSERT INTO orders (user_id, status, total_price, created_at) 
            VALUES (:user_id, 'pending', :total_price, CURRENT_TIMESTAMP)
        ");
        $result = $stmt->execute([
            ':user_id' => $user_id,
            ':total_price' => $total_price
        ]);
        
        return $result ? $this->connection->lastInsertId() : false;
    }
}
?>