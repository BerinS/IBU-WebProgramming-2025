<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * @OA\Post(
 *     path="/auth/register",
 *     tags={"Authentication"},
 *     summary="Register a new user",
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             type="object",
 *             required={"email", "password", "first_name", "last_name"},
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="password", type="string"),
 *             @OA\Property(property="first_name", type="string"),
 *             @OA\Property(property="last_name", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean"),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     )
 * )
 */
Flight::route('POST /auth/register', function() {
    try {
        error_log("Registration attempt started");
        $data = Flight::request()->data->getData();
        error_log("Received registration data: " . json_encode($data));
        
        if (!isset($data['email']) || !isset($data['password'])) {
            error_log("Missing required fields");
            Flight::json(['error' => 'Email and password are required'], 400);
            return;
        }

        error_log("Calling auth service register method");
        $auth_service = Flight::auth_service();
        if (!$auth_service) {
            error_log("Auth service not properly initialized");
            Flight::json(['error' => 'Internal server error - Auth service not available'], 500);
            return;
        }

        $response = $auth_service->register($data);
        error_log("Registration response: " . json_encode($response));

        if ($response['success']) {
            Flight::json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $response['data']
            ], 200);
        } else {
            $status = isset($response['status']) ? $response['status'] : 500;
            Flight::json([
                'success' => false,
                'message' => $response['error'],
                'error' => $response['error']
            ], $status);
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        Flight::json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
    }
});

/**
 * @OA\Post(
 *     path="/auth/login",
 *     tags={"Authentication"},
 *     summary="Login user",
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             type="object",
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="password", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     )
 * )
 */
Flight::route('POST /auth/login', function() {
    $data = Flight::request()->data->getData();
    error_log("Login route - received data: " . json_encode($data));

    if (!isset($data['email']) || !isset($data['password'])) {
        error_log("Login route - missing email or password");
        Flight::json(['error' => 'Email and password are required'], 400);
        return;
    }

    $response = Flight::auth_service()->login($data);
    error_log("Login route - auth service response: " . json_encode($response));

    if ($response['success']) {
        error_log("Login route - sending success response");
        Flight::json([
            'success' => true,
            'message' => 'User logged in successfully',
            'data' => $response['data']
        ], 200);
    } else {
        error_log("Login route - sending error response");
        $status = isset($response['status']) ? $response['status'] : 401;
        Flight::json([
            'success' => false,
            'message' => $response['error'],
            'error' => $response['error']
        ], $status);
    }
});
?>
