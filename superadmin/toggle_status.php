<?php include '../includes/functions.php'; ?>

<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
    header('Location: ../index.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: manage_users.php");
    exit;
}

$stmt = $pdo->prepare("SELECT user_type, status FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['user_type'] === 'superadmin') {
    header("Location: manage_users.php");
    exit;
}

$current = $user['status'];

// cycle: active → suspended → banned → active
$newStatus = 'active';

if ($current === 'active') $newStatus = 'suspended';
elseif ($current === 'suspended') $newStatus = 'banned';
elseif ($current === 'banned') $newStatus = 'active';

$stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
$stmt->execute([$newStatus, $id]);

header("Location: manage_users.php");
exit;
