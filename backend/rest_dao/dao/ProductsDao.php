<?php
require_once './BaseDao.php';

class ProductsDao extends BaseDao {
    public function __construct() {
        parent::__construct("products");
    }

    // Product-specific methods
    public function getByCategory($category_id) {
        $stmt = $this->connection->prepare("SELECT * FROM products WHERE category_id = :category_id");
        $stmt->execute([':category_id' => $category_id]);
        return $stmt->fetchAll();
    }

    public function searchByName($name) {
        $stmt = $this->connection->prepare("SELECT * FROM products WHERE name LIKE :name");
        $stmt->execute([':name' => "%$name%"]);
        return $stmt->fetchAll();
    }
}
?>