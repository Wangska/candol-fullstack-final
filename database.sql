-- Crypto Wallet Database Structure
-- Run this script to create the database and tables

CREATE DATABASE IF NOT EXISTS crypto_wallet;
USE crypto_wallet;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Wallets table to store wallet information
CREATE TABLE IF NOT EXISTS wallets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    wallet_name VARCHAR(100) NOT NULL,
    wallet_address VARCHAR(255) UNIQUE NOT NULL,
    private_key VARCHAR(255) NOT NULL,
    balance DECIMAL(20, 8) DEFAULT 0.00000000,
    currency VARCHAR(10) DEFAULT 'BTC',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Transactions table to track all transactions
CREATE TABLE IF NOT EXISTS transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    wallet_id INT NOT NULL,
    transaction_type ENUM('send', 'receive') NOT NULL,
    amount DECIMAL(20, 8) NOT NULL,
    to_address VARCHAR(255),
    from_address VARCHAR(255),
    transaction_hash VARCHAR(255) UNIQUE,
    fee DECIMAL(20, 8) DEFAULT 0.00000000,
    status ENUM('pending', 'confirmed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE
);

-- Sessions table for user session management
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert some sample data for testing
INSERT IGNORE INTO users (username, email, password_hash) VALUES 
('demo_user', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT IGNORE INTO wallets (user_id, wallet_name, wallet_address, private_key, balance, currency) VALUES 
(1, 'Main Bitcoin Wallet', '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 'KwDiBf89QgGbjEhKnhXJuH7LrciVrZi3qYjgd9M7rFU73sVHnoWn', 0.50000000, 'BTC'),
(1, 'Ethereum Wallet', '0x742d35Cc6634C0532925a3b8D400E6D2D7D3dC4C', 'a8b5c3d2e1f0a9b8c7d6e5f4a3b2c1d0e9f8a7b6c5d4e3f2a1b0c9d8e7f6a5b4', 2.75000000, 'ETH');
