<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/CategoriesDao.php';

class CategoriesService extends BaseService {
    public function __construct() {
        parent::__construct(new CategoriesDao());
    }

    public function get_all() {
        try {
            error_log("[CategoriesService] Attempting to get all categories");
            $result = parent::get_all();
            error_log("[CategoriesService] Successfully retrieved " . count($result) . " categories");
            return $result;
        } catch (Exception $e) {
            error_log("[CategoriesService] Error getting categories: " . $e->getMessage());
            throw $e;
        }
    }

    public function getWithProductCount() {
        try {
            error_log("[CategoriesService] Attempting to get categories with product count");
            $result = $this->dao->getWithProductCount();
            error_log("[CategoriesService] Successfully retrieved " . count($result) . " categories with product count");
            return $result;
        } catch (Exception $e) {
            error_log("[CategoriesService] Error getting categories with product count: " . $e->getMessage());
            throw $e;
        }
    }
}
?>