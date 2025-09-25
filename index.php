<?php
require_once 'includes/session.php';

// Check if user is logged in and redirect accordingly
if (SessionManager::isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
?>
