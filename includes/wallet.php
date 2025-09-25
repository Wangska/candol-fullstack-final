<?php
require_once 'config/database.php';

class WalletManager {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function getUserWallets($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM wallets WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getWalletById($wallet_id, $user_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM wallets WHERE id = ? AND user_id = ?");
            $stmt->execute([$wallet_id, $user_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function createWallet($user_id, $wallet_name, $currency = 'BTC') {
        try {
            // Generate wallet address and private key (simplified for demo)
            $wallet_address = $this->generateWalletAddress($currency);
            $private_key = bin2hex(random_bytes(32));
            
            $stmt = $this->db->prepare("INSERT INTO wallets (user_id, wallet_name, wallet_address, private_key, currency) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([$user_id, $wallet_name, $wallet_address, $private_key, $currency]);
            
            if ($result) {
                return ['success' => true, 'wallet_id' => $this->db->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to create wallet'];
            }
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function updateBalance($wallet_id, $new_balance) {
        try {
            $stmt = $this->db->prepare("UPDATE wallets SET balance = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$new_balance, $wallet_id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getTransactions($wallet_id, $limit = 20) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM transactions WHERE wallet_id = ? ORDER BY created_at DESC LIMIT ?");
            $stmt->execute([$wallet_id, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getAllUserTransactions($user_id, $limit = 50) {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, w.wallet_name, w.currency 
                FROM transactions t 
                JOIN wallets w ON t.wallet_id = w.id 
                WHERE w.user_id = ? 
                ORDER BY t.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$user_id, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function sendTransaction($wallet_id, $to_address, $amount, $fee = 0) {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Get wallet info
            $stmt = $this->db->prepare("SELECT * FROM wallets WHERE id = ?");
            $stmt->execute([$wallet_id]);
            $wallet = $stmt->fetch();
            
            if (!$wallet) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Wallet not found'];
            }
            
            $total_amount = $amount + $fee;
            if ($wallet['balance'] < $total_amount) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Insufficient balance'];
            }
            
            // Create transaction record
            $transaction_hash = hash('sha256', $wallet_id . $to_address . $amount . time() . rand());
            $stmt = $this->db->prepare("
                INSERT INTO transactions (wallet_id, transaction_type, amount, to_address, from_address, transaction_hash, fee, status) 
                VALUES (?, 'send', ?, ?, ?, ?, ?, 'confirmed')
            ");
            $stmt->execute([$wallet_id, $amount, $to_address, $wallet['wallet_address'], $transaction_hash, $fee]);
            
            // Update wallet balance
            $new_balance = $wallet['balance'] - $total_amount;
            $stmt = $this->db->prepare("UPDATE wallets SET balance = ? WHERE id = ?");
            $stmt->execute([$new_balance, $wallet_id]);
            
            $this->db->commit();
            
            return ['success' => true, 'transaction_hash' => $transaction_hash];
            
        } catch (PDOException $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()];
        }
    }
    
    public function receiveTransaction($wallet_id, $from_address, $amount) {
        try {
            // Get wallet info
            $stmt = $this->db->prepare("SELECT * FROM wallets WHERE id = ?");
            $stmt->execute([$wallet_id]);
            $wallet = $stmt->fetch();
            
            if (!$wallet) {
                return ['success' => false, 'message' => 'Wallet not found'];
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Create transaction record
            $transaction_hash = hash('sha256', $wallet_id . $from_address . $amount . time() . rand());
            $stmt = $this->db->prepare("
                INSERT INTO transactions (wallet_id, transaction_type, amount, from_address, to_address, transaction_hash, status) 
                VALUES (?, 'receive', ?, ?, ?, ?, 'confirmed')
            ");
            $stmt->execute([$wallet_id, $amount, $from_address, $wallet['wallet_address'], $transaction_hash]);
            
            // Update wallet balance
            $new_balance = $wallet['balance'] + $amount;
            $stmt = $this->db->prepare("UPDATE wallets SET balance = ? WHERE id = ?");
            $stmt->execute([$new_balance, $wallet_id]);
            
            $this->db->commit();
            
            return ['success' => true, 'transaction_hash' => $transaction_hash];
            
        } catch (PDOException $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()];
        }
    }
    
    private function generateWalletAddress($currency) {
        switch (strtoupper($currency)) {
            case 'BTC':
                return '1' . substr(bin2hex(random_bytes(20)), 0, 33);
            case 'ETH':
                return '0x' . substr(bin2hex(random_bytes(20)), 0, 40);
            case 'LTC':
                return 'L' . substr(bin2hex(random_bytes(20)), 0, 33);
            default:
                return '1' . substr(bin2hex(random_bytes(20)), 0, 33);
        }
    }
    
    public function getTotalBalance($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT currency, SUM(balance) as total_balance 
                FROM wallets 
                WHERE user_id = ? 
                GROUP BY currency
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
