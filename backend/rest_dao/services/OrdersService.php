<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/OrdersDao.php';

class OrdersService extends BaseService {
    public function __construct() {
        parent::__construct(new OrdersDao());
    }

    public function getByUserId($user_id) {
        return $this->dao->getByUserId($user_id);
    }

    public function updateStatus($order_id, $status) {
        return $this->dao->updateStatus($order_id, $status);
    }
}
?>