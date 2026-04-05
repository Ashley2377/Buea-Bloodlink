<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /buea-bloodlink-frontend/login.php');
    exit;
}
require_once 'php/config.php';

// Delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $del = (int)$_POST['delete_user_id'];
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$del]);
    header('Location: /buea-bloodlink-frontend/admin_dashboard.php');
    exit;
}

// load all donors and banks
$donors = $pdo->query('SELECT u.id, u.name, u.email, d.blood_group, d.age, d.location, d.phone FROM users u JOIN donors d ON d.user_id = u.id ORDER BY u.name')->fetchAll(PDO::FETCH_ASSOC);
$banks = $pdo->query('SELECT u.id, h.name AS hospital_name, u.email, h.location FROM users u JOIN hospitals h ON h.user_id = u.id ORDER BY h.name')->fetchAll(PDO::FETCH_ASSOC);
$stock = $pdo->query('SELECT h.name AS hospital_name, bs.blood_group, bs.quantity FROM blood_stock bs JOIN hospitals h ON h.id = bs.hospital_id ORDER BY h.name')->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/components.css">
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
                <li><a href="php/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="page-container">
        <section class="card">
            <h2>Admin Dashboard</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</p>

            <h3>Donors</h3>
            <div class="card-list">
                <?php foreach ($donors as $d): ?>
                    <div class="small-card">
                        <p><strong><?php echo htmlspecialchars($d['name']); ?></strong></p>
                        <p><?php echo htmlspecialchars($d['email']); ?></p>
                        <p>Group: <?php echo htmlspecialchars($d['blood_group']); ?></p>
                        <p>Location: <?php echo htmlspecialchars($d['location']); ?></p>
                        <form method="POST" action="admin_dashboard.php" onsubmit="return confirm('Delete user?');">
                            <input type="hidden" name="delete_user_id" value="<?php echo (int)$d['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <h3>Blood Banks</h3>
            <div class="card-list">
                <?php foreach ($banks as $b): ?>
                    <div class="small-card">
                        <p><strong><?php echo htmlspecialchars($b['bank_name']); ?></strong></p>
                        <p><?php echo htmlspecialchars($b['email']); ?></p>
                        <p>Location: <?php echo htmlspecialchars($b['location']); ?></p>
                        <form method="POST" action="admin_dashboard.php" onsubmit="return confirm('Delete user?');">
                            <input type="hidden" name="delete_user_id" value="<?php echo (int)$b['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <h3>Stock Overview</h3>
            <div class="card-list">
                <?php foreach ($stock as $s): ?>
                    <div class="small-card">
                        <p><strong><?php echo htmlspecialchars($s['bank_name']); ?></strong></p>
                        <p>Group: <?php echo htmlspecialchars($s['blood_group']); ?> - Qty: <?php echo (int)$s['quantity']; ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($stock)): ?>
                    <p>No stock entries recorded.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>