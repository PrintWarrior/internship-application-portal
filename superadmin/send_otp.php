<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

date_default_timezone_set('Asia/Manila');

requireAdminAreaAccess();

$user_id = $_GET['user_id'] ?? 0;

// Check user exists and verified
$stmt = $pdo->prepare("SELECT email, verified FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user && $user['verified']) {
    // Generate OTP
    $otp = generateOTP();

    // Set expiry to 1 hour from now
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Store OTP
    $pdo->prepare("
        INSERT INTO verification_tokens (user_id, token, expiry, type) 
        VALUES (?, ?, ?, 'otp')
    ")->execute([$user_id, $otp, $expiry]);

    // Send email
$body = "
<div style='background:#f2f4f6;padding:40px;font-family:Arial,sans-serif'>
    <div style='max-width:600px;margin:auto;background:white;padding:30px;text-align:center'>

        <h2 style='color:#2c3e50'>Login Temporary Password</h2>

        <p style='font-size:16px;color:#555'>
        Use the following Temporary Password to log in to your account.
        </p>

        <div style='font-size:32px;letter-spacing:8px;
        background:#f4f4f4;padding:15px;margin:20px 0;
        font-weight:bold'>
        $otp
        </div>

        <!-- LOGIN BUTTON -->
        <a href='http://localhost/intern%20app%20portal%20v3/index.php'
           style='display:inline-block;
                  margin-top:20px;
                  padding:12px 25px;
                  background-color:#000;
                  color:white;
                  text-decoration:none;
                  font-size:16px;
                  font-weight:bold;'>
            Login Now
        </a>

        <p style='color:#888;font-size:14px'>
        This Temporary Password will expire in 1 hour.
        </p>

        <hr style='margin:30px 0'>

        <p style='font-size:12px;color:#aaa'>
        Internship Application Portal
        </p>

    </div>
</div>
";
    if (sendEmail($user['email'], 'Your Temporary Password', $body)) {
        $_SESSION['feedback_message'] = 'Temporary Password successfully sent!';
        $_SESSION['feedback_type'] = 'success';
    } else {
        $_SESSION['feedback_message'] = 'Failed to send Temporary Password. Please try again.';
        $_SESSION['feedback_type'] = 'error';
    }
} else {
    $_SESSION['feedback_message'] = 'User not found or not verified.';
    $_SESSION['feedback_type'] = 'error';
}

header('Location: notifications.php');
exit;
?>
