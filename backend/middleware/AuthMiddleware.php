<?php
require_once __DIR__ . '/../dao/config.php';
require_once __DIR__ . '/../data/roles.php';
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

    /**
     * Authorize a single role
     * @param string $requiredRole The role required to access the resource
     * @return bool Returns true if authorized, halts execution if not
     */
    public function authorizeRole($requiredRole) {
        $user = Flight::get('user');
        if (!isset($user->role) || $user->role !== $requiredRole) {
            Flight::json([
                'success' => false,
                'message' => 'Access denied: insufficient privileges'
            ], 403);
            exit();
        }
        return true;
    }

    /**
     * Authorize multiple roles
     * @param array $roles Array of roles that can access the resource
     * @return bool Returns true if authorized, halts execution if not
     */
    public function authorizeRoles($roles) {
        $user = Flight::get('user');
        if (!isset($user->role) || !in_array($user->role, $roles)) {
            Flight::json([
                'success' => false,
                'message' => 'Forbidden: role not allowed'
            ], 403);
            exit();
        }
        return true;
    }

    /**
     * Authorize based on permission
     * @param string $permission The permission required to access the resource
     * @return bool Returns true if authorized, halts execution if not
     */
    public function authorizePermission($permission) {
        $user = Flight::get('user');
        if (!isset($user->permissions) || !in_array($permission, $user->permissions)) {
            Flight::json([
                'success' => false,
                'message' => 'Access denied: permission missing'
            ], 403);
            exit();
        }
        return true;
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