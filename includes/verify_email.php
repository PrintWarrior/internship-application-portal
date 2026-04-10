<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$token = $_GET['token'] ?? '';

// Find token
$stmt = $pdo->prepare("SELECT * FROM verification_tokens WHERE token = ? AND type = 'email_verify' AND expiry > NOW() AND used = 0");
$stmt->execute([$token]);
$tokenData = $stmt->fetch();

if ($tokenData) {
    // Mark token as used
    $pdo->prepare("UPDATE verification_tokens SET used = 1 WHERE token_id = ?")->execute([$tokenData['token_id']]);

    // Mark user as verified
    $pdo->prepare("UPDATE users SET verified = 1 WHERE user_id = ?")->execute([$tokenData['user_id']]);

    // Get user email for notification
    $stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->execute([$tokenData['user_id']]);
    $user = $stmt->fetch();

    // Notify admin
    $message = "User {$user['email']} has verified their email.";
    $action_url = "send_otp.php?user_id={$tokenData['user_id']}";  // ← REMOVE 'admin/'
    $action_label = "Send Temopory Password";
    createNotification(1, $message, $action_url, $action_label, $tokenData['user_id']);

    echo "
<!DOCTYPE html>
<html>
<head>
<title>Email Verified</title>
<style>
body{
    font-family: Arial, sans-serif;
    background:#f2f4f6;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    margin:0;
}

.card{
    background:white;
    padding:40px;
    text-align:center;
    max-width:400px;
}

.card h2{
    color:#2ecc71;
}

.card p{
    color:black;
}
</style>
</head>

<body>

<div class='card'>
    <h2>Email Verified ✔</h2>
    <p>Your email has been successfully verified.</p>
    <p>You can now wait for the administrator to send your Temporary Password via email.</p>
</div>

</body>
</html>
";
} else {
    echo "Invalid or expired verification link.";
}
?>