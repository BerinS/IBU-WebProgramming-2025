<?php
require_once __DIR__ . '/../data/roles.php';

/**
 * @OA\Schema(
 *     schema="Order",
 *     required={"id", "user_id", "status", "total_amount"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="status", type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"}),
 *     @OA\Property(property="total_amount", type="number", format="float"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

Flight::group('/orders', function() {
    /**
     * @OA\Get(
     *     path="/orders",
     *     summary="Get all orders (Admin/Employee only)",
     *     description="Retrieve all orders - requires admin or employee privileges",
     *     tags={"orders"},
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all orders",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient privileges"
     *     )
     * )
     */
    Flight::route('GET /', function() {
        Flight::auth_middleware()->authorizeRoles([Roles::ADMIN, Roles::EMPLOYEE]);
        
        $orders = Flight::ordersService()->get_all();
        Flight::json(['data' => $orders]);
    });

    /**
     * @OA\Get(
     *     path="/orders/user/{user_id}",
     *     summary="Get user's orders",
     *     description="Retrieve orders for a specific user",
     *     tags={"orders"},
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of user's orders"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient privileges"
     *     )
     * )
     */
    Flight::route('GET /user/@user_id', function($user_id) {
        $current_user = Flight::get('user');
        
        // Users can only view their own orders unless they're admin/employee
        if ($current_user->role === Roles::CUSTOMER && $current_user->id != $user_id) {
            Flight::json([
                'success' => false,
                'message' => 'Access denied: You can only view your own orders'
            ], 403);
            return;
        }

        $orders = Flight::ordersService()->getByUserId($user_id);
        Flight::json(['data' => $orders]);
    });

    /**
     * @OA\Post(
     *     path="/orders",
     *     summary="Create a new order",
     *     description="Create a new order from cart items",
     *     tags={"orders"},
     *     security={{"BearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order created successfully"
     *     )
     * )
     */
    Flight::route('POST /', function() {
        $data = Flight::request()->data->getData();
        $current_user = Flight::get('user');

        // Users can only create orders for themselves unless they're admin
        if ($current_user->role === Roles::CUSTOMER && $current_user->id != $data['user_id']) {
            Flight::json([
                'success' => false,
                'message' => 'Access denied: You can only create orders for yourself'
            ], 403);
            return;
        }

        $response = Flight::ordersService()->createFromCart($data['user_id']);
        Flight::json($response);
    });

    /**
     * @OA\Put(
     *     path="/orders/{id}/status",
     *     summary="Update order status (Admin/Employee only)",
     *     description="Update the status of an order - requires admin or employee privileges",
     *     tags={"orders"},
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient privileges"
     *     )
     * )
     */
    Flight::route('PUT /@id/status', function($id) {
        Flight::auth_middleware()->authorizeRoles([Roles::ADMIN, Roles::EMPLOYEE]);
        
        $data = Flight::request()->data->getData();
        if (!isset($data['status'])) {
            Flight::json([
                'success' => false,
                'message' => 'Status is required'
            ], 400);
            return;
        }

        $response = Flight::ordersService()->updateStatus($id, $data['status']);
        Flight::json($response);
    });

    /**
     * @OA\Delete(
     *     path="/orders/{id}",
     *     summary="Delete order (Admin only)",
     *     description="Delete an order - requires admin privileges",
     *     tags={"orders"},
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient privileges"
     *     )
     * )
     */
    Flight::route('DELETE /@id', function($id) {
        Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
        
        $response = Flight::ordersService()->delete($id);
        Flight::json($response);
    });
}); 