<?php
require_once __DIR__ . '/../services/CartService.php';
require_once __DIR__ . '/../services/CartItemsService.php';

Flight::register('cartService', 'CartService');
Flight::register('cartItemsService', 'CartItemsService');



// Get a cart by user ID
Flight::route('GET /cart/@user_id', function($user_id) {
  $cart = Flight::cartService()->getByUser($user_id);

  // If no cart exists, create one
  if (!$cart) {
      $cart = Flight::cartService()->createCartForUser($user_id);
  }

  $items = Flight::cartItemsService()->getByCart($cart['id']);
  $cart['items'] = $items;

  Flight::json($cart);
});



// Add item to cart
Flight::route('POST /cart/items', function() {
  $data = Flight::request()->data->getData();

  if (!isset($data['user_id'], $data['product_id'], $data['quantity'])) {
      Flight::halt(400, "Missing user_id, product_id, or quantity.");
  }

  $result = Flight::cartItemsService()->addItem(
      $data['user_id'],
      $data['product_id'],
      $data['quantity']
  );

  Flight::json(['message' => 'Item added to cart.', 'success' => $result]);
});


// Remove item from cart
Flight::route('DELETE /cart/items/@item_id', function($item_id) {
    $success = Flight::cartItemsService()->delete($item_id);

    if ($success) {
        Flight::json(["message" => "Item removed from cart."]);
    } else {
        Flight::json(["error" => "Failed to remove item or item not found."], 404);
    }
});

// Update quantity of a cart item
Flight::route('PUT /cart/items/@item_id', function($item_id) {
    $data = Flight::request()->data->getData();

    if (!isset($data['quantity'])) {
        Flight::json(["error" => "Quantity is required."], 400);
        return;
    }

    $updated = Flight::cartItemsService()->update($item_id, ['quantity' => $data['quantity']]);

    if ($updated) {
        Flight::json(["message" => "Item quantity updated."]);
    } else {
        Flight::json(["error" => "Failed to update quantity."], 400);
    }
});
