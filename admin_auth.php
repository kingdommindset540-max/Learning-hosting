<?php
// admin_auth.php - Handle admin login
session_start();

// If not POST, redirect to login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.html');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Check credentials (admin / 12345)
if ($username === 'admin' && $password === '12345') {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;
    header('Location: admin_dashboard.php');
    exit;
} else {
    $_SESSION['login_error'] = 'Invalid username or password';
    header('Location: admin.html');
    exit;
}
?>
