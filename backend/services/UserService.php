<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends BaseService {
    public function __construct() {
        parent::__construct(new UserDao());
    }

    public function getByEmail($email) {
        return $this->dao->getByEmail($email);
    }

    // Example of business logic: Validate email uniqueness before creation
    public function createUser($data) {
        if ($this->dao->getByEmail($data['email'])) {
            throw new Exception("Email already exists.");
        }
        return $this->dao->create($data);
    }
}
?>