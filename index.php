<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'vendor/autoload.php';

//Services
require_once __DIR__ . '/backend/services/ProductsService.php'; 
require_once __DIR__ . '/backend/services/CartService.php';
require_once __DIR__ . '/backend/services/CartItemsService.php';

//Routes
require_once __DIR__ . '/backend/routes/ProductsRoutes.php';
require_once __DIR__ . '/backend/routes/CartRoutes.php';

//Route registration
Flight::register('productsService', 'ProductsService');
Flight::register('cartService', 'CartService');
Flight::register('cartItemsService', 'CartItemsService');

Flight::route('/', function() {
    echo 'Welcome to our Watch Store API!';
});

Flight::route('/test-service', function() {
    $service = Flight::productsService();
    echo "Service works!";
});

Flight::start();
?>




