<?php
require_once __DIR__ . '/../data/roles.php';

Flight::group('/user', function() {
    /**
     * @OA\Get(
     *     path="/user/profile",
     *     summary="Get user profile information",
      *     description="Retrieve the profile information of the currently logged-in user",
 *     tags={"user"},
 *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token"
     *     )
     * )
     */
    Flight::route('GET /profile', function() {
        $user = Flight::get('user');
        Flight::json([
            'success' => true,
            'data' => $user
        ]);
    });

    /**
     * @OA\Get(
     *     path="/user/all",
     *     summary="Get all users (Admin only)",
      *     description="Retrieve all users information - requires admin privileges",
 *     tags={"user"},
 *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="role", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient privileges"
     *     )
     * )
     */
    Flight::route('GET /all', function() {
        Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
        
        $users = Flight::user_service()->getAllUsersSafe();
        Flight::json([
            'success' => true,
            'data' => $users
        ]);
    });

    /**
     * @OA\Put(
     *     path="/user/profile",
     *     summary="Update user profile",
      *     description="Update the profile information of the currently logged-in user",
 *     tags={"user"},
 *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="newemail@example.com"),
     *             @OA\Property(property="current_password", type="string", example="current123"),
     *             @OA\Property(property="new_password", type="string", example="new123", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully"
     *     )
     * )
     */
    Flight::route('PUT /profile', function() {
        $current_user = Flight::get('user');
        $data = Flight::request()->data->getData();
        
        // Only allow users to update their own profile unless they're admin
        if (isset($data['user_id']) && $data['user_id'] != $current_user->id) {
            Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
        }
        
        $response = Flight::user_service()->updateProfile($current_user->id, $data);
        Flight::json($response);
    });

    /**
     * @OA\Delete(
     *     path="/user/profile",
     *     summary="Delete user account",
      *     description="Delete the current user's account",
 *     tags={"user"},
 *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account deleted successfully"
     *     )
     * )
     */
    Flight::route('DELETE /profile', function() {
        $current_user = Flight::get('user');
        $data = Flight::request()->data->getData();
        
        // Only allow users to delete their own account unless they're admin
        if (isset($data['user_id']) && $data['user_id'] != $current_user->id) {
            Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
        }
        
        $response = Flight::user_service()->deleteAccount($current_user->id, $data['password']);
        Flight::json($response);
    });

    /**
     * @OA\Put(
     *     path="/user/{id}/role",
     *     summary="Update user role (Admin only)",
      *     description="Update a user's role - requires admin privileges",
 *     tags={"user"},
 *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="role", type="string", enum={"admin", "employee", "customer"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient privileges"
     *     )
     * )
     */
    Flight::route('PUT /@id/role', function($id) {
        Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
        
        $data = Flight::request()->data->getData();
        if (!isset($data['role']) || !in_array($data['role'], [Roles::ADMIN, Roles::EMPLOYEE, Roles::USER])) {
            Flight::json([
                'success' => false,
                'message' => 'Invalid role specified'
            ], 400);
            return;
        }
        
        $response = Flight::user_service()->updateRole($id, $data['role']);
        Flight::json($response);
    });
});
?> 