<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

try {
    echo "Starting JWT test...\n";
    
    $payload = [
        'test' => 'data',
        'iat' => time(),
        'exp' => time() + 3600
    ];
    
    echo "Test payload created\n";
    var_dump($payload);
    
    $secret = JWTConfig::JWT_SECRET();
    echo "Secret key retrieved: " . $secret . "\n";
    
    if (empty($secret)) {
        throw new Exception("JWT secret key is empty!");
    }
    
    echo "Attempting to encode JWT...\n";
    $token = JWT::encode($payload, $secret, 'HS256');
    echo "Token generated successfully: " . $token . "\n";
    
    // Try to decode the token to verify it works
    echo "Attempting to decode JWT...\n";
    $decoded = JWT::decode($token, new Key($secret, 'HS256'));
    echo "Token decoded successfully:\n";
    var_dump($decoded);
    
    echo "JWT Test successful!\n";
} catch (Exception $e) {
    echo "JWT Test failed!\n";
    echo "Error message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 