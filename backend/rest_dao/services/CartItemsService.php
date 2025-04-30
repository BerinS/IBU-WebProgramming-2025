<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/CartItemsDao.php';

class CartItemsService extends BaseService {
    public function __construct() {
        parent::__construct(new CartItemsDao());
    }

    public function getByCart($cart_id) {
        return $this->dao->getByCart($cart_id);
    }
}
?>