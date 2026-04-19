<?php
session_start();
require_once '../includes/functions.php';

requireAdminAreaAccess();

$id = $_GET['id'];

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
