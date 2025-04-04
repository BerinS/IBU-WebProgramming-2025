<?php
require_once 'BaseDao.php';

class CategoriesDao extends BaseDao {
    public function __construct() {
        parent::__construct("categories");
    }

    // Category-specific methods
    public function getWithProductCount() {
        $sql = "SELECT c.*, COUNT(p.id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id 
                GROUP BY c.id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>