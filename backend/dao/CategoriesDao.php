<?php
require_once 'BaseDao.php';

class CategoriesDao extends BaseDao {
    public function __construct() {
        parent::__construct("categories");
    }

    public function get_all() {
        try {
            error_log("[CategoriesDao] Attempting to get all categories");
            $result = parent::get_all();
            error_log("[CategoriesDao] Found " . count($result) . " categories");
            return $result;
        } catch (Exception $e) {
            error_log("[CategoriesDao] Error getting categories: " . $e->getMessage());
            throw $e;
        }
    }

    // Category-specific methods
    public function getWithProductCount() {
        try {
            error_log("[CategoriesDao] Attempting to get categories with product count");
            $sql = "SELECT c.*, COUNT(p.id) as product_count 
                    FROM categories c 
                    LEFT JOIN products p ON c.id = p.category_id 
                    GROUP BY c.id";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            error_log("[CategoriesDao] Found " . count($result) . " categories with product count");
            return $result;
        } catch (Exception $e) {
            error_log("[CategoriesDao] Error getting categories with product count: " . $e->getMessage());
            throw $e;
        }
    }
}
?>