<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
Flight::group('/auth', function() {
   /**
    * @OA\Post(
    *     path="/auth/register",
    *     summary="Register new user.",
    *     description="Add a new user to the database.",
    *     tags={"auth"},
    *     @OA\RequestBody(
    *         description="User registration details",
    *         required=true,
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 required={"password", "email"},
    *                 @OA\Property(
    *                     property="password",
    *                     type="string",
    *                     format="password",
    *                     example="some_password",
    *                     description="User password"
    *                 ),
    *                 @OA\Property(
    *                     property="email",
    *                     type="string",
    *                     format="email",
    *                     example="demo@gmail.com",
    *                     description="User email"
    *                 )
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="User successfully registered",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string", example="User registered successfully"),
    *             @OA\Property(
    *                 property="data",
    *                 type="object",
    *                 @OA\Property(property="id", type="integer", example=1),
    *                 @OA\Property(property="email", type="string", example="demo@gmail.com")
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Bad request - Invalid input data"
    *     ),
    *     @OA\Response(
    *         response=409,
    *         description="Conflict - Email already exists"
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Internal server error"
    *     )
    * )
    */
   Flight::route("POST /register", function () {
       $data = Flight::request()->data->getData();

       if (!isset($data['email']) || !isset($data['password'])) {
           Flight::json(['error' => 'Email and password are required'], 400);
           return;
       }

       $response = Flight::auth_service()->register($data);
  
       if ($response['success']) {
           Flight::json([
               'message' => 'User registered successfully',
               'data' => $response['data']
           ], 200);
       } else {
           $status = isset($response['status']) ? $response['status'] : 500;
           Flight::json(['error' => $response['error']], $status);
       }
   });
   /**
    * @OA\Post(
    *     path="/auth/login",
    *     tags={"auth"},
    *     summary="Login to system using email and password",
    *     description="Authenticate user and return JWT token",
    *     @OA\RequestBody(
    *         description="Login Credentials",
    *         required=true,
    *         @OA\JsonContent(
    *             required={"email","password"},
    *             @OA\Property(
    *                 property="email", 
    *                 type="string", 
    *                 format="email",
    *                 example="demo@gmail.com", 
    *                 description="User email address"
    *             ),
    *             @OA\Property(
    *                 property="password", 
    *                 type="string", 
    *                 format="password",
    *                 example="some_password", 
    *                 description="User password"
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Login successful",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string", example="User logged in successfully"),
    *             @OA\Property(
    *                 property="data",
    *                 type="object",
    *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
    *                 @OA\Property(property="user", type="object",
    *                     @OA\Property(property="id", type="integer", example=1),
    *                     @OA\Property(property="email", type="string", example="demo@gmail.com")
    *                 )
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Bad request - Invalid credentials"
    *     ),
    *     @OA\Response(
    *         response=401,
    *         description="Unauthorized - Invalid email or password"
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Internal server error"
    *     )
    * )
    */
   Flight::route('POST /login', function() {
       $data = Flight::request()->data->getData();

       if (!isset($data['email']) || !isset($data['password'])) {
           Flight::json(['error' => 'Email and password are required'], 400);
           return;
       }

       $response = Flight::auth_service()->login($data);
  
       if ($response['success']) {
           Flight::json([
               'message' => 'User logged in successfully',
               'data' => $response['data']
           ], 200);
       } else {
           $status = isset($response['status']) ? $response['status'] : 401;
           Flight::json(['error' => $response['error']], $status);
       }
   });
});
?>
