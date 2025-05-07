<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/../../../../vendor/autoload.php';

// Define BASE_URL before scanning files
if($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1'){
    define('BASE_URL', 'http://localhost/IBU-WebProgramming-2025/backend');
} else {
    define('BASE_URL', 'https://add-production-server-after-deployment/backend/');
}

// Make sure the correct paths are being scanned
$openapi = \OpenApi\Generator::scan([
    __DIR__ . '/doc_setup.php',  // Fixed underscores
    __DIR__ . '/../../../routes'  // Fixed underscores
]);

header('Content-Type: application/json');
echo $openapi->toJson();
?>