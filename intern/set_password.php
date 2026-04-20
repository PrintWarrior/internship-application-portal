<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'intern') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

requireValidCsrfToken(['json' => true]);

$password = $_POST['password'] ?? '';

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'error' => 'Password too short']);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?")->execute([$hash, $_SESSION['user_id']]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
