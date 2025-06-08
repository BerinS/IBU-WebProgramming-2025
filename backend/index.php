<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

// Load config first to have access to Environment and JWTConfig classes
require_once __DIR__ . '/config.php';

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
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Load all required files (config.php already loaded above)
$required_files = [
    __DIR__ . '/dao/BaseDao.php',
    __DIR__ . '/dao/AuthDao.php',
    __DIR__ . '/dao/UserDao.php',
    __DIR__ . '/dao/CartDao.php',
    __DIR__ . '/dao/CartItemsDao.php',
    __DIR__ . '/dao/OrdersDao.php',
    __DIR__ . '/dao/OrderItemsDao.php',
    __DIR__ . '/services/BaseService.php',
    __DIR__ . '/services/AuthService.php',
    __DIR__ . '/services/UserService.php',
    __DIR__ . '/services/ProductsService.php',
    __DIR__ . '/services/CartService.php',
    __DIR__ . '/services/CartItemsService.php',
    __DIR__ . '/services/OrdersService.php',
    __DIR__ . '/services/CategoriesService.php',
    __DIR__ . '/data/roles.php',
    __DIR__ . '/middleware/AuthMiddleware.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file)) {
        error_log("Required file not found: " . $file);
        http_response_code(500);
        echo json_encode(['error' => 'Server configuration error']);
        exit;
    }
    require_once $file;
}

// Register services
try {
    Flight::register('auth_service', 'AuthService');
    Flight::register('user_service', 'UserService');
    Flight::register('productsService', 'ProductsService');
    Flight::register('cartService', 'CartService');
    Flight::register('cartItemsService', 'CartItemsService');
    Flight::register('ordersService', 'OrdersService');
    Flight::register('categoriesService', 'CategoriesService');
    Flight::register('auth_middleware', 'JWTMiddleware');
    error_log("Services registered successfully");
} catch (Exception $e) {
    error_log("Error registering services: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
}

// Initialize JWT middleware
$jwt_middleware = new JWTMiddleware();
Flight::before('start', array($jwt_middleware, 'handle'));

// Include routes after service registration
require_once __DIR__ . '/routes/AuthRoutes.php';
require_once __DIR__ . '/routes/ProductsRoutes.php';
require_once __DIR__ . '/routes/CartRoutes.php';
require_once __DIR__ . '/routes/OrdersRoutes.php';
require_once __DIR__ . '/routes/UserRoutes.php';
require_once __DIR__ . '/routes/CategoriesRoutes.php';
require_once __DIR__ . '/routes/ConfigRoutes.php';





Flight::start(); 