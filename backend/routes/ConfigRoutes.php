<?php

/**
 * Configuration Routes
 * Provides environment-specific configuration to frontend
 */

/**
 * @OA\Get(
 *      path="/config",
 *      tags={"config"},
 *      summary="Get client configuration",
 *      @OA\Response(
 *          response=200,
 *          description="Client configuration",
 *          @OA\JsonContent(
 *              @OA\Property(property="baseUrl", type="string", example="http://localhost/IBU-WebProgramming-2025/backend"),
 *              @OA\Property(property="frontendUrl", type="string", example="http://localhost/IBU-WebProgramming-2025"),
 *              @OA\Property(property="environment", type="string", example="local")
 *          )
 *      )
 * )
 */
Flight::route('GET /config', function() {
    Flight::json([
        'baseUrl' => JWTConfig::BACKEND_URL(),
        'frontendUrl' => JWTConfig::FRONTEND_URL(),
        'environment' => Environment::detect()
    ]);
});

?> 