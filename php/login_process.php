<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $_SESSION['error'] = 'Provide email and password.';
        header('Location: /buea-bloodlink-frontend/login.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];

        if ($user['role'] === 'admin') {
            header('Location: /buea-bloodlink-frontend/admin_dashboard.php');
        } elseif ($user['role'] === 'donor') {
            header('Location: /buea-bloodlink-frontend/donor_dashboard.php');
        } elseif ($user['role'] === 'hospital') {
            header('Location: /buea-bloodlink-frontend/bloodbank_dashboard.php');
        }
        exit;
    }

    $_SESSION['error'] = 'Wrong email or password.';
    header('Location: /buea-bloodlink-frontend/login.php');
    exit;
}

header('Location: /buea-bloodlink-frontend/login.php');
exit;
