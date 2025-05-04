<?php

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
