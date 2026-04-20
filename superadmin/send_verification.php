<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: notifications.php');
    exit;
}

requireValidCsrfToken(['redirect' => 'notifications.php']);

$user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

// Check user exists and not verified
$stmt = $pdo->prepare("SELECT email, verified FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user && !$user['verified']) {
    // Generate token
    $token = generateToken();
    $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Store token
    $pdo->prepare("INSERT INTO verification_tokens (user_id, token, expiry, type) VALUES (?, ?, ?, 'email_verify')")
        ->execute([$user_id, $token, $expiry]);

    // Send email
    $link = appUrl('/includes/verify_email.php?token=' . urlencode($token));

$body = "
<div style='background:#f2f4f6;padding:40px;font-family:Arial,sans-serif'>
    <div style='max-width:600px;margin:auto;background:white;padding:30px;text-align:center'>
        
        <h2 style='color:#2c3e50'>Internship Application Portal</h2>

        <p style='font-size:16px;color:#555'>
        Your account has been approved by the administrator.
        Please verify your email to activate your account.
        </p>

        <a href='$link' 
        style='display:inline-block;margin-top:20px;padding:12px 25px;
        background:black;color:white;text-decoration:none;
        font-size:16px'>
        Verify Email
        </a>

        <p style='margin-top:25px;font-size:14px;color:#888'>
        This link will expire in 24 hours.
        </p>

        <hr style='margin:30px 0'>

        <p style='font-size:12px;color:#aaa'>
        Internship Application Portal
        </p>

    </div>
</div>
";
    if (sendEmail($user['email'], 'Verify Your Email', $body)) {
        $_SESSION['feedback_message'] = 'Verification email successfully sent!';
        $_SESSION['feedback_type'] = 'success';
    } else {
        $lastEmailError = (string) getLastEmailError();
        if (stripos($lastEmailError, 'SMTP configuration is incomplete') !== false) {
            $_SESSION['feedback_message'] = 'Email service is not configured. Add your SMTP settings to the project .env file, then try again.';
        } else {
            $_SESSION['feedback_message'] = 'Failed to send verification email. Check the SMTP settings or app password and try again.';
        }
        $_SESSION['feedback_type'] = 'error';
    }
} else {
    $_SESSION['feedback_message'] = 'User not found or already verified.';
    $_SESSION['feedback_type'] = 'error';
}

header('Location: notifications.php');
exit;
?>
