<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/ProductsDao.php';

class ProductsService extends BaseService {
    public function __construct() {
        parent::__construct(new ProductsDao());
    }

    public function getByCategory($category_id) {
        return $this->dao->getByCategory($category_id);
    }

    public function searchByName($name) {
        return $this->dao->searchByName($name);
    }
}
?>