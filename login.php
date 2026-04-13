<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Buea BloodLink</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="css/components.css?v=2">
    <script defer src="js/validation.js"></script>
</head>
<body>
    <header>
        <nav class="top-nav">
            <div class="logo">Buea BloodLink</div>
            <ul class="nav-items">
                <li><a href="index.html">Home</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </nav>
    </header>

    <main class="page-container">
        <section class="card form-card">
            <h2>Login</h2>
            <form id="loginForm" action="php/login_process.php" method="POST" onsubmit="return validateLogin();">
                <label for="login_email">Email <span class="required">*</span></label>
                <input type="email" id="login_email" name="email" required placeholder="you@example.com">

                <label for="login_password">Password <span class="required">*</span></label>
                <input type="password" id="login_password" name="password" required placeholder="Your password">

                <button type="submit" class="btn">Login</button>
            </form>
            <p><a href="#" onclick="showForgotPassword(); return false;">Forgot Password?</a></p>
            <p>Don't have account? <a href="register.php">Register now</a>.</p>
        </section>

        <section id="forgot-password" class="card form-card" style="display: none;">
            <h2>Forgot Password</h2>
            <form id="forgotForm" onsubmit="return handleForgot(event);" style="max-width: 400px; margin: auto;">
                <label for="forgot_email">Email</label>
                <input type="email" id="forgot_email" required placeholder="Email" style="width: 100%; margin-bottom: 8px;" />
                <label for="forgot_method">Receive code via:</label>
                <select id="forgot_method" style="width: 100%; margin-bottom: 8px;">
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                </select>
                <button type="submit" class="btn">Send Code</button>
                <p id="forgotMessage" style="color: green; margin-top: 8px;"></p>
            </form>
            <form id="verifyForm" onsubmit="return handleVerify(event);" style="max-width: 400px; margin: auto; display: none;">
                <label for="verify_code">Enter Code</label>
                <input type="text" id="verify_code" required placeholder="6-digit code" style="width: 100%; margin-bottom: 8px;" />
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" required placeholder="New password" style="width: 100%; margin-bottom: 8px;" />
                <button type="submit" class="btn">Reset Password</button>
                <p id="verifyMessage" style="color: red; margin-top: 8px;"></p>
            </form>
        </section>
    </main>

    <script>
        function showForgotPassword() {
            document.querySelector('.form-card').style.display = 'none';
            document.getElementById('forgot-password').style.display = 'block';
        }

        async function handleForgot(event) {
            event.preventDefault();
            const email = document.getElementById('forgot_email').value.trim();
            const method = document.getElementById('forgot_method').value;
            const message = document.getElementById('forgotMessage');

            try {
                const response = await fetch('php/api.php?action=forgot-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, method })
                });
                const data = await response.json();
                if (!response.ok) {
                    message.style.color = 'red';
                    message.innerText = data.error || 'Failed to send code';
                    return false;
                }
                message.style.color = 'green';
                message.innerText = data.message;
                document.getElementById('forgotForm').style.display = 'none';
                document.getElementById('verifyForm').style.display = 'block';
            } catch (error) {
                console.error(error);
                message.style.color = 'red';
                message.innerText = 'Error sending code';
            }
            return false;
        }

        async function handleVerify(event) {
            event.preventDefault();
            const email = document.getElementById('forgot_email').value.trim();
            const code = document.getElementById('verify_code').value.trim();
            const newPassword = document.getElementById('new_password').value.trim();
            const message = document.getElementById('verifyMessage');

            try {
                const response = await fetch('php/api.php?action=reset-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, code, password: newPassword })
                });
                const data = await response.json();
                if (!response.ok) {
                    message.innerText = data.error || 'Failed to reset password';
                    return false;
                }
                message.style.color = 'green';
                message.innerText = data.message;
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            } catch (error) {
                console.error(error);
                message.innerText = 'Error resetting password';
            }
            return false;
        }
    </script>
</body>
</html>