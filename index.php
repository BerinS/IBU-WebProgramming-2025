<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'vendor/autoload.php';

//Services
require_once __DIR__ . '/backend/services/ProductsService.php'; 
require_once __DIR__ . '/backend/services/CartService.php';
require_once __DIR__ . '/backend/services/CartItemsService.php';
require_once __DIR__ . '/backend/services/AuthService.php';
require_once __DIR__ . '/backend/services/UserService.php';


//Routes
require_once __DIR__ . '/backend/routes/ProductsRoutes.php';
require_once __DIR__ . '/backend/routes/CartRoutes.php';
require_once __DIR__ . '/backend/routes/AuthRoutes.php';
require_once __DIR__ . '/backend/routes/UserRoutes.php';

//Middleware
require_once __DIR__ . '/backend/middleware/AuthMiddleware.php';

// CORS setup
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    die();
}

//Route registration
Flight::register('productsService', 'ProductsService');
Flight::register('cartService', 'CartService');
Flight::register('cartItemsService', 'CartItemsService');
Flight::register('auth_service', 'AuthService');
Flight::register('user_service', 'UserService');

// Add JWT middleware - must be before route definitions
Flight::before('start', function(&$params, &$output) {
    $jwt_middleware = new JWTMiddleware();
    $jwt_middleware->handle();
});

// Redirect root to frontend
Flight::route('/', function() {
    Flight::redirect('/frontend/');
});

Flight::route('/test-service', function() {
    $service = Flight::productsService();
    echo "Service works!";
});

Flight::start();
?>