<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Buea BloodLink</title>
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
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <main class="page-container">
        <section class="card form-card">
            <h2>Register</h2>
            <form id="registerForm" action="php/register_process.php" method="POST" onsubmit="return validateRegister();">
                <label for="role">I am a <span class="required">*</span></label>
                <select name="role" id="role" required>
                    <option value="">Select role</option>
                    <option value="donor">Donor</option>
                    <option value="hospital">Hospital</option>
                </select>

                <div id="donorFields" class="role-fields hidden">
                    <label for="donor_name">Name <span class="required">*</span></label>
                    <input type="text" id="donor_name" name="name" placeholder="Your name">

                    <label for="blood_group">Blood Group <span class="required">*</span></label>
                    <select id="blood_group" name="blood_group">
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

                    <label for="age">Age <span class="required">*</span></label>
                    <input type="number" id="age" name="age" min="18" max="100" placeholder="Age">

                    <label for="location">Location <span class="required">*</span></label>
                    <input type="text" id="location" name="location" placeholder="City/Region">

                    <label for="phone">Phone <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" placeholder="Phone number">
                </div>

                <div id="hospitalFields" class="role-fields hidden">
                    <label for="hospital_name">Hospital Name <span class="required">*</span></label>
                    <input type="text" id="hospital_name" name="hospital_name" placeholder="Name of hospital">

                    <label for="hospital_location">Location <span class="required">*</span></label>
                    <input type="text" id="hospital_location" name="hospital_location" placeholder="City/Region">

                    <label for="hospital_phone">Phone <span class="required">*</span></label>
                    <input type="tel" id="hospital_phone" name="hospital_phone" placeholder="Phone number">
                </div>

                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" required placeholder="you@example.com">

                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" required placeholder="Minimum 6 characters">

                <button type="submit" class="btn">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </section>
    </main>
</body>
</html>