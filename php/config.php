<?php
// Database configuration file for Buea BloodLink
// Change these settings to match your MySQL instance
ini_set('display_errors', 1);
error_reporting(E_ALL);
$db_host = 'localhost';
$db_name = 'buea_bloodlink';
$db_user = 'demo';
$db_pass = 'demo';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Create tables if they don't exist
$ddl = [
    "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), email VARCHAR(255) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, role ENUM('admin','donor','hospital') NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
    "CREATE TABLE IF NOT EXISTS donors (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, blood_group VARCHAR(5) NOT NULL, age INT NOT NULL, location VARCHAR(255) NOT NULL, phone VARCHAR(50) NOT NULL, donation_history TEXT, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)",
    "CREATE TABLE IF NOT EXISTS hospitals (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, phone VARCHAR(50) NOT NULL, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)",
    "CREATE TABLE IF NOT EXISTS blood_stock (id INT AUTO_INCREMENT PRIMARY KEY, hospital_id INT NOT NULL, blood_group VARCHAR(5) NOT NULL, quantity INT NOT NULL DEFAULT 0, FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE)",
    "CREATE TABLE IF NOT EXISTS requests (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, blood_group VARCHAR(5) NOT NULL, location VARCHAR(255) NOT NULL, status ENUM('pending','approved','rejected') DEFAULT 'pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL)",
    "CREATE TABLE IF NOT EXISTS donations (id INT AUTO_INCREMENT PRIMARY KEY, donor_id INT NOT NULL, hospital_id INT NOT NULL, blood_group VARCHAR(5) NOT NULL, quantity INT NOT NULL DEFAULT 1, donation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE, FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE)",
    "CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, message TEXT NOT NULL, type VARCHAR(50) DEFAULT 'info', is_read BOOLEAN DEFAULT FALSE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)",
    "CREATE TABLE IF NOT EXISTS reset_codes (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) NOT NULL, code VARCHAR(10) NOT NULL, method ENUM('email','sms') NOT NULL, expires_at TIMESTAMP NOT NULL, used BOOLEAN DEFAULT FALSE)"
];

foreach ($ddl as $sql) {
    $pdo->exec($sql);
}

// Insert default admin user if not exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute(['admin@bueabloodlink.local']);
if (!$stmt->fetch()) {
    $passwordHash = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
    $stmt->execute(['Admin', 'admin@bueabloodlink.local', $passwordHash, 'admin']);
}

// Ensure admin exists
$admin = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
if (!$admin) {
    $defaultPassword = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?, ?, ?, ?)');
    $stmt->execute(['Administrator', 'admin@bueabloodlink.local', $defaultPassword, 'admin']);
}
