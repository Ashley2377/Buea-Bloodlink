<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header('Location: /buea-bloodlink-frontend/login.php');
    exit;
}
require_once 'php/config.php';

$userId = $_SESSION['user_id'];

// Get donor profile
$profile = $pdo->prepare('SELECT d.*, u.email FROM donors d JOIN users u ON u.id = d.user_id WHERE d.user_id = ? LIMIT 1');
$profile->execute([$userId]);
$donor = $profile->fetch(PDO::FETCH_ASSOC);

// Donation history placeholder (could be extended to a separate table)
$history = $donor['donation_history'] ?? 'No donations yet.';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/components.css">
    <script defer src="js/validation.js"></script>
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
                <li><a href="donor_requests.php">My Requests</a></li>
                <li><a href="php/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main class="page-container">
        <section class="card">
            <h2>Donor Dashboard</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</p>
            <div class="profile-grid">
                <div class="card small-card">
                    <h3>Profile</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($donor['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($donor['email']); ?></p>
                    <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($donor['blood_group']); ?></p>
                    <p><strong>Age:</strong> <?php echo htmlspecialchars($donor['age']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($donor['location']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($donor['phone']); ?></p>
                </div>
                <div class="card small-card">
                    <h3>Donation History</h3>
                    <p><?php echo nl2br(htmlspecialchars($history)); ?></p>
                </div>
            </div>

            <h3>Search Blood Banks</h3>
            <input id="bloodFilter" type="text" placeholder="Search by blood group or location" oninput="filterBloodBanks();">
            <div id="bloodBanks" class="card-list"></div>
        </section>
    </main>

    <script>
        function filterBloodBanks() {
            const term = document.getElementById('bloodFilter').value.toLowerCase();
            const container = document.getElementById('bloodBanks');
            container.innerHTML = '';

            const banks = <?php
                $stmt = $pdo->query('SELECT h.id,h.name,h.location,bs.blood_group,bs.quantity FROM hospitals h LEFT JOIN blood_stock bs ON h.id = bs.hospital_id ORDER BY h.name');
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($rows);
            ?>;

            const filtered = banks.filter(r => r.blood_group?.toLowerCase().includes(term) || r.location.toLowerCase().includes(term) || r.name.toLowerCase().includes(term));

            if (!filtered.length) {
                container.innerHTML = '<p>No matching blood banks found.</p>';
                return;
            }

            filtered.forEach(b => {
                const card = document.createElement('div');
                card.className = 'small-card';
                card.innerHTML = '<h4>' + b.name + '</h4>' +
                    '<p><strong>Location:</strong> ' + b.location + '</p>' +
                    '<p><strong>Group:</strong> ' + (b.blood_group || '-') + '</p>' +
                    '<p><strong>Qty:</strong> ' + (b.quantity || 0) + '</p>';
                container.appendChild(card);
            });
        }
        filterBloodBanks();
    </script>
</body>
</html>