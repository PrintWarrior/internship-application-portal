<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT i.title, i.company_id
    FROM internships i
    JOIN companies c ON i.company_id = c.company_id
    WHERE i.internship_id = ?
");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    logAction("Reject Internship", "Superadmin attempted to reject non-existent internship ID $id");
    header('Location: manage_internships.php');
    exit;
}

$stmt = $pdo->prepare("
    UPDATE internships
    SET status = 'rejected'
    WHERE internship_id = ?
");
$stmt->execute([$id]);

$message = "Your internship '{$job['title']}' was rejected by the superadmin.";
notifyCompanyStaff($job['company_id'], $message, "internships.php", "View Details");

logAction(
    "Reject Internship",
    "Superadmin rejected internship '{$job['title']}' (ID $id)"
);

header('Location: manage_internships.php');
exit;
