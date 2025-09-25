<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'includes/wallet.php';

// Check if user is logged in
SessionManager::requireLogin();

$user_id = SessionManager::getUserId();
$username = SessionManager::getUsername();

$auth = new Auth();
$wallet_manager = new WalletManager();

// Get user info
$user = $auth->getUserById($user_id);

// Get user wallets
$wallets = $wallet_manager->getUserWallets($user_id);

// Get total balances by currency
$total_balances = $wallet_manager->getTotalBalance($user_id);

// Get recent transactions
$recent_transactions = $wallet_manager->getAllUserTransactions($user_id, 10);

// Handle AJAX requests for sending transactions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'send_transaction':
            $wallet_id = intval($_POST['wallet_id'] ?? 0);
            $to_address = trim($_POST['to_address'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $fee = floatval($_POST['fee'] ?? 0.0001);
            
            $result = $wallet_manager->sendTransaction($wallet_id, $to_address, $amount, $fee);
            echo json_encode($result);
            exit;
            
        case 'create_wallet':
            $wallet_name = trim($_POST['wallet_name'] ?? '');
            $currency = strtoupper(trim($_POST['currency'] ?? 'BTC'));
            
            if (strlen($wallet_name) < 3) {
                echo json_encode(['success' => false, 'message' => 'Wallet name must be at least 3 characters']);
                exit;
            }
            
            $result = $wallet_manager->createWallet($user_id, $wallet_name, $currency);
            echo json_encode($result);
            exit;
            
        case 'simulate_receive':
            $wallet_id = intval($_POST['wallet_id'] ?? 0);
            $amount = floatval($_POST['amount'] ?? 0);
            $from_address = 'simulation_address';
            
            $result = $wallet_manager->receiveTransaction($wallet_id, $from_address, $amount);
            echo json_encode($result);
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Wallet - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(45deg, #667eea, #764ba2) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .wallet-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .wallet-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .balance-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .transaction-card {
            border-radius: 10px;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }
        
        .transaction-card:hover {
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .btn-gradient {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .currency-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        
        .btc { background: #f7931a; }
        .eth { background: #627eea; }
        .ltc { background: #345d9d; }
        
        .transaction-send { color: #dc3545; }
        .transaction-receive { color: #198754; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-wallet me-2"></i>
                Crypto Wallet
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i>
                        <?php echo htmlspecialchars($username); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                            <i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col">
                <h2>Welcome back, <?php echo htmlspecialchars($username); ?>!</h2>
                <p class="text-muted">Manage your crypto assets and track your transactions</p>
            </div>
        </div>

        <!-- Balance Overview -->
        <div class="row mb-4">
            <?php foreach ($total_balances as $balance): ?>
            <div class="col-md-4 mb-3">
                <div class="card balance-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title opacity-75">Total <?php echo $balance['currency']; ?></h6>
                                <h3 class="mb-0"><?php echo number_format($balance['total_balance'], 8); ?></h3>
                                <small class="opacity-75"><?php echo $balance['currency']; ?></small>
                            </div>
                            <div class="currency-icon <?php echo strtolower($balance['currency']); ?>">
                                <?php echo $balance['currency']; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <!-- Wallets Section -->
            <div class="col-lg-8 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>My Wallets</h4>
                    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#createWalletModal">
                        <i class="fas fa-plus me-2"></i>New Wallet
                    </button>
                </div>

                <div class="row">
                    <?php foreach ($wallets as $wallet): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card wallet-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="card-title"><?php echo htmlspecialchars($wallet['wallet_name']); ?></h6>
                                        <small class="text-muted"><?php echo $wallet['currency']; ?> Wallet</small>
                                    </div>
                                    <div class="currency-icon <?php echo strtolower($wallet['currency']); ?>">
                                        <?php echo $wallet['currency']; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h4 class="text-primary"><?php echo number_format($wallet['balance'], 8); ?></h4>
                                    <small class="text-muted"><?php echo $wallet['currency']; ?></small>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Address:</small>
                                    <div class="font-monospace small text-break">
                                        <?php echo htmlspecialchars($wallet['wallet_address']); ?>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary flex-fill" 
                                            onclick="showSendModal(<?php echo $wallet['id']; ?>, '<?php echo htmlspecialchars($wallet['wallet_name']); ?>', <?php echo $wallet['balance']; ?>)">
                                        <i class="fas fa-paper-plane me-1"></i>Send
                                    </button>
                                    <button class="btn btn-sm btn-outline-success flex-fill" 
                                            onclick="showReceiveModal(<?php echo $wallet['id']; ?>, '<?php echo htmlspecialchars($wallet['wallet_address']); ?>')">
                                        <i class="fas fa-qrcode me-1"></i>Receive
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($wallets)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
                    <h5>No wallets yet</h5>
                    <p class="text-muted">Create your first wallet to get started</p>
                    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#createWalletModal">
                        <i class="fas fa-plus me-2"></i>Create Wallet
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Transactions -->
            <div class="col-lg-4">
                <h4 class="mb-3">Recent Transactions</h4>
                
                <?php if (!empty($recent_transactions)): ?>
                    <?php foreach ($recent_transactions as $tx): ?>
                    <div class="card transaction-card mb-2">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-<?php echo $tx['transaction_type'] == 'send' ? 'arrow-up transaction-send' : 'arrow-down transaction-receive'; ?> me-2"></i>
                                        <strong class="<?php echo $tx['transaction_type'] == 'send' ? 'transaction-send' : 'transaction-receive'; ?>">
                                            <?php echo ucfirst($tx['transaction_type']); ?>
                                        </strong>
                                    </div>
                                    <small class="text-muted"><?php echo htmlspecialchars($tx['wallet_name']); ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="<?php echo $tx['transaction_type'] == 'send' ? 'transaction-send' : 'transaction-receive'; ?>">
                                        <?php echo $tx['transaction_type'] == 'send' ? '-' : '+'; ?>
                                        <?php echo number_format($tx['amount'], 8); ?> <?php echo $tx['currency']; ?>
                                    </div>
                                    <small class="text-muted"><?php echo date('M j, H:i', strtotime($tx['created_at'])); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-exchange-alt fa-2x text-muted mb-3"></i>
                    <p class="text-muted">No transactions yet</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create Wallet Modal -->
    <div class="modal fade" id="createWalletModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Create New Wallet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createWalletForm">
                        <div class="mb-3">
                            <label for="walletName" class="form-label">Wallet Name</label>
                            <input type="text" class="form-control" id="walletName" name="wallet_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency" name="currency">
                                <option value="BTC">Bitcoin (BTC)</option>
                                <option value="ETH">Ethereum (ETH)</option>
                                <option value="LTC">Litecoin (LTC)</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gradient" onclick="createWallet()">
                        <i class="fas fa-plus me-2"></i>Create Wallet
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Send Transaction Modal -->
    <div class="modal fade" id="sendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i>Send Cryptocurrency</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="sendForm">
                        <input type="hidden" id="sendWalletId" name="wallet_id">
                        <div class="alert alert-info">
                            <strong>Sending from:</strong> <span id="sendWalletName"></span><br>
                            <strong>Available:</strong> <span id="sendWalletBalance"></span>
                        </div>
                        <div class="mb-3">
                            <label for="toAddress" class="form-label">Recipient Address</label>
                            <input type="text" class="form-control font-monospace" id="toAddress" name="to_address" required>
                        </div>
                        <div class="mb-3">
                            <label for="sendAmount" class="form-label">Amount</label>
                            <input type="number" class="form-control" id="sendAmount" name="amount" step="0.00000001" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="fee" class="form-label">Network Fee</label>
                            <input type="number" class="form-control" id="fee" name="fee" step="0.00000001" value="0.0001">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="sendTransaction()">
                        <i class="fas fa-paper-plane me-2"></i>Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receive Modal -->
    <div class="modal fade" id="receiveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-qrcode me-2"></i>Receive Cryptocurrency</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p>Send cryptocurrency to this address:</p>
                    <div class="alert alert-info">
                        <div class="font-monospace" id="receiveAddress"></div>
                    </div>
                    <button class="btn btn-outline-primary" onclick="copyAddress()">
                        <i class="fas fa-copy me-2"></i>Copy Address
                    </button>
                    
                    <hr>
                    
                    <div class="alert alert-warning">
                        <strong>Demo Mode:</strong> Simulate receiving funds for testing
                    </div>
                    <input type="hidden" id="receiveWalletId">
                    <div class="mb-3">
                        <input type="number" class="form-control" id="simulateAmount" placeholder="Amount to receive" step="0.00000001" min="0">
                    </div>
                    <button class="btn btn-success" onclick="simulateReceive()">
                        <i class="fas fa-coins me-2"></i>Simulate Receive
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showSendModal(walletId, walletName, balance) {
            document.getElementById('sendWalletId').value = walletId;
            document.getElementById('sendWalletName').textContent = walletName;
            document.getElementById('sendWalletBalance').textContent = balance.toFixed(8);
            document.getElementById('sendAmount').max = balance;
            
            new bootstrap.Modal(document.getElementById('sendModal')).show();
        }

        function showReceiveModal(walletId, address) {
            document.getElementById('receiveWalletId').value = walletId;
            document.getElementById('receiveAddress').textContent = address;
            
            new bootstrap.Modal(document.getElementById('receiveModal')).show();
        }

        function copyAddress() {
            const address = document.getElementById('receiveAddress').textContent;
            navigator.clipboard.writeText(address).then(() => {
                alert('Address copied to clipboard!');
            });
        }

        async function createWallet() {
            const form = document.getElementById('createWalletForm');
            const formData = new FormData(form);
            formData.append('action', 'create_wallet');

            try {
                const response = await fetch('dashboard.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Wallet created successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('An error occurred: ' + error.message);
            }
        }

        async function sendTransaction() {
            const form = document.getElementById('sendForm');
            const formData = new FormData(form);
            formData.append('action', 'send_transaction');

            try {
                const response = await fetch('dashboard.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Transaction sent successfully!\nTransaction Hash: ' + result.transaction_hash);
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('An error occurred: ' + error.message);
            }
        }

        async function simulateReceive() {
            const walletId = document.getElementById('receiveWalletId').value;
            const amount = document.getElementById('simulateAmount').value;
            
            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'simulate_receive');
            formData.append('wallet_id', walletId);
            formData.append('amount', amount);

            try {
                const response = await fetch('dashboard.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Funds received successfully!\nTransaction Hash: ' + result.transaction_hash);
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('An error occurred: ' + error.message);
            }
        }
    </script>
</body>
</html>
