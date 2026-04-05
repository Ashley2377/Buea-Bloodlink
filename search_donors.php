<?php
require_once 'php/config.php';

$blood_group = $_GET['blood_group'] ?? '';
$location = $_GET['location'] ?? '';
$donors = [];

if ($blood_group || $location) {
    $sql = 'SELECT d.*, u.name AS donor_name, u.email FROM donors d JOIN users u ON u.id = d.user_id WHERE 1=1';
    $params = [];

    if ($blood_group) {
        $sql .= ' AND d.blood_group = ?';
        $params[] = $blood_group;
    }

    if ($location) {
        $sql .= ' AND d.location LIKE ?';
        $params[] = '%' . $location . '%';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $donors = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Donors - Buea BloodLink</title>
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
                <li><a href="request.php">Request Blood</a></li>
                <li><a href="search_donors.php">Search Donors</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </nav>
    </header>
    <main class="page-container">
        <section class="card">
            <h2>Search Donors by Blood Group / Location</h2>
            <form method="GET" action="search_donors.php" class="form-card">
                <label for="blood_group">Blood Group</label>
                <select name="blood_group" id="blood_group">
                    <option value="">Any</option>
                    <option value="A+" <?php echo $blood_group == 'A+' ? 'selected' : ''; ?>>A+</option>
                    <option value="A-" <?php echo $blood_group == 'A-' ? 'selected' : ''; ?>>A-</option>
                    <option value="B+" <?php echo $blood_group == 'B+' ? 'selected' : ''; ?>>B+</option>
                    <option value="B-" <?php echo $blood_group == 'B-' ? 'selected' : ''; ?>>B-</option>
                    <option value="AB+" <?php echo $blood_group == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                    <option value="AB-" <?php echo $blood_group == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                    <option value="O+" <?php echo $blood_group == 'O+' ? 'selected' : ''; ?>>O+</option>
                    <option value="O-" <?php echo $blood_group == 'O-' ? 'selected' : ''; ?>>O-</option>
                </select>
                <label for="location">Location</label>
                <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($location); ?>" placeholder="City or region">
                <button type="submit" class="btn">Search</button>
            </form>
        </section>

        <section class="card">
            <h3>Donor Results</h3>
            <?php if (empty($donors)): ?>
                <p>No matched donors yet or search criteria missing.</p>
            <?php else: ?>
                <div class="card-list">
                    <?php foreach ($donors as $donor): ?>
                        <div class="small-card">
                            <p><strong><?php echo htmlspecialchars($donor['donor_name']); ?></strong> (<?php echo htmlspecialchars($donor['blood_group']); ?>)</p>
                            <p>Email: <?php echo htmlspecialchars($donor['email']); ?></p>
                            <p>Age: <?php echo (int)$donor['age']; ?></p>
                            <p>Location: <?php echo htmlspecialchars($donor['location']); ?></p>
                            <p>Phone: <?php echo htmlspecialchars($donor['phone']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>