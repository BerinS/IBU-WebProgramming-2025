<?php
require_once __DIR__ . '/BaseDao.php';

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

    public function getByBrand($brand) {
        $stmt = $this->connection->prepare("SELECT * FROM products WHERE brand = :brand");
        $stmt->execute([':brand' => $brand]);
        return $stmt->fetchAll();
    }
    
    public function getByPriceRange($min_price, $max_price) {
        $stmt = $this->connection->prepare("SELECT * FROM products WHERE price BETWEEN :min_price AND :max_price");
        $stmt->execute([':min_price' => $min_price, ':max_price' => $max_price]);
        return $stmt->fetchAll();
    }
    
    public function getByStockQuantity($min_stock) {
        $stmt = $this->connection->prepare("SELECT * FROM products WHERE stock_quantity >= :min_stock");
        $stmt->execute([':min_stock' => $min_stock]);
        return $stmt->fetchAll();
    }

    public function getByGender($gender) {
        $stmt = $this->connection->prepare("SELECT * FROM products WHERE gender = :gender");
        $stmt->execute([':gender' => $gender]);
        return $stmt->fetchAll();
    }
    
}
?>