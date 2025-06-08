<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/OrdersDao.php';
require_once __DIR__ . '/../dao/OrderItemsDao.php';
require_once __DIR__ . '/../dao/CartDao.php';
require_once __DIR__ . '/../dao/CartItemsDao.php';

class OrdersService extends BaseService {
    private $orderItemsDao;
    private $cartDao;
    private $cartItemsDao;

    public function __construct() {
        parent::__construct(new OrdersDao());
        $this->orderItemsDao = new OrderItemsDao();
        $this->cartDao = new CartDao();
        $this->cartItemsDao = new CartItemsDao();
    }

    public function getByUserId($user_id) {
        return $this->dao->getByUserId($user_id);
    }

    public function getWithItems($order_id) {
        return $this->dao->getWithItems($order_id);
    }

    public function updateStatus($order_id, $status) {
        // Validate status
        $valid_statuses = ['pending', 'shipped'];
        if (!in_array($status, $valid_statuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $result = $this->dao->updateStatus($order_id, $status);
        return [
            'success' => $result,
            'message' => $result ? 'Order status updated successfully' : 'Failed to update order status'
        ];
    }

    public function createFromCart($user_id) {
        try {
            // Get user's cart
            $cart = $this->cartDao->getByUser($user_id);
            if (!$cart) {
                return ['success' => false, 'message' => 'No cart found for user'];
            }

            // Get cart items with product details
            $cart_items = $this->cartItemsDao->getByCart($cart['id']);
            if (empty($cart_items)) {
                return ['success' => false, 'message' => 'Cart is empty'];
            }

            // Calculate total price
            $total_price = 0;
            foreach ($cart_items as $item) {
                $total_price += ($item['price'] * $item['quantity']);
            }

            // Create order (without transaction for now)
            $order_id = $this->dao->createOrder($user_id, $total_price);
            if (!$order_id) {
                throw new Exception('Failed to create order');
            }

            // Create order items
            $order_items = [];
            foreach ($cart_items as $item) {
                $order_items[] = [
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $item['price']
                ];
            }

            $items_created = $this->orderItemsDao->createMultipleOrderItems($order_items);
            if (!$items_created) {
                throw new Exception('Failed to create order items');
            }

            // Clear cart items
            $this->cartItemsDao->clearCart($cart['id']);

            // Return order details
            $order = $this->dao->getWithItems($order_id);
            
            return [
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $order
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ];
        }
    }
}
?>