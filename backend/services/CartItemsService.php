<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/CartDao.php';
require_once __DIR__ . '/../dao/CartItemsDao.php';
require_once __DIR__ . '/../services/CartService.php';

class CartItemsService extends BaseService {
    private $cartService;

    public function __construct() {
        parent::__construct(new CartItemsDao());
        $this->cartService = new CartService();
    }

    // Add this method to fetch items by cart ID
    public function getByCart($cart_id) {
        return $this->dao->getByCart($cart_id);
    }

    public function addItem($user_id, $product_id, $quantity) {        
        // Existing code remains the same
        $cart = $this->cartService->getByUser($user_id);
        if (!$cart) {
            $this->cartService->createCartForUser($user_id);
            $cart = $this->cartService->getByUser($user_id);
        }

        return $this->dao->insert([
            'cart_id' => $cart['id'],
            'product_id' => $product_id,
            'quantity' => $quantity
        ]);
    }
}
?>