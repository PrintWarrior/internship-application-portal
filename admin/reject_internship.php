<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? 0;

// Get internship info first
$stmt = $pdo->prepare("
SELECT i.title, i.company_id
FROM internships i
JOIN companies c ON i.company_id = c.company_id
WHERE i.internship_id=?
");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    logAction("Reject Internship", "Attempted to reject non-existent internship ID $id");
    header("Location: manage_internships.php");
    exit;
}

// Reject internship
$stmt = $pdo->prepare("
    UPDATE internships
    SET status='rejected'
    WHERE internship_id=?
");
$stmt->execute([$id]);

$message = "Your internship '{$job['title']}' was rejected by the admin.";

notifyCompanyStaff($job['company_id'], $message, "internships.php", "View Details");

// Log action
logAction(
    "Reject Internship",
    "Rejected internship '{$job['title']}' (ID $id)"
);

header("Location: manage_internships.php");
exit;
