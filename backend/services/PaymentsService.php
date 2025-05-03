<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/PaymentsDao.php';

class PaymentsService extends BaseService {
    public function __construct() {
        parent::__construct(new PaymentsDao());
    }

    public function getByOrder($order_id) {
        return $this->dao->getByOrder($order_id);
    }
}
?>