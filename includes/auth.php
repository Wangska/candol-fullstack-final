<?php
require_once 'config/database.php';
require_once 'includes/session.php';


class Auth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function register($username, $email, $password) {
        try {
            // Check if user already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $result = $stmt->execute([$username, $email, $password_hash]);
            
            if ($result) {
                $user_id = $this->db->lastInsertId();
                
                // Create default wallet for new user
                $this->createDefaultWallet($user_id);
                
                return ['success' => true, 'message' => 'Account created successfully'];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT id, username, password_hash, is_active FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['is_active']) {
                    SessionManager::login($user['id'], $user['username']);
                    return ['success' => true, 'message' => 'Login successful'];
                } else {
                    return ['success' => false, 'message' => 'Account is deactivated'];
                }
            } else {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function getUserById($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT id, username, email, created_at FROM users WHERE id = ? AND is_active = 1");
            $stmt->execute([$user_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    private function createDefaultWallet($user_id) {
        try {
            // Generate a simple wallet address (in a real app, use proper crypto libraries)
            $wallet_address = '1' . substr(bin2hex(random_bytes(20)), 0, 33);
            $private_key = bin2hex(random_bytes(32));
            
            $stmt = $this->db->prepare("INSERT INTO wallets (user_id, wallet_name, wallet_address, private_key, currency) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, 'Main Wallet', $wallet_address, $private_key, 'BTC']);
            
        } catch (PDOException $e) {
            // Log error but don't fail registration
            error_log("Failed to create default wallet: " . $e->getMessage());
        }
    }
    
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // Verify current password
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!password_verify($current_password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Update password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $result = $stmt->execute([$new_password_hash, $user_id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to change password'];
            }
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>
