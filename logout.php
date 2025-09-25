<?php
require_once 'includes/session.php';

SessionManager::logout();
header('Location: login.php');
exit;
?>
