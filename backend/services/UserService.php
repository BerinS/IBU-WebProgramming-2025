<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends BaseService {
    private $user_dao;

    public function __construct() {
        $this->user_dao = new UserDao();
        parent::__construct($this->user_dao);
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

    public function updateProfile($user_id, $data) {
        // Validate input
        if (empty($data['current_password'])) {
            return [
                'success' => false,
                'error' => 'Current password is required',
                'status' => 400
            ];
        }

        // Verify current password
        $user = $this->user_dao->get_by_id($user_id);
        if (!$user || !password_verify($data['current_password'], $user['password'])) {
            return [
                'success' => false,
                'error' => 'Current password is incorrect',
                'status' => 401
            ];
        }

        $update_data = [];

        // Handle email update
        if (!empty($data['email']) && $data['email'] !== $user['email']) {
            // Check if email is already taken
            if ($this->user_dao->get_user_by_email($data['email'])) {
                return [
                    'success' => false,
                    'error' => 'Email is already taken',
                    'status' => 409
                ];
            }
            $update_data['email'] = $data['email'];
        }

        // Handle password update
        if (!empty($data['new_password'])) {
            $update_data['password'] = password_hash($data['new_password'], PASSWORD_BCRYPT);
        }

        // If there are no changes
        if (empty($update_data)) {
            return [
                'success' => true,
                'message' => 'No changes made to profile'
            ];
        }

        // Update user data
        try {
            $this->user_dao->update($user_id, $update_data);
            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error updating profile: ' . $e->getMessage(),
                'status' => 500
            ];
        }
    }

    public function deleteAccount($user_id, $password) {
        // Verify password
        $user = $this->user_dao->get_by_id($user_id);
        if (!$user || !password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'error' => 'Invalid password',
                'status' => 401
            ];
        }

        try {
            $this->user_dao->delete($user_id);
            return [
                'success' => true,
                'message' => 'Account deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error deleting account: ' . $e->getMessage(),
                'status' => 500
            ];
        }
    }
}
?>