<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/OrderItemsDao.php';

class OrderItemsService extends BaseService {
    public function __construct() {
        parent::__construct(new OrderItemsDao());
    }

    public function getByOrder($order_id) {
        return $this->dao->getByOrder($order_id);
    }
}
?>