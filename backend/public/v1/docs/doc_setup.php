<?php
/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="IBU E-commerce API",
 *     description="API Documentation for Web Programming Course Project",
 *     @OA\Contact(
 *         email="web2001programming@gmail.com",
 *         name="Course Team"
 *     )
 * )
 */

/**
 * @OA\Server(
 *     url=BASE_URL,
 *     description="API Server"
 * )
 */

/**
 * @OA\Components(
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT"
 *     )
 * )
 */

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for user authentication"
 * )
 */
?>