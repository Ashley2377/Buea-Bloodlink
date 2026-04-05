<?php
session_start();
require_once 'php/config.php';
require_once 'php/notify.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /buea-bloodlink-frontend/login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blood_group = trim($_POST['blood_group'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $qty = (int)($_POST['quantity'] ?? 1);

    if ($blood_group && $location && $qty > 0) {
        $stmt = $pdo->prepare('INSERT INTO requests (user_id, blood_group, location, quantity, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $blood_group, $location, $qty, 'pending']);

        $profile = $pdo->prepare('SELECT u.email, d.phone FROM users u LEFT JOIN donors d ON d.user_id = u.id WHERE u.id = ? LIMIT 1');
        $profile->execute([$userId]);
        $user = $profile->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $subject = 'Blood Request Submitted';
            $body = "<p>Your request for <strong>$blood_group</strong> blood in <strong>$location</strong> is submitted.</p>";
            $body .= "<p>Quantity: $qty, Status: pending.</p>";
            notifyRequest($user['email'], $user['phone'], $subject, $body);
        }

        $message = 'Request submitted successfully.';
    } else {
        $message = 'Please add blood group, location and valid quantity.';
    }
}

// list requests
if ($role === 'hospital' || $role === 'admin') {
    $requests = $pdo->query('SELECT r.*, u.name AS requester_name, u.email AS requester_email FROM requests r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare('SELECT r.*, u.name AS requester_name, u.email AS requester_email FROM requests r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = ? ORDER BY r.created_at DESC');
    $stmt->execute([$userId]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blood Request - Buea BloodLink</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/components.css" />
</head>
<body>
    <header>
        <nav class="top-nav">
            <div class="logo">Buea BloodLink</div>
            <ul class="nav-items">
                <li><a href="index.html">Home</a></li>
                <li><a href="index.html#howitworks">How It Works</a></li>
                <li><a href="search_donors.php">Search Donors</a></li>
                <li><a href="request.php">Request Blood</a></li>
                <?php if ($role === 'donor'): ?>
                    <li><a href="donor_requests.php">My Requests</a></li>
                <?php endif; ?>
                <?php if ($role !== 'admin'): ?>
                    <li><a href="php/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="admin_dashboard.php">Admin</a></li>
                    <li><a href="php/logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main class="page-container">
        <section class="card">
            <h2>Blood Request Workflow</h2>
            <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
            <form method="POST" action="request.php" class="form-card">
                <label for="blood_group">Blood Group</label>
                <select id="blood_group" name="blood_group" required>
                    <option value="">Select</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
                <label for="location">Location</label>
                <input type="text" name="location" id="location" required placeholder="City/Region" />
                <label for="quantity">Required Quantity</label>
                <input type="number" name="quantity" id="quantity" min="1" value="1" required />
                <button type="submit" class="btn">Submit Request</button>
            </form>
        </section>

        <section class="card">
            <h3>Requests</h3>
            <?php if (!$requests): ?>
                <p>No requests yet.</p>
            <?php else: ?>
                <div class="card-list">
                    <?php foreach ($requests as $req): ?>
                        <div class="small-card">
                            <p><strong>Request ID:</strong> <?php echo (int)$req['id']; ?></p>
                            <p><strong>Requested By:</strong> <?php echo htmlspecialchars($req['requester_name'] ?: 'Guest'); ?></p>
                            <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($req['blood_group']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($req['location']); ?></p>
                            <p><strong>Quantity:</strong> <?php echo (int)$req['quantity']; ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($req['status']); ?></p>
                            <p><small>On <?php echo htmlspecialchars($req['created_at']); ?></small></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>