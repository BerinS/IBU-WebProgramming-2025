<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/CartDao.php';

class CartService extends BaseService {
    public function __construct() {
        parent::__construct(new CartDao());
    }

    public function getByUser($user_id) {
        return $this->dao->getByUser($user_id);
    }
}
?>