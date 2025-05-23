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

try {
    // Make sure the correct paths are being scanned
    $paths = [
        __DIR__ . '/doc_setup.php',
        __DIR__ . '/../../../routes'
    ];

    // Verify paths exist
    foreach ($paths as $path) {
        if (!file_exists($path)) {
            throw new Exception("Path does not exist: " . $path);
        }
    }

    // Configure OpenAPI Generator with specific settings
    $openapi = \OpenApi\Generator::scan($paths, [
        'validate' => true,
        'format' => 'json'
    ]);

    // Set headers
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    
    // Get the OpenAPI specification as an array first
    $spec = json_decode($openapi->toJson(), true);
    
    if ($spec === null) {
        throw new Exception("Failed to generate valid OpenAPI specification");
    }

    // Ensure we have the basic required OpenAPI structure
    $spec = array_merge([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'IBU E-commerce API',
            'version' => '1.0.0'
        ],
        'paths' => [],
        'components' => [
            'schemas' => [],
            'securitySchemes' => []
        ]
    ], $spec);

    // Output the JSON with proper formatting
    echo json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Swagger generation failed',
        'message' => $e->getMessage(),
        'paths_scanned' => $paths ?? [],
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>