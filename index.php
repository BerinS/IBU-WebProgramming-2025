<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'vendor/autoload.php';
require_once __DIR__ . '/backend/services/ProductsService.php'; // <-- ADD THIS
require_once __DIR__ . '/backend/routes/ProductsRoutes.php';

Flight::register('productsService', 'ProductsService');


Flight::route('/', function() {
    echo 'Welcome to our Watch Store API!';
});

Flight::route('/test-service', function() {
    $service = Flight::productsService();
    echo "Service works!";
});

Flight::start();
?>




