<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../vendor/autoload.php';

// Load all required files
$required_files = [
    __DIR__ . '/config.php',
    __DIR__ . '/dao/BaseDao.php',
    __DIR__ . '/dao/AuthDao.php',
    __DIR__ . '/dao/UserDao.php',
    __DIR__ . '/dao/CartDao.php',
    __DIR__ . '/dao/CartItemsDao.php',
    __DIR__ . '/services/BaseService.php',
    __DIR__ . '/services/AuthService.php',
    __DIR__ . '/services/UserService.php',
    __DIR__ . '/services/ProductsService.php',
    __DIR__ . '/services/CartService.php',
    __DIR__ . '/services/CartItemsService.php',
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

// Add a test route
Flight::route('GET /test', function() {
    Flight::json(['status' => 'success', 'message' => 'Test route working']);
});

// Add a database test route
Flight::route('GET /test-db', function() {
    try {
        $db = Database::connect();
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        Flight::json(['status' => 'success', 'tables' => $tables]);
    } catch (Exception $e) {
        Flight::json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});

// Add a route to check users table structure
Flight::route('GET /test-users-table', function() {
    try {
        $db = Database::connect();
        $stmt = $db->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Flight::json(['status' => 'success', 'columns' => $columns]);
    } catch (Exception $e) {
        Flight::json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});

// Add a route to check products table structure
Flight::route('GET /test-products-table', function() {
    try {
        $db = Database::connect();
        $stmt = $db->query("SHOW CREATE TABLE products");
        $tableInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Also get foreign key constraints
        $stmt2 = $db->query("
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM
                information_schema.KEY_COLUMN_USAGE
            WHERE
                REFERENCED_TABLE_NAME = 'products'
                OR TABLE_NAME = 'products'
        ");
        $constraints = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        Flight::json([
            'status' => 'success', 
            'table_info' => $tableInfo,
            'constraints' => $constraints
        ]);
    } catch (Exception $e) {
        Flight::json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});

Flight::start(); 