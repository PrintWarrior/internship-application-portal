<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();

// Clear logs
$stmt = $pdo->prepare("DELETE FROM system_logs");
$stmt->execute();

// Optional: log the action
$stmt = $pdo->prepare("
INSERT INTO system_logs (user_id, action)
VALUES (?, 'Cleared system logs')
");
$stmt->execute([$_SESSION['user_id']]);

header("Location: system_logs.php");
exit;
