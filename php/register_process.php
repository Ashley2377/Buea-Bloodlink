<?php
session_start();
require_once 'config.php';

// Simple sanitization
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = sanitize($_POST['role'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$role || !$email || strlen($password) < 6) {
        $_SESSION['error'] = 'Please fill required fields with valid data.';
        header('Location: /buea-bloodlink-frontend/register.php');
        exit;
    }

    $name = sanitize($_POST['name'] ?? $_POST['bank_name'] ?? '');
    $location = sanitize($_POST['location'] ?? '');

    // Check existing user
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Email already registered.';
        header('Location: /buea-bloodlink-frontend/register.php');
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $email, $passwordHash, $role]);
    $userId = $pdo->lastInsertId();

    if ($role === 'donor') {
        $blood_group = sanitize($_POST['blood_group'] ?? '');
        $age = (int)($_POST['age'] ?? 0);
        $phone = sanitize($_POST['phone'] ?? '');

        $stmt = $pdo->prepare('INSERT INTO donors (user_id, blood_group, age, location, phone) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $blood_group, $age, $location, $phone]);

    } elseif ($role === 'hospital') {
        $hospitalName = sanitize($_POST['hospital_name'] ?? '');
        $hospitalLocation = sanitize($_POST['hospital_location'] ?? '');
        $hospitalPhone = sanitize($_POST['hospital_phone'] ?? '');

        $stmt = $pdo->prepare('INSERT INTO hospitals (user_id, name, location, phone) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $hospitalName, $hospitalLocation, $hospitalPhone]);
    }

    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;
    $_SESSION['user_name'] = $name;

    if ($role === 'donor') {
        header('Location: /buea-bloodlink-frontend/donor_dashboard.php');
    } elseif ($role === 'hospital') {
        header('Location: /buea-bloodlink-frontend/bloodbank_dashboard.php');
    }
    exit;
}

header('Location: /buea-bloodlink-frontend/register.php');
exit;
