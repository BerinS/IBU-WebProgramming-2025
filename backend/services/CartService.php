<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/CartDao.php';

class CartService extends BaseService {
    public function __construct() {
        parent::__construct(new CartDao());
    }

    //Create a cart in case there is none
    public function createCartForUser($user_id) {
        $new_cart = [
            'user_id' => $user_id,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->dao->insert($new_cart);
        return $this->dao->getByUser($user_id);
    }
    

    public function getByUser($user_id) {
        return $this->dao->getByUser($user_id);
    }
}
?>