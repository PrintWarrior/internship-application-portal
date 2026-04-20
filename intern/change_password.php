<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'intern') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken(['redirect' => 'profile.php']);
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    
    // Remove confirm password check since it's handled in modal
    // $confirm = $_POST['confirm_password'];

    // Check if new password is empty
    if (empty($new)) {
        header("Location: profile.php?password_error=1");
        exit;
    }

    // Password strength validation
    if (strlen($new) < 6) {
        header("Location: profile.php?password_error=1&msg=password_too_short");
        exit;
    }

    // Get current password hash
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (password_verify($current, $user['password_hash'])) {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE user_id=?");
        $stmt->execute([$newHash, $user_id]);
        
        header("Location: profile.php?password_success=1");
        exit;
    } else {
        header("Location: profile.php?password_error=1");
        exit;
    }
}
?>
