<?php
require_once 'BaseDao.php';

class CartItemsDao extends BaseDao {
    public function __construct() {
        parent::__construct("cart_items");
    }

    public function getByCart($cart_id) {
        $sql = "SELECT ci.*, p.name, p.price, p.image_url 
                FROM cart_items ci 
                JOIN products p ON ci.product_id = p.id 
                WHERE ci.cart_id = :cart_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':cart_id' => $cart_id]);
        return $stmt->fetchAll();
    }

    public function clearCart($cart_id) {
        $stmt = $this->connection->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
        return $stmt->execute([':cart_id' => $cart_id]);
    }
}
?>