<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once __DIR__ . '/vendor/autoload.php';

// Load config first to have access to Environment and JWTConfig classes
require_once __DIR__ . '/backend/config.php';

//Services
require_once __DIR__ . '/backend/services/ProductsService.php'; 
require_once __DIR__ . '/backend/services/CartService.php';
require_once __DIR__ . '/backend/services/CartItemsService.php';
require_once __DIR__ . '/backend/services/AuthService.php';
require_once __DIR__ . '/backend/services/UserService.php';
require_once __DIR__ . '/backend/services/OrdersService.php';
require_once __DIR__ . '/backend/services/CategoriesService.php';


//Routes
require_once __DIR__ . '/backend/routes/ProductsRoutes.php';
require_once __DIR__ . '/backend/routes/CartRoutes.php';
require_once __DIR__ . '/backend/routes/AuthRoutes.php';
require_once __DIR__ . '/backend/routes/UserRoutes.php';
require_once __DIR__ . '/backend/routes/OrdersRoutes.php';
require_once __DIR__ . '/backend/routes/CategoriesRoutes.php';
require_once __DIR__ . '/backend/routes/ConfigRoutes.php';

//Middleware
require_once __DIR__ . '/backend/middleware/AuthMiddleware.php';

// CORS setup - environment aware
if (Environment::isLocal()) {
    header('Access-Control-Allow-Origin: *');
} else {
    // For production, set specific allowed origins
    $allowedOrigins = explode(',', $_ENV['ALLOWED_ORIGINS'] ?? getenv('ALLOWED_ORIGINS') ?: JWTConfig::FRONTEND_URL());
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $allowedOrigins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    }
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
header('Access-Control-Allow-Credentials: true');

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
Flight::register('ordersService', 'OrdersService');
Flight::register('categoriesService', 'CategoriesService');

// Register Auth Middleware
Flight::register('auth_middleware', 'JWTMiddleware');

// Add global middleware - must be before route definitions
Flight::before('start', function(&$params, &$output) {
    // Get the current request path
    $path = Flight::request()->url;
    
    // Skip middleware for excluded paths
    $excluded_paths = [
        '/auth/login',
        '/auth/register',
        '/docs',
        '/',
        '/test-service',
        '/products',
        '/config'
    ];
    
    // Check if the current path should be excluded
    foreach ($excluded_paths as $excluded) {
        if (strpos($path, $excluded) === 0) {
            return true;
        }
    }
    
    // Apply JWT verification for all other routes
    return Flight::auth_middleware()->handle();
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