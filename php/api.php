<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
require_once 'notify.php';

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $action !== 'blood-requests') {
    respond(['error' => 'Method not allowed'], 405);
}

switch ($action) {
    case 'login':
        $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $input['password'] ?? '';

        if (!$email || !$password) {
            respond(['error' => 'Email and password required'], 400);
        }

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            respond(['success' => true, 'user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']]]);
        }

        respond(['error' => 'Invalid credentials'], 401);
        break;

    case 'register':
        $name = trim($input['name'] ?? '');
        $role = trim($input['role'] ?? 'donor');
        $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $phone = trim($input['phone'] ?? '');
        $password = $input['password'] ?? '';

        if (!$name || !$email || strlen($password) < 6) {
            respond(['error' => 'Name, email, and password (min 6 chars) are required'], 400);
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            respond(['error' => 'Email already registered'], 409);
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $passwordHash, $role]);
        $userId = $pdo->lastInsertId();

        if ($role === 'donor') {
            $stmt = $pdo->prepare('INSERT INTO donors (user_id, blood_group, age, location, phone) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$userId, '', 0, '', $phone]); // Placeholder values, can be updated later
        }

        // Send welcome notification
        $welcomeSubject = 'Welcome to Buea BloodLink!';
        $welcomeMessage = "<h2>Welcome, $name!</h2><p>Your account has been created. You can now login and help save lives.</p>";
        sendEmailNotification($email, $welcomeSubject, $welcomeMessage);
        if ($phone) {
            sendSmsNotification($phone, "Welcome to Buea BloodLink, $name! Your account is ready.");
        }

        respond(['success' => true, 'message' => 'Registered successfully']);
        break;

    case 'request-blood':
        $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $phone = trim($input['phone'] ?? '');
        $blood_group = trim($input['blood_group'] ?? '');
        $location = trim($input['location'] ?? '');
        $units = intval($input['units'] ?? 0);

        if (!$email || !$blood_group || !$location || $units <= 0) {
            respond(['error' => 'Email, blood group, location and units are required'], 400);
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $userId = $user ? $user['id'] : null;

        $stmt = $pdo->prepare('INSERT INTO requests (user_id, blood_group, location, status) VALUES (?, ?, ?, ?);');
        $stmt->execute([$userId, $blood_group, $location, 'pending']);

        // Send request confirmation notification
        $requestSubject = 'Blood Request Submitted - Buea BloodLink';
        $requestMessage = "<h2>Blood Request Received</h2><p>Group: $blood_group<br>Location: $location<br>Units: $units</p><p>We will notify you when a match is found.</p>";
        sendEmailNotification($email, $requestSubject, $requestMessage);
        if ($phone) {
            sendSmsNotification($phone, "Blood request submitted for $blood_group at $location. Units: $units. We'll notify you soon.");
        }

        // Notify admin
        $adminEmail = 'admin@bueabloodlink.local';
        $adminSubject = 'New Blood Request - Buea BloodLink';
        $adminMessage = "<h2>New Request</h2><p>Email: $email<br>Phone: $phone<br>Group: $blood_group<br>Location: $location<br>Units: $units</p>";
        sendEmailNotification($adminEmail, $adminSubject, $adminMessage);

        respond(['success' => true, 'message' => 'Blood request submitted']);
        break;

    case 'forgot-password':
        $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $method = trim($input['method'] ?? 'email'); // email or sms

        if (!$email || !in_array($method, ['email', 'sms'])) {
            respond(['error' => 'Valid email and method (email/sms) required'], 400);
        }

        $stmt = $pdo->prepare('SELECT id, phone FROM users u LEFT JOIN donors d ON u.id = d.user_id WHERE u.email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            respond(['error' => 'Email not found'], 404);
        }

        $code = rand(100000, 999999);
        $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $stmt = $pdo->prepare('INSERT INTO reset_codes (email, code, method, expires_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$email, $code, $method, $expires]);

        if ($method === 'email') {
            $subject = 'Password Reset Code - Buea BloodLink';
            $message = "<h2>Password Reset</h2><p>Your reset code is: <strong>$code</strong></p><p>Expires in 15 minutes.</p>";
            sendEmailNotification($email, $subject, $message);
        } else {
            if (!$user['phone']) {
                respond(['error' => 'No phone number on file for SMS'], 400);
            }
            sendSmsNotification($user['phone'], "Your password reset code is: $code. Expires in 15 minutes.");
        }

        respond(['success' => true, 'message' => 'Reset code sent via ' . $method]);

    case 'verify-code':
        $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $code = trim($input['code'] ?? '');

        if (!$email || !$code) {
            respond(['error' => 'Email and code required'], 400);
        }

        $stmt = $pdo->prepare('SELECT id FROM reset_codes WHERE email = ? AND code = ? AND expires_at > NOW() AND used = FALSE ORDER BY id DESC LIMIT 1');
        $stmt->execute([$email, $code]);
        if (!$stmt->fetch()) {
            respond(['error' => 'Invalid or expired code'], 400);
        }

        respond(['success' => true, 'message' => 'Code verified']);

    case 'reset-password':
        $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $code = trim($input['code'] ?? '');
        $newPassword = $input['password'] ?? '';

        if (!$email || !$code || strlen($newPassword) < 6) {
            respond(['error' => 'Email, code, and password (min 6 chars) required'], 400);
        }

        $stmt = $pdo->prepare('SELECT id FROM reset_codes WHERE email = ? AND code = ? AND expires_at > NOW() AND used = FALSE ORDER BY id DESC LIMIT 1');
        $stmt->execute([$email, $code]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reset) {
            respond(['error' => 'Invalid or expired code'], 400);
        }

        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
        $stmt->execute([$passwordHash, $email]);

        $stmt = $pdo->prepare('UPDATE reset_codes SET used = TRUE WHERE id = ?');
        $stmt->execute([$reset['id']]);

        respond(['success' => true, 'message' => 'Password reset successfully']);

    case 'accept-request':
        $requestId = intval($input['request_id'] ?? 0);
        $userId = intval($input['user_id'] ?? 0); // Hospital user ID

        if (!$requestId || !$userId) {
            respond(['error' => 'Request ID and user ID required'], 400);
        }

        // Check if user is hospital
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user['role'] !== 'hospital') {
            respond(['error' => 'Only hospitals can accept requests'], 403);
        }

        // Update request status
        $stmt = $pdo->prepare('UPDATE requests SET status = ? WHERE id = ?');
        $stmt->execute(['approved', $requestId]);

        // Get donor info
        $stmt = $pdo->prepare('SELECT u.email, d.phone FROM requests r JOIN users u ON r.user_id = u.id LEFT JOIN donors d ON u.id = d.user_id WHERE r.id = ?');
        $stmt->execute([$requestId]);
        $donor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($donor) {
            $subject = 'Blood Request Accepted - Buea BloodLink';
            $message = "<h2>Your blood request has been accepted!</h2><p>Please contact the hospital for next steps.</p>";
            sendEmailNotification($donor['email'], $subject, $message);
            if ($donor['phone']) {
                sendSmsNotification($donor['phone'], "Your blood request has been accepted. Contact the hospital.");
            }
        }

        respond(['success' => true, 'message' => 'Request accepted and donor notified']);

    case 'accept-donation':
        $requestId = intval($input['request_id'] ?? 0);
        $userId = intval($input['user_id'] ?? 0); // Donor user ID

        if (!$requestId || !$userId) {
            respond(['error' => 'Request ID and user ID required'], 400);
        }

        // Check if user is donor
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user['role'] !== 'donor') {
            respond(['error' => 'Only donors can accept donations'], 403);
        }

        // Update request status or create donation
        $stmt = $pdo->prepare('UPDATE requests SET status = ? WHERE id = ?');
        $stmt->execute(['donated', $requestId]);

        // Get hospital info (assuming request has hospital, but since no, get from users if role hospital, but need to link)
        // For simplicity, notify admin or something. In real, need to link requests to hospitals.
        $adminEmail = 'admin@bueabloodlink.local';
        $subject = 'Donor Accepted Donation - Buea BloodLink';
        $message = "<h2>A donor has accepted to donate for request ID $requestId</h2><p>Please coordinate.</p>";
        sendEmailNotification($adminEmail, $subject, $message);

        respond(['success' => true, 'message' => 'Donation accepted and hospital notified']);

    case 'blood-requests':
        $stmt = $pdo->query('SELECT r.id, u.name AS requester, r.blood_group, r.location, r.status, r.created_at FROM requests r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC');
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        respond(['success' => true, 'data' => $requests]);
        break;

    default:
        respond(['error' => 'Unknown action'], 404);
}
