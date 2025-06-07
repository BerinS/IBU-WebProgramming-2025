<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../data/roles.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTMiddleware {
    private $excluded_paths = [
        '/auth/login',
        '/auth/register',
        '/docs',
        '/test',
        '/test-db',
        '/test-users-table',
        '/test-products-table'
    ];

    private function isExcludedPath($path) {
        error_log("[AuthMiddleware] Checking if path is excluded: " . $path);
        // Exact match only
        foreach ($this->excluded_paths as $excluded) {
            if ($path === $excluded) {
                error_log("[AuthMiddleware] Path matches excluded path: " . $excluded);
                return true;
            }
        }
        error_log("[AuthMiddleware] Path is not excluded");
        return false;
    }

    public function handle() {
        error_log("[AuthMiddleware] Starting authentication check");
        
        // Get the current request path
        $path = Flight::request()->url;
        error_log("[AuthMiddleware] Checking path: " . $path);

        // Check if the path is excluded from authentication
        if ($this->isExcludedPath($path)) {
            error_log("[AuthMiddleware] Path is excluded from authentication");
            return true; // Allow access to excluded paths
        }

        // Get the Authorization header
        $headers = getallheaders();
        $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        error_log("[AuthMiddleware] Authorization header present: " . ($auth_header ? 'yes' : 'no'));

        // Check if the token exists
        if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            error_log("[AuthMiddleware] No token provided or invalid format");
            header('Content-Type: application/json');
            Flight::json([
                'success' => false,
                'message' => 'No token provided or invalid token format'
            ], 401);
            return false;
        }

        try {
            // Extract the token
            $token = $matches[1];
            error_log("[AuthMiddleware] Token extracted, attempting to decode");
            
            // Verify the token
            $decoded = JWT::decode(
                $token, 
                new Key(JWTConfig::JWT_SECRET(), 'HS256')
            );

            error_log("[AuthMiddleware] Token decoded successfully");
            // Add decoded token to Flight instance for use in routes
            Flight::set('user', (object)[
                'id' => $decoded->id,
                'email' => $decoded->email,
                'role' => $decoded->role,
                'permissions' => $decoded->permissions
            ]);
            
            error_log("[AuthMiddleware] User data set in Flight: " . json_encode(Flight::get('user')));
            return true;
        } catch (\Exception $e) {
            error_log("[AuthMiddleware] Token validation failed: " . $e->getMessage());
            header('Content-Type: application/json');
            Flight::json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 401);
            return false;
        }
    }

    /**
     * Authorize a single role
     * @param string $requiredRole The role required to access the resource
     * @return bool Returns true if authorized, halts execution if not
     */
    public function authorizeRole($requiredRole) {
        error_log("[AuthMiddleware] Checking role authorization. Required role: " . $requiredRole);
        $user = Flight::get('user');
        error_log("[AuthMiddleware] User role: " . ($user ? $user->role : 'none'));
        
        if (!isset($user->role) || $user->role !== $requiredRole) {
            error_log("[AuthMiddleware] Role authorization failed");
            header('Content-Type: application/json');
            Flight::json([
                'success' => false,
                'message' => 'Access denied: insufficient privileges'
            ], 403);
            return false;
        }
        error_log("[AuthMiddleware] Role authorization successful");
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
}
?> 