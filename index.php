<?php
require_once 'includes/functions.php';

startSecureSession();
sendSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        /* Add notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            font-weight: bold;
            z-index: 1000;
            animation: slideIn 0.5s ease-out;
            border: 1px solid black;
            border-radius: 0;
            box-shadow: none;
        }

        .notification.success {
            background-color: white;
            color: black;
        }

        .notification.error {
            background-color: black;
            color: white;
        }

        #notification-ok {
            background-color: transparent;
            color: inherit;
            border: 1px solid currentColor;
            border-radius: 0;
            padding: 8px 16px;
            font: inherit;
            cursor: pointer;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Optional: Hide the old error div */
        .old-error {
            display: none;
        }
    </style>
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>

<body>
    <!-- Add notification container -->


    <div class="container">
        <!-- Logo image container -->
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="Logo" class="logo">
        </div>

        <h2>Internship Application Portal</h2>

        <form action="includes/login.php" method="POST">
            <?= csrf_input() ?>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required placeholder="Enter email...">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required placeholder="Enter password...">
            </div>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="html/register.php">Register</a></p>
        <p>Forgot your password? <a href="forgot_password.php">Reset Password</a></p>
    </div>

    <div id="notification" class="notification" style="display:none;">
        <span id="notification-message"></span>
        <br><br>
        <button id="notification-ok">OK</button>
    </div>

    <script>
        // Function to show notification
        function showNotification(message, type, redirect = null) {

            const notification = document.getElementById('notification');
            const messageBox = document.getElementById('notification-message');
            const okBtn = document.getElementById('notification-ok');

            messageBox.textContent = message;
            notification.className = 'notification ' + type;
            notification.style.display = 'block';

            okBtn.onclick = function () {
                notification.style.display = 'none';

                if (redirect) {
                    window.location.href = redirect;
                }
            };
        }

        // Check for session messages on page load
        window.addEventListener('load', function () {
            <?php if (isset($_SESSION['error'])): ?>
                showNotification('<?= addslashes($_SESSION['error']); ?>', 'error');
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                showNotification(
                    "<?= addslashes($_SESSION['success']); ?>",
                    "success",
                    "<?= $_SESSION['redirect_to'] ?? '' ?>"
                );
                <?php
                unset($_SESSION['success']);
                unset($_SESSION['redirect_to']);
            endif;
            ?>

            // Also check URL parameters (if you want to support that too)
            const urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('error')) {
                showNotification(urlParams.get('message') || 'An error occurred.', 'error');
                // Clear URL parameters
                const url = new URL(window.location.href);
                url.searchParams.delete('error');
                url.searchParams.delete('success');
                url.searchParams.delete('message');
                window.history.replaceState({}, document.title, url.pathname);
            } else if (urlParams.has('success')) {
                showNotification(urlParams.get('message') || 'Success!', 'success');
                // Clear URL parameters
                const url = new URL(window.location.href);
                url.searchParams.delete('error');
                url.searchParams.delete('success');
                url.searchParams.delete('message');
                window.history.replaceState({}, document.title, url.pathname);
            }
        });
    </script>
    <script src="js/responsive-nav.js"></script>
</body>

</html>
