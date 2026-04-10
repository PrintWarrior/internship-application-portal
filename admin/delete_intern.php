<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php'; // ADD THIS

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? 0;

// Get user_id first
$stmt = $pdo->prepare("SELECT user_id FROM interns WHERE intern_id=?");
$stmt->execute([$id]);
$intern = $stmt->fetch();

if ($intern) {

    // delete intern profile
    $stmt = $pdo->prepare("DELETE FROM interns WHERE intern_id=?");
    $stmt->execute([$id]);

    // delete user account
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->execute([$intern['user_id']]);

    // RECORD LOG
    logAction("Delete Intern", "Deleted intern ID $id");
}

header("Location: manage_interns.php");
exit;