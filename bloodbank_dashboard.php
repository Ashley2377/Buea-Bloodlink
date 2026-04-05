<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    header('Location: /buea-bloodlink-frontend/login.php');
    exit;
}
require_once 'php/config.php';

$userId = $_SESSION['user_id'];

$bank = $pdo->prepare('SELECT h.*, u.email FROM hospitals h JOIN users u ON u.id = h.user_id WHERE h.user_id = ? LIMIT 1');
$bank->execute([$userId]);
$bankInfo = $bank->fetch(PDO::FETCH_ASSOC);

// handle stock form submission from this same page
require_once 'php/notify.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['blood_group'])) {
        $blood_group = htmlspecialchars($_POST['blood_group']);
        $quantity = (int)$_POST['quantity'];
        if ($blood_group && $quantity > 0) {
            $stmt = $pdo->prepare('SELECT id FROM blood_stock WHERE bank_id = ? AND blood_group = ?');
            $stmt->execute([$bankInfo['id'], $blood_group]);
            $stocks = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($stocks) {
                $stmt = $pdo->prepare('UPDATE blood_stock SET quantity = quantity + ? WHERE id = ?');
                $stmt->execute([$quantity, $stocks['id']]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO blood_stock (bank_id, blood_group, quantity) VALUES (?, ?, ?)');
                $stmt->execute([$bankInfo['id'], $blood_group, $quantity]);
            }
        }
    }

    if (isset($_POST['update_request_status']) && isset($_POST['request_id'])) {
        $status = in_array($_POST['update_request_status'], ['approved', 'rejected']) ? $_POST['update_request_status'] : 'pending';
        $requestId = (int)$_POST['request_id'];
        $stmt = $pdo->prepare('SELECT r.*, u.email, d.phone FROM requests r LEFT JOIN users u ON u.id = r.user_id LEFT JOIN donors d ON d.user_id = u.id WHERE r.id = ? LIMIT 1');
        $stmt->execute([$requestId]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt2 = $pdo->prepare('UPDATE requests SET status = ? WHERE id = ?');
        $stmt2->execute([$status, $requestId]);

        if ($request) {
            $subject = 'Blood Request ' . ucfirst($status);
            $message = "<p>Your request for <strong>{$request['blood_group']}</strong> in <strong>{$request['location']}</strong> has been <strong>$status</strong>.</p>";
            notifyRequest($request['email'], $request['phone'], $subject, $message);
        }
    }

    header('Location: /buea-bloodlink-frontend/bloodbank_dashboard.php');
    exit;
}

$stockList = $pdo->prepare('SELECT * FROM blood_stock WHERE bank_id = ?');
$stockList->execute([$bankInfo['id']]);
$stocks = $stockList->fetchAll(PDO::FETCH_ASSOC);

// load requests for blood bank
$requests = $pdo->query('SELECT r.*, u.name AS requester_name, u.email AS requester_email FROM requests r LEFT JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/components.css">
</head>
<body>
    <header>
        <nav class="top-nav">
            <div class="logo">Buea BloodLink</div>
            <ul class="nav-items">
                <li><a href="index.html">Home</a></li>
                <li><a href="php/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main class="page-container">
        <section class="card">
            <h2>Blood Bank Dashboard</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</p>
            <div class="profile-grid">
                <div class="card small-card">
                    <h3>Bank Info</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($bankInfo['name']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($bankInfo['location']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($bankInfo['email']); ?></p>
                </div>
                <div class="card small-card">
                    <h3>Add / Update Stock</h3>
                    <form method="POST" action="bloodbank_dashboard.php">
                        <label for="blood_group">Blood Group</label>
                        <select name="blood_group" required>
                            <option value="">Select group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                        <label for="quantity">Quantity</label>
                        <input type="number" name="quantity" min="1" required>
                        <button class="btn" type="submit">Save Stock</button>
                    </form>
                </div>
            </div>

            <h3>Current Stock</h3>
            <div class="card-list">
                <?php if (!$stocks): ?>
                    <p>No stock added yet.</p>
                <?php else: ?>
                    <?php foreach ($stocks as $stock): ?>
                        <div class="small-card">
                            <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($stock['blood_group']); ?></p>
                            <p><strong>Quantity:</strong> <?php echo htmlspecialchars($stock['quantity']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <h3>Blood Requests</h3>
            <?php if (!$requests): ?>
                <p>No requests yet.</p>
            <?php else: ?>
                <div class="card-list">
                    <?php foreach ($requests as $req): ?>
                        <div class="small-card">
                            <p><strong>Request #<?php echo (int)$req['id']; ?></strong></p>
                            <p><strong>From:</strong> <?php echo htmlspecialchars($req['requester_name'] ?? 'Unknown'); ?> (<?php echo htmlspecialchars($req['requester_email'] ?? '-'); ?>)</p>
                            <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($req['blood_group']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($req['location']); ?></p>
                            <p><strong>Quantity:</strong> <?php echo (int)$req['quantity']; ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($req['status']); ?></p>
                            <form method="POST" action="bloodbank_dashboard.php">
                                <input type="hidden" name="request_id" value="<?php echo (int)$req['id']; ?>">
                                <button class="btn" type="submit" name="update_request_status" value="approved">Approve</button>
                                <button class="btn btn-danger" type="submit" name="update_request_status" value="rejected">Reject</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>