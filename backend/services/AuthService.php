<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/AuthDao.php';
require_once __DIR__ . '/../dao/config.php';
require_once __DIR__ . '/../data/roles.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService extends BaseService {
   private $auth_dao;
   public function __construct() {
       $this->auth_dao = new AuthDao();
       parent::__construct(new AuthDao);
   }


   public function get_user_by_email($email){
       return $this->auth_dao->get_user_by_email($email);
   }


   public function register($entity) {  
       try {
           error_log("Starting registration process");
           
           if (empty($entity['email']) || empty($entity['password']) || 
               empty($entity['first_name']) || empty($entity['last_name'])) {
               error_log("Missing required fields in registration data");
               return [
                   'success' => false, 
                   'error' => 'Email, password, first name, and last name are required.',
                   'status' => 400
               ];
           }

           error_log("Checking if email exists: " . $entity['email']);
           $email_exists = $this->auth_dao->get_user_by_email($entity['email']);
           if($email_exists){
               error_log("Email already registered: " . $entity['email']);
               return [
                   'success' => false, 
                   'error' => 'Email already registered.',
                   'status' => 409
               ];
           }

           // Set default role as customer if not specified
           if (!isset($entity['role'])) {
               $entity['role'] = Roles::CUSTOMER;
           }
           error_log("Using role: " . $entity['role']);

           // Create the user data with correct field names
           $user_data = [
               'first_name' => $entity['first_name'],
               'last_name' => $entity['last_name'],
               'email' => $entity['email'],
               'password_hash' => password_hash($entity['password'], PASSWORD_BCRYPT),
               'role' => $entity['role'],
               'created_at' => date('Y-m-d H:i:s')
           ];

           error_log("Attempting to add user to database");
           $id = parent::add($user_data);
           error_log("User added with ID: " . $id);

           if (!$id) {
               error_log("Failed to insert user - no ID returned");
               return [
                   'success' => false,
                   'error' => 'Failed to create user account',
                   'status' => 500
               ];
           }

           $created_user = $this->get_by_id($id);
           if (!$created_user) {
               error_log("Failed to fetch created user with ID: " . $id);
               return [
                   'success' => false,
                   'error' => 'User created but failed to fetch details',
                   'status' => 500
               ];
           }

           unset($created_user['password_hash']);
           error_log("Registration successful for user: " . $created_user['email']);
           return ['success' => true, 'data' => $created_user];

       } catch (Exception $e) {
           error_log("Registration error in AuthService: " . $e->getMessage());
           error_log("Stack trace: " . $e->getTraceAsString());
           return [
               'success' => false,
               'error' => 'Error creating user: ' . $e->getMessage(),
               'status' => 500
           ];
       }
   }


   public function login($entity) {  
       if (empty($entity['email']) || empty($entity['password'])) {
           error_log("Login error: Email or password missing");
           return [
               'success' => false, 
               'error' => 'Email and password are required.',
               'status' => 400
           ];
       }

       error_log("Login attempt for email: " . $entity['email']);
       $user = $this->auth_dao->get_user_by_email($entity['email']);
       
       error_log("User found: " . ($user ? 'Yes' : 'No'));
       if (!$user) {
           error_log("Login failed: User not found");
           return [
               'success' => false, 
               'error' => 'Invalid email or password.',
               'status' => 401
           ];
       }

       error_log("Verifying password for user: " . $entity['email']);
       error_log("Stored hash: " . $user['password_hash']);
       error_log("Provided password: " . substr($entity['password'], 0, 3) . '***');
       
       if(!password_verify($entity['password'], $user['password_hash'])) {
           error_log("Login failed: Password verification failed");
           return [
               'success' => false, 
               'error' => 'Invalid email or password.',
               'status' => 401
           ];
       }

       error_log("Password verified successfully");
       unset($user['password_hash']);
      
       error_log("Creating JWT payload with user data: " . json_encode($user));
       try {
           // First check if JWT class exists
           if (!class_exists('Firebase\JWT\JWT')) {
               error_log("JWT class not found!");
               throw new Exception("JWT class not found. Make sure firebase/php-jwt is properly installed.");
           }
           error_log("JWT class verification passed");

           // Log the role we're working with
           error_log("Processing role: " . $user['role']);
           
           // Get permissions safely
           try {
               $permissions = $this->getRolePermissions($user['role']);
               error_log("Permissions retrieved successfully: " . json_encode($permissions));
           } catch (Exception $perm_error) {
               error_log("Error getting permissions: " . $perm_error->getMessage());
               $permissions = []; // Fallback to empty permissions
           }

           // Create a simpler payload structure
           try {
               $jwt_payload = [
                   'id' => $user['id'],
                   'email' => $user['email'],
                   'role' => strtolower($user['role']), // Ensure role is lowercase
                   'permissions' => $permissions,
                   'iat' => time(),
                   'exp' => time() + (60 * 60 * 24) // valid for 24 hours
               ];
               error_log("JWT payload created with structure: " . json_encode($jwt_payload));
           } catch (Exception $payload_error) {
               error_log("Error creating payload: " . $payload_error->getMessage());
               throw $payload_error;
           }

           // Get and verify secret key
           try {
               $secret = JWTConfig::JWT_SECRET();
               error_log("Retrieved secret key length: " . strlen($secret));
               
               if (empty($secret)) {
                   error_log("JWT secret key is empty!");
                   throw new Exception("JWT secret key is not configured properly.");
               }
               error_log("JWT secret key validation passed");
           } catch (Exception $secret_error) {
               error_log("Error with secret key: " . $secret_error->getMessage());
               throw $secret_error;
           }

           error_log("Starting JWT token encoding with algorithm HS256");
           try {
               $token = JWT::encode($jwt_payload, $secret, 'HS256');
               error_log("JWT token generated successfully: " . substr($token, 0, 20) . '...');
           } catch (Exception $jwt_error) {
               error_log("JWT encoding failed with error: " . $jwt_error->getMessage());
               error_log("JWT encoding error trace: " . $jwt_error->getTraceAsString());
               throw $jwt_error;
           }

           $response_data = [
               'success' => true, 
               'data' => [
                   'token' => $token,
                   'user' => [
                       'id' => $user['id'],
                       'email' => $user['email'],
                       'role' => $user['role'],
                       'first_name' => $user['first_name'],
                       'last_name' => $user['last_name'],
                       'permissions' => $this->getRolePermissions($user['role'])
                   ]
               ]
           ];
           error_log("Final response data prepared (token truncated): " . json_encode(array_merge(
               $response_data,
               ['data' => ['token' => substr($token, 0, 20) . '...', 'user' => $response_data['data']['user']]]
           )));
           return $response_data;

       } catch (Exception $e) {
           error_log("JWT generation error: " . $e->getMessage());
           error_log("Error occurred in file: " . $e->getFile() . " on line: " . $e->getLine());
           error_log("Stack trace: " . $e->getTraceAsString());
           error_log("JWT payload that failed: " . json_encode($jwt_payload ?? null));
           return [
               'success' => false,
               'error' => 'Error generating token: ' . $e->getMessage(),
               'status' => 500
           ];
       }
   }

   /**
    * Get permissions for a specific role
    * @param string $role The role to get permissions for
    * @return array Array of permissions
    */
   private function getRolePermissions($role) {
       error_log("Getting permissions for role: " . $role);
       $permissions = [];
       
       // Convert role to lowercase for case-insensitive comparison
       $role = strtolower($role);
       
       switch ($role) {
           case 'admin':
               $permissions = [
                   'create_product',
                   'update_product',
                   'delete_product',
                   'view_all_orders',
                   'manage_users',
                   'manage_inventory'
               ];
               break;
               
           case 'employee':
               $permissions = [
                   'view_products',
                   'update_product',
                   'view_orders',
                   'update_order_status'
               ];
               break;
               
           case 'customer':
               $permissions = [
                   'view_products',
                   'place_order',
                   'view_own_orders',
                   'manage_cart'
               ];
               break;
               
           default:
               error_log("Warning: Unknown role '" . $role . "', using default permissions");
               $permissions = [
                   'view_products',
                   'view_own_orders'
               ];
       }
       
       error_log("Permissions assigned: " . json_encode($permissions));
       return $permissions;
   }
}
