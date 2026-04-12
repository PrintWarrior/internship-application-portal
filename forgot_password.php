<?php
require_once 'includes/db.php';
require_once 'includes/phpmailer/vendor/autoload.php';
date_default_timezone_set('Asia/Manila');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'];

    // Check if email exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {

        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Save token
        $stmt = $pdo->prepare("UPDATE users SET reset_token=?, token_expiry=? WHERE email=?");
        $stmt->execute([$token, $expiry, $email]);

        // Reset link
        $link = "http://localhost/intern%20app%20portal%20v3/reset_password.php?token=" . $token;

        $mail = new PHPMailer(true);

        try {
            // SMTP config
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'internshipapplicationportal@gmail.com';
            $mail->Password = 'fqwzszpjofuhlqzf';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('internshipapplicationportal@gmail.com', 'Internship Portal');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 2px solid #000000; padding: 20px;'>
                    <div style='text-align: center; border-bottom: 2px solid #000000; padding-bottom: 15px; margin-bottom: 20px;'>
                        <h2 style='margin: 0; text-transform: uppercase;'>Password Reset</h2>
                    </div>
                    <p>Hello,</p>
                    <p>You requested a password reset for your Internship Portal account.</p>
                    <p>Click the button below to reset your password:</p>
                    <p style='text-align: center; margin: 25px 0;'>
                        <a href='$link' 
                           style='display: inline-block; padding: 12px 25px; background-color: #000000; color: #ffffff; text-decoration: none; font-weight: bold; text-transform: uppercase; border: 2px solid #000000;'>
                           Reset Password
                        </a>
                    </p>
                    <p>If you did not request this, please ignore this email.</p>
                    <hr style='border: none; border-top: 2px solid #000000; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #666666;'>This link will expire in 1 hour.</p>
                </div>
            ";

            $mail->AltBody = "Password Reset Request\n\nClick this link to reset your password: $link\n\nIf you did not request this, ignore this email.";

            $mail->send();

            $message = "Reset link sent to your email. Please check your inbox.";
            $message_type = "success";

        } catch (Exception $e) {
            $message = "Mailer Error: " . $mail->ErrorInfo;
            $message_type = "error";
        }

    } else {
        $message = "Email not found. Please check your email address.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Internship Portal</title>
    <link rel="stylesheet" href="assets/css/forgot_password.css">
    <link rel="icon" href="assets/img/icon.png" type="image/x-icon">
</head>
<body>
    <div class="container">
        <!-- Logo at top -->
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="Logo" class="logo">
        </div>
        
        <h2>Forgot Password</h2>
        
        <?php if ($message): ?>
            <div class="message-container">
                <div class="message <?= $message_type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" placeholder="Enter your registered email" required>
            </div>
            <button type="submit">Send Reset Link</button>
        </form>
        
        <div class="back-link">
            <a href="index.php">Back to Login</a>
        </div>
    </div>
</body>
</html>