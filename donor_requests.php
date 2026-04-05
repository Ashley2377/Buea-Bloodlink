<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header('Location: /buea-bloodlink-frontend/login.php');
    exit;
}
require_once 'php/config.php';

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_request_id'])) {
    $cancelId = (int)$_POST['cancel_request_id'];
    $stmt = $pdo->prepare('UPDATE requests SET status = ? WHERE id = ? AND user_id = ?');
    $stmt->execute(['rejected', $cancelId, $userId]);
    header('Location: /buea-bloodlink-frontend/donor_requests.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - Buea BloodLink</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/components.css">
</head>
<body>
    <header>
        <nav class="top-nav">
            <div class="logo">Buea BloodLink</div>
            <ul class="nav-items">
                <li><a href="donor_dashboard.php">Donor Dashboard</a></li>
                <li><a href="request.php">Request Blood</a></li>
                <li><a href="php/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="page-container">
        <section class="card">
            <h2>My Blood Requests</h2>
            <?php if (empty($requests)): ?>
                <p>No blood requests yet.</p>
            <?php else: ?>
                <div class="card-list">
                    <?php foreach ($requests as $request): ?>
                        <div class="small-card">
                            <p><strong>ID:</strong> <?php echo (int)$request['id']; ?></p>
                            <p><strong>Group:</strong> <?php echo htmlspecialchars($request['blood_group']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($request['location']); ?></p>
                            <p><strong>Quantity:</strong> <?php echo (int)$request['quantity']; ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($request['status']); ?></p>
                            <p><small>Submitted: <?php echo htmlspecialchars($request['created_at']); ?></small></p>
                            <?php if ($request['status'] === 'pending'): ?>
                                <form method="post" action="donor_requests.php" onsubmit="return confirm('Cancel this request?');">
                                    <input type="hidden" name="cancel_request_id" value="<?php echo (int)$request['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>