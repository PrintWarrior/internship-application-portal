<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

startSecureSession();
sendSecurityHeaders();

date_default_timezone_set('Asia/Manila');

$token = $_GET['token'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token=? AND token_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(400);
    $user = null;
}

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken();

    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } elseif (strlen($new) < 6) {
        $message = "Password must be at least 6 characters.";
        $message_type = "error";
    } else {

        $hashed = password_hash($new, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE users 
            SET password_hash=?, reset_token=NULL, token_expiry=NULL 
            WHERE user_id=?
        ");
        $stmt->execute([$hashed, $user['user_id']]);

        header("Location: index.php?reset=success");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Internship Portal</title>
    <link rel="stylesheet" href="assets/css/reset_password.css">
    <link rel="icon" href="assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <div class="container">
        <!-- Logo at top -->
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="Logo" class="logo">
        </div>
        
        <h2>Reset Password</h2>
        
        <?php if (!$user): ?>
            <div class="message-container">
                <div class="message error">
                    This password reset link is invalid or has expired.
                </div>
            </div>
        <?php else: ?>
        
        <?php if ($message): ?>
            <div class="message-container">
                <div class="message <?= $message_type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <?= csrf_input() ?>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required>
            </div>
            
            <!--<div class="password-requirements">
                <p>Password Requirements:</p>
                <ul>
                    <li>Minimum 6 characters</li>
                    <li>Use a mix of letters and numbers</li>
                </ul>
            </div>-->
            
            <button type="submit">Reset Password</button>
        </form>
        <?php endif; ?>
        
        <!--<div class="back-link">
            <a href="index.php">Back to Login</a>
        </div>-->
    </div>
    
    <script>
        // Optional: Add password strength indicator
        const newPass = document.getElementById('new_password');
        const confirmPass = document.getElementById('confirm_password');
        
        function checkPasswordMatch() {
            if (newPass.value !== confirmPass.value && confirmPass.value !== '') {
                confirmPass.style.borderColor = '#000000';
                confirmPass.style.backgroundColor = '#f5f5f5';
            } else {
                confirmPass.style.borderColor = '#000000';
                confirmPass.style.backgroundColor = '#ffffff';
            }
        }
        
        confirmPass.addEventListener('keyup', checkPasswordMatch);
    </script>
    <script src="js/responsive-nav.js"></script>
</body>
</html>
