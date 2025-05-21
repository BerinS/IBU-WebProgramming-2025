<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/AuthDao.php';
require_once __DIR__ . '/../dao/config.php';
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
           'user' => $user,
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
                   'user' => $user
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
}
