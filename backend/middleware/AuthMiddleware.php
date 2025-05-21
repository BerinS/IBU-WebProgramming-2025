<?php
require_once __DIR__ . '/../dao/config.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTMiddleware {
    private $excluded_paths = [
        '/auth/login',
        '/auth/register',
        '/docs',
        '/',
        '/test-service'
    ];

    public function handle() {
        // Get the current request path
        $path = Flight::request()->url;

        // Check if the path is excluded from authentication
        if ($this->isExcludedPath($path)) {
            return true; // Allow access to excluded paths
        }

        // Get the Authorization header
        $headers = getallheaders();
        $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';

        // Check if the token exists
        if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            Flight::json([
                'success' => false,
                'message' => 'No token provided or invalid token format'
            ], 401);
            exit();
        }

        try {
            // Extract the token
            $token = $matches[1];
            
            // Verify the token
            $decoded = JWT::decode(
                $token, 
                new Key(JWTConfig::JWT_SECRET(), 'HS256')
            );

            // Add decoded token to Flight instance for use in routes
            Flight::set('user', $decoded->user);
            
            return true;
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 401);
            exit();
        }
    }

    private function isExcludedPath($path) {
        // Check if the path starts with any of the excluded paths
        foreach ($this->excluded_paths as $excluded) {
            if (strpos($path, $excluded) === 0) {
                return true;
            }
        }
        
        return false;
    }
}
?> 