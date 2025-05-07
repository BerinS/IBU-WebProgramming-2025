<?php

/**
 * @OA\Get(
 *      path="/products",
 *      tags={"products"},
 *      summary="Get products with optional filters",
 *      @OA\Parameter(
 *          name="category_id",
 *          in="query",
 *          @OA\Schema(type="integer"),
 *          description="Filter by category ID"
 *      ),
 *      @OA\Parameter(
 *          name="name",
 *          in="query",
 *          @OA\Schema(type="string"),
 *          description="Search by product name"
 *      ),
 *      @OA\Parameter(
 *          name="brand",
 *          in="query",
 *          @OA\Schema(type="string"),
 *          description="Filter by brand"
 *      ),
 *      @OA\Parameter(
 *          name="min_price",
 *          in="query",
 *          @OA\Schema(type="number", format="float"),
 *          description="Minimum price for price range filter"
 *      ),
 *      @OA\Parameter(
 *          name="max_price",
 *          in="query",
 *          @OA\Schema(type="number", format="float"),
 *          description="Maximum price for price range filter"
 *      ),
 *      @OA\Parameter(
 *          name="min_stock",
 *          in="query",
 *          @OA\Schema(type="integer"),
 *          description="Minimum stock quantity filter"
 *      ),
 *      @OA\Parameter(
 *          name="gender",
 *          in="query",
 *          @OA\Schema(type="string"),
 *          description="Filter by gender category"
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Array of products matching filters"
 *      )
 * )
 */

Flight::route('GET /products', function() {
    $query = Flight::request()->query;

    if (isset($query['category_id'])) {
        $products = Flight::productsService()->getByCategory($query['category_id']);

    } elseif (isset($query['name'])) {
        $products = Flight::productsService()->searchByName($query['name']);

    } elseif (isset($query['brand'])) {
        $products = Flight::productsService()->getByBrand($query['brand']);

    } elseif (isset($query['min_price']) && isset($query['max_price'])) {
        $products = Flight::productsService()->getByPriceRange($query['min_price'], $query['max_price']);

    } elseif (isset($query['min_stock'])) {
        $products = Flight::productsService()->getByStockQuantity($query['min_stock']);

    } elseif (isset($query['gender'])) {
        $products = Flight::productsService()->getByGender($query['gender']);

    } else {
        $products = Flight::productsService()->get_all();
    }

    Flight::json($products);
});


/**
 * @OA\Post(
 *     path="/products",
 *     tags={"products"},
 *     summary="Create a new product",
 *     @OA\RequestBody(
 *         description="Product data",
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "price", "category_id"},
 *             @OA\Property(property="name", type="string", example="Smart Watch Pro"),
 *             @OA\Property(property="description", type="string", example="Premium smart watch with health tracking"),
 *             @OA\Property(property="price", type="number", format="float", example=299.99),
 *             @OA\Property(property="category_id", type="integer", example=1),
 *             @OA\Property(property="brand", type="string", example="TechBrand"),
 *             @OA\Property(property="stock_quantity", type="integer", example=100),
 *             @OA\Property(property="gender", type="string", example="unisex"),
 *             @OA\Property(property="image_url", type="string", example="watch.jpg")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Product added successfully"),
 *             @OA\Property(property="product", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Missing required fields")
 *         )
 *     )
 * )
 */

 
// add a new product
Flight::route('POST /products', function() {
    $data = Flight::request()->data->getData();

    try {
        $product = Flight::productsService()->add($data);
        Flight::json(['message' => 'Product added successfully', 'product' => $product]);
    } catch (Exception $e) {
        Flight::json(['error' => $e->getMessage()], 400);
    }
});
