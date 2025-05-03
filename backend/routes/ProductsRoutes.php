<?php

Flight::route('GET /products', function() {
    // Get all query parameters from the URL
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
