<?php
session_start();
require_once '../includes/functions.php';

requireAdminAreaAccess();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_users.php");
    exit;
}

requireValidCsrfToken(['redirect' => 'manage_users.php']);

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

// prevent deleting superadmin
$stmt = $pdo->prepare("SELECT user_type FROM users WHERE user_id=?");
$stmt->execute([$id]);
$type = $stmt->fetchColumn();

if ($type === 'superadmin') {
    die("Cannot delete protected admin account.");
}

$stmt = $pdo->prepare("DELETE FROM users WHERE user_id=?");
$stmt->execute([$id]);

header("Location: manage_users.php");
exit;
