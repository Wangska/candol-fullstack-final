<?php
// Setup script to initialize the database
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Wallet - Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .setup-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card setup-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-wallet text-primary" style="font-size: 3rem;"></i>
                            <h2 class="mt-3">Crypto Wallet Setup</h2>
                            <p class="text-muted">Follow these steps to get started</p>
                        </div>

                        <div class="mb-4">
                            <h5><i class="fas fa-database text-info me-2"></i>Database Setup</h5>
                            <ol>
                                <li>Start your XAMPP server (Apache + MySQL)</li>
                                <li>Open phpMyAdmin (<code>http://localhost/phpmyadmin</code>)</li>
                                <li>Import the <code>database.sql</code> file to create the database structure</li>
                                <li>Or copy and execute the SQL commands from <code>database.sql</code> manually</li>
                            </ol>
                        </div>

                        <div class="mb-4">
                            <h5><i class="fas fa-cog text-warning me-2"></i>Configuration</h5>
                            <p>Edit <code>config/database.php</code> if needed to match your MySQL credentials:</p>
                            <div class="alert alert-light">
                                <code>
                                    DB_HOST: localhost<br>
                                    DB_USERNAME: root<br>
                                    DB_PASSWORD: (your MySQL password)<br>
                                    DB_NAME: crypto_wallet
                                </code>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5><i class="fas fa-user text-success me-2"></i>Demo Account</h5>
                            <p>A demo account is pre-created for testing:</p>
                            <div class="alert alert-info">
                                <strong>Username:</strong> demo_user<br>
                                <strong>Password:</strong> password
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5><i class="fas fa-shield-alt text-danger me-2"></i>Security Notes</h5>
                            <ul>
                                <li>This is a demo application - not for production use</li>
                                <li>Private keys are stored in plain text (use proper encryption in production)</li>
                                <li>No real cryptocurrency transactions are performed</li>
                                <li>Change default passwords before deployment</li>
                            </ul>
                        </div>

                        <div class="text-center">
                            <a href="index.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-rocket me-2"></i>Go to Application
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
