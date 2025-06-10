<?php
require_once __DIR__ . '/../data/roles.php';

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
    Flight::auth_middleware()->authenticate();
    // Verify user can only access their own cart unless admin
    $current_user = Flight::get('user');
    
    if ($current_user->role !== Roles::ADMIN && $current_user->id != $user_id) {
        Flight::json([
            'success' => false,
            'message' => 'Access denied: You can only view your own cart'
        ], 403);
        return;
    }

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
    Flight::auth_middleware()->authenticate();
    $data = Flight::request()->data->getData();
    $current_user = Flight::get('user');

    if (!isset($data['user_id'], $data['product_id'], $data['quantity'])) {
        Flight::halt(400, "Missing user_id, product_id, or quantity.");
    }

    // Verify user can only modify their own cart unless admin
    if ($current_user->role !== Roles::ADMIN && $current_user->id != $data['user_id']) {
        Flight::json([
            'success' => false,
            'message' => 'Access denied: You can only modify your own cart'
        ], 403);
        return;
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
    Flight::auth_middleware()->authenticate();
    $current_user = Flight::get('user');
    $cart_item = Flight::cartItemsService()->get_by_id($item_id);

    // Verify the item belongs to the user's cart
    if (!$cart_item) {
        Flight::json(["error" => "Item not found"], 404);
        return;
    }

    $cart = Flight::cartService()->get_by_id($cart_item['cart_id']);
    if ($current_user->role !== Roles::ADMIN && $current_user->id != $cart['user_id']) {
        Flight::json([
            'success' => false,
            'message' => 'Access denied: You can only remove items from your own cart'
        ], 403);
        return;
    }

    $success = Flight::cartItemsService()->delete($item_id);

    if ($success) {
        Flight::json(["message" => "Item removed from cart."]);
    } else {
        Flight::json(["error" => "Failed to remove item."], 400);
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
    Flight::auth_middleware()->authenticate();
    $data = Flight::request()->data->getData();
    $current_user = Flight::get('user');
    $cart_item = Flight::cartItemsService()->get_by_id($item_id);

    if (!$cart_item) {
        Flight::json(["error" => "Item not found"], 404);
        return;
    }

    // Verify the item belongs to the user's cart
    $cart = Flight::cartService()->get_by_id($cart_item['cart_id']);
    if ($current_user->role !== Roles::ADMIN && $current_user->id != $cart['user_id']) {
        Flight::json([
            'success' => false,
            'message' => 'Access denied: You can only update items in your own cart'
        ], 403);
        return;
    }

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

/**
 * @OA\Delete(
 *     path="/cart/{user_id}",
 *     summary="Delete/clear entire cart",
 *     description="Deletes all items from the cart and the cart itself",
 *     tags={"Cart"},
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         description="User ID whose cart to delete",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Cart successfully deleted",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Cart deleted successfully.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Cart not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Cart not found.")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
Flight::route('DELETE /cart/@user_id', function($user_id) {
    Flight::auth_middleware()->authenticate();
    $current_user = Flight::get('user');
    
    // Verify user can only delete their own cart unless admin
    if ($current_user->role !== Roles::ADMIN && $current_user->id != $user_id) {
        Flight::json([
            'success' => false,
            'message' => 'Access denied: You can only delete your own cart'
        ], 403);
        return;
    }

    $cart = Flight::cartService()->getByUser($user_id);
    
    if (!$cart) {
        Flight::json(["error" => "Cart not found."], 404);
        return;
    }

    // Delete all items in the cart first
    Flight::cartItemsService()->deleteAllByCart($cart['id']);
    
    // Then delete the cart itself
    $success = Flight::cartService()->delete($cart['id']);

    if ($success) {
        Flight::json(["message" => "Cart deleted successfully."]);
    } else {
        Flight::json(["error" => "Failed to delete cart."], 500);
    }
});
