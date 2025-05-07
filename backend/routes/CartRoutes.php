<?php
require_once __DIR__ . '/../services/CartService.php';
require_once __DIR__ . '/../services/CartItemsService.php';

Flight::register('cartService', 'CartService');
Flight::register('cartItemsService', 'CartItemsService');

/**
 * @OA\Tag(
 *     name="Cart",
 *     description="API endpoints for shopping cart management"
 * )
 */

/**
 * @OA\Get(
 *     path="/cart/{user_id}",
 *     summary="Get a user's cart with items",
 *     description="Returns the cart for a specified user. If no cart exists, one will be created.",
 *     tags={"Cart"},
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Cart retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="user_id", type="integer", example=5),
 *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-01 12:34:56"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-01 12:34:56"),
 *             @OA\Property(
 *                 property="items",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=10),
 *                     @OA\Property(property="cart_id", type="integer", example=1),
 *                     @OA\Property(property="product_id", type="integer", example=42),
 *                     @OA\Property(property="quantity", type="integer", example=2),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-01 12:34:56"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-01 12:34:56"),
 *                     @OA\Property(property="product_name", type="string", example="Premium T-Shirt"),
 *                     @OA\Property(property="product_price", type="number", format="float", example=29.99)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Server error")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */

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


/**
 * @OA\Post(
 *     path="/cart/items",
 *     summary="Add item to cart",
 *     description="Adds a product to the user's cart. Will create a cart if one doesn't exist.",
 *     tags={"Cart"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "product_id", "quantity"},
 *             @OA\Property(property="user_id", type="integer", example=5),
 *             @OA\Property(property="product_id", type="integer", example=42),
 *             @OA\Property(property="quantity", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Item successfully added",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Item added to cart."),
 *             @OA\Property(property="success", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request - missing parameters",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Missing user_id, product_id, or quantity.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Failed to add item to cart.")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */

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


/**
 * @OA\Delete(
 *     path="/cart/items/{item_id}",
 *     summary="Remove item from cart",
 *     description="Deletes a specific item from the cart",
 *     tags={"Cart"},
 *     @OA\Parameter(
 *         name="item_id",
 *         in="path",
 *         required=true,
 *         description="Cart item ID to delete",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Item successfully removed",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Item removed from cart.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Item not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Failed to remove item or item not found.")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */

// Remove item from cart
Flight::route('DELETE /cart/items/@item_id', function($item_id) {
    $success = Flight::cartItemsService()->delete($item_id);

    if ($success) {
        Flight::json(["message" => "Item removed from cart."]);
    } else {
        Flight::json(["error" => "Failed to remove item or item not found."], 404);
    }
});


/**
 * @OA\Put(
 *     path="/cart/items/{item_id}",
 *     summary="Update cart item quantity",
 *     description="Updates the quantity of a specific item in the cart",
 *     tags={"Cart"},
 *     @OA\Parameter(
 *         name="item_id",
 *         in="path",
 *         required=true,
 *         description="Cart item ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"quantity"},
 *             @OA\Property(property="quantity", type="integer", example=3)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Quantity successfully updated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Item quantity updated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request - missing quantity or update failed",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Quantity is required.")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */

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
