<?php
require_once __DIR__ . '/BaseService.php'; 
require_once __DIR__ . '/../dao/ProductsDao.php';

class ProductsService extends BaseService {
    public function __construct() {
        // Verify ProductsDao can be instantiated
        $dao = new ProductsDao();
        parent::__construct($dao);
    }

    public function getByCategory($category_id) {
        return $this->dao->getByCategory($category_id);
    }

    public function searchByName($name) {
        return $this->dao->searchByName($name);
    }
}
?>