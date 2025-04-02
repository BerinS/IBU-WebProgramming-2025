<?php
  require_once './ProductsDAO.php';
  $productDao = new ProductsDao();

  // Fetch all reviews
    $products = $productDao->getAll();
    print_r($products);
?>
