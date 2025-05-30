<?php
require_once __DIR__ . '/../data/roles.php';

/**
 * @OA\Tag(
 *     name="categories",
 *     description="API endpoints for category management"
 * )
 */

Flight::group('/categories', function() {
    /**
     * @OA\Get(
     *     path="/categories",
     *     tags={"categories"},
     *     summary="Get all categories",
     *     @OA\Response(
     *         response=200,
     *         description="List of all categories",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    Flight::route('GET /', function() {
        error_log("[CategoriesRoutes] Attempting to get all categories");
        try {
            $categories = Flight::categoriesService()->get_all();
            error_log("[CategoriesRoutes] Successfully retrieved categories");
            Flight::json($categories);
        } catch (Exception $e) {
            error_log("[CategoriesRoutes] Error getting categories: " . $e->getMessage());
            Flight::json(['error' => 'Failed to retrieve categories'], 500);
        }
    });

    /**
     * @OA\Get(
     *     path="/categories/with-count",
     *     tags={"categories"},
     *     summary="Get all categories with product count",
     *     @OA\Response(
     *         response=200,
     *         description="List of all categories with product count",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="product_count", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    Flight::route('GET /with-count', function() {
        error_log("[CategoriesRoutes] Attempting to get categories with product count");
        try {
            $categories = Flight::categoriesService()->getWithProductCount();
            error_log("[CategoriesRoutes] Successfully retrieved categories with count");
            Flight::json($categories);
        } catch (Exception $e) {
            error_log("[CategoriesRoutes] Error getting categories with count: " . $e->getMessage());
            Flight::json(['error' => 'Failed to retrieve categories with count'], 500);
        }
    });
}); 