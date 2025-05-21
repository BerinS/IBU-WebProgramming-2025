<?php

Flight::group('/user', function() {
    /**
     * @OA\Get(
     *     path="/user/profile",
     *     summary="Get user profile information",
     *     description="Retrieve the profile information of the currently logged-in user",
     *     tags={"user"},
     *     security={{"BearerAuth": {}}},
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
     * @OA\Put(
     *     path="/user/profile",
     *     summary="Update user profile",
     *     description="Update the profile information of the currently logged-in user",
     *     tags={"user"},
     *     security={{"BearerAuth": {}}},
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
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token"
     *     )
     * )
     */
    Flight::route('PUT /profile', function() {
        $user = Flight::get('user');
        $data = Flight::request()->data->getData();
        
        $response = Flight::user_service()->updateProfile($user['id'], $data);
        Flight::json($response);
    });

    /**
     * @OA\Delete(
     *     path="/user/profile",
     *     summary="Delete user account",
     *     description="Delete the current user's account",
     *     tags={"user"},
     *     security={{"BearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid password or token"
     *     )
     * )
     */
    Flight::route('DELETE /profile', function() {
        $user = Flight::get('user');
        $data = Flight::request()->data->getData();
        
        $response = Flight::user_service()->deleteAccount($user['id'], $data['password']);
        Flight::json($response);
    });
});
?> 