<?php

// Get all watches (products)
Flight::route('GET /products', function() {
  // Get optional query parameters
  $category_id = Flight::request()->query['category_id'] ?? null;
  $name = Flight::request()->query['name'] ?? null;
  
  if ($category_id) {
      // Get products by category
      $products = Flight::productsService()->getByCategory($category_id);
  } elseif ($name) {
      // Search products by name
      $products = Flight::productsService()->searchByName($name);
  } else {
      // Get all products
      $products = Flight::productsService()->get_all();
  }
  
  Flight::json($products);
});

?>
