<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/AuthDao.php';
require_once __DIR__ . '/../dao/config.php';
require_once __DIR__ . '/../data/roles.php';
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
       if (empty($entity['email']) || empty($entity['password'])) {
           return [
               'success' => false, 
               'error' => 'Email and password are required.',
               'status' => 400
           ];
       }


       $email_exists = $this->auth_dao->get_user_by_email($entity['email']);
       if($email_exists){
           return [
               'success' => false, 
               'error' => 'Email already registered.',
               'status' => 409
           ];
       }

       // Set default role as customer if not specified
       if (!isset($entity['role'])) {
           $entity['role'] = Roles::USER;
       }

       $entity['password'] = password_hash($entity['password'], PASSWORD_BCRYPT);

       try {
           $entity = parent::add($entity);
           unset($entity['password']);
           return ['success' => true, 'data' => $entity];
       } catch (Exception $e) {
           return [
               'success' => false,
               'error' => 'Error creating user: ' . $e->getMessage(),
               'status' => 500
           ];
       }
   }


   public function login($entity) {  
       if (empty($entity['email']) || empty($entity['password'])) {
           return [
               'success' => false, 
               'error' => 'Email and password are required.',
               'status' => 400
           ];
       }


       $user = $this->auth_dao->get_user_by_email($entity['email']);
       
       if(!$user || !password_verify($entity['password'], $user['password'])) {
           return [
               'success' => false, 
               'error' => 'Invalid email or password.',
               'status' => 401
           ];
       }


       unset($user['password']);
      
       $jwt_payload = [
           'user' => [
               'id' => $user['id'],
               'email' => $user['email'],
               'role' => $user['role'],
               'permissions' => $this->getRolePermissions($user['role'])
           ],
           'iat' => time(),
           'exp' => time() + (60 * 60 * 24) // valid for 24 hours
       ];


       try {
           $token = JWT::encode(
               $jwt_payload,
               JWTConfig::JWT_SECRET(),
               'HS256'
           );

           return [
               'success' => true, 
               'data' => [
                   'token' => $token,
                   'user' => $jwt_payload['user']
               ]
           ];
       } catch (Exception $e) {
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
       $permissions = [];
       
       switch ($role) {
           case Roles::ADMIN:
               $permissions = [
                   'create_product',
                   'update_product',
                   'delete_product',
                   'view_all_orders',
                   'manage_users',
                   'manage_inventory'
               ];
               break;
               
           case Roles::EMPLOYEE:
               $permissions = [
                   'view_products',
                   'update_product',
                   'view_orders',
                   'update_order_status'
               ];
               break;
               
           case Roles::USER:
               $permissions = [
                   'view_products',
                   'place_order',
                   'view_own_orders',
                   'manage_cart'
               ];
               break;
       }
       
       return $permissions;
   }
}
